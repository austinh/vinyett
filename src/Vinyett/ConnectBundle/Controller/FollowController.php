<?php

namespace Vinyett\ConnectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

use Vinyett\ConnectBundle\Entity\Follow;

use JMS\SecurityExtraBundle\Annotation\Secure;


class FollowController extends Controller
{

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function removeFollowAction($with)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $follow = $em->getRepository("ConnectBundle:Follow")->findOneBy(array("actor" => $user->getId(), "following" => $with));
        
        $follow_url = $follow->getFollowing()->getUrlUsername();
        
        /* Debating to use ACL to manage permissions 
        if($securityContext->isGranted('EDIT', $follow) == false && !empty($follow))
        { 
            throw new AccessDeniedException();
        }
        */
        if($follow->getActor()->getId() != $user->getId()) 
        { 
            throw new AccessDeniedException();
        }
        
        $em->remove($follow);
        $em->flush();
        
        return $this->redirect($this->generateUrl('photostream', array("username" => $follow_url)));
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function ajaxCreateFollowAction($with)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
    
        $check = $em->getRepository("ConnectBundle:Follow")->findOneBy(array("actor" => $user->getId(), "following" => $with));
        if($check) 
        { 
            return new Response(json_encode(array("was_created" => true, "follow_data" => array("follow_id" => $check->getId()))));
        }
    
        $follow = new Follow();
        $follow->setActor($user);
        $follow->setFollowing($em->getReference("UserBundle:User", $with));
        $follow->setAffinity(rand(5, 10)); //A little kick starter..
        $follow->setWeight(array());
        
        $em->persist($follow);
        $em->flush();
        
        return new Response(json_encode(array("was_created" => true, "follow_data" => array("follow_id" => $follow->getId()))));
        
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function ajaxUpdateFollowDetailsAction($connection, $detail, $value)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get("security.context");
        $am = $this->get("affinity.manager");
        $follow = $em->getRepository("ConnectBundle:Follow")->find($connection);
        
        /* Debating to use ACL to manage permissions 
        if($securityContext->isGranted('EDIT', $follow) == false && !empty($follow))
        { 
            throw new AccessDeniedException();
        }
        */
        if($follow->getActor()->getId() != $securityContext->getToken()->getUser()->getId()) 
        { 
            throw new AccessDeniedException();
        }
        
        switch($detail)
        { 
            case "friend":
                $follow->setIsFriend($value);
                if($value == true)
                { 
                    $am->adjustAffinity($follow, 7);
                }
            break; 
            
            case "family":
                $follow->setIsFamilty($value);
                if($value == true)
                { 
                    $am->adjustAffinity($follow, 8);
                }
            break;
            
            case "photofeed":
                $follow->setIsInPhotofeed($value);
            break;
        }
        $em->flush();
        
        return new Response(json_encode(array("was_updated" => true)));
    }
}
