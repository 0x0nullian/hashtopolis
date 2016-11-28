<?php

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
  
  public static function scanImportDirectory(){
    if (file_exists(dirname(__FILE__)."/import") && is_dir(dirname(__FILE__)."/import")) {
      $impdir = opendir(dirname(__FILE__)."/import");
      $impfiles = array();
      while ($f = readdir($impdir)) {
        if ($f[0] != '.' && $f != "." && $f != ".." && !is_dir($f)) {
          $impfiles[] = $f;
        }
      }
      return $impfiles;
    }
    return array();
  }
  
  public static function filesize($file){
    //TODO: put code for 64-bit file size determination here
    if(!file_exists($file)){
      return 0;
    }
    return filesize($file);
  }
  
  public static function refresh() {
    header("Location: " . $_SERVER['PHP_SELF'] . (strlen($_SERVER['QUERY_STRING'])>0)?"?":"" . $_SERVER['QUERY_STRING']);
    die();
  }
  
  public static function checkSuperHashlist($list) {
    global $FACTORIES;
    //detect superhashlists and create array of all lists
    
    if ($list->getFormat() == 3) {
      $jF = new JoinFilter("Hashlist", "hashlistId", "hashlistId");
      $qF = new QueryFilter("superHashlistId", $list->getId(), "=");
      $joined = $FACTORIES::getSuperHashlistHashlistFactory()->filter(array('join' => array($jF), 'filter' => array($qF)));
      $lists = $joined['Hashlist'];
      return $lists;
    }
    return array($list);
  }
  
  //OLD PART
  
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
   * Checks if a given email is of valid syntax
   *
   * @param string $email
   *          email address to check
   * @return true if valid email, false if not
   */
  public static function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }
  
  public static function checkWriteFiles($arr) {
    foreach ($arr as $path) {
      if (!is_writable($path)) {
        return false;
      }
    }
    return true;
  }
  
  public static function tickdone($prog, $total) {
    // show tick of progress is done
    if ($total > 0 && $prog == $total) {
      return " <img src='static/check.png' alt='Finished'>";
    }
    return "";
  }
  
  public static function getUsernameById($id) {
    global $FACTORIES;
    
    $user = $FACTORIES::getUserFactory()->get($id);
    if ($user === null) {
      return "Unknown-$id";
    }
    return $user->getUsername();
  }
  
  public static function writecache() {
    // flush temporary cache to the actual tables
    global $tbl, $crack_cas, $cid, $agid, $superhash, $hlist, $DB;
    $DB->query("UPDATE tmphlcracks SET zaps=1 WHERE cracked>0");
    if ($superhash) {
      $DB->query("UPDATE hashlists SET cracked=cracked+(SELECT IFNULL(SUM(cracked),0) FROM tmphlcracks) WHERE id=$hlist");
    }
    $DB->query("UPDATE hashlists JOIN tmphlcracks ON hashlists.id=tmphlcracks.hashlist SET hashlists.cracked=hashlists.cracked+tmphlcracks.cracked");
    $DB->query("INSERT IGNORE INTO zapqueue (hashlist,agent,time,chunk) SELECT hashlistusers.hashlist,hashlistusers.agent,$crack_cas,$cid FROM hashlistusers JOIN tmphlcracks ON hashlistusers.hashlist=tmphlcracks.hashlist AND tmphlcracks.zaps=1 WHERE hashlistusers.agent!=$agid");
    // increase the timer so the chunks won't timeout during the result writing
    $crack_cas = time();
    $DB->query("UPDATE chunks SET cracked=cracked+(SELECT IFNULL(SUM(cracked),0) FROM tmphlcracks),solvetime=$crack_cas WHERE id=$cid");
    $DB->query("UPDATE tmphlcracks SET cracked=0,zaps=0");
  }
  
  public static function subtract($x, $y) {
    return ($x - $y);
  }
  
  public static function delete_task($task) {
    global $FACTORIES;
    
    $qF = new QueryFilter("taskId", $task, "=");
    $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => array($qF)));
    $FACTORIES::getAgentErrorFactory()->massDeletion(array('filter' => array($qF)));
    $FACTORIES::getTaskFileFactory()->massDeletion(array('filter' => array($qF)));
    
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => array($qF)));
    if ($chunks) {
      foreach ($chunks as $chunk) {
        $qF = new QueryFilter("", $value, $operator);
      }
    } //TODO: will not work until refactored
    
    $ans4 = $ans3 && $DB->query("UPDATE hashes JOIN chunks ON hashes.chunk=chunks.id AND chunks.task=$task SET chunk=NULL");
    $ans5 = $ans4 && $DB->query("UPDATE hashes_binary JOIN chunks ON hashes_binary.chunk=chunks.id AND chunks.task=$task SET chunk=NULL");
    $ans6 = $ans5 && $DB->query("DELETE FROM zapqueue WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
    $ans7 = $ans6 && $DB->query("DELETE FROM chunks WHERE task=$task");
    
    $ans8 = $ans7 && $DB->query("DELETE FROM tasks WHERE id=$task");
    
    return ($ans8);
  }
  
  public static function bintohex($dato) {
    $ndato = "";
    for ($i = 0; $i < strlen($dato); $i++) {
      $zn = dechex(ord($dato[$i]));
      while (strlen($zn) < 2) {
        $zn = "0" . $zn;
      }
      $ndato .= $zn;
    }
    return $ndato;
  }
  
  public static function sectotime($soucet) {
    // convert seconds to human readable format
    $vysledek = "";
    if ($soucet > 86400) {
      $dnu = floor($soucet / 86400);
      if ($dnu > 0) {
        $vysledek .= $dnu . "d ";
      }
      $soucet = $soucet % 86400;
    }
    $vysledek .= gmdate("H:i:s", $soucet);
    return $vysledek;
  }
  
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
  
  public static function nicenum($num, $treshold = 1024, $divider = 1024) {
    // display nicely formated number divided into correct units
    $r = 0;
    while ($num > $treshold) {
      $num /= $divider;
      $r++;
    }
    $rs = array(
      "",
      "k",
      "M",
      "G"
    );
    $vysnew = Util::niceround($num, 2);
    return $vysnew . " " . $rs[$r];
  }
  
  public static function showperc($part, $total, $decs = 2) {
    // show nicely formated percentage
    if ($total > 0) {
      $vys = round(($part / $total) * 100, $decs);
      if ($vys == 100 && $part < $total) {
        $vys -= 1 / (10 ^ $decs);
      }
      if ($vys == 0 && $part > 0) {
        $vys += 1 / (10 ^ $decs);
      }
    }
    else {
      $vys = 0;
    }
    $vysnew = Util::niceround($vys, $decs);
    return $vysnew;
  }
  
  public static function uploadFile($tmpfile, $source, $sourcedata) {
    // upload file from multiple sources
    global $uperrs;
    
    $povedlo = false;
    $msg = "<b>Adding file $tmpfile:</b><br>";
    if (!file_exists($tmpfile)) {
      switch ($source) {
        case "paste":
          $msg .= "Creating file from text field...";
          if (file_put_contents($tmpfile, $sourcedata)) {
            $msg .= "OK";
            $povedlo = true;
          }
          else {
            $msg .= "ERROR!";
          }
          break;
        
        case "upload":
          $hashfile = $sourcedata;
          $hashchyba = $hashfile["error"];
          if ($hashchyba == 0) {
            $msg .= "Moving uploaded file...";
            if (move_uploaded_file($hashfile["tmp_name"], $tmpfile) && file_exists($tmpfile)) {
              $msg .= "OK";
              $povedlo = true;
            }
            else {
              $msg .= "ERROR";
            }
          }
          else {
            $msg .= "Upload file error: " . $uperrs[$hashchyba];
          }
          break;
        
        case "import":
          $msg .= "Loading imported file...";
          if (file_exists("import/" . $sourcedata)) {
            rename("import/" . $sourcedata, $tmpfile);
            if (file_exists($tmpfile)) {
              $msg .= "OK";
              $povedlo = true;
            }
            else {
              $msg .= "DST ERROR";
            }
          }
          else {
            $msg .= "SRC ERROR";
          }
          break;
        
        case "url":
          $local = basename($sourcedata);
          $msg .= "Downloading remote file <a href=\"$sourcedata\" target=\"_blank\">$local</a>...";
          
          $furl = fopen($sourcedata, "rb");
          if (!$furl) {
            $msg .= "SRC ERROR";
          }
          else {
            $floc = fopen($tmpfile, "w");
            if (!$floc) {
              $msg .= "DST ERROR";
            }
            else {
              $downed = 0;
              $bufsize = 131072;
              $cas_pinfo = time();
              while (!feof($furl)) {
                if (!$data = fread($furl, $bufsize)) {
                  $msg .= "READ ERROR";
                  break;
                }
                fwrite($floc, $data);
                $downed += strlen($data);
                if ($cas_pinfo < time() - 10) {
                  $msg .= Util::nicenum($downed, 1024) . "B...\n";
                  $cas_pinfo = time();
                }
              }
              fclose($floc);
              $msg .= "OK (" . Util::nicenum($downed, 1024) . "B)";
              $povedlo = true;
            }
            fclose($furl);
          }
          break;
        
        default:
          $msg .= "Wrong file source.";
          break;
      }
    }
    else {
      $msg .= "File already exists.";
    }
    $msg .= "<br>";
    return array($povedlo, $msg);
  }
  
  public static function niceround($num, $dec) {
    // round to specific amount of decimal places
    $stri = strval(round($num, $dec));
    if ($dec > 0) {
      $pozice = strpos($stri, ".");
      if ($pozice === false) {
        $stri .= ".00";
      }
      else {
        while (strlen($stri) - $pozice <= $dec) {
          $stri .= "0";
        }
      }
    }
    return $stri;
  }
  
  public static function shortenstring($co, $kolik) {
    // shorten string that would be too long
    $ret = "<span title='$co'>";
    if (strlen($co) > $kolik) {
      $ret .= substr($co, 0, $kolik - 3) . "...";
    }
    else {
      $ret .= $co;
    }
    $ret .= "</span>";
    return $ret;
  }
  
  public static function prefixNum($num, $size) {
    $string = "" . $num;
    while (strlen($string) < $size) {
      $string = "0" . $string;
    }
    return $string;
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
    else {
      return true;
    }
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
      $result .= $charset[rand(0, strlen($charset) - 1)];
    }
    return $result;
  }
  
  public static function createPrefixedString($table, $dict) {
    $concat = "";
    $counter = 0;
    $size = count($dict);
    
    foreach ($dict as $key => $val) {
      if ($counter < $size - 1) {
        $concat = $concat . "`" . $table . "`" . "." . "`" . $key . "`" . " AS " . $val . ",";
        $counter = $counter + 1;
      }
      else {
        $concat = $concat . "`" . $table . "`" . "." . "`" . $key . "`" . " AS " . $val;
        $counter = $counter + 1;
      }
    }
    
    return $concat;
  }
  
  public static function startsWith($search, $pattern) {
    if (strpos($search, $pattern) === 0) {
      return true;
    }
    else {
      return false;
    }
  }
  
  public static function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
  }
  
  public static function deleteDuplicatesFromJoinResult($dict) {
    $pkStack = array();
    $nonDuplicates = array();
    foreach ($dict as $elem) {
      if (!in_array($elem->getId(), $pkStack)) {
        array_push($pkStack, $elem->getId());
        array_push($nonDuplicates, $elem);
      }
    }
    return $nonDuplicates;
  }
  
  public function hextobin($data) {
    $res = "";
    for ($i = 0; $i < strlen($data) - 1; $i += 2) {
      $res .= chr(hexdec(substr($data, $i, 2)));
    }
    return $res;
  }
  
  public static function getMessage($type, $msg) {
    return "<div class='alert alert-$type'>$msg</div>";
  }
}
