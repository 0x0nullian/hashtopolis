<?php

use DBA\QueryFilter;
use DBA\Config;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::CONFIG_VIEW_PERM);

$TEMPLATE = new Template("config");
$MENU->setActive("config_server");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $configHandler = new ConfigHandler();
  $configHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$configuration = array();
$configSectionId = (isset($_GET['view'])) ? $_GET['view'] : 1;
$qF = new QueryFilter(Config::CONFIG_SECTION_ID, $configSectionId, "=");
$entries = Factory::getConfigFactory()->filter([Factory::FILTER => $qF]);
$OBJECTS['configSectionId'] = 0;
foreach ($entries as $entry) {
  $set = new DataSet();
  $set->addValue('item', $entry->getItem());
  $set->addValue('value', $entry->getValue());
  $configuration[] = $set;
  $OBJECTS['configSectionId'] = $configSectionId;
}

$OBJECTS['pageTitle'] = "Configuration";

$OBJECTS['configuration'] = $configuration;

echo $TEMPLATE->render($OBJECTS);




