<?php

namespace Vinyett\StreamBundle\Feed\Constructors;

class PhotoFavoriteConstructor extends Constructor
{

  /*
   * render()
   *
   * Renders the twig template, then returns it
   * stored in your activity object
   *
   * @return Activity
   */
    public function render() 
    {
      
      $em = $this->getEntityManager();
      $activity = $this->getActivity();
      $aw = $this->getActivityCacheWorker();
      
      if(!$activity) 
      { 
          throw new \Exception("No activity object to render! Set an activity on the constructor before rendering.");
      }            
      
      $data = unserialize($activity->getData());
      
      $comments = $aw->fetchLiveDataFor("comments", $activity->getPhoto()->getId());
      
      return $this->getTemplating()->render("StreamBundle:Feed:photo.favorite.html.twig", array("comments" => $comments, "activity" => $activity, "photo" => $data["Vinyett\PhotoBundle\Entity\Photo"]));
      
    }

}