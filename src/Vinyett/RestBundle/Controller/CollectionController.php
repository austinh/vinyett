<?php

namespace Vinyett\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller;
    
use Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\Security\Acl\Domain\ObjectIdentity,
    Symfony\Component\Security\Acl\Domain\UserSecurityIdentity,
    Symfony\Component\Security\Acl\Permission\MaskBuilder;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Routing\ClassResourceInterface;
    
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Controller\Annotations\RequestParam;
    
use Vinyett\PhotoBundle\Form\CollectionType;
use Vinyett\PhotoBundle\Entity\Photo;
use Vinyett\PhotoBundle\Entity\Collection;
use Vinyett\PhotoBundle\Entity\CollectionPhoto;
   
use FOS\RestBundle\Controller\Annotations\RouteResource;


class CollectionController extends FOSRestController
{
    /**
     * [POST] /rest/collections
     *
     * Used to create a new photo from the API.
     * 
     * @access public
     * @return View
     */
    public function postCollectionsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get("security.context");
        $user = $this->get("security.context")->getToken()->getUser();
        $request = $this->getRequest();
        
        $collection = new Collection();
        $collection->setOwner($user);
    
        $form = $this->createForm(new CollectionType(), $collection);
        $form->bind($this->bindRequestToForm($request, $form));
    
        if ($form->isValid()) {
            $view = $this->view();
            
            $photos = array_unique($request->request->get("photos"));
            
            for($i = 0; $i < count($photos); $i++)
            { 
                //$photos[$i]
                $collection_photo = new CollectionPhoto();
                $collection_photo->setPhoto($em->getReference("PhotoBundle:Photo", $photos[$i]));
                $collection_photo->setCollection($collection);
                $collection_photo->setPosition($i);
                
                $em->persist($collection_photo);
                
                $collection->AddCollectionPhoto($collection_photo);
            }
            
        
            $em->persist($collection);
            $em->flush();
            
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($collection);
            
            try {
                $acl = $aclProvider->findAcl($objectIdentity);
            } catch (\Symfony\Component\Security\Acl\Exception\Exception $e) {
                $acl = $aclProvider->createAcl($objectIdentity);
            }
            
            $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_OPERATOR);
            $aclProvider->updateAcl($acl);
    
