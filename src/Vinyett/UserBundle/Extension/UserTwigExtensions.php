<?php


namespace Vinyett\UserBundle\Extension;

class UserTwigExtensions extends \Twig_Extension {

    public function getFunctions() 
    {
        return array(
            'avatar_path'   => new \Twig_Function_Method($this, 'avatar'),
            'render_name'   => new \Twig_Function_Method($this, 'render_name'),
            'render_gender_pronouns'   => new \Twig_Function_Method($this, 'render_gender_pronouns'),
            'is_self' => new \Twig_Function_Method($this, 'is_self'),
        );
    }
    
    public function is_self($a, $b) 
    { 
        if($a->getId() == $b->getId()) { 
            return true;
        } else { 
            return false;
        }
    }
    
    public function avatar($user, $large = false) 
    {
      if($user->getPhotoSquare() && $large == false) { 
        return $user->getPhotoSquare();
      } 
      
      if($user->getPhotoSquare() && $large == true) { 
        return $user->getPhotoSquare();
      } 
    
      if($large == false) { 
          return "images/default100.png";
      } else { 
          return "images/default200.gif";
      }
    
    }
    
    public function render_name($user)
    { 
    
      return $user->getFirstName()." ".$user->getLastName();
    
    }
    
    public function render_gender_pronouns($user)
    { 
      return ($user->getGender() == 1?"his":"her");
    }
  
    public function getName()
    {
        return 'user_twig_extension';
    }

}
