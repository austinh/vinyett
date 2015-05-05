<?php

namespace Vinyett\StreamBundle\Feed\Constructors;


abstract class MultipleConstructor
{

    protected $entity_manager;

    protected $templating;

    protected $user;

    protected $stories;
    
    protected $activity_cache_worker;

    protected $container;

    abstract public function synthesize();

    public function __construct() 
    { 
        $this->stories = array();
    }

    public function inject($entityManager, $twigManager, $user, $activity_cache_worker, $container)
    {
        $this->entity_manager = $entityManager;
        $this->user = $user;
        $this->templating = $twigManager;
        $this->activity_cache_worker = $activity_cache_worker;
        $this->container = $container;

        return $this;
    }


    public function getEntityManager() {
        return $this->entity_manager;
    }


    public function getUser() {
        return $this->user;
    }


    public function getTemplating() {
        return $this->templating;
    }
    
    
    public function getActivityCacheWorker()
    {
        return $this->activity_cache_worker;
    }    
    
    
    public function getContainer()
    { 
        return $this->container;
    }


    public function addStory($story)
    {
        $this->stories[] = $story;
    }


    public function getStories() {
        return $this->stories;
    }
    
    /**
     * A shortcut method to get the first stories 
     * information.
     * 
     * @access public
     * @return void
     */
    public function first() 
    { 
        $stories = $this->getStories();
        return $stories[0];
    }


    /**
     * Gets the activities from the stories.
     * 
     * @return void
     */
    public function getActivities() 
    { 
        $activities = array();
        foreach($this->getStories() as $story)
        { 
            $activities[] = $story->getActivity();
        }
        
        return $activities;
        
    }
    
    
    /**
     * Generates an average edge score to base the new function for you.
     * 
     * @return float
     */
    public function averageEdges() 
    { 
        $edges = 0;
        $i = 0;
        foreach($this->getStories() as $story)
        { 
            $edges = $edges + $story->getEdge(); $i++;
        }
        
        $average_edge = $edges/$i;
        
        return $average_edge;
    }

}