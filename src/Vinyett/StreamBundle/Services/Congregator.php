<?php 

namespace Vinyett\StreamBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;

use Vinyett\StreamBundle\Feed\Constructors as Constructors;
use Vinyett\StreamBundle\Feed\Congregator\Rule;

use Vinyett\StreamBundle\Entity\Activity;
use Vinyett\PhotoBundle\Entity\Photo;




/**
 * Congregator class.
 */
class Congregator 
{ 
    
    /**
     * 
     **/
    private $rules;
    
    private $entity_manager;
    
    private $user;
    
    private $templating;
    
    private $activity_cache_worker;
    
    private $stories;
    
    private $container;
    
    /**
     * __construct function.
     *
     * @return void
     */
    public function __construct($entity_manager, $twigManager, $security_context, $activity_cache_worker, $container)
    { 
        $this->entity_manager =$entity_manager;
        $this->user = $security_context->getToken()->getUser();
        $this->templating = $twigManager;
        $this->activity_cache_worker = $activity_cache_worker;
        $this->container;
        
        $this->rules = array();
    }
    
    
    public function getContainer()
    { 
        return $this->container;
    }
    
    
    public function getActivityCacheWorker()
    { 
        return $this->activity_cache_worker;
    }
    
    
    public function getStories()
    { 
        return $this->stories;
    }
    
    
    public function setStories($stories)
    { 
        $this->stories = $stories;
    }
    
    
    public function getEntityManager()
    { 
        return $this->entity_manager;
    }
    
    
    public function getTemplating() 
    {
	    return $this->templating;
	}
    
    
    public function getUser()
    { 
        return $this->user;
    }
    
    
    public function getRules()
    { 
        return $this->rules;
    }
    
    
    /**
     * Adds a Rule class to the congregation list.
     * 
     * @access public
     * @param Rule $rule
     * @return void
     */
    public function addRule(Rule $rule)
    { 
        $this->rules[] = $rule;
    }
    
    
    /**
     * Congregates and oganizes the news feed stories that are 
     * ready to be displayed back to the user.
     *
     * NOTE: This changes the persistence of existing objects
     * and detaches and attaches new ones.
     * 
     * @access public
     * @return void
     */
    public function congregate() 
    {
        // 1. Break the activities into sections, assign each activity a section
        // 2. Loop through activities to see if they meet rule criteria 
        // 3. If they do, they are removed from the list of activities and added to a 
        //        list for those specific conditions (i.e., PHOTO_COMMENT on ID 14).
        // 4. Lists are compressed into group stream objects based on the previous rule created.
        //      4a. Used stories are detached and DESTROYED
        //      4b. New stories are attached to EM with averaged edges.
        // 5. The new new stream stories are then sorted back into the list of activity stories
        // 6. New compressed stories are set along 
        
        
        $activity_sections = $this->section($this->getStories());
        
        $stories = array();
        foreach($activity_sections as $activity_section)
        {
            $stories = array_merge($this->matchToRules($activity_section), $stories);
        }
        return $this->sortStoriesByEdgeRank($stories);
    }
    
    
    /**
     * Takes stories and breaks them into sections which will be 
     * congregated independent of each other.
     * 
     * @access public
     * @param mixed $stories
     * @return void
     */
    public function section($stories) 
    { 
        $total_stories = count($stories);
        if($total_stories < 25 ) { 
            $chunks = 1;
        } elseif($total_stories >= 25 && $total_stories < 50) { //This was just a quick implementation, it will be fixed later.
            $chunks = 2;
        } elseif($total_stories >= 50 && $total_stories < 75) {
            $chunks = 4;
        } elseif($total_stories >= 75 && $total_stories < 100) {
            $chunks = 5;
        } elseif($total_stories >= 100) { 
            $chunks = 6;
        }
        
        $stories_per_chunk = ceil($total_stories/$chunks);
        
        $activity_sections = array(0 => array());
        
        $i = 0;
        $story_i = 0;
        foreach($stories as $story)
        { 
            if($i == $stories_per_chunk)
            {
                $story_i++;
                $activity_sections[$story_i] = array();
                $i = 0; //reset the iterator for simplicity
            }
            
            $activity_sections[$story_i][] = $story;
            
            $i++;
        }
        
        return $activity_sections;
        
    }
 
 
 
 
    /**
     * Matches the inputted stories to rules and merges them accordingly.
     * This function is heavily commented to understand the flow of data...
     * 
     * @param array $activities
     *
     * @return void
     */
    public function matchToRules($stories)
    { 
        $binds = array();  
        //We loop through activities, matching them to rules
        foreach($this->getRules() as $rule)
        { 
            $binds[$rule->getType()] = $rule->getTag();
        }
        
        //Make sum rulez
        foreach($stories as $story) 
        { 
            $this->generatePerspectiveRule($story, $binds); //Rules work like TYPE_(PARAMETERMD5_IDENTIFIERS based on parameters
        }
        
        //Subdivide the story list into their rules...
        $stories_by_rule = array();
        foreach($stories as $item)
        {
            $trule = $item->_rule;
            //$stories_by_rule[$trule][] = $item->getActivity()->getActivityType()." on ".$item->getActivity()->getPhoto()->getId();
            $stories_by_rule[$trule][] = $item;
        }
        
        //die(\Utilities::arrays($stories_by_rule));
        
        //We have two chunks of stories, story pools and stories.
        $story_clusters = $this->cast($stories_by_rule); //These are unsorted merged stories.
            
        return $story_clusters;
        
    }
    


