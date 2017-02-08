<?php

use DBA\Factory;

//set to 1 for debugging
ini_set("display_errors", "1");

//is required for running well with php7
//TODO: check if this is still a problem
ini_set('pcre.jit', '0');

$OBJECTS = array();

$VERSION = "0.2.0 ALPHA";
$HOST = $_SERVER['HTTP_HOST'];
if (strpos($HOST, ":") !== false) {
  $HOST = substr($HOST, 0, strpos($HOST, ":"));
}

//TODO: this script stuff needs to be removed!
$SCRIPTVERSION = "0.1.0 ALPHA";
$SCRIPTNAME = "hashtopussy.php";

$OBJECTS['version'] = $VERSION;
$OBJECTS['host'] = $HOST;

//START CONFIG
$CONN['user'] = '__DBUSER__';
$CONN['pass'] = '__DBPASS__';
$CONN['server'] = '__DBSERVER__';
$CONN['db'] = '__DBDB__';
$CONN['installed'] = false; //set this to true if you config the mysql and setup manually
//END CONFIG

$INSTALL = false;
if ($CONN['installed']) {
  $INSTALL = true;
}

// include all .class.php files in inc dir
$dir = scandir(dirname(__FILE__));
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/" . $entry);
  }
}
require_once(dirname(__FILE__)."/templating/Statement.class.php");
require_once(dirname(__FILE__)."/templating/Template.class.php");

// include all handlers
require_once(dirname(__FILE__)."/handlers/Handler.php");
$dir = scandir(dirname(__FILE__) . "/handlers/");
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/handlers/" . $entry);
  }
}

// DEFINES
include(dirname(__FILE__)."/defines.php");
include(dirname(__FILE__)."/protocol.php");

// include DBA
require_once(dirname(__FILE__)."/../dba/init.php");

$FACTORIES = new Factory();
$LANG = new Lang();

$gitcommit = "not versioned";
$out = array();
exec("cd '".dirname(__FILE__)."/../' && git rev-parse HEAD", $out);
if (isset($out[0])) {
  $gitcommit = substr($out[0], 0, 7);
}
$out = array();
exec("cd '".dirname(__FILE__)."/../' && git rev-parse --abbrev-ref HEAD", $out);
if (isset($out[0])) {
  $gitcommit .= " branch " . $out[0];
}
$OBJECTS['gitcommit'] = $gitcommit;

$LOGIN = null;
$MENU = new Menu();
$OBJECTS['menu'] = $MENU;
$OBJECTS['messages'] = array();
if ($INSTALL) {
  $LOGIN = new Login();
  $OBJECTS['login'] = $LOGIN;
  if ($LOGIN->isLoggedin()) {
    $OBJECTS['user'] = $LOGIN->getUser();
  }
  
  $res = $FACTORIES::getConfigFactory()->filter(array());
  $CONFIG = new DataSet();
  foreach ($res as $entry) {
    $CONFIG->addValue($entry->getItem(), $entry->getValue());
  }
  $OBJECTS['config'] = $CONFIG;
  
  //set autorefresh to false for all pages
  $OBJECTS['autorefresh'] = -1;
}


