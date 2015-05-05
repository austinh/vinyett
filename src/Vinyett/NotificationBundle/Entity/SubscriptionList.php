<?php

namespace Vinyett\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SubscriptionList
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\NotificationBundle\Entity\SubscriptionListRepository")
 */
class SubscriptionList
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
     * @ORM\Column(name="object_identity", type="string", length=255)
     */
    private $object_identity;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=255)
     */
    private $event;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="\Vinyett\UserBundle\Entity\User", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="Subscribers",
     *      joinColumns={@ORM\JoinColumn(name="subscription_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="subscriber_id", referencedColumnName="id")}
     *      )
     */
    private $subscribers;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;


    public function __construct() {
        $this->subscribers = new ArrayCollection();
        $this->created_at = new \DateTime();
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
     * Set object_identity
     *
     * @param string $objectIdentity
     * @return SubscriptionList
     */
    public function setObjectIdentity($objectIdentity)
    {
        $this->object_identity = $objectIdentity;
    
        return $this;
    }

    /**
     * Get object_identity
     *
     * @return string 
     */
    public function getObjectIdentity()
    {
        return $this->object_identity;
    }

    /**
     * Set event
     *
     * @param string $event
     * @return SubscriptionList
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
     * Set active
     *
     * @param boolean $active
     * @return SubscriptionList
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return SubscriptionList
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
     * Add subscribers
     *
     * @param \Vinyett\NotificationBundle\Entity\User $subscribers
     * @return SubscriptionList
     */
    public function addSubscriber(\Vinyett\UserBundle\Entity\User $subscriber)
    {
        if(!$this->subscribers->contains($subscriber))
        {
            $this->subscribers[] = $subscriber;
        }
    
        return $this;
    }

    /**
     * Remove subscribers
     *
     * @param \Vinyett\NotificationBundle\Entity\User $subscribers
     */
    public function removeSubscriber(\Vinyett\UserBundle\Entity\User $subscribers)
    {
        $this->subscribers->removeElement($subscribers);
    }

    /**
     * Get subscribers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }
}