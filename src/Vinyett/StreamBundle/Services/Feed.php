<?php

namespace Vinyett\StreamBundle\Services;

use Vinyett\StreamBundle\Feed\Congregator\Rule;
use Vinyett\StreamBundle\Entity\NewsStoryCache;
use Vinyett\StreamBundle\Feed\Constructors as Constructors;

class Feed 
{ 
    private $entity_manager;
    
    private $user;
    
    private $templating;
    
    private $_raw_activities = array();
    
    private $_proccessed_stories = array();
    
    private $_merged = false;
    
    private $activity_cache_worker;
    
    private $container;
    
    /**
     * Constructor
     *
	 * @param EntityManager $entity_manager
     */
    public function __construct($entity_manager, $twigManager, $security_context, $congregator, $activity_cache_worker, $container) 
    { 
        $this->entity_manager =$entity_manager;
        $this->user = $security_context->getToken()->getUser();
        $this->templating = $twigManager;
        $this->congregator = $congregator;
        $this->activity_cache_worker = $activity_cache_worker;
        $this->container = $container;
    }    
    
    public function getEntityManager()
    { 
        return $this->entity_manager;
    }
    
    
    public function getCongregator() 
    { 
        return $this->congregator;
    }
    
    
    public function getTemplating() {
	    return $this->templating;
	}
	
	
	public function getContainer() 
	{ 
    	return $this->container;
	}
    
    
    public function getUser()
    { 
        return $this->user;
    }
 
    
    public function getActivityCacheWorker() 
    { 
        return $this->activity_cache_worker;
    }
 
    
    public function getRawActivities()
    { 
        return $this->_raw_activities;
    }
 
    
    public function setRawActivities($activities)
    { 
        $this->_raw_activities = $activities;
    }    
 
    
    public function setProccessedStories($_proccessed_stories)
    { 
        $this->_proccessed_stories = $_proccessed_stories;
    }
 
    
    public function getProccessedStories()
    { 
        return $this->_proccessed_stories;
    }
    

    /**
     * Takes in and stores acitivities to be started
     *
	 * @param array $activities Array of activities to be turned into a feed
	 *
     * @return this
     */
    public function append($activities) 
    { 
        $this->setRawActivities(array_merge($this->getRawActivities(), $activities));
        
        return $this;
    }    
    
    
    /**
     * Pulls all of the previous news items from the DB, converts these
     * activities into new news items and then merges them all together, 
     * trunicating it into a readable feed (the end items are cut off to 
     * prevent breaks in feed times and extremely long feeds).
     *
     * @return boolean
     */
    public function merge() 
    { 
        /*if($this->_merged == true) 
        { 
            throw new \Exception("News stories already merged! Calling this method twice might overwrite your entire stream with weird things!");
        } else {
            $this->_merged = true;
        }*/
    
        $em = $this->getEntityManager();
        $user = $this->getUser();
        
        //$news_story_caches = $em->getRepository("StreamBundle:NewsStoryCache")->findBy(array("user" => $user->getId()));
        $this->_proccessed_stories = $this->processRawActivities(true); //true parameter allows news feed caches to be persisted 
                                                                    //(but no flushing, crawlers need to skim first!).

        //$this->_proccessed_stories = array_merge($news_story_caches, $new_news_story_caches); //Converge it all!

        $this->bindCongregator(); //Binds crawlers and post-feed processes (such as compression).
        //$this->trunicateStories(); This cuts the feed down to ~50 items.
        
        $user->setLastStreamUpdate(new \DateTime());
        
        $em->flush();
                
        return true;

    }
    
    
    
    public function recycle($stories) 
    { 
        $this->_proccessed_stories = $stories;
        return $this->ready();
    }


    /**
     * Takes activities, creates a template ready dataset that is cached 
     * into a NewsStoryCache object
	 *
     * @return array
     */
    public function processRawActivities() 
    { 
        
        $activities = $this->getRawActivities();
        $news_objects = array();
        
        
        foreach($activities as $activity)
        { 
            $news_objects[] = $this->processRawActivity($activity);
        }
        
        return $news_objects;
        
    }

    /**
     * Takes an activity and builds a NewsStoryCache from it (and persists it!)
	 *
	 * @param Activity $activity An activity object
	 *
     * @return NewsStoryCache
     */
    public function processRawActivity($activity) 
    { 
    
        $em = $this->getEntityManager();
        
        $cache = new NewsStoryCache();
        $cache->setUser($this->getUser());
        $cache->setActivity($activity["object"]);
        $cache->setHtml($this->render($activity["object"]));
        $cache->setPhoto($em->getReference("PhotoBundle:Photo", $activity["object"]->getPhoto()->getId()));
        $cache->setEdge($activity["edge"]);
        $cache->setActivityCreatedAt($activity["object"]->getCreatedAt());
        
        $em->persist($cache);
    
        return $cache;
    }


   /*
    * Takes an activity and constructs an element from the data contained within it.
    * 
    * Elements are constructed by dedicated constructors that pull the information to 
    * create a full item. This only happens once, ever, (items published to the news feed are
    * pretty temporary and aren't reconstructed. 
    *
    * @param type name desctiption
    * @return float
    */
    public function render($activity)
    { 
    
      $constructor = $this->getConstructorFromType($activity->getActivityType());
      $constructor->setActivity($activity);

      return $constructor->render();
    
    }


    /*z 
     *
     * Returns the object of the constructor. The object will be properly set up
     * by this function, as well.
     *
     * NOTE: For now we use a simple and ugly switch(). Whenever I get time,
     * I'll move it to a config file or to a database (for API extensibility).
     *
  	 * @param type name desctiption
     * @return float
     */
    public function getConstructorFromType($type) 
    { 
      
      switch($type)
      { 
        case "PHOTO_COMMENT": //Friend.new
          $constructor = new Constructors\PhotoCommentConstructor();
        break;
        
        case "PHOTO_FAVORITE":
          $constructor = new Constructors\PhotoFavoriteConstructor();
        break;
        
        case "PHOTO_PROFILED":
          $constructor = new Constructors\PhotoProfiledConstructor();
        break;
        
        case "PHOTO_MAPPED":
          $constructor = new Constructors\PhotoMappedConstructor();
        break;
        case "PHOTO_UPLOADED":
          $constructor = new Constructors\PhotoUploadedConstructor();
        break;
      }
      
      //Inject dependencies
      $constructor->inject($this->getEntityManager(), $this->getTemplating(), $this->getUser(), $this->getActivityCacheWorker(), $this->getContainer());
          
      return $constructor;
    }


    /**
     * Readys the feed to be viewed
	 *
     * @return this
     */
    public function ready() 
    { 
        $stories = $this->getProccessedStories();
        
        $cmp = function($a, $b)
        {
            if ($a->getEdge() == $b->getEdge()) {
                return 0;
            }
            return ($a->getEdge() > $b->getEdge() ? -1 : 1);
        };
        
        usort($stories, $cmp);
        
        return $stories;
    }

    /**
     * Attaches crawler and Congregator to the stories
     * 
     * @return void
     */
    public function bindCongregator() 
    { 
        $congregator = $this->getCongregator();
        
        $congregator->addRule(new Rule("PHOTO_FAVORITE", array("id"), false));
        $congregator->addRule(new Rule("PHOTO_COMMENT", array("id"), false));
        $congregator->addRule(new Rule("PHOTO_UPLOADED", array("owner"), false));
        $congregator->setStories($this->getProccessedStories());
        
        $stories = $congregator->congregate();
        
        $this->setProccessedStories($stories); //$congregator->orderStoriesByEdge($stories));
        
        return;
    }







}