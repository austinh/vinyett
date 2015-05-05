<?php
namespace Vinyett\NotificationBundle\Entity;

/**
 * NotificationObjectIdentity interface.
 *
 * Requires an entity to define it's type to be used by the 
 * Notification framework
 */
interface NotificationObjectIdentity
{ 
    public function getObjectType();
}