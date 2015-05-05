<?php 

namespace Vinyett\StreamBundle\Feed\Congregator;

use Doctrine\Common\Collections\ArrayCollection;

use Vinyett\StreamBundle\Entity\Activity;
use Vinyett\PhotoBundle\Entity\Photo;



/**
 * Rule class.
 */
class Rule 
{ 

    protected $type;
    
    protected $parameters = array();
    
    protected $section_aware = true;

    public function getType()
    { 
        return $this->type;
    }

    public function getParameters()
    { 
        return $this->parameters;
    }
    
    public function isSectionAware()
    { 
        return $this->section_aware;
    }

    /**
     * Builds the rule for congregating stream items.
     * 
     * @param mixed $type
     * @param array $parameters
     * @param bool $secton_aware (default: true)
     *
     * @return void
     */
    public function __construct($type, $parameters = array())
    {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->section_aware = true;
    }

    /**
     * Returns a unique name to store values according to rules.
     * 
     * @access public
     * @return void
     */
    public function getName() 
    { 
        return $this->getType()."_".substr(md5(implode($this->getParameters())), 0, 20);
    }


    /**
     * Generates a stored tag...
     * 
     * @access public
     * @return void
     */
    public function getTag() 
    { 
        return array("type" => $this->getType(), "name" => $this->getName(), "parameters" => $this->getParameters());
    }


}