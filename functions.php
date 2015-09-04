<?php 
/**
 * php - intrd common functions
 * 
* @package php-common
* @version 1.0
* @category functions
* @author intrd - http://dann.com.br/
* @link https://github.com/intrd/php-common/
* @copyright 2015 intrd
* @license http://creativecommons.org/licenses/by/4.0/
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

/**
 * mc_encrypt - Highly Secure Data Encryption (MCrypt, Rijndael-256, and CBC)
 * @param  text $encrypt 
 * @return text          
 */
function mc_encrypt($encrypt){
	$key=ENCRYPTION_KEY;
    $encrypt = serialize($encrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*', $key);
    $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
    return $encoded;
}
/**
 * mc_decrypt - Highly Secure Data Encryption (MCrypt, Rijndael-256, and CBC)
 * @param  text $decrypt 
 * @return text          
 */
function mc_decrypt($decrypt){
	$key=ENCRYPTION_KEY;
    $decrypt = explode('|', $decrypt.'|');
    $decoded = base64_decode($decrypt[0]);
    $iv = base64_decode($decrypt[1]);
    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    $key = pack('H*', $key);
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if($calcmac!==$mac){ return false; }
    $decrypted = unserialize($decrypted);
    return $decrypted;
}


function url_get($url,$cookie_jar_file,$fperm,$header){
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
  $content = curl_exec( $ch );
  //die;
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );
  fclose($fp);
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

function url_post($url,$data,$cookie_jar_file,$fperm,$header){
    if (!file_exists($cookie_jar_file)) $fperm="wb";
    $fields = '';
    foreach($data as $key => $value) { 
      $fields .= $key . '=' . $value . '&'; 
    }
    //vd($fields);
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
    //vd($options);
    //die;
    //vd($url);
    //die;
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
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
    if($header['http_code'] == 200){
        return $info;
    }else{
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
  $result=explode($start,$string);
  $result=$result[1];
  $result=explode($end,$result);
  $result=$result[0];
  return $result;
}

?>