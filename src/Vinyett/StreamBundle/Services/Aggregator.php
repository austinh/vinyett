<?php

namespace Vinyett\StreamBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;

use Vinyett\StreamBundle\Entity\Activity;
use Vinyett\PhotoBundle\Entity\Photo;

use Vinyett\StreamBundle\Object\ActivitySet;

class Aggregator 
{ 


    private $entity_manager;
    
    private $user;
    
    private $activity_cache_worker;
    
    /**
     * Constructor
     *
	 * @param EntityManager $entity_manager
     */
    public function __construct($entity_manager, $security_context, $activity_cache_worker) 
    { 
        $this->entity_manager =$entity_manager;
        $this->user = $security_context->getToken()->getUser();
        $this->activity_cache_worker = $activity_cache_worker;
    }    
    
    public function getEntityManager()
    { 
        return $this->entity_manager;
    }
    
    
    public function getUser()
    { 
        return $this->user;
    }
    
    public function getActivityCacheWorker() 
    { 
        return $this->activity_cache_worker;
    }
    
    
    /**
     * Returns a pool of activities to be converted into news feed objects
	 *
     * @return ArrayCollection
     */
    public function aggregate() 
    { 
        $connections = $this->aggregateConnections();
        $em = $this->getEntityManager();
        $user = $this->getUser();
        
        if(empty($connections)) 
        { 
            return array(); //No friends yet :(
        }
        
        $activities = $em->getRepository("StreamBundle:Activity")->fetchActivitiesByConnections($user->getLastStreamUpdate(), $this->convergeConnections($connections));
         
        if($activities) 
        {
            //Activity sets are activities grouped by user (this makes determining affinity a lot easier
            $activity_sets = $this->buildActivitySets($activities, $connections);
            $this->generateEdgeRanks($activity_sets);
            
            return $this->prune($this->flattenActivities($activity_sets));
        } else { 
            return array(); //No new stories.
        }

    }
    
    
    public function prune($activities)
    { 
        return $activities;
    }
    
    
    /**
     * Takes a group of activity sets and flattens them into a series of 
     * activities (organized by edge rank!)
     *
	 * @param ArrayCollection $activity_sets An array collection of activity sets
	 *
     * @return ArrayCollection
     */
    public function flattenActivities($activity_sets) 
    { 
        $activities = array();
        $acw = $this->getActivityCacheWorker();
        
        foreach($activity_sets as $activity_set)
        { 
            foreach($activity_set->getActivities() as $activity)
            { 
                $acw->storePhoto($activity->getPhoto()->getId());
                $activities[] = array("object" => $activity, "edge" => $activity->getEdgeRank());
            }
        }
        
        //Why doesn't usort return the sorted array as the same pointer?
        usort($activities, function (array $a, array $b) { return $a["edge"] < $b["edge"]; });
        
        return $activities;

    }    
    
    
    /**
     * Turns connections into an array of IDs to stick into a query
     *
	 * @param ArrayCollection $connections Array of Doctrine objects.
	 *
     * @return array
     */
    public function convergeConnections($connections) 
    { 
        $ids = array();
        foreach($connections as $connection)
        { 
            $ids[] = $connection->getFollowing()->getId();
        }
        return $ids;
    }    
    

    /**
     * Returns a list of connections to fetch activies for.
     *
     * @return Array Collection
     */
    public function aggregateConnections() 
    { 
        //This function will be a lot better in the future...
        //For now, we just dump all of your connections.
        
        $em = $this->getEntityManager();
        
        $connections = $em->getRepository("ConnectBundle:Follow")->findBy(array("actor" => $this->getUser()->getId()));
        
        return $connections;
        
    }


    /**
     * Builds activity sets from a set of activities. Activity sets are basically just 
     * groups of activities by actor,
     *
	 * @param  $ 
	 *
     * @return 
     */
    public function buildActivitySets($activities, $connections) 
    { 
        $activity_sets = new ArrayCollection(); //Because ArrayCollections rock.
        
        foreach($activities as $activity) 
        { 
            $activity_set = $activity_sets->get($activity->getActor()->getId());
            if(empty($activity_set))
            { 
                //print($activity->getActor()->getId()); die();
                $activity_set = new ActivitySet();
                $activity_set->setConnection($activity_set->findConnection($connections, $activity->getActor()->getId()));
                
                $activity_sets->set($activity->getActor()->getId(), $activity_set);
            }        
            
            $activity_set->addActivity($activity);   
            
        }
        
        return $activity_sets;

    }
    
    
    /**
     * Opens the config file with the weights of stream items
	 *
     * @return arrat
     */
    public function getStreamWeights() 
    { 
        //For now we just return an array...
        $values = array("PHOTO_COMMENT" => 6, 
                        "PHOTO_FAVORITE" => 3,
                        "PHOTO_PROFILED" => 3,
                        "PHOTO_MAPPED" => 4,
                        "PHOTO_UPLOADED" => 7);
    
        return $values;
    }    


    /**
     * Loops through the activity sets and creates edge ranks for all of the activities
     * stored
     *
     * NOTE: This is going to need a lot of reworking
     *
	 * @param ArrayCollection $activity_sets Array collection of activity sets
	 *
     * @return boolean
     */
    public function generateEdgeRanks($activity_sets) 
    { 
        $weights = $this->getStreamWeights();
        $time = new \DateTime();
        
        foreach($activity_sets as $activity_set)
        { 
            $activity_set->weighEdgeRanks($time, $weights);
        }
        
        return true;
        
    }












}