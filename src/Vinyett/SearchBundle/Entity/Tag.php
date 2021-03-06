<?php

namespace Vinyett\SearchBundle\Entity;

use FPN\TagBundle\Entity\Tag as BaseTag;

class Tag extends BaseTag

{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var Vinyett\SearchBundle\Entity\Tagging
     */
    protected $tagging;

    public function __construct($name)
    {
        $this->tagging = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct($name);
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
     * Add tagging
     *
     * @param Vinyett\SearchBundle\Entity\Tagging $tagging
     */
    public function addTagging(\Vinyett\SearchBundle\Entity\Tagging $tagging)
    {
        $this->tagging[] = $tagging;
    }

    /**
     * Get tagging
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTagging()
    {
        return $this->tagging;
    }
}