<?php

namespace Vinyett\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class LayoutController extends Controller
{
    
    public function notificationAction()
    {
     	$user = $this->get('security.context')->getToken()->getUser();
     	$em = $this->getDoctrine()->getEntityManager();
     	
     	$notifications = $em->getRepository("NotificationBundle:Notification")->totalUnread($user);
     
    	return $this->render("NotificationBundle:Layout:notification.html.twig", array("total" => $notifications));
        
    }
}
