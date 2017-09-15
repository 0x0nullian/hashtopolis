<?php

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::USER) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("crackers/index");
$MENU->setActive("binaries_list");

//catch actions here...
if (isset($_POST['action']) && Util::checkCSRF($_POST['csrf'])) {
  $hashcatHandler = new HashcatHandler();
  $hashcatHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new']) && $LOGIN->getLevel() >= DAccessLevel::SUPERUSER) {
  $TEMPLATE = new Template("crackers/new");
  $MENU->setActive("hashcat_new");
  $oF = new OrderFilter(HashcatRelease::TIME, "DESC LIMIT 1");
  $releases = $FACTORIES::getHashcatReleaseFactory()->filter(array($FACTORIES::ORDER => $oF));
  $rootDir = "";
  $common = "";
  if (sizeof($releases) > 0) {
    $rootDir = $releases[0]->getRootdir();
  }
  $OBJECTS['rootDir'] = htmlentities($rootDir, ENT_QUOTES, "UTF-8");
}
else if (isset($_GET['id'])) {
  $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($_GET['id']);
  if ($binaryType !== null) {
    $OBJECTS['binaryType'] = $binaryType;
    $TEMPLATE = new Template("crackers/detail");
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $OBJECTS['binaries'] = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
  }
}
else {
  $oF = new OrderFilter(CrackerBinaryType::TYPE_NAME, "ASC");
  $OBJECTS['binaryTypes'] = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array($FACTORIES::ORDER => $oF));
  $binariesVersions = new DataSet();
  foreach ($OBJECTS['binaryTypes'] as $binaryType) {
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $binaries = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
    $arr = array();
    foreach ($binaries as $binary) {
      if (!isset($arr[$binary->getVersion()])) {
        $arr[$binary->getVersion()] = $binary->getVersion();
      }
    }
    $binariesVersions->addValue($binaryType->getId(), implode("<br>", $arr));
  }
  $OBJECTS['versions'] = $binariesVersions;
}

echo $TEMPLATE->render($OBJECTS);




