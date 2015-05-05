<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use JMS\SerializerBundle\Annotation\Expose;

use JMS\SerializerBundle\Annotation\PreSerialize;


/**
 * Vinyett\PhotoBundle\Entity\Collection
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\PhotoBundle\Entity\CollectionRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Collection
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
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var integer $total_photos
     *
     * @ORM\Column(name="total_photos", type="integer")
     * @Expose
     */
    private $total_photos = 0;

    /**
     * @var integer $total_comments
     *
     * @ORM\Column(name="total_comments", type="integer")
     * @Expose
     */
    private $total_comments = 0;

    /**
     * @var datetime $date_created
     *
     * @ORM\Column(name="date_created", type="datetime")
     * @Expose
     */
    private $date_created;

    /**
     * @var datetime $date_updated
     *
     * @ORM\Column(name="date_updated", type="datetime")
     * @Expose
     */
    private $date_updated;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Expose
     */
    private $description;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     * @Expose
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\PhotoBundle\Entity\Photo")
     * @ORM\JoinColumn(name="cover_photo_id", referencedColumnName="id", onDelete="SET NULL")
     * @Expose
     */
    private $cover_photo;
    
    /**
     * @ORM\OneToMany(targetEntity="CollectionPhoto", mappedBy="collection", fetch="EXTRA_LAZY")
     */
    private $collection_photos;
    
    /**
     * var $user 
     * @Expose
     **/
    private $user; 
       
    /**
     * var $photos 
     * @Expose
     **/
    private $photos;
     

    public function __construct() {
        $this->collection_photos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date_updated = new \DateTime();
        $this->date_created = new \DateTime();
        $this->title = "Untitled";
    }
    
    /**
     * @PreSerialize
     */
    public function preSerialize() { 
        //Compress the user...
        $this->user = $this->getOwner()->getId();
        
        $photos = array();
        foreach($this->getCollectionPhotos() as $collection_photo) 
        { 
            $photos[] = $collection_photo->getPhoto()->getId();    
        }
        
        $this->total_photos = count($this->getCollectionPhotos());
        $this->photos = $photos;
    }
    
    /**
     * Sets the current time for the updated_at field when updated.
     *  
     * @ORM\PreUpdate
     *
     * @return null
     */
    public function onUpdate() 
    { 
        $this->date_updated = new \DateTime();
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
     * Set total_photos
     *
     * @param integer $totalPhotos
     */
    public function setTotalPhotos($totalPhotos)
    {
        $this->total_photos = $totalPhotos;
    }

    /**
     * Get total_photos
     *
     * @return integer 
     */
    public function getTotalPhotos()
    {
        return $this->total_photos;
    }

    /**
     * Set total_comments
     *
     * @param integer $totalComments
     */
    public function setTotalComments($totalComments)
    {
        $this->total_comments = $totalComments;
    }

    /**
     * Get total_comments
     *
     * @return integer 
     */
    public function getTotalComments()
    {
        return $this->total_comments;
    }

    /**
     * Set date_created
     *
     * @param datetime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;
    }

    /**
     * Get date_created
     *
     * @return datetime 
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date_updated
     *
     * @param datetime $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->date_updated = $dateUpdated;
    }

    /**
     * Get date_updated
     *
     * @return datetime 
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set cover_photo_id
     *
     * @param integer $coverPhotoId
     */
    public function setCoverPhotoId($coverPhotoId)
    {
        $this->cover_photo_id = $coverPhotoId;
    }

    /**
     * Get cover_photo_id
     *
     * @return integer 
     */
    public function getCoverPhotoId()
    {
        return $this->cover_photo_id;
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
     * Set cover_photo
     *
     * @param Vinyett\PhotoBundle\Entity\Photo $coverPhoto
     */
    public function setCoverPhoto(\Vinyett\PhotoBundle\Entity\Photo $coverPhoto)
    {
        $this->cover_photo = $coverPhoto;
    }

    /**
     * Get cover_photo
     *
     * @return Vinyett\PhotoBundle\Entity\Photo 
     */
    public function getCoverPhoto()
    {
        return $this->cover_photo;
    }


    /**
     * Add collection_photos
     *
     * @param Vinyett\PhotoBundle\Entity\CollectionPhoto $collectionPhotos
     * @return Collection
     */
    public function addCollectionPhoto(\Vinyett\PhotoBundle\Entity\CollectionPhoto $collectionPhotos)
    {
        $this->collection_photos[] = $collectionPhotos;
        return $this;
    }

    /**
     * Get collection_photos
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCollectionPhotos()
    {
        return $this->collection_photos;
    }

    /**
     * Remove collection_photos
     *
     * @param \Vinyett\PhotoBundle\Entity\CollectionPhoto $collectionPhotos
     */
    public function removeCollectionPhoto(\Vinyett\PhotoBundle\Entity\CollectionPhoto $collectionPhotos)
    {
        $this->collection_photos->removeElement($collectionPhotos);
    }
}