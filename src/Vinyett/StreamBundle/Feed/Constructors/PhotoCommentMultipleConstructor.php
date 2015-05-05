<?php

namespace Vinyett\StreamBundle\Feed\Constructors;

use Vinyett\StreamBundle\Entity\NewsStoryCache;

class PhotoCommentMultipleConstructor extends MultipleConstructor
{
    public function synthesize()
    {
        $em = $this->getEntityManager();
        $cache = new NewsStoryCache();
        
        $cache->setUser($this->first()->getUser());
        //$cache->setData($this->storyActivitiesToData());
        $cache->setPhoto($em->getReference("PhotoBundle:Photo", $this->first()->getActivity()->getPhoto()->getId()));
        $cache->setHtml($this->render());
        $cache->setEdge($this->averageEdges());
        $cache->setActivityCreatedAt($this->first()->getActivityCreatedAt());

        return $cache;
    }

    public function render()
    {
        $em = $this->getEntityManager();
        $activity = $this->first()->getActivity();
        $aw = $this->getActivityCacheWorker();

        $data = unserialize($activity->getData());

        $actors = array();
        foreach($this->getActivities() as $activity)
        {
            $adata = unserialize($activity->getData());
            $actors[$activity->getActor()->getId()] = $activity->getActor();
        }
        
        $comments = $aw->fetchLiveDataFor("comments", $activity->getPhoto()->getId());
         
        return $this->getTemplating()->render("StreamBundle:Feed:photo.comment.multiple.html.twig", array("actors" => $actors, "activities" => $this->getActivities(), "photo" => $data["Vinyett\PhotoBundle\Entity\Photo"], "comments" => $comments));
    }
}