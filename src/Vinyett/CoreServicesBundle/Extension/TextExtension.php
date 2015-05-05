<?php


namespace Vinyett\CoreServicesBundle\Extension;

class TextExtension extends \Twig_Extension {

    public function getName()
    {
        return 'text_twig_extension';
    }
    
    public function getFunctions() 
    {
        return array(
            'implode_array_into_listphrase'   => new \Twig_Function_Method($this, 'implode_array_into_listphrase', array('is_safe' => array('html'))),
            'pluralize' => new \Twig_Function_Method($this, 'pluralize'),
            //'truncate' => new \Twig_Function_Method($this, 'truncate'),
        );
    } 
    
    
    
    public function pluralize($number, $word, $flip = false) { 
    
    	if($number != "1") { 
    	
    		$endchar = substr($word, -1); 
    		if($endchar == "s") { 
    			$plural = "es";
    		} else { 
    			$plural = "s"; 
    		}
    		
    	$word = $word.$plural;
    	
    	}
    	
    	return $word;
    
    }
		
		
    public function isPluralActionVerb($number) 
    { 
        if($number == "1") {
        	$verb = "is"; 
        } else { 
        	$verb = "are";
        }
        
        return $verb;
    }
    
    public function truncate($string, $limit, $break=".", $pad="...")
    {
    	 // return with no change if string is shorter than $limit
    	if(strlen($string) <= $limit) return $string;
    
    	// is $break present between $limit and the end of the string?
    	if(false !== ($breakpoint = strpos($string, $break, $limit))) {
    	  if($breakpoint < strlen($string) - 1) {
    		   $string = substr($string, 0, $breakpoint) . $pad;
    		 }
    	 }
    
     return $string;
    }
	
   public function render_with_period($text) {
     if ($text) {
        // this prevents us from adding an extra period at the end unnecessarily
        $text = rtrim($text);

        $len = strlen($text);
        $quot = array(6 => array('&#039;' => 1,
                                 '&quot;' => 1),
                      4 => array('</a>' => 1),
                      1 => array('\'' => 1,
                                 '"' => 1,
                                 ')' => 0));
        $punc = array('.' => 1, '?' => 1, '!' => 1, ':' => 1);

        // Put a period inside closing quotes if not there already
        foreach ($quot as $l => $quotes) {
            $last = substr($text, $len-$l);
            if (isset($quotes[$last])) {
            // already has a period inside it
            if (isset($punc[$text{$len-$l-1}])) {
                return $text;
            }
            // needs a period added inside it
            if ($quotes[$last]) {
                return substr($text, 0, $len-$l).'.'.substr($text, $len-$l);
            }
            // should add a period after it, skip to below
            break;
            }
        }

        // Append a period if text doesn't already end in one of . ? !
        if (!isset($punc[$text{$len-1}])) {
            $text .= '.';
        }
    }
    return $text;
    }
	
	
	public function implode_array_into_listphrase($listarray) {
        $total = count($listarray);

        switch ($total) {
            case 0:
            case 1:
                $return = (string)reset($listarray);
            break;
            case 2:
                $return = $listarray[0] . ' and ' . $listarray[1];
            break;
            case 3:
                $return = $listarray[0] . ', ' . $listarray[1] . ' and ' . $listarray[2];
            break;
            default:
                $return = $this->render_large_list($listarray);
            break;
        }
        return $this->sanitize_summary_text($return);
    }

    public function render_large_list($items, $is_or = null, $locale = null) {
        if (! $items || count($items) == 0) {
            return "";
        }

        $count = count($items);

        if ($count < 4) {
            error_log("Call to render_large_list with a small list; please " .
               "code explicit cases in calling code for small numbers of " .
               "items to allow for better translations.");
        }

        // Get rid of key/value pairs so we can access the array numerically.
        $items = array_values($items);
        if ($count == 1) {
            return $items[0];
        }

        $first_count = (int)(($count - 1) / 2);
        $second_count = $count - 1 - $first_count;
        $rest_of_list = $this->_render_list_fragment(
                                            array_slice($items, 0, $first_count),
                                            array_slice($items, $first_count, $second_count),
                                            $is_or, $locale);
        if ($is_or) {
            return $rest_of_list . ' or ' . $items[$count - 1];
        } else {
            return $rest_of_list . ' and ' . $items[$count - 1];
        }
    }

    /**
     * Based on _render_list_fragment from FB Open Platform.
     * Read their description for the best example. 
     *
     * Renders a fragment of a list. Helper function for render_large_list().
     * Splits the list in half recursively such that the depth of nesting of
     * fb:intl tags is O(log n). This is needed because we limit the depth of
     * the PHP function call stack, and the naive implementation (stringing
     * fb:intl tags together in a linear walk through the list) makes us
     * bomb out with a stack-depth fatal in real-world cases.
     *
     * All this for a function people probably ought not to be calling from a
     * UI perspective anyway!
     */
    
     public function _render_list_fragment($first_half, $second_half, $is_or, $locale) {
        $first_count = count($first_half);
        $second_count = count($second_half);

        if ($first_count == 1 && $second_count == 0) {
            return $first_half[0];
        } else if ($first_count == 0 && $second_count == 1) {
            return $second_half[0];
        } else if ($first_count == 1 && $second_count == 1) {
                $item1 = $first_half[0];
                $item2 = $second_half[0];
        } else {
                $item1 = $this->_render_list_fragment(
                                        array_slice($first_half, 0, $first_count / 2),
                                        array_slice($first_half, $first_count / 2),
                                        $is_or, $locale);
                $item2 = $this->_render_list_fragment(
                                        array_slice($second_half, 0, $second_count / 2),
                                        array_slice($second_half, $second_count / 2),
                                        $is_or, $locale);
        }
        
        if ($is_or) {
            return $item1 .', ' . $item2;
        } else {
            return $item1 .', ' . $item2;
        }
    }


    /**
     * Sanitizes a paragraph that that uses other functions below.  Currently just removes
     * excess commas
     *
     * @param string $string
     * @return string
     */
    public function sanitize_summary_text($string)
    {
        $string = str_replace(",,", ",", $string);
        $string = str_replace(",.", ".", $string);
        return $string;
    }

    
}
