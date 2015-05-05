<?php

namespace Vinyett\StreamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vinyett\StreamBundle\Entity\Activity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\StreamBundle\Entity\ActivityRepository")
 */
class Activity
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
     * @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     */
    private $actor;

    /**
     * @var integer $source_id
     *
     * @ORM\Column(name="source_id", type="integer", nullable=true)
     */
    private $source_id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\PhotoBundle\Entity\Photo")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     */
    private $photo;

    /**
     * @var string $source_type
     *
     * @ORM\Column(name="source_type", type="string", length=50, nullable=true)
     */
    private $source_type;

    /**
     * @var string $activity_type
     *
     * @ORM\Column(name="activity_type", type="string", length=50)
     */
    private $activity_type;

    /**
     * @var integer $edge_rank
     */
    private $edge_rank = 0;

    /**
     * @var string $parent_type
     *
     * @ORM\Column(name="parent_type", type="string", length=50, nullable=true)
     */
    private $parent_type;

    /**
     * @var integer $parent_id
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parent_id;

    /**
     * @var text $data
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    
    public function __construct() 
    { 
        
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
     * Set source_id
     *
     * @param integer $sourceId
     */
    public function setSourceId($sourceId)
    {
        $this->source_id = $sourceId;
    }

    /**
     * Get source_id
     *
     * @return integer 
     */
    public function getSourceId()
    {
        return $this->source_id;
    }

    /**
     * Set source_type
     *
     * @param string $sourceType
     */
    public function setSourceType($sourceType)
    {
        $this->source_type = $sourceType;
    }

    /**
     * Get source_type
     *
     * @return string 
     */
    public function getSourceType()
    {
        return $this->source_type;
    }

    /**
     * Set activity_type
     *
     * @param string $activityType
     */
    public function setActivityType($activityType)
    {
        $this->activity_type = $activityType;
    }

    /**
     * Get activity_type
     *
     * @return string 
     */
    public function getActivityType()
    {
        return $this->activity_type;
    }

    /**
     * Set edge_rank
     *
     * @param integer $edgeRank
     */
    public function setEdgeRank($edgeRank)
    {
        $this->edge_rank = $edgeRank;
    }

    /**
     * Get edge_rank
     *
     * @return integer 
     */
    public function getEdgeRank()
    {
        return $this->edge_rank;
    }

    /**
     * Set parent_type
     *
     * @param string $parentType
     */
    public function setParentType($parentType)
    {
        $this->parent_type = $parentType;
    }

    /**
     * Get parent_type
     *
     * @return string 
     */
    public function getParentType()
    {
        return $this->parent_type;
    }

    /**
     * Set parent_id
     *
     * @param integer $parentId
     */
    public function setParentId($parentId)
    {
        $this->parent_id = $parentId;
    }

    /**
     * Get parent_id
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Set data
     *
     * @param text $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return text 
     */
    public function getData()
    {
        return $this->data;
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
     * Set actor_id
     *
     * @param integer $actorId
     */
    public function setActorId($actorId)
    {
        $this->actor_id = $actorId;
    }

    /**
     * Get actor_id
     *
     * @return integer 
     */
    public function getActorId()
    {
        return $this->actor_id;
    }

    /**
     * Set actor
     *
     * @param Vinyett\UserBundle\Entity\User $actor
     */
    public function setActor(\Vinyett\UserBundle\Entity\User $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Get actor
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getActor()
    {
        return $this->actor;
    }
}