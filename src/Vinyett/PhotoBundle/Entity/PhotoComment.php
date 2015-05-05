<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use JMS\SerializerBundle\Annotation\Expose;
use JMS\SerializerBundle\Annotation\PreSerialize;

/**
 * Vinyett\PhotoBundle\Entity\PhotoComment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\PhotoBundle\Entity\PhotoCommentRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class PhotoComment
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *     
     * @Expose
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     * @Expose 
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\PhotoBundle\Entity\Photo", inversedBy="comments")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id", onDelete="CASCADE")
     * @Expose
     */
    private $photo;

    /**
     * @var string $ip_address
     *
     * @ORM\Column(name="ip_address", type="string", length=50, nullable=true)
     */
    private $ip_address;

    /**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text")
     * @Expose
     */
    private $content;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Expose
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;
    
    
    /**
     * @Expose
     */
    protected $options = array();
    
        
    /**
     * @PreSerialize
     */
    public function preSerialize() { 
        $this->photo = $this->getPhoto()->getId();
    }
    
    
    /**
     * Constructor
	 *
     * @return null
     */
    public function __construct() 
    { 
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }
    
    /**
     * Sets the current time for the updated_at field when updated.
     *  
     * @ORM\PreUpdate
     */
    public function onUpdate() 
    { 
        $this->updated_at = new \DateTime();
    }    
    
    
    /**
     * Updates the comment count for the photo and user.
     *  
     * @ORM\PrePersist 
     */
    public function increasePhotoCommentCount()
    {
        $this->getPhoto()->setTotalComments($this->getPhoto()->getTotalComments()+1);
    }
    
    
    /**
     * Updates the comment count for the photo and user.
     *  
     * @ORM\PreRemove
     */
    public function decreasePhotoCommentCount()
    {
        $this->getPhoto()->setTotalComments($this->getPhoto()->getTotalComments()-1);
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
     * Set ip_address
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;
    }

    /**
     * Get ip_address
     *
     * @return string 
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
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
     * Set owner
     *
     * @param Vinyett\UserBundle\Entity\User $owner
     */
    public function setOwner(\Vinyett\UserBundle\Entity\User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set photo
     *
     * @param Vinyett\PhotoBundle\Entity\Photo $photo
     */
    public function setPhoto(\Vinyett\PhotoBundle\Entity\Photo $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Get photo
     *
     * @return Vinyett\PhotoBundle\Entity\Photo 
     */
    public function getPhoto()
    {
        return $this->photo;
    }
    
    /**
     * Set Options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get Options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}