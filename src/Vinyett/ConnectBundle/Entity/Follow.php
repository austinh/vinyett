<?php

namespace Vinyett\ConnectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use JMS\SerializerBundle\Annotation\Expose;
use JMS\SerializerBundle\Annotation\PreSerialize;

/**
 * Vinyett\ConnectBundle\Entity\Follow
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\ConnectBundle\Entity\FollowRepository")
 * @ExclusionPolicy("all")
 */
class Follow
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    private $id;

    /** 
     * @ORM\ManyToOne( targetEntity="\Vinyett\UserBundle\Entity\User" ) 
     * @ORM\JoinColumn( name="actor_id", referencedColumnName="id" ) 
     * @Assert\NotNull()
     */
    private $actor;
    
    
	/**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="following_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Expose
     */
    private $following;

    /**
     * @var boolean $is_friend
     *
     * @ORM\Column(name="is_friend", type="boolean")
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     * @Expose
     */
    private $is_friend = false;

    /**
     * @var boolean $is_family
     *
     * @ORM\Column(name="is_family", type="boolean")
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     * @Expose
     */
    private $is_family = false;
    
    /**
     * @var boolean $is_in_photofeed
     *
     * @ORM\Column(name="is_in_photofeed", type="boolean")
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     * @Expose
     */
    private $is_in_photofeed = true;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Expose
     */
    private $created_at;
    
    /**
     * @var datetime $last_interaction
     *
     * @ORM\Column(name="last_interaction", type="datetime")
     */
    private $last_interaction;

    /**
     * @var float $affinity
     *
     * @ORM\Column(name="affinity", type="float")
     */
    private $affinity;
    
    /**
     * @var float $weight
     *
     * @ORM\Column(name="weight", type="array")
     */
    private $weight = array();

    /**
     * @var boolean $is_phantom
     *
     * Used to determine if the Follow object is a real (persisted) object or 
     * just a phantom object (which is used to hold the basic data for creating
     * template nulls.
     *
     * @Expose
     */
    protected $is_phantom = false;


    public function __construct() 
    { 
        $this->affinity = rand(5, 10);
        $this->created_at = new \DateTime();
        $this->last_interaction = new \DateTime();
    }
    
    /**
     * @PreSerialize
     */
    public function preSerialize() { 
        //..
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
     * Set is_friend
     *
     * @param boolean $isFriend
     */
    public function setIsFriend($isFriend)
    {
        $this->is_friend = $isFriend;
    }

    /**
     * Get is_friend
     *
     * @return boolean 
     */
    public function getIsFriend()
    {
        return $this->is_friend;
    }

    /**
     * Set is_family
     *
     * @param boolean $isFamily
     */
    public function setIsFamily($isFamily)
    {
        $this->is_family = $isFamily;
    }

    /**
     * Get is_family
     *
     * @return boolean 
     */
    public function getIsFamily()
    {
        return $this->is_family;
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
     * Set affinity
     *
     * @param float $affinity
     */
    public function setAffinity($affinity)
    {
        $this->affinity = $affinity;
    }

    /**
     * Get affinity
     *
     * @return float 
     */
    public function getAffinity()
    {
        return $this->affinity;
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

    /**
     * Set following
     *
     * @param Vinyett\UserBundle\Entity\User $following
     */
    public function setFollowing(\Vinyett\UserBundle\Entity\User $following)
    {
        $this->following = $following;
    }

    /**
     * Get following
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * Set is_in_photofeed
     *
     * @param boolean $isInPhotofeed
     */
    public function setIsInPhotofeed($isInPhotofeed)
    {
        $this->is_in_photofeed = $isInPhotofeed;
    }

    /**
     * Get is_in_photofeed
     *
     * @return boolean 
     */
    public function getIsInPhotofeed()
    {
        return $this->is_in_photofeed;
    }

    /**
     * Set last_interaction
     *
     * @param datetime $lastInteraction
     */
    public function setLastInteraction($lastInteraction)
    {
        $this->last_interaction = $lastInteraction;
    }

    /**
     * Get last_interaction
     *
     * @return datetime 
     */
    public function getLastInteraction()
    {
        return $this->last_interaction;
    }

    /**
     * Set weight
     *
     * @param array $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return array 
     */
    public function getWeight()
    {
        return $this->weight;
    }
    
        
    public function setIsPhantom($is_phantom)
    { 
        $this->is_phantom = $is_phantom;
    }
    
    public function getIsPhantom()
    { 
        return $this->is_phantom;
    }
}