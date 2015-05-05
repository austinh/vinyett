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
class NotificationTemplating
{
    protected $entity_manager;

    protected $templating;

    protected $user;
    
    protected $logger;


    public function __construct(EntityManager $em, EngineInterface $templating, $security_context, $logger)
    {
    
        $this->entity_manager = $em;
        $this->templating = $templating;
        $this->logger = $logger;
        $this->user = $security_context->getToken()->getUser();

        return $this;
    }

    public function getEntityManager() {
        return $this->entity_manager;
    }
    
    public function getUser()
    { 
        return $this->user;
    }

    public function getTwig() {
        return $this->templating;
    }
    
    public function getLogger()
    { 
        return $this->logger;
    }
    
    public function render($notifications) 
    { 
        $templating = $this->getTwig();
        $template = null;
        foreach($notifications as $notification)
        { 
            $template .= $templating->render("NotificationBundle:Notifications:".$notification->getNotifyReference()->getEvent().".html.twig", array("references" => unserialize($notification->getNotifyReference()->getExternalReferences()), "notify_object" => $notification->getNotifyReference()));
        }
        
        return $template;
    }



}