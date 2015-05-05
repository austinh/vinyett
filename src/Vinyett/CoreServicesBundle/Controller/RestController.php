<?php

namespace Vinyett\CoreServicesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/*use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface; */  

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

use \HTMLPurifier_Config;
use \HTMLPurifier;

class RestController extends Controller
{
    
    public function photoAction($photo)
    {
        $request = $this->getRequest();
        $user = $this->get("security.context")->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if ($request->getMethod() == "GET")
        {
            $photos = $em->getRepository("PhotoBundle:Photo")->findBy(array("owner" => $user->getId()));
            
            $ap = array();
            foreach($photos as $photo)
            {
                $ap[] = $photo->toAjaxArray();
            }
        
            return $this->render('CoreServicesBundle:Rest:response.html.twig', array("response" => json_encode($ap)));
        }
        
        if ($request->getMethod() == "PUT")
        { 
            $photo = $em->getRepository("PhotoBundle:Photo")->findOne($photo);
        
            $logger = $this->get("logger");
            $logger->info($request->request->get('param'));
            
            return $this->render('CoreServicesBundle:Rest:response.html.twig', array("response" => json_encode($ap)));
        }
    }
}
