<?php
namespace Vinyett\NotificationBundle\Extension;
 
class NotificationTwigExtension extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'render_notification_list'    => new \Twig_Function_Method($this, 'twig_notification_render_tag', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
  public function getName()
  {
      return 'twig_notification_render_tag';
  }
  
  function twig_notification_render_tag($notification_manager)
  {
      return $notification_manager->renderNotificationList();
  }

}