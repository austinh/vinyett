<?php

namespace Vinyett\StreamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vinyett\StreamBundle\Entity\NewsStoryCache
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\StreamBundle\Entity\NewsStoryCacheRepository")
 */
class NewsStoryCache
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
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\StreamBundle\Entity\Activity")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id")
     */
    private $activity;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\PhotoBundle\Entity\Photo")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     */
    private $photo;

    /**
     * @var text $html
     *
     * @ORM\Column(name="html", type="text")
     */
    private $html;

    /**
     * @var float $edge
     *
     * @ORM\Column(name="edge", type="float")
     */
    private $edge;

    /**
     * @var text $data
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;

    /**
     * @var datetime $activity_created_at
     *
     * @ORM\Column(name="activity_created_at", type="datetime")
     */
    private $activity_created_at;

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
     * @var string $rule
     */
    protected $rule;

    public function __construct() 
    { 
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        
        $this->collections = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set html
     *
     * @param text $html
     * @return NewsStoryCache
     */
    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Get html
     *
     * @return text 
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set edge
     *
     * @param float $edge
     * @return NewsStoryCache
     */
    public function setEdge($edge)
    {
        $this->edge = $edge;
        return $this;
    }

    /**
     * Get edge
     *
     * @return float 
     */
    public function getEdge()
    {
        return $this->edge;
    }

    /**
     * Set activity_created_at
     *
     * @param datetime $activityCreatedAt
     * @return NewsStoryCache
     */
    public function setActivityCreatedAt($activityCreatedAt)
    {
        $this->activity_created_at = $activityCreatedAt;
        return $this;
    }

    /**
     * Get activity_created_at
     *
     * @return datetime 
     */
    public function getActivityCreatedAt()
    {
        return $this->activity_created_at;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     * @return NewsStoryCache
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
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
     * @return NewsStoryCache
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
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
     * Set user
     *
     * @param Vinyett\UserBundle\Entity\User $user
     * @return NewsStoryCache
     */
    public function setUser(\Vinyett\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
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
     * Set activity
     *
     * @param Vinyett\StreamBundle\Entity\Activity $activity
     * @return NewsStoryCache
     */
    public function setActivity(\Vinyett\StreamBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * Get activity
     *
     * @return Vinyett\StreamBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set data
     *
     * @param text $data
     * @return NewsStoryCache
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
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
     * Set photo
     *
     * @param Vinyett\PhotoBundle\Entity\Photo $photo
     * @return NewsStoryCache
     */
    public function setPhoto(\Vinyett\PhotoBundle\Entity\Photo $photo = null)
    {
        $this->photo = $photo;
        return $this;
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
}