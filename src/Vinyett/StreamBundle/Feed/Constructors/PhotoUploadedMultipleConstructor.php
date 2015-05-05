<?php

namespace Vinyett\StreamBundle\Feed\Constructors;

use Vinyett\StreamBundle\Entity\NewsStoryCache;

class PhotoUploadedMultipleConstructor extends MultipleConstructor
{
    public function synthesize()
    {
        $em = $this->getEntityManager();
        $data = unserialize($this->first()->getActivity()->getData());
        $photo = $data["Vinyett\PhotoBundle\Entity\Photo"];
    
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
      $aw = $this->getActivityCacheWorker();

      //$data = unserialize($activity->getData());
            
      $photos = array();
      foreach($this->getActivities() as $activity) 
      {   
          $actors[$activity->getActor()->getId()] = $activity->getActor();
          $photos[] = $activity->getPhoto();
      }
      
      return $this->getTemplating()->render("StreamBundle:Feed:photo.uploaded.multiple.html.twig", array("actors" => $actors, "photos" => $photos));

    }
}