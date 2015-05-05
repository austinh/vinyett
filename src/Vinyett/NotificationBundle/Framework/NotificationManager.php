<?php

namespace Vinyett\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Vinyett\UserBundle\Entitiy\User;


/**
 * NotificationManager
 *
 * @package NotificationBundle
 * @author Daniel Griffin
 *
 **/
class NotificationManager
{
    protected $entity_manager;
    protected $templating;
    protected $user; 

		
    public function __construct(EntityManager $em, EngineInterface $templating, SecurityContextInterface $securityContext)
    {   
		$this->entity_manager = $em;
		$this->templating = $templating;
		$this->user = $securityContext->getToken()->getUser();
    		
        return $this;
    }
    
    public function getEntityManager() {
	    return $this->entity_manager;
		}
    
    public function getTwig() {
	    return $this->templating;
		}
    
    public function getUser() { 
    	return $this->user;
    }






}













?>