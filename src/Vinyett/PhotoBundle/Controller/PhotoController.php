<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Vinyett\StreamBundle\Entity\Activity;

use Vinyett\PhotoBundle\Entity\Photo;
use Vinyett\PhotoBundle\Entity\Favorite;
use Vinyett\PhotoBundle\Entity\PhotoComment;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;


class PhotoController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function viewAction($photo_id)
    {
        //Temporary
        $photo_repo = $this->getDoctrine()->getRepository("PhotoBundle:Photo");
        $user = $this->get("security.context")->getToken()->getUser();
        
        $photo = $photo_repo->findOneBy(array("id" => $photo_id));
        $owner = $photo->getOwner();

        //Will be empty out in the case of no photo OR user.
        if(!$photo)
        {
            if($photo->getPrivacyLevel() == 0) { 
                throw $this->createNotFoundException('The photo does not exist');
            }
        }

        //For previous/next chained photos
        //$neighbors = $photo_repo->findNeighboringPhotos($photo->getId(), "photostream", $owner->getId());

        //We're going to build an activity stream
        $follow = $this->getDoctrine()->getRepository('ConnectBundle:Follow')->findOneBy(array("actor" => $user->getId(), "following" => $photo->getOwner()->getId()));
        //$favorites = $this->getDoctrine()->getRepository("PhotoBundle:Favorite")->findBy(array("photo" => $photo->getId()));
        $comments = $this->getDoctrine()->getRepository("PhotoBundle:PhotoComment")->findBy(array("photo" => $photo->getId()));
        //$activities = $this->buildActivityStream($photo, $favorites, $comments);

        $favorite = $this->getDoctrine()->getRepository("PhotoBundle:Favorite")->findOneBy(array("owner" => $user->getId(),  "photo" => $photo->getId()));

        //Set up the comment form (if logged in), we use an empty object
        $comment_form = $this->createFormBuilder(new PhotoComment())
                             ->add("content", "purified_textarea")
                             ->getForm();

        //For the permissions on comments, we preload them cuz hell no.
        $aclProvider = $this->get('security.acl.provider');
        $oids = array();
        foreach ($comments as $comment) {
            $oid = ObjectIdentity::fromDomainObject($comment);
            $oids[] = $oid;
        }
        try {
            $aclProvider->findAcls($oids);
        } catch(\Exception $exception) 
        {
        }

        return $this->render("PhotoBundle:Photo:photo.html.twig", array("photo" => $photo,
                                                                        "follow" => $follow,
                                                                        "favorite" => $favorite,
                                                                        "comments" => $comments,
                                                                        "comment_form" => $comment_form->createView()
                                                                    ));
    }


    /**
     * Builds the photbox column to be sent to a box.
     * 
     * @access public
     * @return void
     */
    public function photoboxColumnAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();

        $photo = $em->getRepository("PhotoBundle:Photo")->find($request->request->get("photo"));


        //We're going to build an activity stream
        $favorites = $this->getDoctrine()->getRepository("PhotoBundle:Favorite")->findBy(array("photo" => $photo->getId()));
        $comments = $this->getDoctrine()->getRepository("PhotoBundle:PhotoComment")->findBy(array("photo" => $photo->getId()));
        $activities = $this->buildActivityStream($photo, $favorites, $comments);

        return $this->render("PhotoBundle:photobox:column.html.twig", array("photo" => $photo, "activities" => $activities));
    }
    
    
    /**
     * Creates options for a photo.
     * 
     * @access public
     * @return void
     */
    public function photoboxOptionsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $photo = $em->getRepository("PhotoBundle:Photo")->find($request->query->get("photo"));
        
        return $this->render("PhotoBundle:photobox:options.html.twig", array("photo" => $photo));
    }
    



    /**
     * Returns pagelets for the photo ajax extension
     *
     * @access public
     * @return void
     */
    public function ajaxPageletAction()
    {
        $request = $this->getRequest();
        $pages = explode(",", $request->query->get("templates"));
        return $this->determinePagelets($pages);
    }


    /**
     * Determines the pagelet to load the page parameter given
     *
     * @access public
     * @param mixed $page
     * @return void
     */
    public function determinePagelets($pages)
    {   
        $templates = array();
        
        foreach($pages as $page) { 
            $templates[] = array("name" => $page, "template" => $this->renderView("PhotoBundle:Pagelet:".$page.".pagelet.html.twig"));
        }
        
        return new Response(json_encode($templates));
    }


    /**
     * Creates the activity stream
     *
     * @param Photo $photo The Photo Object
     *
     * @return ActivityStreamer
     */
    public function buildActivityStream(Photo $photo, $favorites, $comments)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ac = $this->get("activity.streamer");

        $ac->addActivities("getCreatedAt", $favorites, "favorite");
        $ac->addActivities("getCreatedAt", $comments, "comment");

        return $ac;
    }


    /**
     * @Secure(roles="ROLE_USER")
     */
    public function ajaxAction($photo_id)
    {
        $photo_repo = $this->getDoctrine()->getRepository("PhotoBundle:Photo");
        $photo = $photo_repo->findPhotoFor($this->get("security.context")->getToken()->getUser(), $photo_id);

        //Will be empty out in the case of no photo
        if(!$photo)
        {
            throw $this->createNotFoundException('The photo does not exist');
        }

        // returns JSON
        return new Response(json_encode(array("photo" => $photo)));
    }

    /**
     * Based on what's already in the database, we will either add or
     * remove someones favorite.
     *
     * @Secure(roles="ROLE_USER")
     */
    public function ajaxToggleFavoriteAction($photo_id)
    {
        $photo_repo = $this->getDoctrine()->getRepository("PhotoBundle:Photo");
        $photo = $photo_repo->findOneBy(array("id" => $photo_id));
        $em = $this->getDoctrine()->getEntityManager();
        $am = $this->get("activity.manager");
        $afm = $this->get("affinity.manager");

        $user = $this->get("security.context")->getToken()->getUser();

        //Will be empty out in the case of no photo
        if(!$photo)
        {
            throw $this->createNotFoundException('The photo does not exist');
        }

        $favorite = $this->getDoctrine()->getRepository("PhotoBundle:Favorite")->findOneBy(array("owner" => $user->getId(),  "photo" => $photo->getId()));
        if($favorite)
        {
            $em->remove($favorite);
            $photo->setTotalFavorites($photo->getTotalFavorites() - 1);

            $em->flush();

            $response = array("was_unfavorited" => true);
        } else {
            $fav = new Favorite();
            $fav->setOwner($user);
            $fav->setPhoto($photo);

            $em->persist($fav);
            $photo->setTotalFavorites($photo->getTotalFavorites() + 1);

            $em->flush();

            $am->createAndBagFromResource($fav, $photo, "PHOTO_FAVORITE");
            $am->syncBag();

            $afm->adjustAffinity($photo->getOwner()->getId(), 5);

            $response = array("was_favorited" => true);
        }

        //Do whatever..

        return new Response(json_encode($response));
    }

    /**
     * Based on what's already in the database, we will either add or
     * remove someones favorite.
     *
     * @Secure(roles="ROLE_USER")
     */
    public function ajaxAddCommentAction($photo_id, Request $request)
    {
        $photo_repo = $this->getDoctrine()->getRepository("PhotoBundle:Photo");
        $photo = $photo_repo->findOneBy(array("id" => $photo_id));

        $em = $this->getDoctrine()->getEntityManager();
        $am = $this->get("activity.manager");
        $afm = $this->get("affinity.manager");

        $aclProvider = $this->get('security.acl.provider');

        $user = $this->get("security.context")->getToken()->getUser();
        $securityContext = $this->get('security.context');

        //Will be empty in the case of no photo
        if(!$photo)
        {
            throw $this->createNotFoundException('The photo does not exist');
        }

        //Create the comment objet and form
        $comment = new PhotoComment();
        $comment_form = $this->createFormBuilder($comment)
        ->add("content", "purified_textarea")
        ->getForm();

        $comment_form->bindRequest($request);

        if (!$comment_form->isValid()) {
            return new Response(json_encode(array("error" => true, "message" => "Comment invalid", "code" => "comment_not_valid")));
        }

        $comment->setOwner($user);
        $comment->setPhoto($photo);

        $em->persist($comment);

        //Because I'm too lazy to write a listener for now
        $photo->setTotalComments($photo->getTotalComments() + 1);
        $user->setCommentCount($user->getCommentCount() + 1);

        $em->flush();


        //Then we set up permissions for the comment.
        $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($comment));
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        $securityIdentity2 = UserSecurityIdentity::fromAccount($photo->getOwner()); //The owner is also an operator of this comment...

        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OPERATOR);
        $acl->insertObjectAce($securityIdentity2, MaskBuilder::MASK_OPERATOR);
        $aclProvider->updateAcl($acl);


        $am->createAndBagFromResource($comment, $photo, "PHOTO_COMMENT");
        $am->syncBag();

        $afm->adjustAffinity($photo->getOwner()->getId(), 9);

        return new Response(json_encode(array("was_commented" => true, "comment" => array("id" => $comment->getId(), "iso_time" => $comment->getCreatedAt()->format(\DateTime::ISO8601), "content" => nl2br($comment->getContent())))));
    }


    /**
     * Deletes a comment ajax style
     *
     * At some point, I should add the ability to remove activities based on the comment.
     *
     * @Secure(roles="ROLE_USER")
     */
    public function ajaxDeleteCommentAction($comment_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $comment = $em->getRepository("PhotoBundle:PhotoComment")->findOneBy(array("id" => $comment_id));

        $photo = $em->getRepository("PhotoBundle:Photo")->findOneBy(array("id" => $comment->getPhoto()->getId()));

        $aclProvider = $this->get('security.acl.provider');

        $user = $this->get("security.context")->getToken()->getUser();
        $securityContext = $this->get('security.context');

        //Will be empty in the case of no photo
        if(!$comment)
        {
            throw $this->createNotFoundException('The photo does not exist');
        }

        if ($this->get('security.context')->isGranted('EDIT', $comment) === false) //Editable?
            {
            throw new AccessDeniedException();
        }

        //Remove
        $objectIdentity = ObjectIdentity::fromDomainObject($comment);
        $aclProvider->deleteAcl($objectIdentity);


        $photo->setTotalComments($photo->getTotalComments()-1);
        $em->remove($comment);

        $em->flush();

        return new Response(json_encode(array("was_deleted" => true)));

    }
















}