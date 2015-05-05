<?php

namespace Vinyett\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationPreference
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\NotificationBundle\Entity\NotificationPreferenceRepository")
 */
class NotificationPreference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=100)
     */
    private $event;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="transport", type="string", length=100)
     */
    private $transport;

    /**
     * @var boolean
     *
     * @ORM\Column(name="value", type="boolean")
     */
    private $value;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set event
     *
     * @param string $event
     * @return NotificationPreferences
     */
    public function setEvent($event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return string 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return NotificationPreferences
     */
    public function setMethod($method)
    {
        $this->method = $method;
    
        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set transport
     *
     * @param string $transport
     * @return NotificationPreferences
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
    
        return $this;
    }

    /**
     * Get transport
     *
     * @return string 
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set value
     *
     * @param boolean $value
     * @return NotificationPreferences
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return boolean 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set owner
     *
     * @param \Vinyett\UserBundle\Entity\User $owner
     * @return NotificationPreference
     */
    public function setOwner(\Vinyett\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;
    
        return $this;
    }

    /**
     * Get owner
     *
     * @return \Vinyett\UserBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }
}