<?php 

namespace Vinyett\CoreServicesBundle\Services;

class Purifier 
{ 
    /*
     * @var HTMLPurifier $purifier
     */
    protected $purifier;

    /*
     * @var HTMLPurifier_Config $config;
     */
    protected $config;
    
    /**
     * constructor
     *
     * @return this;
     */
    public function __construct() 
    {  
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.TidyLevel', 'heavy');
        
        $this->config = $config;
    }

    public function getConfig()
    { 
        return $this->config;
    }
    
    public function getPurifier() 
    { 
        if(empty($purifier))
        { 
            $this->purifier = new \HTMLPurifier($this->getConfig());
        }
        
        return $this->purifier;
    }
    

}