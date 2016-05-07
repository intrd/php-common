<?php 
/**
 * php - intrd common functions
 * 
* @package php-common
* @category functions
* @author intrd - http://dann.com.br/
* @copyright 2016 intrd
* @license Creative Commons Attribution-ShareAlike 4.0 - http://creativecommons.org/licenses/by-sa/4.0/
* @link https://github.com/intrd/php-common/
* Version & Dependencies: See composer.json
*/


namespace php;
class intrdCommons {

  /**
  * For safe multipart POST request for PHP5.3 ~ PHP 5.4.
  *
  * @param resource $ch cURL resource
  * @param array $assoc "name => value"
  * @param array $files "name => path"
  * @return bool
  */
  public function curl_custom_postfields($ch, array $assoc = array(), array $files = array()) {
     
      // invalid characters for "name" and "filename"
      static $disallow = array("\0", "\"", "\r", "\n");
     
      // build normal parameters
      foreach ($assoc as $k => $v) {
          $k = str_replace($disallow, "_", $k);
          $body[] = implode("\r\n", array(
              "Content-Disposition: form-data; name=\"{$k}\"",
              "",
              filter_var($v),
          ));
      }
     
      // build file parameters
      foreach ($files as $k => $v) {
          switch (true) {
              case false === $v = realpath(filter_var($v)):
              case !is_file($v):
              case !is_readable($v):
                  continue; // or return false, throw new InvalidArgumentException
          }
          $data = file_get_contents($v);
          $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
          $k = str_replace($disallow, "_", $k);
          $v = str_replace($disallow, "_", $v);
          $body[] = implode("\r\n", array(
              "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
              "Content-Type: application/octet-stream",
              "",
              $data,
          ));
      }
     
      // generate safe boundary
      do {
          $boundary = "---------------------" . md5(mt_rand() . microtime());
      } while (preg_grep("/{$boundary}/", $body));
     
      // add boundary for each parameters
      array_walk($body, function (&$part) use ($boundary) {
          $part = "--{$boundary}\r\n{$part}";
      });
     
      // add final boundary
      $body[] = "--{$boundary}--";
      $body[] = "";
     
      // set options
      return @curl_setopt_array($ch, array(
          CURLOPT_POST       => true,
          CURLOPT_POSTFIELDS => implode("\r\n", $body),
          CURLOPT_HTTPHEADER => array(
              "Expect: 100-continue",
              "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
          ),
      ));
  }

  public function getDatesFromRange($start, $end) {
      $interval = new DateInterval('P1D');

      $realEnd = new DateTime($end);
      $realEnd->add($interval);

      $period = new DatePeriod(
           new DateTime($start),
           $interval,
           $realEnd
      );

      foreach($period as $date) { 
          $array[] = $date->format('Y-m-d'); 
      }

      return $array;
  }

  public function get_browsers(){
    $browsers[]="Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0";
    $browsers[]="Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36";
    $browsers[]="Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36";
    $browsers[]="Mozilla/5.0 (Windows NT 10.0; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0";
    $browsers[]="Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36";
    $rr=rand(0,count($browsers)-1);
    return $browsers[$rr];
  }

  public function weekday_ptBR($D){
    $ptBR = array(
      'Sun' => 'Domingo', 
      'Mon' => 'Segunda-Feira',
      'Tue' => 'Terca-Feira',
      'Wed' => 'Quarta-Feira',
      'Thu' => 'Quinta-Feira',
      'Fri' => 'Sexta-Feira',
      'Sat' => 'Sábado'
    );
    return $ptBR[$D];
  }

  /**
   * format_PostData - format _POST data to SQL INSERT format
   * @param  array $postdata
   * @return text
   */
  public function format_PostData($postdata){
    $postdata="( ".implode(', ',array_keys($postdata))." ) VALUES ( '".implode('\', \'',$postdata)."'  )";
    return $postdata;
  }

