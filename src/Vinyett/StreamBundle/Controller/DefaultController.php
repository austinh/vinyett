<?php

namespace Vinyett\StreamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('StreamBundle:Default:index.html.twig', array('name' => $name));
    }
}
