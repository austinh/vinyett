<?php

namespace Vinyett\NotificationBundle\Transport;

class EmailTransport extends AbstractTransport
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
        //$em = $this->getContainer()->getDoctrine()->getEntityManager();
        
        $this->getContainer()->get('logger')->info("Email pledged");
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