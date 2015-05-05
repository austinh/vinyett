<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Vinyett\PhotoBundle\Entity\Photo;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;


/**
 *
 */
class UploaderController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        return $this->render('PhotoBundle:Uploader:index.html.twig');
    }
    
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function tempAction()
    {
    
        $photo = new Photo();
        $form = $this->createFormBuilder($photo)
                     ->add("file")
                     ->getForm();
        
        return $this->render('PhotoBundle:Uploader:temp.html.twig', array("form" => $form->createView()));
    }
    
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function processImageAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $photo = new Photo();
        $form = $this->createFormBuilder($photo)
                     ->add("file")
                     ->getForm();
    
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
            
                $photo->setOwner($this->get("security.context")->getToken()->getUser());

                $em->persist($photo);
                $em->flush(); //Add the object..
                
                //Add it's permissions, creating the ACL
                $aclProvider = $this->get('security.acl.provider');
                $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($photo));
                $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_OPERATOR);
                $aclProvider->updateAcl($acl);
                
                return $this->redirect($this->generateUrl("view_photo", array("username" => $user->getUsername(), "photo_id" => $photo->getId())));
            }
        }
        
        return $this->redirect($this->generateUrl("homepage"));
    }
    













    
    
}



