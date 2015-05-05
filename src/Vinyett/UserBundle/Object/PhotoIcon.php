<?php

namespace Vinyett\UserBundle\Object;

use Symfony\Component\Validator\Constraints as Assert;

class PhotoIcon 
{ 
    
    /**
     * @Assert\File(
     *     maxSize = "6291456",
     *     mimeTypes = {"image/gif", "image/jpeg", "image/png"},
     *     mimeTypesMessage = "Please upload a valid image"
     * )
     */
    protected $photo;  
    
    public function getPhoto()
    {
        return $this->photo;
    }  

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }
}