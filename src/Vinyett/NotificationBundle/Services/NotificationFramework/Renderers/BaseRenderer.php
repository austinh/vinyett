<?php 

namespace Vinyett\NotificationBundle\Services\NotificationFramework\Renderers;

abstract class BaseRenderer
{

		protected $entity_manager;
		protected $templating;
		protected $user;
		protected $note;

		public function __construct($note, $em, $twig, $user)
		{
			$this->note = $note;
			$this->entity_manager = $em;
			$this->templating = $twig;
			$this->user = $user;
			
			return $this;	
		}	
		
		public function getEntityManager() {
	    return $this->entity_manager;
		}
    
    public function getTwig() {
	    return $this->templating;
		}
    
    public function getUser() { 
    	return $this->user;
    }
    
    public function getNote()
    { 
    	return $this->note;
    }
    
    public function render() { 
    	
    	return $this->getTemplate($this->getTwig(), $this->getNote());
    	
    }
		
    abstract public function getTemplate($templating, $notification);
    
    

}