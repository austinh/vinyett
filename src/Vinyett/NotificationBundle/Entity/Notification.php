<?php

namespace Vinyett\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vinyett\NotificationBundle\Entity\Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\NotificationBundle\Entity\NotificationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Notification
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\NotificationBundle\Entity\NotifyObject")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $notify_reference;

    /**
     * @var boolean $is_new
     *
     * @ORM\Column(name="is_new", type="boolean")
     */
    private $is_new = true;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;


		/**
     * Constructs a new instance of Notification
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->is_new = true;
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
     * Set user_id
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param text $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get body
     *
     * @return text 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set image
     *
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set type
     *
     * @param smallint $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return smallint 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set out_id
     *
     * @param integer $outId
     */
    public function setOutId($outId)
    {
        $this->out_id = $outId;
    }

    /**
     * Get out_id
     *
     * @return integer 
     */
    public function getOutId()
    {
        return $this->out_id;
    }

    /**
     * Set is_new
     *
     * @param boolean $isNew
     */
    public function setIsNew($isNew)
    {
        $this->is_new = $isNew;
    }

    /**
     * Get is_new
     *
     * @return boolean 
     */
    public function getIsNew()
    {
        return $this->is_new;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function setCurrentUpdated()
    {
        $this->updated_at = new \DateTime();
    } 

    /**
     * Set user
     *
     * @param Vinyett\UserBundle\Entity\User $user
     */
    public function setUser(\Vinyett\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set owner
     *
     * @param \Vinyett\UserBundle\Entity\User $owner
     * @return Notification
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

    /**
     * Set notify_reference
     *
     * @param \Vinyett\NoticationBundle\Entity\NotifyObject $notifyReference
     * @return Notification
     */
    public function setNotifyReference(\Vinyett\NotificationBundle\Entity\NotifyObject $notifyReference = null)
    {
        $this->notify_reference = $notifyReference;
    
        return $this;
    }

    /**
     * Get notify_reference
     *
     * @return \Vinyett\NoticationBundle\Entity\NotifyObject 
     */
    public function getNotifyReference()
    {
        return $this->notify_reference;
    }
}