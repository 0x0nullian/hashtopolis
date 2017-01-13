<?php
use DBA\ComparisonFilter;
use DBA\File;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\StoredValue;

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
  public static function cast($obj, $to_class) {
    if (class_exists($to_class)) {
      $obj_in = serialize($obj);
      $obj_out = 'O:' . strlen($to_class) . ':"' . $to_class . '":' . substr($obj_in, $obj_in[2] + 7);
      return unserialize($obj_out);
    }
    else {
      return false;
    }
  }
  
  /**
   * Scans the import-directory for files.
   * @return array of all files in the top-level directory /../import
   */
  public static function scanImportDirectory() {
    $directory = dirname(__FILE__) . "/../import";
    if (file_exists($directory) && is_dir($directory)) {
      $importDirectory = opendir($directory);
      $importFiles = array();
      while ($file = readdir($importDirectory)) {
        if ($file[0] != '.' && $file != "." && $file != ".." && !is_dir($file)) {
          $importFiles[] = new DataSet(array("file" => $file, "size" => Util::filesize($directory . "/" . $file)));
        }
      }
      return $importFiles;
    }
    return array();
  }
  
  /**
   * Calculates variable. Used in Templates
   * @param $in mixed calculation to be done
   * @return mixed
   */
  public static function calculate($in) {
    return $in;
  }
  
  /**
   * Saves a file into the DB using the FileFactory
   * @param $path string
   * @param $name string
   * @param $type string
   * @return bool result of the save()-function.
   */
  public static function insertFile($path, $name, $type) {
    global $FACTORIES;
    
    $fileType = 0;
    if ($type == 'rule') {
      $fileType = 1;
    }
    $file = new File(0, $name, Util::filesize($path), 1, $fileType);
    $file = $FACTORIES::getFileFactory()->save($file);
    if ($file == null) {
      return false;
    }
    return true;
  }
  
  /**
   * Get the next task for an agent
   * @param $agent \DBA\Agent should be the object
   * @param $priority int
   * @return \DBA\Task
   */
  public static function getNextTask($agent, $priority = 0) {
    global $FACTORIES;
    
    //TODO: handle the case, if a task is a single assignment task
    $priorityFilter = new QueryFilter("priority", $priority, ">");
    $trustedFilter = new QueryFilter("secret", $agent->getIsTrusted(), "<=", $FACTORIES::getHashlistFactory()); //check if the agent is trusted to work on this hashlist
    $cpuFilter = new QueryFilter("isCpuTask", $agent->getCpuOnly(), "="); //assign non-cpu tasks only to non-cpu agents and vice versa
    $crackedFilter = new ComparisonFilter("cracked", "hashCount", "<");
    //$qF5 = new QueryFilter("secret", $agent->getIsTrusted(), "<=", $FACTORIES::getFileFactory());
    $hashlistIDJoin = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
    //$jF2 = new JoinFilter($FACTORIES::getTaskFileFactory(), "taskId", "taskId");
    //$jF3 = new JoinFilter($FACTORIES::getFileFactory(), "fileId", "fileId", $FACTORIES::getTaskFileFactory());
    $descOrder = new OrderFilter("priority", "DESC LIMIT 1");
    $nextTask = $FACTORIES::getTaskFactory()->filter(array('filter' => array($priorityFilter, $trustedFilter, $cpuFilter, $crackedFilter), 'join' => array($hashlistIDJoin), 'order' => array($descOrder)));
    if (sizeof($nextTask['Task']) > 0) {
      return $nextTask['Task'][0];
    }
    return null;
  }
  
  /**
   * Used by the solver. Cleans the zap-queue
   */
  public static function zapCleaning() {
    //TODO NOT YET IMPLEMENTED
    global $FACTORIES;
    
    $entry = $FACTORIES::getStoredValueFactory()->get("lastZapCleaning");
    if ($entry == null) {
      $entry = new StoredValue("lastZapCleaning", 0);
      $FACTORIES::getStoredValueFactory()->save($entry);
    }
    if (time() - $entry->getVal() > 600) {
      //TODO: zap cleaning
      $entry->setVal(time());
      $FACTORIES::getStoredValueFactory()->update($entry);
    }
  }
  
  /**
   * @param $file string Filepath you want to get the size from
   * @return int -1 if the file doesn't exist. Else filesize()
   */
  public static function filesize($file) {
    //TODO: put code for 64-bit file size determination here
    if (!file_exists($file)) {
      return -1;
    }
    return filesize($file);
  }
  
  /**
   * Refreshes the page
   */
  public static function refresh() {
    global $_SERVER;
    
    $url = $_SERVER['PHP_SELF'];
    if (strlen($_SERVER['QUERY_STRING']) > 0) {
      $url .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: $url");
    die();
  }
  
  /**
   * @param $list hashlist-object
   * @return Hashlist[] of all superhashlists belonging to the $list
   */
  public static function checkSuperHashlist($list) {
    global $FACTORIES;
    
    if ($list->getFormat() == 3) {
      $hashlistJoinFilter = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
      $superHashListFilter = new QueryFilter("superHashlistId", $list->getId(), "=");
      $joined = $FACTORIES::getSuperHashlistHashlistFactory()->filter(array('join' => array($hashlistJoinFilter), 'filter' => array($superHashListFilter)));
      $lists = $joined['Hashlist'];
      return $lists;
    }
    return array($list);
  }
  
  //OLD PART
  /**
   * @return string 0.0.0.0 or the client IP
   */
  public static function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (!$ip) {
      return "0.0.0.0";
    }
    return $ip;
  }
  
  /**
   * Checks if a file is writable
   */
  public static function checkWriteFiles($arr) {
    foreach ($arr as $path) {
      if (!is_writable($path)) {
        return false;
      }
    }
    return true;
  }
  
  /**
   * Iterates through all chars, converts them to 0x__ and concats the hexes
   * @param $binString String you want to convert
   * @return string Hex-String
   */
  public static function bintohex($binString) {
    $return = "";
    for ($i = 0; $i < strlen($binString); $i++) {
      $hex = dechex(ord($binString[$i]));
      while (strlen($hex) < 2) {
        $hex = "0" . $hex;
      }
      $return .= $hex;
    }
    return $return;
  }
  
  /**
   * @param $prog int progress so far
   * @param $total int total to be done
   * @return string either the check.png with Finished or an empty string
   */
  public static function tickdone($prog, $total) {
    // show tick of progress is done
    if ($total > 0 && $prog == $total) {
      return " <img src='static/check.png' alt='Finished'>";
    }
    return "";
  }
  
  /**
   * Used in Template
   * @param $id int ID for the user
   * @return string username or unknown-id
   */
  public static function getUsernameById($id) {
    global $FACTORIES;
    
    $user = $FACTORIES::getUserFactory()->get($id);
    if ($user === null) {
      return "Unknown-$id";
    }
    return $user->getUsername();
  }
  
  /**
   * Used in Template. Subtracts two variables
   */
  public static function subtract($x, $y) {
    return ($x - $y);
  }
  
  /**
   * Used in Template. Converts seconds to human readable format
   * @param $seconds
   * @return string
   */
  public static function sectotime($seconds) {
    $return = "";
    if ($seconds > 86400) {
      $days = floor($seconds / 86400);
      if ($days > 0) {
        $return .= $days . "d ";
      }
      $seconds = $seconds % 86400;
    }
    $return .= gmdate("H:i:s", $seconds);
    return $return;
  }
  
  /**
   * Used in Template
   * @param $val string of the array
   * @param $id int index of the array
   * @return string the element or empty string
   */
  public static function getStaticArray($val, $id) {
    $platforms = array(
      "unknown",
      "NVidia",
      "AMD",
      "CPU"
    );
    $oses = array(
      "<img src='static/win.png' alt='Win' title='Windows'>",
      "<img src='static/unix.png' alt='Unix' title='Linux'>"
    );
    $formats = array(
      "Text",
      "HCCAP",
      "Binary",
      "Superhashlist"
    );
    $formattables = array(
      "hashes",
      "hashes_binary",
      "hashes_binary"
    );
    $states = array(
      "New",
      "Init",
      "Running",
      "Paused",
      "Exhausted",
      "Cracked",
      "Aborted",
      "Quit",
      "Bypass",
      "Trimmed",
      "Aborting..."
    );
    switch ($id) {
      case 'os':
        return $oses[$val];
        break;
      case 'states':
        return $states[$val];
        break;
      case 'formats':
        return $formats[$val];
        break;
      case 'formattables':
        return $formattables[$val];
        break;
      case 'platforms':
        if ($val == '-1') {
          return $platforms;
        }
        return $platforms[$val];
        break;
    }
    return "";
  }
  
  /**
   * @param $num int integer you want formatted
   * @param int $threshold default 1024
   * @param int $divider default 1024
   * @return string Formatted Integer
   */
  public static function nicenum($num, $threshold = 1024, $divider = 1024) {
    $r = 0;
    while ($num > $threshold) {
      $num /= $divider;
      $r++;
    }
    $rs = array(
      "",
      "k",
      "M",
      "G"
    );
    $return = Util::niceround($num, 2);
    return $return . " " . $rs[$r];
  }
  
  /**
   * Formats percentage nicely
   * @param $part int progress
   * @param $total int total value
   * @param int $decs decimals you want rounded
   * @return string formatted percentage
   */
  public static function showperc($part, $total, $decs = 2) {
    if ($total > 0) {
      $percentage = round(($part / $total) * 100, $decs);
      if ($percentage == 100 && $part < $total) {
        $percentage -= 1 / (10 ^ $decs);
      }
      if ($percentage == 0 && $part > 0) {
        $percentage += 1 / (10 ^ $decs);
      }
    }
    else {
      $percentage = 0;
    }
    $return = Util::niceround($percentage, $decs);
    return $return;
  }
  
  /**
   * @param $target string File you want to write to
   * @param $type string paste, upload, import or url
   * @param $sourcedata string
   * @return array (boolean, string) success, msg detailing what happened
   */
  public static function uploadFile($target, $type, $sourcedata) {
    //global $uperrs;
    
    $success = false;
    $msg = "<b>Adding file $target:</b><br>";
    if (!file_exists($target)) {
      switch ($type) {
        case "paste":
          $msg .= "Creating file from text field...";
          if (file_put_contents($target, $sourcedata)) {
            $msg .= "OK";
            $success = true;
          }
          else {
            $msg .= "ERROR!";
          }
          break;
        
        case "upload":
          $hashfile = $sourcedata;
          if ($hashfile["error"] == 0) {
            $msg .= "Moving uploaded file...";
            if (move_uploaded_file($hashfile["tmp_name"], $target) && file_exists($target)) {
              $msg .= "OK";
              $success = true;
            }
            else {
              $msg .= "ERROR";
            }
          }
          else {
            $msg .= "Upload file error: "; //. $uperrs[$hashfile["error"]];
          }
          break;
        
        case "import":
          $msg .= "Loading imported file...";
          if (file_exists("import/" . $sourcedata)) {
            rename("import/" . $sourcedata, $target);
            if (file_exists($target)) {
              $msg .= "OK";
              $success = true;
            }
            else {
              $msg .= "Could not move source to target";
            }
          }
          else {
            $msg .= "Source file does not exist";
          }
          break;
        
        case "url":
          $local = basename($sourcedata);
          $msg .= "Downloading remote file <a href=\"$sourcedata\" target=\"_blank\">$local</a>...";
          
          $furl = fopen($sourcedata, "rb");
          if (!$furl) {
            $msg .= "Could not open url at source data";
          }
          else {
            $fileLocation = fopen($target, "w");
            if (!$fileLocation) {
              $msg .= "Could not open target";
            }
            else {
              $downed = 0;
              $buffersize = 131072;
              $last_logged = time();
              while (!feof($furl)) {
                if (!$data = fread($furl, $buffersize)) {
                  $msg .= "READ ERROR";
                  break;
                }
                fwrite($fileLocation, $data);
                $downed += strlen($data);
                if ($last_logged < time() - 10) {
                  $msg .= Util::nicenum($downed, 1024) . "B...\n";
                  $last_logged = time();
                }
              }
              fclose($fileLocation);
              $msg .= "OK (" . Util::nicenum($downed, 1024) . "B)";
              $success = true;
            }
            fclose($furl);
          }
          break;
        
        default:
          $msg .= "Unknown import type.";
          break;
      }
    }
    else {
      $msg .= "File already exists.";
    }
    $msg .= "<br>";
    return array($success, $msg);
  }
  
  /**
   * Round to a specific amount of decimal points
   * @param $num Number
   * @param $dec Number of decimals
   * @return string Rounded value
   */
  public static function niceround($num, $dec) {
    $return = strval(round($num, $dec));
    if ($dec > 0) {
      $pointPosition = strpos($return, ".");
      if ($pointPosition === false) {
        $return .= ".00";
      }
      else {
        while (strlen($return) - $pointPosition <= $dec) {
          $return .= "0";
        }
      }
    }
    return $return;
  }
  
  /**
   * Cut a string to a certain number of letters. If the string is too long, instead replaces the last three letters with ...
   * @param $string String you want to short
   * @param $length Number of Elements you want the string to have
   * @return string Formatted string
   */
  public static function shortenstring($string, $length) {
    // shorten string that would be too long
    $return = "<span title='$string'>";
    if (strlen($string) > $length) {
      $return .= substr($string, 0, $length - 3) . "...";
    }
    else {
      $return .= $string;
    }
    $return .= "</span>";
    return $return;
  }
  
  /**
   * Adds 0s to the beginning of a number until it reaches size.
   */
  public static function prefixNum($number, $size) {
    $formatted = "" . $number;
    while (strlen($formatted) < $size) {
      $formatted = "0" . $formatted;
    }
    return $formatted;
  }
  
  /**
   * Converts a given string to hex code.
   *
   * @param string $string
   *          string to convert
   * @return string converted string into hex
   */
  public static function strToHex($string) {
    return implode(unpack("H*", $string));
  }
  
  /**
   * This sends a given email with text and subject to the address.
   *
   * @param string $address
   *          email address of the receiver
   * @param string $subject
   *          subject of the email
   * @param string $text
   *          html content of the email
   * @return true on success, false on failure
   */
  public static function sendMail($address, $subject, $text) {
    $header = "Content-type: text/html; charset=utf8\r\n";
    $header .= "From: Hashtopussy <noreply@hashtopussy>\r\n";
    if (!mail($address, $subject, $text, $header)) {
      return false;
    }
    return true;
  }
  
  /**
   * Generates a random string with mixedalphanumeric chars
   *
   * @param int $length
   *          length of random string to generate
   * @return string random string
   */
  public static function randomString($length) {
    $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $result = "";
    for ($x = 0; $x < $length; $x++) {
      $result .= $charset[mt_rand(0, strlen($charset) - 1)];
    }
    return $result;
  }
  
  /**
   * TODO Document me
   */
  public static function createPrefixedString($table, $dict) {
    $arr = array();
    foreach ($dict as $key => $val) {
      $arr[] = "`" . $table . "`" . "." . "`" . $key . "`" . " AS `" . $table . "." . $key . "`";
    }
    return implode(", ", $arr);
  }
  
  /**
   * Checks if $search starts with $pattern. Shortcut for strpos==0
   */
  public static function startsWith($search, $pattern) {
    if (strpos($search, $pattern) === 0) {
      return true;
    }
    return false;
  }
  
  /**
   * if pattern is empty or if pattern is at the end of search
   */
  public static function endsWith($search, $pattern) {
    // search forward starting from end minus needle length characters
    return $pattern === "" || (($temp = strlen($search) - strlen($pattern)) >= 0 && strpos($search, $pattern, $temp) !== FALSE);
  }
  
  /**
   * Converts a hex to binary
   */
  public static function hextobin($data) {
    $res = "";
    for ($i = 0; $i < strlen($data) - 1; $i += 2) {
      $res .= chr(hexdec(substr($data, $i, 2)));
    }
    return $res;
  }
  
  /**
   * Get an alert div with type and msg
   */
  public static function getMessage($type, $msg) {
    return "<div class='alert alert-$type'>$msg</div>";
  }
}
