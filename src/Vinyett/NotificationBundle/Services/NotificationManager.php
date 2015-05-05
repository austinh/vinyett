<?php

namespace Vinyett\NotificationBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Vinyett\NotificationBundle\Entity\Notification;
use Vinyett\UserBundle\Entitiy\User;
use Vinyett\NotificationBundle\Services\NotificationFramework\Renderers as render;


/**
 * NotificationManager
 *
 * Used for the model interactions of notifications...
 *
 * @package NotificationBundle
 * @author Daniel Griffin
 *
 **/
class NotificationManager
{
    protected $entity_manager;
    protected $templating;
    protected $user; //This class SHOULD ONLY BE INITATED FOR THE CURRENT SESSIONED USER. To prevent access, we pull the active user from the security context
		
		protected $notifications_collection;
		protected $unread_count;
		protected $loaded = false;
		
		protected $notifications_type_pool = array(
			1 => array("identifier" => "friend.request", "renderer" => "FriendRequestRenderer"),
			2 => array("identifier" => "friend.request.success", "renderer" => "FriendRequestRenderer")
		);
		
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
    
    public function getType($type)
    { 
    	return $this->notifications_type_pool[$type];
    }
    
    //To load notifications, first we count your new notifications.
    //
    // More than 6 new, we load all of the new, 
    // Less than 6, we load all the new and additional to total the notifications to 6.
    public function preloadListNotifications() 
    { 
    	$em = $this->getEntityManager();
    
    	$this->notifications_collection = $em->getRepository("NotificationBundle:Notification")->getNotificationsListData($this->getUser());
    	$this->loaded = true;
    	
    	return $this;
    }
    
    //Loop through, plug the notifications into their class loader, render them with Twig, then display the result here...
    public function renderNotificationList()
    { 
    	if($this->loaded == false) 
    	{ 
    		throw new \Exception("You must call preloadListNotifications before rendering a list!");
    	}
    	
    	$collection = '';
    	foreach($this->notifications_collection as $note)
    	{ 
    		$type = $this->getType($note->getType());
    		$class = 'Vinyett\NotificationBundle\Services\NotificationFramework\Renderers\\'.$type['renderer'];
    		$renderer =  new $class($note, $this->entity_manager, $this->templating, $this->getUser());
    		$collection .= $renderer->render();
    	}
    	
    	if(empty($collection)) { 
    	 return '<div style="text-align:center; padding:10px;" id="no_notifications">No recent notifications</div>';
    	}
    	
    	return $collection;
    	
    }

}

?>