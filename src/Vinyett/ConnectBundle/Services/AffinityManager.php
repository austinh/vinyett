<?php 

namespace Vinyett\ConnectBundle\Services;

use Vinyett\ConnectBundle\Entity\Follow;

class AffinityManager {

    private $entity_manager;
    
    private $user;
    
    public function getEntityManager() 
    { 
        return $this->entity_manager;
    }
    
    public function getUser()
    { 
        return $this->user;
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
     * Adds an affinity adjustment into the database for a specific follow
     * 
     * This also updates the overall affinity score. (The logic is going to 
     * have to be pushed to the database to remove the duo queries.
     *
	 * @param mixed $following Follow object or ID.
	 * @param interger $weight Between 1-10
	 *
     * @return boolean
     */
    public function adjustAffinity($following, $weight) 
    { 
        $em = $this->getEntityManager();
        $user = $this->getUser();        

        //We get the follow
        if(is_integer($following))
        {
            if($following == $user->getId())
            {
                return false;
            }
            //We want the active users end of the relationship, if one exists...
            $follow = $em->getRepository("ConnectBundle:Follow")->findOneBy(array("actor" => $user->getId(), "following" => $following));
        } else { 
            $follow = $following;
        }
        
        if(!$follow)
        { 
            return false;
        }
    
        if($weight > 10 || $weight < 1) 
        {
            throw new \Exception("Weight must be between 1 and 10.");
        }
        
        $days = (time() - $follow->getLastInteraction()->getTimestamp()) / (60 * 60 * 24);
        if($days > 30) 
        { 
            $days = 30; //At this moment, affinity only decays to a maximum of 30 days.
        }
        
        $days = abs($days-30)+1; //Inverts a 1 into a 30, vise versa, and down the line
        
        $weights = $follow->getWeight();
        $weights_value = 0;
        foreach($weights as $wtv) 
        { 
            $weights_value = $weights_value+$wtv;
        }
        
        //Get a new affinity value
        $affinity = $days+count($weights)+$weights_value;
        $affinity = $affinity + $weight;
        
        $weights[] = $weight;
        
        $follow->setAffinity(round($affinity, 2));
        $follow->setWeight($weights);
        $follow->setLastInteraction(new \DateTime());
        
        $em->persist($follow);
        $em->flush();
        
        return true;
    }
    
    
    
    
    
    
    
    
    
    
}