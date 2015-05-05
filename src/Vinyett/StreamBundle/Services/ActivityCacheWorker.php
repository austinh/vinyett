<?php

namespace Vinyett\StreamBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;

use Vinyett\StreamBundle\Entity\Activity;
use Vinyett\PhotoBundle\Entity\Photo;


/**
 * ActivityCacheWorker class
 *
 * This service is resposible for keeping tabs on the articial photo 
 * object stored in activity objects for fetching information about them. 
 *
 * (e.g., keeping a collection of photo options and fetching comments about
 * the photos represented in the feed. 
 */
class ActivityCacheWorker 
{ 
    /**
     * @var EntityManager $entity_manager
     */
    private $entity_manager;
    
    /**
     * @var User $user
     */
    private $user;
    
    /**
     * @var ArrayCollection
     */
    protected $_stored_photos = array();
    
    /**
     * @var array $query_builds
     */
    private $query_builds = array();
    
    /**
     * 
     * (default value: false)
     * 
     * @var bool $_has_executed
     */
    private $_has_executed = false;
    
    
    private $data;
    
    /**
     * Constructor
     *
	 * @param EntityManager $entity_manager
     */
    public function __construct($entity_manager, $security_context) 
    { 
        $this->entity_manager =$entity_manager;
        $this->user = $security_context->getToken()->getUser();
        $this->data = new ArrayCollection();
    }    
    
    public function getEntityManager()
    { 
        return $this->entity_manager;
    }
    
    
    public function getUser()
    { 
        return $this->user;
    }
    
    
    public function getStoredPHotos()
    {
        return $this->_stored_photos;
    }
    
    public function hasExecuted() { 
        return $this->_has_executed;
    }
    
    public function getData()
    { 
        return $this->data;
    }
    
    /**
     * Stores a photo's ID to be used for querying later.
     * 
     * @param mixed $photo
     * @return void
     */
    public function storePhoto($photo)
    {
        if(is_object($photo))
        { 
            $id = $photo->getId(); //The objects are useless beyond their IDs
        } else { 
            $id = $photo;
        }
        
        $this->_stored_photos[] = $id;
        $this->_stored_photos = array_values(array_unique($this->getStoredPhotos(), SORT_NUMERIC)); //No duplicates!
    }
    
    /**
     * Dumps all of the stored photos in an array.
     * 
     * @access public
     * @return void
     */
    public function dumpPhotoAssociations() 
    { 
        return $this->getStoredPhotos();
    }
    
    
    /**
     * This will automatically store the query you want to execute
     *
     * NOTE: This method was temporarily suspended and will be implimented in a later build
     * 
     * @param string $name Name of the query
     * @param QueryBuilder $qb Query Builder object
     * @return void
     */
    public function appendQueryBuilder($name, QueryBuilder $qb)
    { 
        $this->query_builds[$name] = $qb; 
    }

    
    public function fetchLiveDataFor($type, $photo)
    {
        if($this->hasExecuted() == false) 
        { 
            $this->fetchData();
                    
            $comments = $this->getData()->get("comments");
            $organized_comments = array();
            foreach($comments as $comment) 
            { 
                $organized_comments[$comment->getPhoto()->getId()][] = $comment;
            }
                    
            $this->getData()->set("organized_comments", $organized_comments);
        } else { 
            $organized_comments = $this->getData()->get("organized_comments");            
        }
        
        if(array_key_exists($photo, $organized_comments))
        {
            return $organized_comments[$photo];
        } else { 
            return array(); 
        }
    }


    public function fetchData() 
    { 
        $em = $this->getEntitymanager();
                
        $ids = $this->getStoredPhotos();
        //die(print(\Utilities::arrays($ids)));
            
        $cqb = $em->createQueryBuilder();
        $cqb->select("c")
            ->from("PhotoBundle:PhotoComment", "c")
            ->andWhere('c.photo IN (:ids)')
            //->groupBy("c.photo")
            ->orderBy("c.created_at", "ASC")
            ->setParameter('ids', $ids);
        
        $this->getData()->set("comments", $cqb->getQuery()->getResult());
        
        $this->_has_executed = true;
        
    }









}