<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Vinyett\StreamBundle\Entity\Activity;

use Vinyett\PhotoBundle\Entity\Photo;
use Vinyett\PhotoBundle\Entity\Collection;
use Vinyett\PhotoBundle\Entity\Favorite;
use Vinyett\PhotoBundle\Entity\PhotoComment;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;


class CollectionController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function showAction() 
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $collection = $em->getRepository("PhotoBundle:Collection")->find($request->get("collection_id"));
    
        return $this->render("PhotoBundle:Collection:index.html.twig", array("collection" => $collection));
    }
}