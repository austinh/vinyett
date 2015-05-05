<?php 

namespace Vinyett\PhotoBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;


/*
 * Responsible for converging items into a feed to be rendered into supplied templates.
 */
class ActivityStreamer 
{ 

    /*
     * @var ArrayCollection $activities
     */
    protected $activities;
    
    /*
     * @var array $template
     */    
    protected $template = array();

    public function getActivities() 
    {   
        return $this->activities;
    }  
    
    public function getTemplateStorage() 
    {   
        return $this->template;
    }     

    /**
     * Constructor
     *
     * @return this
     */
    public function __construct() 
    { 
        $this->activities = new ArrayCollection();
        
        return $this;
    }

    /*
     * Takes a resource, time, and type and adds it to an internal array to be streamed.
     *
	 * @param \DateTime $datetime Time the object was created (or to be streamed at)
	 * @param mixed $resource The object that will be rendered
	 * @param string $type_id A unique identifier for the type of object (e.g., "favorite", "comment")
	 *
     * @return this
     */
    public function addActivity(\DateTime $datetime, $resource, $type_id) 
    { 
        $activity = array("timestamp" => $datetime->getTimestamp(), "resource" => $resource, "type_id" => $type_id, "multiple_resources" => false);
    
        //We use the UNIX value as the key... cuz I'm lazy.
        $this->getActivities()->set($activity["timestamp"], $activity);
        
        return $this;
    }
    
    /**
     * Adds an array of entities to the activity tree
     * 
     * NOTE: Assumes that the timekey is stored as DateTime object
     *
	 * @param string $time_function Function name to get sorting date (assumed to be DateTime return)
	 * @param array $resources Resources to add to the activiy list
	 * @param string $type_id Type to submit resources as
	 *
     * @return boolean 
     */
    public function addActivities($time_function, $resources, $type_id) 
    { 
        foreach($resources as $resource) 
        { 
            $this->addActivity($resource->$time_function(), $resource, $type_id);
        }
        return $this;
    }    
    
    /**
     * Stores a template for a type_id
     *
	 * @param string $type_id A unique identifier aligned with the $type_id's of addActivity
	 * @param string $template Path to the template for this type
	 *
     * @return this
     */
    public function setTypeIdTemplate($type_id, $template) 
    { 
        $templates = $this->getTemplateStorage();

        $templates[$type_id] = $template;
        
        return $this;

    }
    
    /**
     * Renders the stream into cute little format to be
     * processed by the template helpers
	 *
 	 * @param bool $do_aggregate Switch to enable aggregating of stories
 	 * @param array $aggregate_for Story types to merge OR
 	 * @param array $skip_aggregate_for Story to skip merging
 	 *
     * @return array
     */
    public function render($do_aggregate = true, $skip_aggregate_for = array()) 
    { 
        $local_feed = $this->getActivities()->toArray();
        ksort($local_feed); //woo!
        
        
        if($do_aggregate == true)
        {
            $sorted_feed = new ArrayCollection();

            //We do aggregation her per type object
            foreach($local_feed as $stream_item) 
            { 
                if(!in_array($stream_item['type_id'], $skip_aggregate_for))
                {
                    //Get the last object
                    $last = $sorted_feed->last();
                    //See if it is the same type
                    if(!empty($last))
                    {
                        if($last["type_id"] == $stream_item["type_id"])
                        {
                            if(!in_array("resources", $last))
                            {
                                $last["resources"] = array($last["resource"]);
                                $last["resource"] = null;
                                $last["multiple_resources"] = true;
                            }
                            
                            $resources = array_merge($last["resources"], array($stream_item["resource"]));
                            $last["resources"] = $resources;
                            
                            //Remove the previous object
                            $sorted_feed->remove($sorted_feed->key()); //Replace this last instance.
                            $sorted_feed->set($last["timestamp"], $last);
                        } else { 
                            //If they end up not matching, we just add it to the stream
                            $sorted_feed->set($stream_item["timestamp"], $stream_item);
                        }
                    } else { 
                        //If it's the only object, we just add it
                        $sorted_feed->set($stream_item["timestamp"], $stream_item);
                    }
                } else { 
                    //If we shouldn't aggregate it, we just add it to the sorted_feed
                    $sorted_feed->set($stream_item["timestamp"], $stream_item);
                }
            }
            
            $local_feed = $sorted_feed->toArray();
        }
        return $local_feed;

    } 

    /**
     * Used to seperate the logic to see if a stream item should be 
     * aggregated
     *
	 * @param string $type_id The type of the object being checked
     * @param array $skip_aggregate_for An array of $type_id's to be skipped.
	 *
     * @return boolean
     */
    private function _shouldAggregate($type_id, $skip_aggregate_for) 
    { 
        if(in_array($type_id, $skip_aggregate_for))
        { 
            return false;
        } else { 
            return true;
        }
    }














}