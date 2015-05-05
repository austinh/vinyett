<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('PhotoBundle:Default:index.html.twig', array('name' => $name));
    }
}
