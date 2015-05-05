<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use JMS\SecurityExtraBundle\Annotation\Secure;

use DoctrineExtensions\Taggable\Entity\TagMetadata;

use Vinyett\StreamBundle\Entity\Activity;


class ModifyController extends Controller
{
    /**
     * Handles the edit-details page for photos.
     *
     * @Secure(roles="ROLE_USER")
     */  
    public function editAction($username, $photo_id)
    { 
        return $this->render("PhotoBundle:Modify:edit.html.twig");   
    }
    
    /**
     * Removes tag from a photo via AJAX.
     *
     * @Secure(roles="ROLE_USER")
     */  
    public function removeTagAction($photo_id) 
    { 
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $tag_manager = $this->get("fpn_tag.tag_manager");
        
        $photo = $em->getRepository("PhotoBundle:Photo")->findOneBy(array("id" => $photo_id, "owner" => $this->get("security.context")->getToken()->getUser()->getId())); //Watched by the em
        
        if (!$photo) { //Found?
            throw $this->createNotFoundException('No photo found for id '.$photo_id);
        }
        
        if ($this->get('security.context')->isGranted('EDIT', $photo) === false) //Editable?
        {
            throw new AccessDeniedException();
        } 
        
        //Remove quotes, tags, and explode the string into an array
        $pretag = $request->request->get("tag");
        $tag = $tag_manager->loadOrCreateTag($pretag);
        
        //First we save them into the modal... 
        $tag_manager->loadTagging($photo);
        $tag_manager->removeTag($tag, $photo);
        $tag_manager->saveTagging($photo);
        
        return new Response(json_encode(array("tag_removed" => true)));
    }   
    
    /**
     * Adds tags to the photo via AJAX.
     *
     * @Secure(roles="ROLE_USER")
     */  
    public function addTagsAction($photo_id) 
    { 
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $tag_manager = $this->get("fpn_tag.tag_manager");
        
        $tag_string = strtolower(substr($request->request->get('tags'), 0, -1)); //We do this because the Javascript adds a "," at the end.
        
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id); //Watched by the em
        
        if (!$photo) { //Found?
            throw $this->createNotFoundException('No photo found for id '.$photo_id);
        }
        
        if ($this->get('security.context')->isGranted('EDIT', $photo) === false) //Editable?
        {
            throw new AccessDeniedException();
        } 
        
        //Remove quotes, tags, and explode the string into an array
        $pretags = explode(",", stripslashes(str_replace("'", "", str_replace('"', "", strip_tags($tag_string)))));
        
        //First we save them into the modal... 
        $tag_manager->loadTagging($photo);
        
        $tags = $tag_manager->loadOrCreateTags($pretags);
        
        //We're going to store the user tagging object!
        $tag_metadata = new TagMetadata();
        $tag_metadata->add("TagUser", $this->get("security.context")->getToken()->getUser());
        
        $tag_manager->addTagsWithMetadata($tags, $tag_metadata, $photo);
        $tag_manager->saveTagging($photo);
        
        return new Response(json_encode(array("has_tagged" => true)));
    }  
    
    /**
     * Updates the Geotagging of a photo.
     *
     * @Secure(roles="ROLE_USER")
     */        
    public function geoTagAction($photo_id)
    { 
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $am = $this->get("activity.manager");
        $user = $this->get("security.context")->getToken()->getUser();
        
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id); //Watched by the em
        
        if (!$photo) { //Found?
            throw $this->createNotFoundException('No photo found for id '.$photo_id);
        }
        
        if ($this->get('security.context')->isGranted('EDIT', $photo) === false) //Editable?
        {
            throw new AccessDeniedException();
        } 

        $lat = $request->request->get("lat");
        $long = $request->request->get("lng");
        $zoom = $request->request->get("zoom");
        $name = $request->request->get("name");
        
        $photo->setGeoHasLocation(true);
        $photo->setGeoLongitude($long);
        $photo->setGeoLatitude($lat);
        $photo->setGeoZoomLevel($zoom);
        $photo->setGeoDisplayName($name);
        
        $em->flush();
        
        $a = new Activity();
        $a->setActor($user);
        $a->setPhoto($photo);
        $a->setActivityType("PHOTO_MAPPED");
        $a->setEdgeRank($am->createDefaultEdgeRank($a->getActivityType(), $photo));
        $a->setData(serialize(array("Vinyett\PhotoBundle\Entity\Photo" => $photo)));
        
        $am->addToActivityBag($a);
        $am->syncBag();
        
        return new Response(json_encode(array("was_saved" => true)));
    } 
    
    /**
     * Adds a photo as the users profile image and then redirects you to your profile...
     *
     * @Secure(roles="ROLE_USER")
     */ 
    public function addAsProfilePhotoAction($photo_id)
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $am = $this->get("activity.manager");
        $user = $this->get("security.context")->getToken()->getUser();
        
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id); //Watched by the em
        
        if (!$photo) { //Found?
            throw $this->createNotFoundException('No photo found for id '.$photo_id);
        }
        
        $user->setProfilePhoto($photo);
        $em->flush();
        
        $a = new Activity();
        $a->setActor($user);
        $a->setPhoto($photo);
        $a->setActivityType("PHOTO_PROFILED");
        $a->setEdgeRank($am->createDefaultEdgeRank($a->getActivityType(), $photo));
        $a->setData(serialize(array("Vinyett\PhotoBundle\Entity\Photo" => $photo)));
        
        $am->addToActivityBag($a);
        $am->syncBag();
        
        $url = $this->get('router')->generate('photostream', array('username' => $user->getUrlUsername()));
        return $this->redirect($url);
        
    }
    
    /**
     * Updates the title through an AJAX request.
     *
     * @Secure(roles="ROLE_USER")
     */     
    public function updateTitleAction() 
    { 
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        
        $photo_id = $request->request->get('photo_id');
        $title = $request->request->get('title');
    
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id);
        
        if (!$photo) { //Found?
            throw $this->createNotFoundException('No product found for id '.$photo_id);
        }
        
        if ($this->get('security.context')->isGranted('EDIT', $photo) === false) //Editable?
        {
            throw new AccessDeniedException();
        }
        
        //Update the Photo
        $photo->setTitle(strip_tags($title));
        $em->flush();     

        //We return a JSON string of the new 'cleaned' title to be placed into the template.
        return new Response($photo->getTitle());
    }
    
    /**
     * Updates the description through an AJAX request.
     *
     * @Secure(roles="ROLE_USER")
     */     
    public function updateDescriptionAction() 
    { 
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        
        $photo_id = $request->request->get('photo_id');
        $description = $request->request->get('description');
    
        $photo = $em->getRepository("PhotoBundle:Photo")->find($photo_id);
        
        if (!$photo) { //Found?
            throw $this->createNotFoundException('No product found for id '.$photo_id);
        }
        
        if ($this->get('security.context')->isGranted('EDIT', $photo) === false) //Editable?
        {
            throw new AccessDeniedException();
        }
        
        $description = $this->get("core_services.purifier")->getPurifier()->purify($description);
        
        //Update the Photo
        $photo->setDescription($description);
        $em->flush();     

        //We return a JSON string of the new 'cleaned' title to be placed into the template.
        return new Response($photo->getDescription());
    }
}
