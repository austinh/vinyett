<?php 

namespace Vinyett\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints\Email,
    Symfony\Component\Validator\Constraints\Length,
    Symfony\Component\Validator\Constraints\All;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Vinyett\Userbundle\Entity\Invitation;

class InviteController extends Controller
{
    
    /**
     * prepareAction function.
     * 
     * @access public
     */
    public function prepareAction()
    {
        $user = $this->get("security.context")->getToken()->getUser();
        $form = $this->getInviteForm($user->getTotalInvites());
    
        return $this->render('UserBundle:Invite:prepare.html.twig', array("form" => $form->createView()));
    }
    

    /**
     * Creates a form (with validation) for sending invites.
     * 
     * $emails should be an array of null values for the amount of fields
     * you want to be shown and validated UNLESS there is some reason to include a pre-existing
     * email address.
     *
     * @access protected
     * @param array $emails
     * @return Form
     */
    protected function getInviteForm($total_invites)
    {
        $emails = array();
        $i = 0;
        while($i < $total_invites) {
            if($i == 5)
            { 
                break;
            }
            $emails[$i] = null;
            $i++;
        }
    
        $form = $this->createFormBuilder(array("emails" => $emails), array(
                                            'csrf_protection' => false,
                                        ))
                     ->add("emails", "collection", array(
                        "type" => "email",
                        "options" => array(
                            "required" => false,
                            "error_bubbling" => true,
                            "attr" => array("class" => "text_input", "placeholder" => "name@email.com")
                            )
                        ))
                     ->getForm();
                     
        return $form;
    }
    
    
    /**
     * sendAction function.
     * 
     * @access public
     */
    public function sendAction() 
    { 
        $request = $this->getRequest();
        $user = $this->get("security.context")->getToken()->getUser();
        $form = $this->getInviteForm($user->getTotalInvites());
        $em = $this->getDoctrine()->getEntityManager();
        
        $form->bind($request);
        if(!$form->isValid())
        { 
        
            return new Response($form->getErrorsAsString());
            
        } else { 
            
            $form_data = $form->getData();
            $emails = array_filter($form_data['emails']);
            
            foreach($emails as $email) 
            { 
                $invite = new Invitation();
                $invite->setEmail($email);
                $invite->setSender($user);
                $em->persist($invite);
                
                $message = \Swift_Message::newInstance()
                                            ->setSubject("Hello from Vinyett!")
                                            ->setFrom(array('robo@vinyett.com' => "Vinyett"))
                                            ->setTo($invite->getEmail())
                                            ->setBody($this->renderView("UserBundle:Invite:invite.mail.html.twig", array("email" => $invite->getEmail(), "code" => $invite->getCode())), 'text/html')
                                            ->addPart($this->renderView("UserBundle:Invite:invite.mail.plain.twig", array("email" => $invite->getEmail(), "code" => $invite->getCode())), 'text/plain');
                
                $this->get('mailer')->send($message);
            }
            
            $em->flush();
            
            return new Response(json_encode(array("sent" => true)));
            
            
        }
    }
    
    
    
    
    
    
    
    
}
