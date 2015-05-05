<?php

namespace Vinyett\StreamBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Proxy\Proxy;

use Vinyett\StreamBundle\Entity\Activity;
use Vinyett\PhotoBundle\Entity\Photo;

class ActivityManager
{ 
    /*
     * @var Entity Manager $entity_manager
     */
    private $entity_manager;
    
    /*
     * @var User $user
     */
    private $user;
    
    /*
     * @var ArrayCollection $activity_bag
     */
    private $activity_bag = null;
    
    
    public function getEntityManager()
    { 
        return $this->entity_manager;
    }
    
    
    public function getUser()
    { 
        return $this->user;
    }
    
    public function getActivityBag() 
    { 
        return $this->activity_bag;
    }
    
    /**
     * Constructor
     */
    public function __construct($entity_manager, $security_context) 
    { 
        $this->entity_manager = $entity_manager;
        $this->user = $security_context->getToken()->getUser();
    }
    

    /**
     * Takes an object and turns it into an Activity object (which is stored in
     * an internal bag).
     *
	 * @param mixed $resource an already persisted (and flushed! entity)
	 * @param Photo $photo Photo object (since most activities refer to an object)
	 *
     * @return Activity
     */
    public function createAndBagFromResource($resource, Photo $photo, $activity_type) 
    { 
        $activity = new Activity();
        
        $activity->setActor($this->getUser());
        $activity->setSourceId($resource->getId());
        
        //To make sure a Doctrine Proxy isn't accidentially passed instead of the raw entity
        if ($resource instanceof Proxy) {
            $activity->setSourceType(get_parent_class($resource));
        } else {
            $activity->setSourceType(get_class($resource));
        }
        $activity->setActivityType($activity_type);
        $activity->setEdgeRank(0);//$this->generateEdgeRank($resource, $photo, $type));
        $activity->setPhoto($photo);
        
        //Let's create some data
        $data = array();
        
        $data["Vinyett\PhotoBundle\Entity\Photo"] = $photo;
        $data["resource"] = $resource;
        
        $activity->setData(serialize($data));
        
        $this->addToActivityBag($activity);
        
        return true;
        
    }

    /**
     * Adds an activity to the internal bag
     *
	 * @param Activity $activity Activity to store
	 *
     * @return boolean
     */
    public function addToActivityBag(Activity $activity) 
    { 
        if($this->getActivityBag() == null)
        { 
            //NOTE: soon (when I have time) I'm going to write an attribute bag for this instead
            //of storing in an ArrayCollection.
            $this->activity_bag = new ArrayCollection();
        }
        
        $this->getActivityBag()->add($activity);
    }
    
    
    /**
     * Dumps the bag without storing objects
	 *
     * @return boolean
     */
    public function dumpBag() 
    { 
        $this->getActivityBag()->clear();
        
        return true;
    }    
    
    
    /**
     * Stores a bag and resets it
	 *
     * @return boolean
     */    
    public function syncBag() 
    {
        $em = $this->getEntityManager();
        $bag = $this->getActivityBag();
        
        foreach($bag as $activity)
        { 
            $em->persist($activity);
        }
        
        $bag->clear();
        $em->flush(); 
        
        return true;
    }
    
    
    /**
     * generates an edge rank for the story type
     *
	 * @param string $activity_type A valid activity type
	 * @param Photo $photo A Photo object (if applicable)
	 *
     * @return interger
     */
    public function createDefaultEdgeRank($activity_type, $photo) 
    { 
        return 0;
    }    
    
    
    
    
    
    
    
    



}