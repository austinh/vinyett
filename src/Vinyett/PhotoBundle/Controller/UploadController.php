<?php

namespace Vinyett\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Vinyett\PhotoBundle\Entity\Photo;


class UploadController extends Controller
{
    /**
     * Returns pagelets for the photo ajax extension
     *
     * @access public
     * @return void
     */
    public function ajaxPageletAction()
    {
        $request = $this->getRequest();
        $pages = explode(",", $request->query->get("templates"));
        return $this->determinePagelets($pages);
    }


    /**
     * Determines the pagelet to load the page parameter given
     *
     * @access public
     * @param mixed $page
     * @return void
     */
    public function determinePagelets($pages)
    {   
        $templates = array();
        
        foreach($pages as $page) { 
            $templates[] = array("name" => $page, "template" => $this->renderView("PhotoBundle:Pagelet:".$page.".pagelet.html.twig"));
        }
        
        return new Response(json_encode($templates));
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    { 
        if ($this->container->has('profiler'))
        {
            $this->container->get('profiler')->disable();
        }
        return $this->render("PhotoBundle:Upload:index.html.twig");
    }

}