<?php

namespace Vinyett\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception\HttpException;
    
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Routing\ClassResourceInterface;
    
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Controller\Annotations\RequestParam;
    
use Vinyett\ConnectBundle\Form\FollowType;
use Vinyett\ConnectBundle\Entity\Follow;
   
class FriendController extends FOSRestController
{
    /**
     * [GET] /rest/friends/{friend_id}/options
     *
     * Returns the list of options and URLs to access said options.
     * 
     * @access public
     * @return void
     */
    public function optionsFriendsAction($friend_relaionship_id)
    {
        $view = $this->view();
        $view->setStatusCode(501); //Not Implemented, yet. When the API becomes open...
        
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    

    /**
     * [GET] /rest/friends
     *
     * Fetches all of the users a person follows
     *
     * NOTE: The default is to fetch a users own photos.
     * 
     * @QueryParam(name="offset", requirements="\d+", default="0", description="Offset to fetch results from.")
     * @QueryParam(name="for", default="self", description="Whose thoughrs to grab.")
     */
    public function getFriendsAction(ParamFetcher $paramFetcher)
    {
        $view = $this->view();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $offset = $paramFetcher->get('offset');
        $for = ($paramFetcher->get('for')=='self'?$user->getId():$paramFetcher->get('for'));
        
        $data = $em->getRepository("ConnectBundle:Follow")->findFollowing($for);
        
        $view->setData($data)
             ->setStatusCode(200);
        
        return $this->get('fos_rest.view_handler')->handle($view);
    } 


    /**
     * [POST] /rest/friends 
     *
     * Used to have the current session user to follow another
     * 
     * @access public
     * @return void
     */
    public function postFriendsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $na = $this->get("notification.publisher");
        $am = $this->get('spy_timeline.action_manager');
        $securityContext = $this->get("security.context");
        $user = $this->get("security.context")->getToken()->getUser();
        $request = $this->getRequest();
        
        $follow = new Follow();
        $follow->setActor($user);
        $follow->setIsInPhotoFeed(true);
    
        $form = $this->createForm(new FollowType(), $follow);
        $form->bind($this->bindRequestToForm($request, $form));
    
        if ($form->isValid()) {
            $view = $this->view();
        
            $em->persist($follow);
            $em->flush();
            
            /* Handle notifications */
            $manager = $na->createManager("follow.add");
            $manager->from($user);
            $manager->to($follow->getFollowing());
            $na->publish($manager);
        
            /* Add action */
            $subject = $am->findOrCreateComponent($user);
            $dc = $am->findOrCreateComponent($follow->getFollowing());
            $action = $am->create($subject, 'started following', array('directComplement' => $dc));
            $am->updateAction($action);
    
            /* Finish by adding to the view */
            $view->setData($follow);
            $view->setStatusCode(201);
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        
        throw new HttpException(400, "Invalid parameter data, check your parameters. (Hint: ".$form->getErrorsAsString().")");  
    }


    /**
     * [GET] /rest/friends/{friend_id}?aspect={aspect}
     *
     * Fetches a relationship where the viewer (or aspect) is following 
     * friend_id.
     * 
     * @QueryParam(name="aspect", default="self", description="From whose aspect are we finding this relationship")
     * @access public
     * @param mixed $photo_id
     * @return void
     */
    public function getFriendAction(ParamFetcher $paramFetcher, $friend_relaionship_id)
    {
        $view = $this->view();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $aspect_id = ($paramFetcher->get('aspect')=='self'?$user->getId():$paramFetcher->get('aspect'));
        
        $follow = $em->getRepository("ConnectBundle:Follow")->findOneBy($friend_relaionship_id);
        if($follow)
        {
            $view->setData($follow)
                 ->setStatusCode(200);
        } else { 
            throw new HttpException(404, "No relationship exists.");
        }
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    

    /**
     * [PUT] /rest/photos/{friend_id}
     *
     * Saves the changes to a photo entity from the API.
     * 
     * @access public
     * @param mixed $photo_id
     * @return void
     */
    public function putFriendAction($friend_relaionship_id)
    {                
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get("security.context");
        $user = $this->get("security.context")->getToken()->getUser();
        $request = $this->getRequest();
        
        $follow = $em->getRepository("ConnectBundle:Follow")->find($friend_relaionship_id);
        $follow->setIsFriend((bool) $request->get("is_friend"));
        $follow->setIsFamily((bool) $request->get("is_family"));
        $follow->setIsInPhotofeed((bool) $request->get("is_in_photofeed"));
        if($user->getId() != $follow->getActor()->getId())
        {
            throw new HttpException(401, "Cannot change relationships you are not the actor of.");
        }
    
        $form = $this->createForm(new FollowType(), $follow);
        $form->bind($this->bindRequestToForm($request, $form));
    
        if ($form->isValid()) {
            $view = $this->view();
            
            $em->flush();
    
            $view->setData($follow);
            $view->setStatusCode(200);
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        
        throw new HttpException(400, "Invalid parameter data, check your parameters. (Hint: ".$form->getErrorsAsString().")");  
        
        return $this->get('fos_rest.view_handler')->handle($view);
    }


    /**
     * [DELETE] /rest/photos/{photo_id}
     *
     * Deletes a photo...
     * 
     * @access public
     * @param interger $slug
     *
     * @return ViewHandler
     */
    public function deleteFriendAction($friend_relaionship_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get('security.context');
        $request = $this->getRequest();
    
        $follow = $em->getRepository("ConnectBundle:Follow")->findOneBy(array("id" => $friend_relaionship_id));
        if($securityContext->getToken()->getUser()->getId() != $follow->getActor()->getId())
        {
            throw new HttpException(401, "Cannot remove relationships you are not the actor of.");
        }
        
        $em->remove($follow);
        $em->flush();
        
        $view = $this->view();
        $view->setStatusCode(204);
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    
    
    /**
     * Takes the values of a form and finds the matches in the 
     * request object.
     * 
     * @access protected
     * @param Request $request
     * @param Form $form
     *
     * @return array
     */
    protected function bindRequestToForm($request, $form)
    { 
        $children = $form->getChildren(); 
        $data = $request->request->all();
        $data = array_intersect_key($data, $children);
        
        return $data;
    }
    
    
}