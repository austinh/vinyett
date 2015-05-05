<?php

namespace Vinyett\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotifyObject
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\NotificationBundle\Entity\NotifyObjectRepository")
 */
class NotifyObject
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
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $recipient;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="external_references", type="text", nullable=true)
     */
    private $external_references;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=255)
     */
    private $event;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime")
     */
    private $sent_at;

    
    public function __construct()
    { 
        $this->created_at = new \DateTime();
        $this->sent_at = new \DateTime();
    }
    
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
     * Set recipient
     *
     * @param string $recipient
     * @return NotifyObject
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    
        return $this;
    }

    /**
     * Get recipient
     *
     * @return string 
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set sender
     *
     * @param string $sender
     * @return NotifyObject
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    
        return $this;
    }

    /**
     * Get sender
     *
     * @return string 
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set external_references
     *
     * @param string $externalReferences
     * @return NotifyObject
     */
    public function setExternalReferences($externalReferences)
    {
        $this->external_references = $externalReferences;
    
        return $this;
    }

    /**
     * Get external_references
     *
     * @return string 
     */
    public function getExternalReferences()
    {
        return $this->external_references;
    }

    /**
     * Set event
     *
     * @param string $event
     * @return NotifyObject
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return NotifyObject
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set sent_at
     *
     * @param \DateTime $sentAt
     * @return NotifyObject
     */
    public function setSentAt($sentAt)
    {
        $this->sent_at = $sentAt;
    
        return $this;
    }

    /**
     * Get sent_at
     *
     * @return \DateTime 
     */
    public function getSentAt()
    {
        return $this->sent_at;
    }
}