<?php 

class Utilities 
{ 

  public static function methods($obj)
  {
    echo "<pre>";
    $arr = get_class_methods(get_class($obj));
    foreach ($arr as $method) {
      echo "function $method()<br>";
    }
    echo "</pre>";
  }
  
  public static function arrays($obj){
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
  }
  
  public static function from_camel_case($str) {
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    return preg_replace_callback('/([A-Z])/', $func, $str);
  }
 
  /**
   * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
   * @param    string   $str                     String in underscore format
   * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
   * @return   string                              $str translated into camel caps
   */
  public static function to_camel_case($str, $capitalise_first_char = false) {
    if($capitalise_first_char) {
      $str[0] = strtoupper($str[0]);
    }
    $func = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z])/', $func, $str);
  }
	  
}
