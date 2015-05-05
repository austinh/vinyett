<?php


namespace Vinyett\CoreServicesBundle\Extension;

class TimeTwigExtension extends \Twig_Extension {

    public function getFilters() {
        return array(
            'render_ago'  => new \Twig_Filter_Method($this, 'render_ago'),
            'render_date'  => new \Twig_Filter_Method($this, 'render_date'),
            'render_isodate'  => new \Twig_Filter_Method($this, 'render_isodate'),
            'render_nice_date'  => new \Twig_Filter_Method($this, 'render_nice_date'),
        );
    }

    function render_isodate($datetime) 
    { 
      return $datetime->format(\DateTime::ISO8601);
    }
    
    function render_nice_date($datetime) 
    { 
        return $datetime->format("M jS, Y \a\\t g:ia");
    }
    
    function render_date($time) 
    {
      
    }
    
    function render_ago($time)
    {
    
       $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
       $lengths = array("60","60","24","7","4.35","12","10");
    
       $now = time();
    
           $difference     = $now - $time->getTimestamp();
           $tense         = "ago";
    
       for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
           $difference /= $lengths[$j];
       }
    
       $difference = round($difference);
    
      //Exceptions (Less than 10 seconds says "Just a few seconds ago")
      if($periods[$j] == "second" && $difference <= 10)
      { 
        return "Just a few seconds ago";
      }
  
      //Exceptions (less than 59 seconds says "A few moments ago")
      if($periods[$j] == "second" && $difference <= 59)
      { 
        return "A few moments ago";
      }
    
      //Exceptions (less than 2 minutes ago says "About a minute ago")
      if($periods[$j] == "minute" && $difference < 2)
      { 
        return "About a minute ago";
      }
      
      //Exceptions (anytime this week will state On Friday)
      if($periods[$j] == "day") 
      { 
        return "On ".ucfirst(date("l", $time->getTimestamp()));
      }
      
      //If requests are older than 1 week we show the original post date ("Last Friday")
      if($periods[$j] == "week" && $difference > 2) 
      {
        return "Last ".ucfirst(date("l", $time->getTimestamp()));
      }
    
      //Add punctuation
       if($difference != 1) {
           $periods[$j].= "s";
       }
    
       return "$difference $periods[$j] ago";
    }

    public function getName()
    {
        return 'time_twig_extension';
    }

}
