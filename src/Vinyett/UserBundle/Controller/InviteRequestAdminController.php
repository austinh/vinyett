<?php
// src/Tutorial/BlogBundle/Controller/CommentAdminController.php

namespace Vinyett\UserBundle\Controller;

use Vinyett\UserBundle\Entity\Invitation;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class InviteRequestAdminController extends Controller
{
    /**
     * Sends out notifications to users who have subscribed and also registers their invitation
     * 
     * @access public
     * @param ProxyQueryInterface $selectedModelQuery
     * @return void
     */
    public function batchActionInvite(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false)
        {
            throw new AccessDeniedException();
        }
    
        $em = $this->getDoctrine()->getEntityManager();
        $selectedModels = $selectedModelQuery->execute();
                    
        foreach($selectedModels as $request) 
        { 
            $invite = new Invitation();
            $invite->setEmail($request->getEmail());
            $invite->setSender(null);
            $em->persist($invite);
            
            $request->setInvited(true);
            //$request->setInvitation($invite);
            
            $message = \Swift_Message::newInstance()
                                        ->setSubject("An early welcome to Vinyett!")
                                        ->setFrom(array('robo@vinyett.com' => "Vinyett"))
                                        ->setTo($invite->getEmail())
                                        ->setBody($this->renderView("UserBundle:Invite:beta_invitation.mail.html.twig", array("email" => $invite->getEmail(), "code" => $invite->getCode())), 'text/html')
                                        ->addPart($this->renderView("UserBundle:Invite:beta_invitation.mail.plain.twig", array("email" => $invite->getEmail(), "code" => $invite->getCode())), 'text/plain');
            
            $this->get('mailer')->send($message);
        }
        
        $em->flush();
        
        $this->get('session')->setFlash('sonata_flash_success', "The invites were successfully sent out to ".count($request)." requests");
        
        return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
    }
}