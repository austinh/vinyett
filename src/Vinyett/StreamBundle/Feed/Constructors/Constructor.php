<?php

namespace Vinyett\StreamBundle\Feed\Constructors;


abstract class Constructor
{

    protected $entity_manager;

    protected $templating;

    protected $user;

    protected $activity_cache_worker;

    protected $activity;
    
    protected $container;


    abstract public function render();


    public function inject($entityManager, $twigManager, $user, $activity_cache_worker, $container)
    {
        $this->entity_manager = $entityManager;
        $this->user = $user;
        $this->templating = $twigManager;
        $this->activity_cache_worker = $activity_cache_worker;
        $this->container = $container;

        return $this;
    }


    public function getEntityManager() 
    {
        return $this->entity_manager;
    }
    
    
    public function getContainer()
    { 
        return $this->container;
    }


    public function getUser() 
    {
        return $this->user;
    }


    public function getTemplating() 
    {
        return $this->templating;
    }
    

    public function getActivityCacheWorker()
    {
        return $this->activity_cache_worker;
    }
    

    public function setActivity($activity)
    {
        $this->activity = $activity;
    }


    public function getActivity() 
    {
        return $this->activity;
    }

}