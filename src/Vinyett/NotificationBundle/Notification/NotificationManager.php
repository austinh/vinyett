<?php

namespace Vinyett\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Vinyett\UserBundle\Entitiy\User;

use Vinyett\NotificationBundle\Entity\Subscriptionlist;
use Vinyett\NotificationBundle\Entity\NotifyObject;


/**
 * NotificationManager
 *
 * @package NotificationBundle
 * @author Daniel Griffin
 *
 **/
class NotificationManager
{

    /**
     * Every notification is based off some event, and so the
     * the Manager must be aware of what is happening.
     *
     * HOWEVER, the Manager doesn't care what the event is,
     * therefore there doesn't check to see if the event is valid.
     *
     * Meaning you should do that before just tossing events into this
     * thing. (Invalid events prevent subscription lookups and WILL raise exceptions
     * in the composition layer).
     *
     * @var mixed
     */
    protected $event;

    /**
     * @var $entity_manager
     */
    protected $entity_manager;

    /**
     * @var $subscriptionlist
     */
    public $subscriptionlist;

    /**
     * @var $logger
     */
    public $logger;
    
    /**
     * @var $serializer
     */
    protected $serializer;

    /**
     * @var $actor
     */
    public $actor;
    
    /**
     * @var $receivers
     */
    public $receivers;
    
    /**
     * @var $references
     */
    public $references;
    
    /**
     * @var $notify_objects
     */
    public $notify_objects = array();


    public function __construct($event, EntityManager $em, $logger, $serializer)
    {
        $this->event = $event;
        $this->entity_manager = $em;
        $this->logger = $logger;
        $this->serializer = $serializer;
        
        //$this->notify_objects = new ArrayCollection();

        return $this;
    }

    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    public function getEvent()
    {
        return $this->event;
    }
    
    public function getLogger()
    { 
        return $this->logger;
    }
    
    public function getSerializer()
    { 
        return $this->serializer;
    }


    public function getReceivers()
    { 
        $receivers = $this->receivers;
        //Remove the sender (actor) from receiving the message
        //(since they know what they're doing)
        $return = array();
        foreach($receivers as $p)
        {
            if($p->getId() != $this->actor->getId())
            { 
                $return[] = $p;
            }
        }
        
        return $return;
    }

    /**
     * Sets the actor who triggered the event.
     *
     * @access public
     * @param Vinyett\UserBundle\Entity\User $user
     * @return void
     */
    public function from($user)
    {
        $this->actor = $user;
    }
    

    /**
     * Assign a person to receive a notification
     * 
     * @access public
     * @param mixed $to
     * @return void
     */
    public function to($to)
    {
        $this->receivers = array($to);
    }
    
    /**
     * Assign a people to receive a notification
     * 
     * @access public
     * @param mixed $to
     * @return void
     */
    public function toAll($to)
    {
        $this->receivers = $to;
    }


    /**
     * Adds references to be stored within the notification object.
     * 
     * @access public
     * @param array $references
     * @return void
     */
    public function addResources(array $references) 
    { 
        $this->references = serialize($references);
    }
    

    /**
     * Loads or creates a new subscriber list based on the object
     * and the event occuring on the object.
     *
     * E.g., Photo 1 has a subscription list for photo.comment events.
     *
     * @access public
     * @param mixed $object
     * @return void
     */
    public function loadOrCreateSubscriberList($object)
    {
        $em = $this->getEntityManager();

        $identity = $this->findObjectIdentity($object);
        $subscriptionlist = $em->getRepository("NotificationBundle:SubscriptionList")->load($identity, $this->getEvent());
        if(!$subscriptionlist)
        {
            $subscriptionlist = new SubscriptionList();
            $subscriptionlist->setObjectIdentity($identity);
            $subscriptionlist->setEvent($this->getEvent());

            $em->persist($subscriptionlist); //Make sure it's a part of the entity manager...
       }

        $this->subscriptionlist = $subscriptionlist;

        return $subscriptionlist;
    }


    /**
     * Creates an identity for the object (to be used as an identifier for
     * looking up subscription lists).
     *
     * @access public
     * @param mixed $object
     * @return string
     */
    public function findObjectIdentity($object)
    {
        $object_type = $object->getObjectType();
        $object_id = $object->getId();

        return $object_type."-".$object_id;
    }
    
    
    /**
     * Finalizes NotifyObjects to be processed and sent to a 
     * transport (if the user settings allow it).
     * 
     * @access public
     * @return void
     */
    public function createNotifyObjects()
    { 
        $em = $this->getEntityManager();
    
        foreach($this->getReceivers() as $to) 
        {         
            $no = new NotifyObject();
            $no->setRecipient($to);
            $no->setSender($this->actor);
            $no->setExternalReferences($this->references);
            $no->setEvent($this->getEvent());
            
            $em->persist($no); //We save copies of these objects in the database!
            
            $this->notify_objects[] = $no;
        }
    }
    
    
    /**
     * Generates a collection of Notify objects that can be sent for that type
     * based on the user's preferences (if supplied). If no preferences are supplied
     * all notify objects will be returned.
     * 
     * @access public
     * @param string $type
     * @param mixed $preferences (default: null) We assume that the liste of preferences is 
     * @param boolean $default A default preference if none is assigned.
     * @return void
     */
    public function fetchNotifyObjectsForType($type, $preferences = array(), $default = false) 
    { 
        $response = array();
        foreach($this->notify_objects as $no)
        { 
            /* Decide if it's okay to process this NO for this transport method. */
            $valid_to_send = false;
            if(array_key_exists($no->getRecipient()->getId(), $preferences)) { 
                if(array_key_exists($type, $preferences[$no->getRecipient()])) {
                    $valid_to_send = $preferences[$no->getRecipient()][$type];
                } else { 
                    $valid_to_send = $default;
                }
            } else { 
                $valid_to_send = $default;
            }
            
            if($valid_to_send == true) 
            { 
                $response[] = $no;
            }
        }
        
        return $response;
    }




}



















?>