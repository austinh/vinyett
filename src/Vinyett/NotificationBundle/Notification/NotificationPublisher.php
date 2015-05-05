<?php

namespace Vinyett\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Vinyett\UserBundle\Entitiy\User;


/**
 * NotificationPublisher
 *
 * Used for the model interactions of notifications...
 *
 * @package NotificationBundle
 * @author Daniel Griffin
 *
 **/
class NotificationPublisher
{
    protected $entity_manager;

    protected $templating;

    protected $user;
    
    protected $container;
    
    protected $logger;
    
    protected $serializer;


    public function __construct(EntityManager $em, EngineInterface $templating, $container, $logger, $serializer)
    {
    
        $this->entity_manager = $em;
        $this->templating = $templating;
        $this->logger = $logger;
        $this->container = $container;
        $this->serializer = $serializer;

        return $this;
    }

    public function getEntityManager() {
        return $this->entity_manager;
    }

    public function getTwig() {
        return $this->templating;
    }
    
    public function getContainer() 
    {
        return $this->container;
    }
    
    public function getLogger()
    { 
        return $this->logger;
    }
    
    public function getSerializer()
    { 
        return $this->serializer;
    }


    /**
     * Returns an initated NotificationManager class.
     *
     * This functin also searches event list for your event,
     * and stores it as a NotificationEvent object.
     *
     * @access public
     * @param mixed $event_name
     * @return void
     */
    public function createManager($event_name)
    {
        $event = $this->verifyEventCatalog($event_name);
        
        if(!$event)
        { 
            throw new \Exception($event_name." is not a valid event. Be sure you've registered this event.");
        }
    
        $manager = new NotificationManager($event, $this->getEntityManager(), $this->getLogger(), $this->getSerializer());
        
        return $manager;
    }
    
    
    public function verifyEventCatalog($event_name)
    { 
        if(!array_key_exists($event_name, $this->getContainer()->getParameter('notification.events')))
        {
            throw new \Exception("No event named ".$event_name);
        }
    
        return $event_name;
    }
    
    
    /**
     * Uses a NotificationManager instance to publish notifications.
     * 
     * @access public
     * @param NotificationManager $manager
     * @return void
     */
    public function publish(NotificationManager $manager)
    { 
    
        $em = $this->getEntityManager();
    
        if(!$manager->receivers) 
        { 
            //throw new \Exception("There's no one addressed to this notification!");
        }
        
        $manager->createNotifyObjects();
        
        /*
            Basic overview
            
            - Uses the manager's receivers to find how each user expects
              to be notified of the event based on NotificationPreference. If no 
              value is found, a default one will be used. 
              
            - From the options retrieved above, we loop though each reciever and make
              a NotifyObject from the manager impression, which is then ran through 
              the available PLUGINS (rename this) to be handled.
        */
        
        $transports = $this->availableTransports(); //fetch transport classes...
        //die(\Utilities::arrays($transports));
        $preferences = $this->buildPreferences($manager->receivers, $manager->getEvent());
        
        foreach($transports as $type => $transport)
        { 
            $transport_class = $transport['class'];
        
            $active_transport = new $transport_class($this->getContainer()); //We inject the container so the transport has access to a full range of services.
            $objects = $manager->fetchNotifyObjectsForType($type, $preferences, $transport);
            if(count($objects) > 0) //Don't event execute if no objects...
            {
                $active_transport->process($objects); //filterNotificationsToType returns NotifyObjects for the provided type to be processed. 
            }
        }
        
        $em->flush(); //Save everything!
        
        return true;
        
    }
    
    
    /**
     * Returns a list of transports to be handed NotifiyObjects to
     * 
     * @access public
     * @return void
     */
    public function availableTransports() 
    { 
        $container = $this->getContainer();
        $transports = $container->getParameter("notification.transports");
        
        return $transports;
    }


    /**
     * Builds preferences based on how a user expects to receive a message 
     * based on the event type.
     *
     * Basically, if the event is "photo.comment" we will search the NotificationPreferences 
     * table for preferences that have the event, "photo.comment" and are associated with
     * one of the receivers. We'll sort it and fill in missing values with defaults before 
     * giving the preferences back.
     * 
     * @access public
     * @param array $users
     * @return array
     */
    public function buildPreferences($users, $event) 
    { 
        $em = $this->getEntityManager();
        
        $user_ids = array();
        foreach($users as $user)
        { 
            $user_ids[] = $user->getId();
        }
        
        $qb = $em->createQueryBuilder();
        $qb->select(array("np"))
           ->from("NotificationBundle:NotificationPreference", "np")
           ->where("np.event = :event")
           ->andWhere('np.owner IN (:ids)')
           ->setParameters(array('ids' => $user_ids, "event" => $event)); 
        
        $np = $qb->getQuery()->getResult();        
        
        $preferences = array();
        
        foreach($np as $preference)
        { 
            $preferences[$preference->getOwner()->getId()][$preference->getTransport()] = $preference->getValue();
        }
        
        return $preferences;
        
    }






}








