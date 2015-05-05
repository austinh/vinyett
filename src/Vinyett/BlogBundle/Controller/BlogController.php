<?php

namespace Vinyett\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BlogController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()
                 ->select("b")
                 ->from("BlogBundle:Post", "b")
                 ->orderBy("b.created_at", "DESC")
                 ->where("b.is_public = true")
                 ->setMaxResults(10);
        $posts = $qb->getQuery()->getResult();

        return $this->render('BlogBundle:Default:index.html.twig', array("posts" => $posts));
    }
}
