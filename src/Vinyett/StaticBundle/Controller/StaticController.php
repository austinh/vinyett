<?php

namespace Vinyett\StaticBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Vinyett\UserBundle\Entity\InviteRequest,
    Vinyett\UserBundle\Entity\Invitation,
    Vinyett\UserBundle\Form\Type\InviteRequestType;

class StaticController extends Controller
{
   /* 
    * Redirects away from the static homepage is logged in
    */
    public function homepageAction()
    {
        if($this->get('security.context')->isGranted('ROLE_USER'))
        { 
            return $this->redirect($this->generateUrl("home"));
        }
        
        /* Set up the invite box */
        $em = $this->getDoctrine()->getEntityManager();
        $success = false;
        $request = $this->getRequest();
        $invite = new InviteRequest();
        
        $form = $this->createForm(new InviteRequestType(), $invite);
        
        if ($request->isMethod('POST')) {
        
            $form->bind($request);
    
            if ($form->isValid()) {
                if(true) {
                    $invition = new Invitation();
                    $invition->setEmail($invite->getEmail());
                    $invition->setSender(null);
                    $em->persist($invition);
                    $em->flush();
                    
                    $message = \Swift_Message::newInstance()
                                                ->setSubject("An early welcome to Vinyett!")
                                                ->setFrom(array('robo@vinyett.com' => "Vinyett"))
                                                ->setTo($invition->getEmail())
                                                ->setBody($this->renderView("UserBundle:Invite:beta_invitation.mail.html.twig", array("email" => $invition->getEmail(), "code" => $invition->getCode())), 'text/html')
                                                ->addPart($this->renderView("UserBundle:Invite:beta_invitation.mail.plain.twig", array("email" => $invition->getEmail(), "code" => $invition->getCode())), 'text/plain');
                    
                    $this->get('mailer')->send($message);
                } else {
                    // Save the object
                    $em->persist($invite);
                    $em->flush();
                    
                    //Now deliver the message....
                    $message = \Swift_Message::newInstance()
                        ->setSubject("Hello from Vinyett!")
                        ->setFrom(array('robo@vinyett.com' => "Vinyett"))
                        ->setTo($invite->getEmail())
                        ->setBody($this->renderView("UserBundle:Invite:invite_request.mail.html.twig", array("invite_request" => $invite)), 'text/html')
                        ->addPart($this->renderView("UserBundle:Invite:invite_request.mail.plain.twig", array("invite_request" => $invite)), 'text/plain');
                        
                    $this->get('mailer')->send($message);
                }
                    $success = true;
            }
        }
    
        return $this->render('StaticBundle:Static:homepage.html.twig', array("was_successful" => $success, "invite" => $invite, "form" => $form->createView()));
    }
    
    public function aboutAction()
    { 
        return $this->render('StaticBundle:Static:about.html.twig');
    }
}
