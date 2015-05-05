<?php


namespace Vinyett\PhotoBundle\Extension;

class ActivityStreamerExtension extends \Twig_Extension {

    /*
     * @var array $templates
     */
    protected $templates = array();


    private $environment;
    

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }


    public function getName()
    {
        return 'activity_streamer_twig_extension';
    }
    
    
    public function getEnvironment() 
    { 
        return $this->environment;
    }
    

    public function getFunctions() 
    {
        return array(
            'activity_stream'   => new \Twig_Function_Method($this, 'render_activity_stream', array('is_safe' => array('html'))),
            'activity_bind_templates' => new \Twig_Function_Method($this, 'bind_activity_templates', array('is_safe' => array('html'))),
            'activity_find_users' => new \Twig_Function_Method($this, 'find_resource_users', array('is_safe' => array('html'))),
        );
    } 
    
    
    /**
     * Renders the activity list
     *
	 * @param ActivityStreamer $activity_streamer Activity Streamer object
	 * @param array $skipped_types The Type_id's of objects to be skipped in aggregation
	 *
     * @return string
     */
    public function render_activity_stream($activity_streamer, $aggregate = true, $skipped_types = array()) 
    { 
        //$this->getEnvironment->render()
        
        //Let's create a usable activities array
        $activities = $activity_streamer->render($aggregate, $skipped_types);
        
        $stream = array();
        
        foreach($activities as $activity) 
        { 
            $type_id = $activity["type_id"];
            //array("timestamp" => $datetime->getTimestamp(), "resource" => $resource, "type_id" => $type_id, "resources", "multiple_resources");
            if(empty($this->templates[$type_id]))
            { 
                throw new \Exception("No template bound to type_id: $type_id");
            }
            
            if($activity['multiple_resources'])
            { 
                $template = $this->templates[$type_id.".multiple"];
                $stream[] = $this->getEnvironment()->render($template, array("resources" => $activity["resources"])); 
            } else { 
                $template = $this->templates[$type_id];
                $stream[] = $this->getEnvironment()->render($template, array("resource" => $activity["resource"]));
            }
        }
        
        
        return implode(" ", $stream);
    }    
    

    /**
     * Binds templates to a type_id
     *
	 * @param array $template_binding Array of the type_id and the template bound to them.
	 *
     * @return null
     */  
    public function bind_activity_templates($template_binding) 
    { 
        foreach($template_binding as $type_id => $template) 
        { 
            $this->templates[$type_id] = $template;
        }
    }
    
    
    /**
     * Searches through an array of resources and pulls out an array of all of
     * the users in those resources
     *
	 * @param array $resources Array of resources
	 *
     * @return array
     */
    public function find_resource_users($resources, $hyperlinked = true) 
    { 
        $resource_users = array();
        foreach($resources as $resource)
        { 
            if($hyperlinked == true) 
            { 
                //Hardlinks :(
                $resource_users[] = '<a href="/photos/'.$resource->getOwner()->getUrlUsername().'">'.$resource->getOwner()->getUsername().'</a>';
            } else { 
                $resource_users[] = $resource->getOwner();
            }
        }
        
        return $resource_users;
    }    
    
    
    
    
        
    
    
    
}
