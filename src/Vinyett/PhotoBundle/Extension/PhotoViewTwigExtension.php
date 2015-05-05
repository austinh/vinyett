<?php


namespace Vinyett\PhotoBundle\Extension;

class PhotoViewTwigExtension extends \Twig_Extension {

    public function getName()
    {
        return 'photo_view_twig_extension';
    }
    

    public function getFunctions() 
    {
        return array(
            'photo_neighbors'   => new \Twig_Function_Method($this, 'render_HTML_photo_neighbors', array('is_safe' => array('html'))),
            'photo_previous_global_options'   => new \Twig_Function_Method($this, 'render_photo_previous_global_options', array('is_safe' => array('html'))),
            'photo_next_global_options'   => new \Twig_Function_Method($this, 'render_photo_next_global_options', array('is_safe' => array('html'))),
        );
    } 
    
    /**
     * Creates the global options for previous image.
     *
	 * @param DoctrineCollection $photo_chain Collection of photo objects to be rendered
	 * @param Photo $current_photo Object of the viewing photo
     * @return string
     */   
    public function render_photo_previous_global_options($photo_chain, $current_photo) 
    { 
        $photos = $this->wrapHTMLToPhotos($photo_chain, $current_photo);
            
        $total = count($photos);  
        $current = $this->getCurrentPhotoIndex($photos);
        $prev_index = $current-1;
        
        if(!empty($photos[$prev_index])) 
        { 
            $photo_information = $photos[$prev_index]['object'];
            return $this->dumpGlobalOptions($photo_information, "previous");
        } else {
            return "Global.photo.neighbors.previous = null;";   
        }
    }
    
    
    /**
     * Creates the global options for next image.
     *
	 * @param DoctrineCollection $photo_chain Collection of photo objects to be rendered
	 * @param Photo $current_photo Object of the viewing photo
     * @return string
     */   
    public function render_photo_next_global_options($photo_chain, $current_photo) 
    {
        $photos = $this->wrapHTMLToPhotos($photo_chain, $current_photo);
            
        $total = count($photos);  
        $current = $this->getCurrentPhotoIndex($photos);
        $next_index = $current+1;
        
        if(!empty($photos[$next_index])) 
        { 
            $photo_information = $photos[$next_index]['object'];
            return $this->dumpGlobalOptions($photo_information, "next");
        } else {
            return "Global.photo.neighbors.next = null;";   
        }
    }
    
    
    /**
     * Turns the response from PhotoRepository::findNeighboringPhotos() into a correctly formatted HTML element
     * to display in the PhotoBundle:Photo:view.html.twig remplate
     *
 	 * @param DoctrineCollection $photo_chain Collection of photo objects to be rendered
 	 * @param Photo $current_photo Object of the viewing photo
     * @return string
     */    
    public function render_HTML_photo_neighbors($photo_chain, $current_photo) 
    {
        $filtered_photos = $this->compressNeighbors($this->wrapHTMLToPhotos($photo_chain, $current_photo));
        
        return $filtered_photos;
    }
    
    
    /**
     * Filters the Photo objects by finding the current image and adding placeholders on 
     * either side of it.
     *
     * Also returns just HTML in the array (drops off the photo object and metadata.
     *
	 * @param array $photos Array of Photo objects
     * @return string
     */
    private function compressNeighbors($photos) 
    { 
        $total = count($photos);  
        $current = $this->getCurrentPhotoIndex($photos);
        
        $html = null;
        
        //Loop and merge the HTML down
        foreach($photos as $photo) 
        {
            $html .= $photo['html'];
        }
        
        //Then add placeholders to either side if needed.
        $placeholder = '<div class="photo_cycle"><img src="/images/50px_placeholder.jpg" /></div>';
        
        if($total == 5) {
            return $html; //return them all...
        } else if($total == 1) { //There is only one image (the one currently showing) 
            $html = $placeholder.$placeholder.$html.$placeholder.$placeholder;
        } elseif($total == 2 && $current == 0) { // There are two and current is the first
            $html = $placeholder.$placeholder.$html.$placeholder; //we append two to the front and one to the back
        } elseif($total == 2 && $current == 1) { //There are two and current is the second
            $html = $placeholder.$html.$placeholder.$placeholder; //We append one to the front and two to the back
        } elseif($total == 3 && $current == 0) { //There are three and current is in the front 
            $html = $placeholder.$placeholder.$html; //We append two to the front
        } elseif($total == 3 && $current == 1) { //There are three and current is in the middle
            $html = $placeholder.$html.$placeholder; //We append one to the front and to the back
        } elseif($total == 3 && $current == 2) { //There are three and current is in the end 
            $html = $html.$placeholder.$placeholder; //We attach two the the back
        } elseif($total == 4 && $current == 1) { //There are four and current is in the 2nd front (won't be in 1st)
            $html = $placeholder.$html; //We attach one to the front
        } elseif($total == 4 && $current == 2) { //There are four and current is in the 2nd back (won't be in back)
            $html = $html.$placeholder; //We attach one to the back
        }
        
        return $html;
    }
    
