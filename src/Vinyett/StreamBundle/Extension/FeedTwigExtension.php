<?php


namespace Vinyett\StreamBundle\Extension;

class FeedTwigExtension extends \Twig_Extension {

    protected $user;
    
    protected $photo_chain = array();
    
    public function getUser() 
    { 
        return $this->user;
    }

    public function __construct($security_context)
    {
        $token = $security_context->getToken();
        if(!empty($token))
        {
            $this->user = $security_context->getToken()->getUser();
        } else { 
            $this->user = new \Vinyett\UserBundle\Entity\User();
        }
    }

    public function getFunctions() 
    {
        return array(
            'render_feed_story'   => new \Twig_Function_Method($this, 'render_feed_story', array(
                'is_safe' => array('all') 
             )),
             'guess_photo_grid_style' => new \Twig_Function_Method($this, 'print_photo_grid_style'), 
        );
    }
    
    public function getFilters() {
        return array(
            'or_you'  => new \Twig_Filter_Method($this, 'or_you'),
        );
    }
    
    
    /**
     * getPhotoChain function.
     * 
     * @access public
     * @return void
     */
    public function getPhotoChain() 
    {
        return $this->photo_chain;
    }
    
    
    /**
     * Returns the class for the associated amount of photos in a 
     * gridded stream view (used in photo.uploaded.multiple.html.twig)
     * 
     * @access public
     * @param integer $total_photos
     * @return string
     */
    public function print_photo_grid_style($total_photos) 
    { 
        switch($total_photos)
        { 
            default: 
                $class = "photo_grid_trunicated";
            break;
            
            case 2: 
                $class = "photo_grid_two";
            break;
            
            case 3: 
                $class = "photo_grid_three";
            break;
            
            case 4:
                $class = "photo_grid_full";
            break;
        }
        
        return $class;
    }
    
    
    /**
     * or_you function.
     * 
     * @access public
     * @param mixed $user
     * @return void
     */
    public function or_you($user) 
    { 
        $audience = $this->getUser();
        
        if($user == $audience->getUsername())
        { 
            $name = "you";
        } else { 
            $name = $user;
        }
        
        return $name;
    }
    
    
    /**
     * render_feed_story function.
     * 
     * @access public
     * @param mixed $story
     * @return void
     */
    public function render_feed_story($story) 
    {
        if(!in_array($story->getPhoto()->getId(), $this->getPhotoChain()))
        {
            $this->photo_chain[] = $story->getPhoto()->getId();
            return $story->getHtml();
        }
    }
    
  
    public function getName()
    {
        return 'feed_twig_extension';
    }

}
