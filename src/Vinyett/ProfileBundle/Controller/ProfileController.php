<?php

namespace Vinyett\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\HttpException;
    
use JMS\SecurityExtraBundle\Annotation\Secure;
    
use FOS\RestBundle\View\View;

use Vinyett\PhotoBundle\Entity\Photo;
use Vinyett\ConnectBundle\Entity\Follow;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

use Doctrine\ORM\Proxy\Proxy;
use Doctrine\Common\Collections\ArrayCollection;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Vinyett\CoreServicesBundle\View\PagerView;


class ProfileController extends Controller
{
    /**
     * Shows the photo page for a user.
     * 
     * @Secure(roles="ROLE_USER")
     *
     * @access public
     * @param string $username
     * @param integer $page
     * @param Request $request
     */
    public function photosAction($username, $page, Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('PhotoBundle:Photo');
        $securityContext = $this->get('security.context');
        $aclProvider = $this->get('security.acl.provider');
        $affinity_manager = $this->get("affinity.manager");
        $user = $securityContext->getToken()->getUser();
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        
        try {
            $profile = $this->getDoctrine()->getRepository('UserBundle:User')->findProfileByUsername($username);
        } catch(\Exception $e) { 
            throw $this->createNotFoundException('The user does not exist');
        }

        $followed = null; $following = null;

        if($profile != $user)
        {
            $following = $this->getDoctrine()->getRepository('ConnectBundle:Follow')->findOneBy(array("actor" => $user->getId(), "following" => $profile->getId()));
            $followed = $this->getDoctrine()->getRepository('ConnectBundle:Follow')->findOneBy(array("following" => $user->getId(), "actor" => $profile->getId()));
            
            $affinity_manager->adjustAffinity($following, 1); //We want to adjust OUR end of the relationship.
        }
        
        $collections = $em->getRepository("PhotoBundle:Collection")->createProfileQuery($profile)->getQuery()->getResult();
        
        $profile_following = $em->getRepository("ConnectBundle:Follow")->findFollowing($profile);
        $profile_followed = $em->getRepository("ConnectBundle:Follow")->findFollowedByCount($profile);
        
        return $this->render('ProfileBundle:Profile:index.html.twig', array("profile" => $profile, "collections" => $collections, "follow" => $following, "following" => $profile_following, "followers" => $profile_followed));
    }
    
    
    public function fetchFollowingPageletAction($username)
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $profile = $em->getRepository("UserBundle:User")->findOneByUsername($username);
        
        $following = $em->getRepository("ConnectBundle:Follow")->findFollowing($profile);

        return $this->render('ProfileBundle:Profile:followWindow.pagelet.html.twig', array("profile" => $profile, "follows" => $following));
    }
    
        
    public function fetchFollowedPageletAction($username)
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $profile = $em->getRepository("UserBundle:User")->findOneByUsername($username);
        
        $following = $em->getRepository("ConnectBundle:Follow")->findFollowed($profile);

        return $this->render('ProfileBundle:Profile:followedWindow.pagelet.html.twig', array("profile" => $profile, "follows" => $following));
    }
    
    /**
     * Resets a user's usage level to 0
     * 
     * @Secure(roles="ROLE_ADMIN")
     *
     * @access public
     * @param string $username
     */
    public function adminResetUserUsageAction($username) 
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository("UserBundle:User")->findOneByUsername($username);
        
        $user->setUploadedAmount(0);
        $user->setLastUploadReset(new \DateTime());
        
        $em->flush();
        
        $response = new Response(json_encode(array('success' => true)), 200);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * Updates the profile banner position (outside of the rest API).
     * 
     * @access public
     * @param mixed $offset
     * @return View object
     */
    public function bannerPositionAction() 
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest(); 
        
        //We should probably plug into the API for this, but right now
        //there isn't a reason to build the /rest/user endpoint (it'll come, friends).
        
        $user = $this->get("security.context")->getToken()->getUser();
        $offset = $request->request->get("offset");
        if(!is_numeric($offset))
        { 
            return HttpException(400, "Offset must be a number!");
        }
        
        $user->setProfilePhotoOffset($offset);
        $em->persist($user); 
        $em->flush();
        
        $view = View::create();
        $view->setData($offset);
        $view->setStatusCode(200);
        $view->setFormat("json");
        
        return $this->get('fos_rest.view_handler')->handle($view);
        
    }   
    
    /**
     * Sets the blurb for the active user, eventually to be migrated to the API
     * 
     * @access public
     * @param mixed $offset
     * @return View object
     */
    public function setBlurbAction()
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest(); 
        $user = $this->get("security.context")->getToken()->getUser();
        $blurb = $request->request->get("blurb");
        
        $user->setBlurb($blurb);
        $em->flush();
        
        /* Off load the response into a view object and have FOS render it */
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData($user->getBlurb());
        $view->setFormat("json");
        
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    
    /**
     * Sets the value of a profile banner to a photo (that must exist!)
     * 
     * @access public
     * @param mixed $offset
     * @return View object
     */
    public function setBannerAction() 
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest(); 
        
        //We should probably plug into the API for this, but right now
        //there isn't a reason to build the /rest/user endpoint (it'll come, friends).
        
        $user = $this->get("security.context")->getToken()->getUser();
        $photo = $request->request->get("photo");
        
        if($photo == "none")
        { 
            $user->setProfilePhoto(null);
        } else { 
            $user->setProfilePhoto($em->getReference("PhotoBundle:Photo", $photo));
        }
        
        $user->setProfilePhotoOffset(0);
        $em->persist($user); 
        $em->flush();
        
        $view = View::create();
        $view->setStatusCode(200);
        $view->setFormat("json");
        
        return $this->get('fos_rest.view_handler')->handle($view);
        
    }    
}
