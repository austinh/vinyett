<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use \DateTime;

/**
 * Vinyett\PhotoBundle\Entity\CollectionPhoto
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\PhotoBundle\Entity\CollectionPhotoRepository")
 */
class CollectionPhoto
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
     * @ORM\ManyToOne(targetEntity="Collection", inversedBy="collection_photos")
     * @ORM\JoinColumn(name="collection_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $collection;

    /**
     * @ORM\ManyToOne(targetEntity="Photo", inversedBy="collections")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $photo;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;


    public function __construct() 
    { 
        $this->created_at = new DateTime();
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
     * Set position
     *
     * @param integer $position
     * @return CollectionPhoto
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     * @return CollectionPhoto
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
     * Set collection
     *
     * @param Vinyett\PhotoBundle\Entity\Collection $collection
     * @return CollectionPhoto
     */
    public function setCollection(\Vinyett\PhotoBundle\Entity\Collection $collection = null)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Get collection
     *
     * @return Vinyett\PhotoBundle\Entity\Collection 
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set photo
     *
     * @param Vinyett\PhotoBundle\Entity\Photo $photo
     * @return CollectionPhoto
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