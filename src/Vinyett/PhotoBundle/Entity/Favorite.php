<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vinyett\PhotoBundle\Entity\Favorite
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Vinyett\PhotoBundle\Entity\FavoriteRepository")
 */
class Favorite
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
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\PhotoBundle\Entity\Photo", inversedBy="favorites")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $photo;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * Constructor
	 *
     * @return null
     */
    public function __construct() 
    { 
        $this->created_at = new \DateTime();
    }

    
    /**
     * Updates the comment count for the photo and user.
     *  
     * @ORM\PrePersist 
     */
    public function increasePhotoFavoriteCount()
    {
        $this->getPhoto()->setTotalFavorites($this->getPhoto()->getTotalFavorites()+1);
    }
    
    
    /**
     * Updates the comment count for the photo and user.
     *  
     * @ORM\PreRemove
     */
    public function decreasePhotoFavoriteCount()
    {
        $this->getPhoto()->setTotalFavorites($this->getPhoto()->getTotalFavorites()-1);
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
}