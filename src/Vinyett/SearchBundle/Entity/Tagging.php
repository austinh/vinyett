<?php

namespace Vinyett\SearchBundle\Entity;

use FPN\TagBundle\Entity\Tagging as BaseTagging;

class Tagging extends BaseTagging
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var Vinyett\SearchBundle\Entity\Tag
     */
    protected $tag;


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
     * Get tag
     *
     * @return Vinyett\SearchBundle\Entity\Tag 
     */
    public function getTag()
    {
        return $this->tag;
    }
    /**
     * @var Vinyett\UserBundle\Entity\User
     */
    private $metadata_tag_user;


    /**
     * Set metadata_tag_user
     *
     * @param Vinyett\UserBundle\Entity\User $metadataTagUser
     */
    public function setMetadataTagUser(\Vinyett\UserBundle\Entity\User $metadataTagUser)
    {
        $this->metadata_tag_user = $metadataTagUser;
    }

    /**
     * Get metadata_tag_user
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getMetadataTagUser()
    {
        return $this->metadata_tag_user;
    }
}