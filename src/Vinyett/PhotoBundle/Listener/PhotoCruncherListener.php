<?php

namespace Vinyett\PhotoBundle\Listener;

use Vinyett\PhotoBundle\Entity\Photo;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

//
//  Listens for Doctrine 2's event handlers and connects them 
//  to the PhotoCruncher class.
//
class PhotoCruncherListener 
{ 

    protected $photoCruncher;

    
    public function __construct($photoCruncher = null)
    { 
        $this->photoCruncher = $photoCruncher;
    }


    public function getPhotoCruncher() 
    { 
        return $this->photoCruncher;
    }
    
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        //Only act on the Photo entity
        if ($entity instanceof Photo) {
        
            if (null === $entity->getFile()) {
                return;
            }
        
            $this->getPhotoCruncher()->preProcessPhoto($entity); //Sets the routes to link the entity to the file in the filesys.
        
        }
        
    }
    
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        //Only act on the Photo entity
        if ($entity instanceof Photo) {
            
            //First, we'll stop this listener if we don't have a file to work with...
            if (null === $entity->getFile()) {
                return;
            }
        
            //Then send it to postProcessPhoto (which will save all of the photos for us)
            $this->getPhotoCruncher()->postProcessPhoto($entity);
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args) 
    { 
        $this->postPersist($args); //The same thing...
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {         
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
    
        //Only act on the Photo entity
        if ($entity instanceof Photo) {

            /* remove newsfeedcaches, activities by hand because they aren't bidirectional relationships */
            /* using QueryBuilder */
            /* probably should be moved into own seperate repositories, then executed here */
            
            $qb = $em->createQueryBuilder();
            $qb->delete("StreamBundle:NewsStoryCache", "n")
               ->where("n.photo = :photo")
               ->setParameter("photo", $entity);
            $qb->getQuery()->getResult();
                        
            $qb2 = $em->createQueryBuilder();
            $qb2->delete("StreamBundle:Activity", "a")
               ->where("a.photo = :photo")
               ->setParameter("photo", $entity);
            $qb2->getQuery()->getResult();
        
        }
    }
    
    public function postRemove(LifecycleEventArgs $args)
    {         
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
    
        //Only act on the Photo entity
        if ($entity instanceof Photo) {

            /* Delete photos */
        
        }
    }
    
    
    public function getSubscribedEvents() {
        return array(
            Events::prePersist,
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove
        );
    }

}