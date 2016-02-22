<?php 
/**
 * php - intrd common functions
 * 
* @package php-common
* @version 1.0
* @category functions
* @author intrd - http://dann.com.br/
* @copyright 2015 intrd
* @license http://creativecommons.org/licenses/by/4.0/
* @link https://github.com/intrd/php-common/
*
*/

/**
 * format_PostData - format _POST data to SQL INSERT format
 * @param  array $postdata
 * @return text
 */
function format_PostData($postdata){
  $postdata="( ".implode(', ',array_keys($postdata))." ) VALUES ( '".implode('\', \'',$postdata)."'  )";
  return $postdata;
}

/**
 * db_clean_sqli - try to remove commom SQL injection fuzzing
 * @param  text $inp
 * @return text
 */
function db_clean_sqli($inp) { 
    if(is_array($inp)) 
        return array_map(__METHOD__, $inp); 
    if(!empty($inp) && is_string($inp)) { 
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
    } 
    return $inp; 
}

/**
 * vd - var_dumb beautifier
 * @param  $array/object $var 
 * @return var_dump w/ <pre>
 */
function vd($var){
	echo"<pre>";
	var_dump($var);
	echo"</pre>";
}

function url_check( $url ){
  if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) { //checa se é uma url valida antes de jogar no curl
    return "invalid and malformed URL";
  }
  $options = array(
        CURLOPT_RETURNTRANSFER => true,     
        CURLOPT_HEADER         => false,    
        CURLOPT_FOLLOWLOCATION => true,    
        CURLOPT_ENCODING       => "",       
        CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0", 
        CURLOPT_AUTOREFERER    => true,     
        CURLOPT_CONNECTTIMEOUT => 120,      
        CURLOPT_TIMEOUT        => 120,      
        CURLOPT_MAXREDIRS      => 10,       
        CURLOPT_SSL_VERIFYPEER => false     
    );
  $ch      = curl_init( $url );
  curl_setopt_array( $ch, $options );
  $content = curl_exec( $ch );
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );
  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  if($header['http_code'] == 200){
    return $header['http_code'];
  }else{
    return $header['errmsg'];
  }
}

function url_get($url,$cookie_jar_file,$fperm,$header,$proxy=false,$proxyauth=false,$oauth=false){
  if (!file_exists($cookie_jar_file)) $fperm="wb";
  $fp = fopen($cookie_jar_file, $fperm);
  $options = array(
    CURLOPT_HEADER => 1,   
    CURLOPT_HTTPHEADER => $header, 
    CURLOPT_RETURNTRANSFER => true,     
    CURLOPT_HEADER         => false,    
    CURLOPT_FOLLOWLOCATION => true,     
    CURLOPT_ENCODING       => "",       
    CURLOPT_AUTOREFERER    => true,     
    CURLOPT_CONNECTTIMEOUT => 120,      
    CURLOPT_TIMEOUT        => 120,      
    CURLOPT_MAXREDIRS      => 10,      
    CURLOPT_VERBOSE      => 0, 
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_COOKIEJAR => $cookie_jar_file,
    CURLOPT_COOKIEFILE => $cookie_jar_file,
    CURLOPT_SSL_VERIFYHOST => 0
  );
  //die;
  $ch      = curl_init( $url );
  curl_setopt_array( $ch, $options );
  if($proxy){
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    if($proxyauth) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
  }
  if($oauth){
    curl_setopt($ch, CURLOPT_USERPWD, $oauth);
  }
  $content = curl_exec( $ch );
  //die;
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );
  fclose($fp);
  //vd($ch);
  //die;
  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  $info["header"]=$header;
  $info["content"]=$content;
  if($header['http_code'] == 200){
      return $info;
  }else{
      echo $header['errmsg'];
      return false;
  }
}

function url_post($url,$data,$cookie_jar_file,$fperm,$header,$proxy=false,$proxyauth=false){
    if (!file_exists($cookie_jar_file)) $fperm="wb";
    $fields = '';
    foreach($data as $key => $value) { 
      $fields .= $key . '=' . $value . '&'; 
    }
    //vd($fields);
    //vd($url);
    //die;
    rtrim($fields, '&');
    $fp = fopen($cookie_jar_file, $fperm);
    $options = array(
        CURLOPT_HEADER => 1,   
        CURLOPT_HTTPHEADER => $header, 
        CURLOPT_RETURNTRANSFER => true,     
        CURLOPT_HEADER         => false,    
        CURLOPT_FOLLOWLOCATION => true,     
        CURLOPT_ENCODING       => "",       
        CURLOPT_CONNECTTIMEOUT => 120,      
        CURLOPT_TIMEOUT        => 120,      
        CURLOPT_MAXREDIRS      => 10,      
        CURLOPT_VERBOSE        => 0, 
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST           => count($data),
        CURLOPT_POSTFIELDS     => $fields,
        CURLOPT_COOKIEJAR => $cookie_jar_file,
        CURLOPT_COOKIEFILE => $cookie_jar_file,
        CURLOPT_SSL_VERIFYHOST => 0

    );

    //echo $cookie_jar_file;
    //vd($options);
    //die;
    //vd($url);
    //die;
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    if($proxy){
      curl_setopt($ch, CURLOPT_PROXY, $proxy);
      if($proxyauth) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
    }
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
    fclose($fp);
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    //vd($header);
    $info["header"]=$header;
    $info["content"]=$content;
    //vd($header['http_code']);
    

    if($header['http_code'] == 200){
        return $info;
    }else{
      //vd($header);
      //die;
      //vd($info);
      //die;
      //die;
      //die;
        echo $header['errmsg'];
        return false;
    }
}


function getSubstring($username,$pos){
  $username=explode("/",$username);
  $username=$username[$pos];
  //$username=strtolower($username); 
  return $username;
}

function getLastSubstring($string, $boundstring, $onlyalphanum=1) {
  $string=explode($boundstring,$string);
  foreach ($string as $key=>$value){
    if ($onlyalphanum==1 and ctype_alnum($value)){
      return $value;
    }
  }
  return end($string);
}

function getInnerString($start,$end,$string){
  //echo $start;
  //die;
  $result=explode($start,$string);
  $result=$result[1];
  $result=explode($end,$result);
  $result=$result[0];
  return $result;
}

function array_zip_merge() {
  $output = array();
  // The loop incrementer takes each array out of the loop as it gets emptied by array_shift().
  for ($args = func_get_args(); count($args); $args = array_filter($args)) {
    // &$arg allows array_shift() to change the original.
    foreach ($args as &$arg) {
      $output[] = array_shift($arg);
    }
  }
  return $output;
}

function increase_percent($price, $percentage){
  $price = $price * (1 + ($percentage/100));
  return $price;
}

function fwrite_a($path,$text){
  $file = fopen($path,"a");
  fwrite($file,$text);
  fclose($file);
}


function trimover($string,$qty){
  if (strlen($string) > $qty){
      $string = substr($string, 0, $qty);
      $string .= "...";
      return $string;
  }else{
    return $string;
  }
}

function file_checkstring($file,$string){
  if( exec('grep '.escapeshellarg($string).' '.$file)) {
    return true;
  }
}

function overtrim($string,$qty){
  if (strlen($string) > $qty){
      $string = substr($string, -$qty);
      $string = "...".$string;
      return $string;
  }else{
    return $string;
  }
}

function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

function getIsoWeeksInYear($year) {
    $date = new DateTime;
    $date->setISODate($year, 53);
    return ($date->format("W") === "53" ? 53 : 52);
}
?>
