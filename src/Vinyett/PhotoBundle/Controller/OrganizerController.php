<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Vinyett\PhotoBundle\Entity\Photo;
use Vinyett\PhotoBundle\Entity\Collection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;


/**
 *
 */
class OrganizerController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        if ($this->container->has('profiler'))
        {
            $this->container->get('profiler')->disable();
        }
        
        return $this->render('PhotoBundle:Organizer:index.html.twig');
    }
        

    /**
     * Spits out an ajax list of a users photos.
     *
     * @Secure(roles="ROLE_USER")
     */ 
    public function loadPhotosAction(Request $request)
    {
    
        $user = $this->get("security.context")->getToken()->getUser();
        
        $perpage = $request->request->get('per_page', 10); 
        $page = $request->request->get('page', 1) - 1; //Subtract to get a value based off 0 instead of 1.
        
        $photos = $this->getDoctrine()->getRepository("PhotoBundle:Photo")->findPhotosBy($user, array("on" => $perpage*$page, "with" => $perpage));

        return new Response(json_encode(array("photos" => array("page" => $page+1, "perpage" => $perpage, "total" => count($photos), "photo" => $photos))));
    }
    
    
    /**
     * Saves a new collection and its photos to the database.
     *
     * @Secure(roles="ROLE_USER")
     */ 
    public function syncCollectionAction(Request $request)
    {
        
        $user = $this->get("security.context")->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $set_first_to_cover = false;
        
        $collection_id = $request->request->get("collection_id");
        
        $title = $request->request->get("title");
        $description = $request->request->get("description");
        $cover_id = $request->request->get("cover_id");
        $photos = explode(",", $request->request->get("photos"));
        
        if($cover_id == null)
        { 
            //Do some voodoo... We just use the first image in the set...
            $cover_id = $photos[0];
        }
        
        if($collection_id == "new") 
        {
            $collection = new Collection();
            $collection->setOwner($user); 
            $collection->setTotalPhotos(count($photos));
            $collection->setTitle(strip_tags($title));
            $collection->setDescription(strip_tags($description));
            $collection->setCoverPhoto($em->getReference("PhotoBundle:Photo", $cover_id));
            
            $em->persist($collection); //We go ahead and persist it to the manager
            
        } else { 
            
            $collection = $em->getRepository("PhotoBundle:Collection")->findOneBy(array("id" => $collection_id, "owner" => $user->getId()));
            
            if(!$collection) 
            { 
                throw $this->createNotFoundException('The Collection does not exist');
            }
            
            $collection->setTotalPhotos(count($photos));
            $collection->setTitle(strip_tags($title));
            $collection->setDescription(strip_tags($description));
            $collection->setCoverPhoto($em->getReference("PhotoBundle:Photo", $cover_id));
            
            $collection->getPhotos()->clear(); //We always reset the collection because all elements are reordered.
        }
        
        //Now we add the photos (via reference)
        foreach($photos as $photo) 
        {
            $collection->addPhoto($em->getReference("PhotoBundle:Photo", $photo));
        }
        
        $em->flush();
        
        return new Response(json_encode(
                                        array("collection_created" => true, 
                                              "collection" => 
                                                    array("id" => $collection->getId(), 
                                                        "photo_count" => $collection->getTotalPhotos(),
                                                        "cover_id" => $collection->getCoverPhoto()))));
    }











    
    
}



