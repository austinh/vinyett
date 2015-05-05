<?php

namespace Vinyett\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use JMS\SecurityExtraBundle\Annotation\Secure;


class NotificationsController extends Controller
{
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function showAction()
    {	
    	$em = $this->getDoctrine()->getEntityManager();
    	$templator = $this->get("notification.templating");
    	$user = $this->get("security.context")->getToken()->getUser();
    	
    	$qb = $em->createQueryBuilder();
    	$qb->select(array("n"))
    	   ->from("NotificationBundle:Notification", "n")
    	   ->where("n.owner = :user")
    	   ->setMaxResults(15)
    	   ->orderBy('n.created_at', 'DESC')
    	   ->setParameters(array("user" => $user));
        $objects = $qb->getQuery()->getResult();
        
        $qb2 = $em->createQueryBuilder();
        $qb2->update("NotificationBundle:Notification", "n")
            ->set("n.is_new", $qb->expr()->literal(false))
    	    ->where("n.owner = :user")
    	    ->andWhere("n.is_new = true")
    	    ->setParameters(array("user" => $user));
        $objects2 = $qb2->getQuery()->getResult();
    	if(count($objects) > 0)
    	{
        	return $this->render('NotificationBundle:Notifications:list.html.twig', array("template" => $templator->render($objects)));
        } else { 
            return new Response('<div class="notification_list_empty">Not much happening!</div>');
        }
    }
    
    
    
    public function notifictionAction() 
    { 
        $em = $this->getDoctrine()->getEntityManager();
        
    }
}