    /**
     * Convience function to dump $photo_information into a javascript object
     *
	 * @param Photo $photo_information A Photo object to dump
	 * @param string $subclass Javascript reading the global variables expects either "next" or "previous"
     * @return integer
     */      
    public function dumpGlobalOptions($photo_information, $subclass = "next")
    { 
        return "Global.photo.neighbors.".$subclass." = new Object();
                Global.photo.neighbors.".$subclass.".id = ".$photo_information->getId()."
                Global.photo.neighbors.".$subclass.".owner = new Object();
                Global.photo.neighbors.".$subclass.".owner.id = ".$photo_information->getOwner()->getId().";
                Global.photo.neighbors.".$subclass.".owner.username = '".$photo_information->getOwner()->getUsername()."';
                Global.photo.neighbors.".$subclass.".owner.url_username = '".$photo_information->getOwner()->getUrlUsername()."';";
    }
    
    
    /**
     * Gets the current viewing image position from an array of photos. 
     * NOTE: expects data formatted from wrapHTMLToPhotos() OR an array of wrapHTMLToPhoto() reponses.
     *
	 * @param array $photos array of wrapHTMLToPhoto() responses
     * @return integer
     */    
    private function getCurrentPhotoIndex($photos)
    { 
        $current = 0;
        $i = 0;
    
        foreach($photos as $photo) 
        { 
            if($photo['is_selected'] == true) 
            { 
                $current = $i;
                break;
            }
            $i++;
        }
        
        return $current;
    }
    
    
    /**
     * Convinence function to loop through an array of Photo objects and quickly organize them.
     *
	 * @param array $photos array of Photo Objects
	 * @param Photo $current_photo Object of the viewing photo
     * @return array
     */        
    private function wrapHTMLToPhotos($photos, $current_photo) 
    { 
        $processed_photos = array(); 
        
        foreach($photos as $photo)
        {
            $processed_photos[] = $this->wrapHTMLtoPhoto($photo, $current_photo);
        }
        
        return $processed_photos;
    }
    
    
    /**
     * Inputs a Photo object and converts it into the HTML that will be rendered into the template and creates
     * markers for PhotoViewTwigExtension::filterNeighbors() to quickly bind to and adjust the wheel.
     *
	 * @param Photo $photo Photo Object
	 * @param Photo $current_photo Object of the viewing photo
     * @return array
     */    
    private function wrapHTMLToPhoto($photo, $current_photo) 
    { 
        $src = $photo->getPhotoPathSquare50();
        $class = ($photo->getId() == $current_photo->getId()?"selected":"");
        
        $html = '<div class="photo_cycle"><a href="/app_dev.php/photo/'.$photo->getOwner()->getUrlUsername().'/'.$photo->getId().'"><img src="'.$src.'" alt="empty fo now" class="'.$class.'" /></a></div>';
        $selected = ($photo->getId() == $current_photo->getId()?true:false);
        
        return array("html" => $html, "is_selected" => $selected, "object" => $photo);
    }
    
    
    
    
    
}