    /**
     * Builds a specific rule name for the story 
     * key structure is TYPE_(PARAMETERMD5)_IDENTIFIERS.
     * 
     * @param string $type
     * @param array  $binds
     *
     * @return boolean
     */
    public function generatePerspectiveRule($story, array $binds)
    { 
        $story->_rule = "none";
    
        foreach($binds as $type => $bind) 
        { 
            if($type == $story->getActivity()->getActivityType())
            { 
                $rule_specific = null;
                foreach($bind["parameters"] as $arr) { 
                    $parameter = "get".\Utilities::to_camel_case($arr, true);
                    $rule_specific .= $arr."%".$story->getActivity()->getPhoto()->$parameter().";";
                }
                
                $story->_rule = $bind["name"]."_".$rule_specific;
            }
        }
    }
 
 
    /**
     * Rebuilds the stories that can be compressed into single 
     * feel stories.
     * 
     * @access public
     * @param mixed $story_pools
     * @return void
     */
    public function cast($stories_by_rule) 
    { 
        $casted_stories = array();
        
        foreach($stories_by_rule as $rule => $stories)
        { 
             if($rule == "none" || count($stories) == 1)
            { 
                 $casted_stories = array_merge($casted_stories, $stories); //Stories not bound by rules or rules with only one story are dumped back
            } else {            
                //We are left with stories we want to merge, so...   
                $casted_stories[] = $this->merge($stories);
            }
        }
     
        return $casted_stories;
    }
    
    
    /**
     * Merges stories into a single story. 
     *
     * NOTE: This function expects ALL of the stories to be the same activity type!!!
     *
     * NOTE: For now we cheat and have all of the expected types in this function!
     * 
     * @access public
     * @param mixed $stories
     * @return void
     */
    public function merge($stories)
    { 
        $em = $this->getEntityManager();
    
        $constructor_name = "Vinyett\\StreamBundle\\Feed\\Constructors\\".\Utilities::to_camel_case(strtolower($stories[0]->getActivity()->getActivityType()."_MULTIPLE_CONSTRUCTOR"), true);
        $constructor = new $constructor_name();
        $constructor->inject($this->getEntityManager(), $this->getTemplating(), $this->getUser(), $this->getActivityCacheWorker(), $this->getContainer());
        
        foreach($stories as $story)
        { 
            $constructor->addStory($story);
            $em->detach($story); //This object is going to be recycled into a new story so it's no longer needed in the EM.
        }
        
        $merged_story = $constructor->synthesize(); //Returns an entire NewsStoryCache object
                
        $em->persist($merged_story); //Auutomatically add it to the em
        
        return $merged_story;
        
    }
    
    
    /**
     * Arranges the newly constructed stories by their edge ranks...
     * 
     * @access public
     * @param mixed $stories
     * @return void
     */
    public function sortStoriesByEdgeRank($stories)
    {   
        usort($stories, function ($a, $b) { return $a->getEdge() < $b->getEdge(); });
        return $stories;   
    }
    
    
 
 
 
 
    



}