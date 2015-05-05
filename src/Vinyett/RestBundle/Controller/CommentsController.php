<?php

namespace Vinyett\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller;
    
use Doctrine\ORM\Query,
    Doctrine\ORM\Query\Expr,
    Doctrine\ORM\Query\ResultSetMapping;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Routing\ClassResourceInterface;
    
use FOS\RestBundle\Request\ParamFetcher,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Controller\Annotations\RequestParam;
    
use Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\Security\Acl\Domain\ObjectIdentity,
    Symfony\Component\Security\Acl\Domain\UserSecurityIdentity,
    Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity,
    Symfony\Component\Security\Acl\Permission\MaskBuilder;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Vinyett\PhotoBundle\Entity\PhotoComment,
    Vinyett\PhotoBundle\Form\PhotoCommentType;
   
class CommentsController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [POST] /rest/photos/comments
     *
     * Used to create a new photo from the API.
     * 
     * @access public
     * @return View
     */
    public function postAction($photo_id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $logger = $this->get("logger");
        $photo = $this->getDoctrine()->getRepository("PhotoBundle:Photo")->find($photo_id);
        
        if(!$photo)
        {
            return new Response(null, 404);
        }
        
        $am = $this->get("activity.manager");
        $afm = $this->get("affinity.manager");
        $na = $this->get("notification.publisher");
        $am = $this->get('spy_timeline.action_manager');

        $aclProvider = $this->get('security.acl.provider');

        $user = $this->get("security.context")->getToken()->getUser();
        $securityContext = $this->get('security.context');

        $comment = new PhotoComment();
        $comment->setOwner($user);
        $comment->setPhoto($photo);
    
        $form = $this->createForm(new PhotoCommentType(), $comment);
        $form->bind($this->bindRequestToForm($request, $form));
    
        if ($form->isValid()) {
            $view = $this->view();
        
            $user->increaseCommentCount();
        
            $em->persist($comment);
            $em->flush();
            
            //ACL
            $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($comment));
            $commenter_identity = UserSecurityIdentity::fromAccount($user);
            $photo_owner_identity = UserSecurityIdentity::fromAccount($photo->getOwner()); //The owner is also an operator of this comment...
            $admin_identity = new RoleSecurityIdentity("ROLE_ADMIN");
    
            $acl->insertObjectAce($commenter_identity, MaskBuilder::MASK_OPERATOR);
            $acl->insertObjectAce($photo_owner_identity, MaskBuilder::MASK_OPERATOR);
            $acl->insertObjectAce($admin_identity, MaskBuilder::MASK_OWNER);
            
            $aclProvider->updateAcl($acl);
            
            /* Affinity adjustment */
            $afm->adjustAffinity($photo->getOwner()->getId(), 9);
            
            /* Handle notifications */
            $manager = $na->createManager("photo.comment");
    
            $subscriptionlist = $manager->loadOrCreateSubscriberList($photo);
            $subscriptionlist->addSubscriber($photo->getOwner()); 
            $subscriptionlist->addSubscriber($user); 
            
            $manager->from($user);
            $manager->toAll($subscriptionlist->getSubscribers());
            $manager->addResources(array("photo" => $photo, "comment" => $comment));
            
            $na->publish($manager);
            
            /* Add action */
            $subject = $am->findOrCreateComponent($user);
            $dc = $am->findOrCreateComponent($photo);
            $action = $am->create($subject, 'commented on', array('directComplement' => $dc));
            $action->setDuplicateKey($user."-commentedon-".$photo->getId());
            $am->updateAction($action);
            
            $possible_permissions = array("EDIT", "DELETE", "OWNER");
            $accepted_permissions = array();
            foreach($possible_permissions as $permission)
            { 
                if($securityContext->isGranted($permission, $comment) === true)
                { 
                    $accepted_permissions[] = $permission;
                }
                $comment->setOptions($accepted_permissions);
            }
    
            $view->setData($comment);
            $view->setStatusCode(201);
            $view->setFormat("json"); //WHENEVER WE open the API we'll have to fix this
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        
        $view = View::create($form, 400);
        $view->setFormat("json"); //WHENEVER WE open the API we'll have to fix this
        return $this->get('fos_rest.view_handler')->handle($view);   
    }

    /**
     * [GET] /rest/photos/{{ photo_id }}/comments
     *
     * Fetches a set of comments for the given photo.
     *   
     * @access public
     * @param interger $photo_id
     * @param interger $id
     *
     * @return ViewHandler
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     */
    public function getAction($photo_id)
    {
    
        $view = $this->view();
        $em = $this->getDoctrine()->getEntityManager();
        $aclProvider = $this->get('security.acl.provider');
        $sc = $this->get("security.context");
        
        $data = $em->getRepository("PhotoBundle:PhotoComment")->findByPhotoForRest($em->getReference("PhotoBundle:Photo", $photo_id));
        
        $oids = array();
        foreach ($data as $comment) {
            $oid = ObjectIdentity::fromDomainObject($comment);
            $oids[] = $oid;
        }
        $aclProvider->findAcls($oids);
        
        $view->setData($this->setOptions($data, $sc));
        
        $view->setStatusCode(200); //Not Implemented, yet. When the API becomes open...
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    
    /**
     * Used to set options on a collection of comments.
     */
    public function setOptions($comments, $securityContext) 
    { 
        $possible_permissions = array("EDIT", "DELETE", "OWNER"); //Possible permissions only test against these permissions...
        foreach($comments as $comment) 
        { 
            $accepted_permissions = array();
            foreach($possible_permissions as $permission)
            { 
                if($securityContext->isGranted($permission, $comment) === true)
                { 
                    $accepted_permissions[] = $permission;
                }
                $comment->setOptions($accepted_permissions);
            }
        }
        return $comments;  
    }
    
    public function getCommentAction($slug, $id)
    {
        throw new HttpException(501, "Method not implimented, yet");
        
    } // "get_user_comment"    [GET] /photos/{slug}/comments/{id}

    /**
     * [DELETE] /rest/photos/{{ photo_id }}/comments/{{id}}
     *
     * Deletes a photo from the site. 
     *
     * Todo: Support deletion of timeline and action.     
     *
     * @access public
     * @param interger $photo_id
     * @param interger $id
     *
     * @return ViewHandler
     */
    public function deleteAction($photo_id, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $comment = $em->getRepository("PhotoBundle:PhotoComment")->find($id);
        
        if ($securityContext->isGranted('DELETE', $comment) === false)
        {
            $view = $this->view();
            $view->setStatusCode(401)
                 ->setData($comment); 
                 
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        
        $aclProvider = $this->get('security.acl.provider');
        $acl = $aclProvider->deleteAcl(ObjectIdentity::fromDomainObject($comment));
        
        $user->decreaseCommentCount();
        
        $em->remove($comment);
        $em->flush();
        
        $view = $this->view();
        $view->setStatusCode(204);
        $view->setData(null);
        
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
    