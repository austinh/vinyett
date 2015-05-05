<?php

namespace Vinyett\CoreServicesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        $photo = $this->getDoctrine()->getRepository("PhotoBundle:Photo")->find($name);
    
        $user = $this->get("security.context")->getToken()->getUser();
    
        //adds owner permissions to photo...
        $aclProvider = $this->get('security.acl.provider');
        $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($photo));
        $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl);
    
        return $this->render('CoreServicesBundle:Default:index.html.twig');
    }
}
