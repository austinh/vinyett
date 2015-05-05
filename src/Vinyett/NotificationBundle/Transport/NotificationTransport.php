<?php

namespace Vinyett\NotificationBundle\Transport;

use Vinyett\NotificationBundle\Entity\Notification;

class NotificationTransport extends AbstractTransport
{ 
    
    /**
     * The call that should handle the NotifyObjects sent in the parameter.
     * Example: An Email Transport will email each notifyobject while a 
     * SMS transport will send a text message. 
     *
     * @access public
     * @abstract
     * @param mixed $notify_objects
     * @return void
     */
    public function process($notify_objects) 
    { 
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        
        $this->getContainer()->get('logger')->info("Notification pledged");
        
        foreach($notify_objects as $notify_object)
        {
            $notification = new Notification();
            $notification->setOwner($notify_object->getRecipient());
            $notification->setNotifyReference($notify_object); //For now Notifications only reference one Notify Object. Grouped Notifications in the future will utilze more than one of the same type and resources.
            $notification->setCreatedAt($notify_object->getCreatedAt());
            
            $em->persist($notification);
        }
    }
    

    /**
     * Should return the default method for deliver if no option is specified for
     * the user. 
     * 
     * @access public
     * @param mixed $event Name of the event
     * @return boolean
     */
    public function getDefaultDeliveryPreference($event)
    {
        return true; //Always send inner site notifications by default.
    }

}