<?php

namespace Vinyett\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Secure("ROLE_USER")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
}
