<?php

namespace Vinyett\UserBundle\Controller;

use Vinyett\UserBundle\Entity\User;
use Vinyett\ConnectBundle\Entity\Follow;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

class RegistrationController extends BaseController
{
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = false;// $this->container->getParameter('fos_user.registration.confirmation.enabled');
        
        $request = $this->container->get('request');
        $em = $this->container->get("doctrine.orm.entity_manager");
        $na = $this->container->get("notification.publisher");
            
        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            if($user->getInvitation()) { 
                if($user->getInvitation()->getSender()) { 
                    $follow = new Follow();
                    $follow->setActor($user);
                    $follow->setFollowing($user->getInvitation()->getSender());
                    $follow->setAffinity(10);
                    $follow->setWeight(array());
                    $em->persist($follow);
                    
                    $follow_back = new Follow();
                    $follow_back->setActor($user->getInvitation()->getSender());
                    $follow_back->setFollowing($user);
                    $follow_back->setAffinity(10);
                    $follow_back->setWeight(array());
                    $em->persist($follow_back);
                    
                    $em->flush();
                    
                    /* Handle notifications */
                    $manager = $na->createManager("friend.join");
                    $manager->from($user);
                    $manager->to($user->getInvitation()->getSender());
                    $na->publish($manager);
                }
            }

            $authUser = false;
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $authUser = true;
                $route = 'fos_user_registration_confirmed';
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }


        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
            'email' => $request->query->get("email"),
            'invitation' => $request->query->get("invitation")
        ));
    }
    
    
    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        return new RedirectResponse($this->container->get('router')->generate('homepage'));
    }

    
    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $response = new RedirectResponse($this->container->get('router')->generate("homepage"));
        $this->authenticateUser($user, $response);

        return $response;
    }
    
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}