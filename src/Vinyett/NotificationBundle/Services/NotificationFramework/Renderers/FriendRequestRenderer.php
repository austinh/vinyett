<?php

namespace Vinyett\NotificationBundle\Services\NotificationFramework\Renderers;

use Vinyett\NotificationBundle\Services\NotificationFramework\Renderers\BaseRenderer;

class FriendRequestRenderer extends BaseRenderer
{ 
  protected $friend;

	public function getTemplate($templating, $notification) 
	{ 	
		$this->getDependencies();
		if($notification->getType() == 1) {
  		return $templating->render("NotificationBundle:List:friend.request.html.twig", array("notification" => $notification, "friend" => $this->friend));
	  } else { 
      return $templating->render("NotificationBundle:List:friend.request.success.html.twig", array("notification" => $notification, "friend" => $this->friend));
	  }
	}

  public function getDependencies() 
  {  
    $em = $this->getEntityManager();
    $this->friend = $em->getRepository("UserBundle:User")->find($this->getNote()->getOutId());
    
    return true;
  }

}