<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vinyett\PhotoBundle\Entity\PersonTag
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\PhotoBundle\Entity\PersonTagRepository")
 */
class PersonTag
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
     * @ORM\ManyToOne(targetEntity="Photo", inversedBy="person_tags", cascade={"all"})
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="tagged_id", referencedColumnName="id")
     */
    private $tagged;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="tagger_id", referencedColumnName="id")
     */
    private $tagger;

    /**
     * @var datetime $date_tagged
     *
     * @ORM\Column(name="date_tagged", type="datetime")
     */
    private $date_tagged;

    /**
     * @var boolean $was_removed
     *
     * @ORM\Column(name="was_removed", type="boolean")
     */
    private $was_removed = false;

    public function __construct() {
        
        $this->date_tagged = new DateTime();
        
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
     * Set photo_id
     *
     * @param integer $photoId
     */
    public function setPhotoId($photoId)
    {
        $this->photo_id = $photoId;
    }

    /**
     * Get photo_id
     *
     * @return integer 
     */
    public function getPhotoId()
    {
        return $this->photo_id;
    }

    /**
     * Set owner_id
     *
     * @param integer $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;
    }

    /**
     * Get owner_id
     *
     * @return integer 
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tagger_id
     *
     * @param integer $taggerId
     */
    public function setTaggerId($taggerId)
    {
        $this->tagger_id = $taggerId;
    }

    /**
     * Get tagger_id
     *
     * @return integer 
     */
    public function getTaggerId()
    {
        return $this->tagger_id;
    }

    /**
     * Set date_tagged
     *
     * @param datetime $dateTagged
     */
    public function setDateTagged($dateTagged)
    {
        $this->date_tagged = $dateTagged;
    }

    /**
     * Get date_tagged
     *
     * @return datetime 
     */
    public function getDateTagged()
    {
        return $this->date_tagged;
    }

    /**
     * Set was_removed
     *
     * @param boolean $wasRemoved
     */
    public function setWasRemoved($wasRemoved)
    {
        $this->was_removed = $wasRemoved;
    }

    /**
     * Get was_removed
     *
     * @return boolean 
     */
    public function getWasRemoved()
    {
        return $this->was_removed;
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
     * Set tagged
     *
     * @param Vinyett\PhotoBundle\Entity\User $tagged
     */
    public function setTagged(\Vinyett\UserBundle\Entity\User $tagged)
    {
        $this->tagged = $tagged;
    }

    /**
     * Get tagged
     *
     * @return Vinyett\PhotoBundle\Entity\User 
     */
    public function getTagged()
    {
        return $this->tagged;
    }

    /**
     * Set tagger
     *
     * @param Vinyett\PhotoBundle\Entity\User $tagger
     */
    public function setTagger(\Vinyett\UserBundle\Entity\User $tagger)
    {
        $this->tagger = $tagger;
    }

    /**
     * Get tagger
     *
     * @return Vinyett\PhotoBundle\Entity\User 
     */
    public function getTagger()
    {
        return $this->tagger;
    }
}