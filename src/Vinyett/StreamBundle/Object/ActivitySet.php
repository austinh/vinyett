<?php
namespace Vinyett\StreamBundle\Object;

use Doctrine\Common\Collections\ArrayCollection;

class ActivitySet
{ 

    protected $connection;
    
    protected $activities;
    
    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }
    
    public function getConnection()
    { 
        return $this->connection;
    }


    public function setConnection($connection)
    { 
        $this->connection = $connection;
    }

    
    public function AddActivity($activity)
    {
        $this->getActivities()->set($activity->getId(), $activity);
    }
    
    public function getActivities() 
    { 
        return $this->activities;
    }
    
    /**
     * Searches an array of connects and returns the relevant one. 
     * i.e. the one between the activity set owner and user.
     *
     * NOTE: For now this continously loops, but in the future it will store 
     * an organized array set
     *
	 * @param ArrayCollection $connections Array of collections
	 * @param integer $activity_owner ID of the activity owner
	 *
     * @return Follow Object
     */
    public function findConnection($connections, $activity_owner) 
    { 
        foreach($connections as $connection) 
        { 
            if($connection->getFollowing()->getId() == $activity_owner)
            { 
                return $connection;
            }
        }
        
        throw new \Exception("No connection was found");
    }
    
    /**
     * Builds edge ranks for each activity in the set.
     *
     * NOTE: For now, we use Facebook's Edge Rank algorithm. e = ue we de
     *
     * @param DateTime $time The time to base the edge from (this keeps all
     *                       activity scores consistent).
	 *
     * @return boolean
     */
    public function weighEdgeRanks(\DateTime $time, $weights) 
    { 
        $connection = $this->getConnection();
        
        if(empty($connection))
        { 
            throw new \Exception("No connection was set for this Activity Set before generating edge ranks (this is required!)");
        }
        
        $affinity = $connection->getAffinity();
        
        foreach($this->getActivities() as $activity)
        { 
            $type = $activity->getActivityType();
            $story_weight = $weights[$type];
            
            $edge_rank = ($affinity+($story_weight*10))*pow(1-0.05, floor(($time->getTimestamp() - $activity->getCreatedAt()->getTimestamp())/3600));
            
            $edge_rank = round(substr($edge_rank, 0, 11), 9);
            $activity->setEdgeRank($edge_rank);
        }
        
        return true;
        
    }    









}