  /**
   * db_clean_sqli - try to remove commom SQL injection fuzzing
   * @param  text $inp
   * @return text
   */
  public function db_clean_sqli($inp) { 
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
  public function vd($var){
  	echo"<pre>";
  	var_dump($var);
  	echo"</pre>";
  }

  public function rem_tags($valor){
    $valor=strip_tags($valor);
    //$valor=str_replace("@","",$valor);
    //$valor=str_replace("#","",$valor);
    return $valor;
  }
  public function rem_wrap($value){
      $value=trim($value);
      $vowels = array("\r\n", "\n", "\r");
      $onlyconsonants = str_replace($vowels, "", $value);
      $value=$onlyconsonants;
      //$value=str_replace(":","",$value);
      return $value;
  }

  public function url_check( $url ){
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

  public function url_get($url,$cookie_jar_file,$fperm,$header,$proxy=false,$proxyauth=false,$oauth=false){
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

  public function url_post($url,$data,$cookie_jar_file,$fperm,$header,$proxy=false,$proxyauth=false,$debug=0,$img=false){
      if (!file_exists($cookie_jar_file)) $fperm="wb";
      if(!$img){
        $fields = '';
        foreach($data as $key => $value) { 
          $fields .= $key . '=' . $value . '&'; 
          rtrim($fields, '&');
        }
      }else{
        $img = new CURLFile($data["filename"],'image/jpg',$data["name"]);
        $fields["profile_pic"]=$img;
      }
      
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
          CURLOPT_VERBOSE        => $debug, 
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_POST           => count($data),
          CURLOPT_POSTFIELDS     => $fields,
          CURLOPT_COOKIEJAR => $cookie_jar_file,
          CURLOPT_COOKIEFILE => $cookie_jar_file,
          CURLOPT_SSL_VERIFYHOST => 0

      );
      //vd($options);
      //die;

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
        vd($info);
        return $info;
      }
  }


  public function getSubstring($username,$pos){
    $username=explode("/",$username);
    $username=$username[$pos];
    //$username=strtolower($username); 
    return $username;
  }

  public function getLastSubstring($string, $boundstring, $onlyalphanum=1) {
    $string=explode($boundstring,$string);
    foreach ($string as $key=>$value){
      if ($onlyalphanum==1 and ctype_alnum($value)){
        return $value;
      }
    }
    return end($string);
  }

  public function getInnerString($start,$end,$string){
    //vd($string);
    //die;
    //echo $start;
    //die;
    $result=explode($start,$string);
    $result=$result[1];
    $result=explode($end,$result);
    $result=$result[0];
    return $result;
  }

  public function array_zip_merge() {
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

  public function increase_percent($price, $percentage){
    $price = $price * (1 + ($percentage/100));
    return $price;
  }

  public function fwrite_a($path,$text){
    $file = fopen($path,"a");
    fwrite($file,$text);
    fclose($file);
  }

  public function fwrite_w($path,$text){
    $file = fopen($path,"w");
    fwrite($file,$text);
    fclose($file);
  }


  public function trimover($string,$qty){
    if (strlen($string) > $qty){
        $string = substr($string, 0, $qty);
        $string .= "...";
        return $string;
    }else{
      return $string;
    }
  }

  public function file_checkstring($file,$string){
    if( exec('grep '.escapeshellarg($string).' '.$file)) {
      return true;
    }
  }

  public function overtrim($string,$qty){
    if (strlen($string) > $qty){
        $string = substr($string, -$qty);
        $string = "...".$string;
        return $string;
    }else{
      return $string;
    }
  }

  public function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
      $sort_col = array();
      foreach ($arr as $key=> $row) {
          $sort_col[$key] = $row[$col];
      }

      array_multisort($sort_col, $dir, $arr);
  }

  public function getIsoWeeksInYear($year) {
      $date = new DateTime;
      $date->setISODate($year, 53);
      return ($date->format("W") === "53" ? 53 : 52);
  }

  public function check_dir($array){
    foreach($array as $path){
      if (!file_exists($path)) {
          mkdir($path, 0777, true);
      }
    }
  }

}

?>
