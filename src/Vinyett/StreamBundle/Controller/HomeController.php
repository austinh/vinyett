<?php

namespace Vinyett\StreamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    
    /**
     * @Route("/home", name="home") 
     * @Secure("ROLE_USER")
     */
    public function indexAction()
    {
        //if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        return $this->redirect("feed"); //Everyone gets the timeline view
        //}
    
        //Services
        $aggregator = $this->get("aggregator");
        $feed = $this->get("feed");
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get("security.context");
        $aclProvider = $this->get("security.acl.provider");
        $user = $this->get("security.context")->getToken()->getUser();
    
        //Statistics
        $followed_count = $em->getRepository("ConnectBundle:Follow")->findFollowedByCount($user);
        $following_count = $em->getRepository("ConnectBundle:Follow")->findFollowingCount($user);
        
        /* hmm */
        
        return $this->render('StreamBundle:Home:index.html.twig', array("following_count" => $following_count, "followed_count" => $followed_count));//, array("feed" => $newsfeed->buildStoryboard()));
    }

    /**
     * @Route("/feed/complete_intro", name="doCompleteIntro")
     * @Secure("ROLE_USER")
     */
    public function completeIntroAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.context")->getToken()->getUser();

        $user->setHasCompletedIntro(true);
        $em->flush();

        return new Response(json_encode(array("completed" => true)));
    }

    /**
     * @Route("/feed", name="timeline_home") 
     * @Secure("ROLE_USER")
     */
    public function timelineAction()
    {
        //Services
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->get("security.context");
        $actionManager   = $this->get('spy_timeline.action_manager');
        $timelineManager = $this->get('spy_timeline.timeline_manager');
        $aclProvider = $this->get("security.acl.provider");
        $user = $securityContext->getToken()->getUser();
    
        //Statistics
        $followed_count = $em->getRepository("ConnectBundle:Follow")->findFollowedByCount($user);
        $following_count = $em->getRepository("ConnectBundle:Follow")->findFollowingCount($user);
    
        //Gather yourself as a component and fine your timeline
        $subject = $actionManager->findOrCreateComponent($user);
        $blog_post = $em->getRepository("BlogBundle:Post")->getMostRecentHomepagePost();
        $timeline = $timelineManager->getTimeline($subject, array('page' => 1, 'max_per_page' => '20', 'paginate' => true));
    
    
        return $this->render("StreamBundle:Home:timeline.html.twig", array("blog_post" => $blog_post, "timeline" => $timeline, "following_count" => $following_count, "followed_count" => $followed_count));
    }
}