            $view->setData($collection);
            $view->setStatusCode(201);
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        $view = View::create($form, 400);
        return $this->get('fos_rest.view_handler')->handle($view);   
    }
    
    /**
     * [PUT] /rest/collections/{$collection_id}/photos
     *
     * Takes the photos given and replaces them to the set (in the order provided!)
     * 
     * @access public
     * @param integer $collection_id
     * @return void
    
    public function putPhotosCollectionAction($collection_id) 
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get("security.context");
        $user = $this->get("security.context")->getToken()->getUser();
        $request = $this->getRequest();
        $view = View::create();
        
        $collection = $em->getRepository("PhotoBundle:Collection")->find($collection_id);
        
        if(empty($collection))
        {
            return new Response(null, 404);
        }
        if ($securityContext->isGranted('EDIT', $collection) === false)
        {
            return new Response(null, 401);
        }
        
        $qb = $em->createQueryBuilder();
        $qb->delete("PhotoBundle:CollectionPhoto", "cp")
           ->where("cp.collection = :collection")
           ->setParameter("collection", $collection);
        $qb->getQuery()->getResult();
        
        if($request->request->has("photos"))
        {
            foreach($request->get("photos") as $photo)
            { 
                $collection_photo = new CollectionPhoto();
                $collection_photo->setPhoto($em->getReference("PhotoBundle:Photo", $photo['photo']));
                $collection_photo->setCollection($collection);
                $collection_photo->setPosition($photo['position']);
                
                $em->persist($collection_photo);
                
                $collection->AddCollectionPhoto($collection_photo);
            }
        }
        
        $em->flush();
        
        return new Response(null, 204);
        
    } */
    
    
    /**
     * [POST] /rest/collections/{$collection_id}/photos
     *
     * Adds a photo to the collection
     * 
     * @access public
     * @param integer $collection_id
     * @return void
     */
    public function postPhotosCollectionAction($collection_id) 
    { 
        
    }
    
    
    /**
     * [GET] /rest/collections/{$collection_id}/options
     *
     * Returns the list of options and URLs to access said options.
     * 
     * @access public
     * @return View
     */
    public function optionsCollectionsAction($collection_id)
    {
        $view = $this->view();
        $view->setStatusCode(501); //Not Implemented, yet. When the API becomes open...
        
        return $this->get('fos_rest.view_handler')->handle($view);
    }
    
    
    /**
     * [GET] /rest/collections/{$collection_id}/photos
     *
     * Fetches the photos inside of a collection (and the data for the photos).
     * 
     * @access public
     * @param integer $collection_id
     * @return void
     */
    public function getCollectionPhotosAction($collection_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $collection = $em->getRepository("PhotoBundle:Collection")->find($collection_id);
        
        $view = $this->view();
        $view->setData($collection->getPhotos())
             ->setStatusCode(200);
        return $this->get('fos_rest.view_handler')->handle($view);
    }


    /**
     * [GET] /rest/collections
     *
     * Fetches a collection of collections (ha!) from the API.
     *
     * NOTE: The default is to fetch a users own photos.
     * 
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     * @QueryParam(name="for", default="me", description="Whose collection to grab.")
     */
    public function getCollectionsAction(ParamFetcher $paramFetcher)
    {
        $view = $this->view();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get("security.context")->getToken()->getUser();
        $page = $paramFetcher->get('page');
        $for = $paramFetcher->get('for');
        
        $view->setData($em->getRepository("PhotoBundle:Collection")->findByOwnerForRest(($for == "me"?$user->getId():$for)));
        $view->setStatusCode(200);
        return $this->get('fos_rest.view_handler')->handle($view);
    }


    /**
     * [GET] /rest/collections/{collection_id}
     *
     * Fetches a collection's information from the API.
     * 
     * @access public
     * @param mixed $photo_id
     * @return void
     */
    public function getCollectionAction($collection_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $collection = $em->getRepository("PhotoBundle:Collection")->findForRest($collection_id);
        
        $view = $this->view();
        $view->setData($collection)
             ->setStatusCode(200);
        return $this->get('fos_rest.view_handler')->handle($view);
        
    }
    

    /**
     * [PUT] /rest/collections/{collection_id}
     *
     * Saves the changes to a photo entity from the API.
     * 
     * @access public
     * @param mixed $photo_id
     * @return void
     */
    public function putCollectionAction($collection_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get('security.context');
        $request = $this->getRequest();
        
        $collection = $em->getRepository("PhotoBundle:Collection")->find($collection_id);
        
        if ($securityContext->isGranted('EDIT', $collection) === false)
        {
            $view = $this->view();
            $view->setStatusCode(401);
            
            return $this->get('fos_rest.view_handler')->handle($view);
        }
    
        $form = $this->createForm(new CollectionType(), $collection);
        $form->bind($this->bindRequestToForm($request, $form));
    
        if ($form->isValid()) {
            $view = $this->view();
        
            $photos = array_unique($request->request->get("photos"));
            
            $qb = $em->createQueryBuilder();
            $qb->delete("PhotoBundle:CollectionPhoto", "cp")
               ->where("cp.collection = :collection")
               ->setParameter("collection", $collection);
            $qb->getQuery()->getResult();
            
            for($i = 0; $i < count($photos); $i++)
            { 
                //$photos[$i]
                $collection_photo = new CollectionPhoto();
                $collection_photo->setPhoto($em->getReference("PhotoBundle:Photo", $photos[$i]));
                $collection_photo->setCollection($collection);
                $collection_photo->setPosition($i);
                
                $em->persist($collection_photo);
                
                $collection->AddCollectionPhoto($collection_photo);
            }
        
            $em->flush();
    
            $view->setData($collection);
            $view->setStatusCode(200);
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        $view = View::create($form, 400);
        return $this->get('fos_rest.view_handler')->handle($view);    
    }


    /**
     * [DELETE] /rest/collections/{collection_id}
     *
     * Deletes a photo...
     * 
     * @access public
     * @param interger $slug
     *
     * @return ViewHandler
     */
    public function deleteCollectionAction($collection_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get('security.context');
        $request = $this->getRequest();
    
        $collection = $em->getRepository("PhotoBundle:Collection")->find($collection_id);
        
        if ($securityContext->isGranted('DELETE', $collection) === false)
        {
            $view = $this->view();
            $view->setStatusCode(401)
                 ->setData($collection);
                 
            return $this->get('fos_rest.view_handler')->handle($view);
        }
        
        $qb = $em->createQueryBuilder();
        $qb->delete("PhotoBundle:CollectionPhoto", "cp")
           ->where("cp.collection = :collection")
           ->setParameter("collection", $collection);
        $qb->getQuery()->getResult();
        
        $em->remove($collection);
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