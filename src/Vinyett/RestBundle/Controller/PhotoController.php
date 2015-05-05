<?php

namespace Vinyett\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception\HttpException;

use JMS\SecurityExtraBundle\Annotation\Secure;
    
use Doctrine\ORM\Query\Expr,
    Doctrine\ORM\Query\ResultSetMapping,
    Doctrine\ORM\Query,
    Doctrine\Common\Collections\ArrayCollection;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Routing\ClassResourceInterface;
    
use FOS\RestBundle\Request\ParamFetcher,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Controller\Annotations\RequestParam;
    
use Vinyett\PhotoBundle\Form\PhotoType,
    Vinyett\PhotoBundle\Form\UploadType;
use Vinyett\PhotoBundle\Entity\Photo,
    Vinyett\PhotoBundle\Entity\Favorite,
    Vinyett\StreamBundle\Entity\Activity,
    Vinyett\ConnectBundle\Entity\Follow;

use Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\Security\Acl\Domain\ObjectIdentity,
    Symfony\Component\Security\Acl\Domain\UserSecurityIdentity,
    Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity,
    Symfony\Component\Security\Acl\Permission\MaskBuilder;
   
class PhotoController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [GET] /rest/photos/{photo_id}/options
     *
     * Returns the list of options and URLs to access said options.
     * 
     * @access public
     * @return void
     */
    public function optionsAction($photo_id)
    {
        $view = $this->view();
        $view->setStatusCode(501); //Not Implemented, yet. When the API becomes open...
        
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    

    /**
     * [GET] /rest/photos
     *
     * Fetches a collection of photos from the API.
     *
     * NOTE: The default is to fetch a users own photos.
     * 
     * @Secure("ROLE_USER")
     * @QueryParam(name="offset", requirements="\d+", default="0", description="Offset to fetch results from.")
     * @QueryParam(name="limit", requirements="\d+", default="100", description="Limit result amounts.")
     * @QueryParam(name="for", default="self", description="Whose thoughrs to grab.")
     * @QueryParam(name="type", default="stream", description="Type of streams to grab")
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $view = $this->view();
        $em = $this->getDoctrine()->getEntityManager();
        $sc = $this->get("security.context");
        $user = $this->get("security.context")->getToken()->getUser();
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $followed = null;
        $aclProvider = $this->get('security.acl.provider');
        $sc = $this->get("security.context");
        
        /* Requesting self requires a user */
        if($paramFetcher->get('for') == 'self' && !$user)
        { 
            throw new HttpException(412, "Requesting self requires an active user.");
        }
        
        $for = ($paramFetcher->get('for')=='self'?$user->getId():$paramFetcher->get('for'));
        
        if($for != $user->getId())
        {   
            $followed = $this->getDoctrine()->getRepository('ConnectBundle:Follow')->findOneBy(array("following" => $user->getId(), "actor" => $for));
            if(empty($followed)) {
                $followed = new Follow(); //Not persisted, just empty data tree
                $followed->setIsPhantom(true); //So everyone reading it knows...
            }
        }
        
        if($paramFetcher->get("type") == "stream")
        {
            $qb = $em->getRepository("PhotoBundle:Photo")->getPhotoQueryBuilderFromPerspective($user, $followed, $em->getReference("UserBundle:User", $for))
                 ->setFirstResult($offset)->setMaxResults($limit);
        } elseif($paramFetcher->get("type") == "timeline") 
        { 
            try {
                $qb = $em->getRepository("PhotoBundle:Photo")->getTimelineQueryBuilder($user);
            } catch(\Exception $e) { 
                $view->setStatusCode(204);
                return $this->get('fos_rest.view_handler')->handle($view);
            }
        }
        $data = $qb->getQuery()->getResult();
        
        /* Favorites */
        $photos = new ArrayCollection();
        foreach($data as $photo)
        { 
            $photo[0]->setIsFavorited((bool) $photo['is_favorited']);
            /* Sneaking this in here, adds "timeline" tag to timeline loaded photos... temporary */
            if($paramFetcher->get("type") == "timeline") { 
                $photo[0]->setTimeline(true);
            }
            $photos->add($photo[0]);
        }
        
        /* Permissions */
        $oids = array();
        $pids = array();
        foreach ($photos as $photo) {
            $oids[] = ObjectIdentity::fromDomainObject($photo);
            $pid[] = $photo->getId();
            
            /* Comments now... */
            foreach($photo->getComments() as $comment) 
            { 
                $oids[] = ObjectIdentity::fromDomainObject($comment);
            }
        }
        try { 
            $aclProvider->findAcls($oids);
        } catch (\Exception $e)
        { 
            //$log->Comments without privleges
        }
        $view->setData($this->setOptions($photos, $sc));
        
        
        if(count($photos) > 0)
        {
            $view->setData($this->tagPhotoData($photos));
            $view->setStatusCode(200);
        } else { 
            $view->setData(null);
            $view->setStatusCode(404);
        }
        return $this->get('fos_rest.view_handler')->handle($view);
    } 
        
    /**
     * Used to set options on a collection of photos/comments.
     */
    public function setOptions($photos, $securityContext) 
    { 
        $possible_permissions = array("EDIT", "DELETE", "OWNER"); //Possible permissions only test against these permissions...
        foreach($photos as $photo) 
        { 
            $accepted_permissions = array();
            foreach($possible_permissions as $permission)
            { 
                if($securityContext->isGranted($permission, $photo) === true)
                { 
                    $accepted_permissions[] = $permission;
                }
                $photo->setOptions($accepted_permissions);
            }
            
            foreach($photo->getComments() as $comment)
            { 
                $accepted_permissions = array();
                foreach($possible_permissions as $permission)
                { 
                    if($securityContext->isGranted($permission, $comment) === true)
                    { 
                        $accepted_permissions[] = $permission;
                    }
                    $comment->setOptions($accepted_permissions);
                }
            }
        }
        return $photos;  
    }
    

    /**
     * [POST] /rest/photo 
     *
     * Used to upload new photo from the API.
     *
     * NOTE: This is special because an image resource must be specified!
     * 
     * @access public
     * @return View
     *
     * @Secure("ROLE_USER")
     */
    public function postAction()
    {
    
        $em = $this->getDoctrine()->getEntityManager();
        $am = $this->get('spy_timeline.action_manager');
        $securityContext = $this->get('security.context');
        $serializer = $this->get('serializer');
        /* debug */ 
        $log = $this->get("logger");
        
        $user = $securityContext->getToken()->getUser();
        $photo = new Photo();
        $request = $this->getRequest();
        
        if(!$request->files->has("file"))
        { 
            throw new HttpException(400, "No file specified");
        }
        
        $form = $this->createForm(new UploadType(), $photo); 
        $request_params = array_merge($this->bindRequestToForm($request, $form), 
                                array("file" => $request->files->get("file"))); # Do we need bindRequestToForm anymore (See IgnoreNonSubmittedFieldSubscriber)
                                                                
        $form->bind($request_params);
        
        if ($form->isValid()) {

            $photo->setOwner($user);
            $photo->setPrivacylevel($user->getDefaultPrivacyLevel());

            $user->increasePhotoCount();
            $user->setUploadedAmount(ceil($user->getUploadedAmount() + (($request->files->get("file")->getClientSize()/1024)/1024)));

            $em->persist($photo);
            $em->flush();
            
            $photo->removeFile();
            
            
            /* ACL */
            $aclProvider = $this->get('security.acl.provider');
            $admin_identity = new RoleSecurityIdentity("ROLE_ADMIN");
            
            $objectIdentity = ObjectIdentity::fromDomainObject($photo);
            
            try {
                $acl = $aclProvider->findAcl($objectIdentity);
            } catch (\Symfony\Component\Security\Acl\Exception\Exception $e) {
                $acl = $aclProvider->createAcl($objectIdentity);
            }
            
            $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_OPERATOR);
            $acl->insertObjectAce($admin_identity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);
            
            $subject = $am->findOrCreateComponent($user);
            $dc = $am->findOrCreateComponent($photo);
            $action = $am->create($subject, 'uploaded', array('directComplement' => $dc));
            $am->updateAction($action);
            
        } else { 
            throw new HttpException(400, "Invalid request data.");
        }
    
    
        $view = $this->view();
        $view->setData($photo)
             ->setStatusCode(201);
        
        return $this->get('fos_rest.view_handler')->handle($view);
    }


    /**
     * [GET] /rest/photos/{photo_id}
     *
     * Fetches an individual photo's information from the API.
     * 
     * @access public
     * @param mixed $photo_id
     * @return void
     */
    public function getAction($photo_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $aclProvider = $this->get('security.acl.provider');
        $sc = $this->get("security.context");
        
        $qb = $em->createQueryBuilder();
                   
        $qb->addSelect(array("p", "f", "pc", "co"))
           ->addSelect("CASE WHEN f.photo is NULL THEN false ELSE true END as is_favorited")
           ->from("PhotoBundle:Photo", "p")
           ->where("p.id = :photo")
           ->leftJoin("p.favorites", 'f', 'WITH', 'f.owner = :viewer')
           ->leftJoin("p.comments", "pc")
           ->leftJoin("pc.owner", "co")
           ->setParameters(array("photo" => $photo_id, "viewer" => $user));
           
        $data = $qb->getQuery()->getResult();
        
        /* Loop photo into an array collection */
        $photos = new ArrayCollection();
        foreach($data as $photo)
        { 
            $photo[0]->setIsFavorited((bool) $photo['is_favorited']);
            $photos->add($photo[0]);
        }
        
        /* Permissions */
        $oids = array();
        $oids[] = ObjectIdentity::fromDomainObject($photos[0]);
        
        foreach($photos[0]->getComments() as $comment) 
        { 
            $oids[] = ObjectIdentity::fromDomainObject($comment);
        }        
        try { 
            $aclProvider->findAcls($oids);
        } catch (\Exception $e)
        { /* Silently fail */ }
        
        $photos = $this->setOptions($photos, $sc);
        
        $view = $this->view();
        $view->setData($photos[0])
             ->setStatusCode(200);
        return $this->get('fos_rest.view_handler')->handle($view);
        
    }
    

    /**
     * [PUT] /rest/photos/{photo_id}
     *
     * Saves the changes to a photo entity from the API.
     * 
     * @access public
     * @param mixed $photo_id
     * @return void
     */
    public function putAction($photo_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get('security.context');
        $am = $this->get('spy_timeline.action_manager');
        $logger = $this->get('logger');
        $user = $securityContext->getToken()->getUser();
        
        $request = $this->getRequest();
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id);
        $photo->setHighlighted((bool) $request->get("highlighted"));
        
        if ($securityContext->isGranted('EDIT', $photo) === false)
        {
            $view = $this->view();
            $view->setStatusCode(401)
                 ->setData($photo);
                 
            return $this->get('fos_rest.view_handler')->handle($view);
        }
    
        $form = $this->createForm(new PhotoType(), $photo);
        $form->bind($this->bindRequestToForm($request, $form));
    
        if ($form->isValid()) {
            $view = $this->view();
        
            $em->flush();
            
            $subject = $am->findOrCreateComponent($user);
            $dc = $am->findOrCreateComponent($photo);
            $action = $am->create($subject, 'updated', array('directComplement' => $dc));
            $action->setDuplicateKey($user->getId()."-updated-".$photo->getId());
            $am->updateAction($action);
    
            $view->setData($photo);
            $view->setStatusCode(200);
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        throw new HttpException(400, "Invalid request data: ".$form->getErrorsAsString());
    }


    /**
     * [DELETE] /rest/photos/{photo_id}
     *
     * Deletes a photo...
     *
     * Todo: Support deletion of timeline and action.
     * 
     * @access public
     * @param interger $slug
     *
     * @return ViewHandler
     */
    public function deleteAction($photo_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id);
        
        if ($securityContext->isGranted('DELETE', $photo) === false)
        {
            $view = $this->view();
            $view->setStatusCode(401)
                 ->setData($photo);
                 
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        
        $em->remove($photo);
        
        $aclProvider = $this->get('security.acl.provider');
        $acl = $aclProvider->deleteAcl(ObjectIdentity::fromDomainObject($photo));
        
        $user->decreasePhotoCount();
        
        $em->flush();
        
        $view = $this->view();
        $view->setStatusCode(204);
        $view->setData(null);
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    
    
    /**
     * Takes the values of a form and finds the matches in the 
     * request object.
     * 
     * @access protected
     * @param Request $request
     * @param Form $form
     *
     * @return array
     */
    protected function bindRequestToForm($request, $form)
    { 
        $children = $form->getChildren(); 
        $data = $request->request->all();
        $data = array_intersect_key($data, $children);
        
        return $data;
    }
    
    
    
    /**
     * Adds a favorite to the photo!.
     * 
     * @access public
     * @param mixed $photo_id
     */
    public function favoriteAction($photo_id)
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $am = $this->get('spy_timeline.action_manager');
        $user = $this->get("security.context")->getToken()->getUser();
        
        $favorite = $em->getRepository("PhotoBundle:Favorite")->findOneBy(array("photo" => $photo_id, "owner" => $user->getId()));
        if(empty($favorite))
        { 
            $favorite = new Favorite();
            $favorite->setOwner($user);
            $favorite->setPhoto($em->getReference("PhotoBundle:Photo", $photo_id));
            
            $em->persist($favorite);  
            $em->flush();
    
            $subject = $am->findOrCreateComponent($user);
            $dc = $am->findOrCreateComponent($em->getReference("PhotoBundle:Photo", $photo_id));
            $action = $am->create($subject, 'favorited', array('directComplement' => $dc));
            $action->setDuplicateKey($user->getId()."-favorited-".$photo_id);
            $am->updateAction($action);  
        } else { 
            $em->remove($favorite);
            $favorite = null;
            $em->flush();
        }
        
        $view = $this->view();
        $view->setData($favorite)
             ->setStatusCode(200);
        return $this->get('fos_rest.view_handler')->handle($view);
        
        
    }
    
    
    
    /**
     * Inserts tags into the photo data.
     * 
     * @access public
     * @param mixed $data
     * @return void
     */
    public function tagPhotoData($data) 
    {
        $photos = array();
        foreach($data as $photo)
        { 
            $photos[] = $photo->getId();
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        $qb->select(array("tag", "tagging"))
           ->from("SearchBundle:Tag", "tag")
           ->join('tag.tagging', 'tagging')
           ->where('tagging.resourceType = :resource')
           ->andWhere('tagging.resourceId in (:photos)')
           ->setParameters(array('photos' => $photos, ':resource' => "photo_tag"));
        
        $tags = $qb->getQuery()->getResult();
        
        $photo_tags = array();
        foreach($tags as $tag)
        { 
            foreach($tag->getTagging() as $tagging) 
            { 
                $photo_tags[$tagging->getResourceId()][] = $tag;
            }    
        }
        
        foreach($data as $photo) 
        { 
            if(array_key_exists($photo->getId(), $photo_tags)) { 
                $photo->setTags($photo_tags[$photo->getId()]); 
            }
        }
        
        return $data;
    
        
    }
    
    
}