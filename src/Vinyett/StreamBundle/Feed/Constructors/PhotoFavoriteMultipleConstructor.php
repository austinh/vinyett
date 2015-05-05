<?php

namespace Vinyett\StreamBundle\Feed\Constructors;

use Vinyett\StreamBundle\Entity\NewsStoryCache;

class PhotoFavoriteMultipleConstructor extends MultipleConstructor
{
    public function synthesize()
    {
        $data = unserialize($this->first()->getActivity()->getData());
        $photo = $data["Vinyett\PhotoBundle\Entity\Photo"];
        $em = $this->getEntityManager();
    
        $cache = new NewsStoryCache();
        $cache->setUser($this->first()->getUser());
        //$cache->setData($this->storyActivitiesToData());
        $cache->setPhoto($em->getReference("PhotoBundle:Photo", $photo->getId()));
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
      
      $comments = array();
      $actors = array();
      $recent = null;
      foreach($this->getActivities() as $activity) 
      {   
          $actors[$activity->getActor()->getId()] = $activity->getActor();
          $recent = $activity;
      }
      
      $comments = $aw->fetchLiveDataFor("comments", $activity->getPhoto()->getId());
      
      return $this->getTemplating()->render("StreamBundle:Feed:photo.favorite.multiple.html.twig", array("comments" => $comments, "actors" => $actors, "recent_activity" => $recent, "activities" => $this->getActivities(), "photo" => $data["Vinyett\PhotoBundle\Entity\Photo"]));

    }
}