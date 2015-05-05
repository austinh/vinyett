<?php

namespace Vinyett\StreamBundle\Feed\Constructors;

class PhotoProfiledConstructor extends Constructor
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
      
      if(!$activity) 
      { 
          throw new \Exception("No activity object to render! Set an activity on the constructor before rendering.");
      }
      
      $data = unserialize($activity->getData());
      
      return $this->getTemplating()->render("StreamBundle:Feed:photo.profiled.html.twig", array("activity" => $activity, "photo" => $data["Vinyett\PhotoBundle\Entity\Photo"]));
      
    }

}