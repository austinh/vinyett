<?php

namespace Vinyett\ConnectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Vinyett\ConnectBundle\Entity\Follow;

use JMS\SecurityExtraBundle\Annotation\Secure;


class SearchController extends Controller
{

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function findAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        
        $search_query = $request->query->get("term");

        if(!empty($search_query))
        {
            $qb->select(array('u'))
               ->from('UserBundle:User', 'u')
               ->where(
                   $qb->expr()->like('u.username', ':term')
               )
               ->orderBy('u.username', 'ASC')
               ->setParameter("term", '%'.$search_query.'%')
               ->setMaxResults(10);

            $users = $qb->getQuery()->getResult();
        } else {
            $users = array();
        }
               
        return $this->render('ConnectBundle:Search:search.html.twig', array("users" => $users, "term" => $search_query));
    }
}