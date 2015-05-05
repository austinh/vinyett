<?php

namespace Vinyett\NotificationBundle\Transport;

abstract class AbstractTransport 
{ 
    
    protected $container;
    
    /**
     * Constructs the transport. Because transports can deliver
     * notifications through a wide variety of methods, it's easiest to
     * just pass the entire constructor so we can access anything we need.
     *
     * It's important to call the parent constructor with the correct args 
     * if extended as it does some setup.
     * 
     * @access public
     * @param mixed $container
     * @return AbstractTransport
     */
    public function __construct($container)
    {
        $this->container = $container;

        return $this;
    }
    
    public function getContainer()
    { 
        return $this->container;
    }

    
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
    abstract public function process($notify_objects);
    

    /**
     * Should return the default method for deliver if no option is specified for
     * the user. You are given access to the event to delegate between specific event
     * defaults. 
     *
     * This will probably be moved into configuation files later on.
     * 
     * @access public
     * @abstract
     * @param mixed $event Name of the event
     * @return boolean
     */
    abstract public function getDefaultDeliveryPreference($event);

}