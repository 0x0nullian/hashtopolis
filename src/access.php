<?php

use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\ContainFilter;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\User;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::ACCESS_VIEW_PERM);

$TEMPLATE = new Template("access/index");
$MENU->setActive("users_access");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $accessControlHandler = new AccessControlHandler();
  $accessControlHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("access/new");
  $OBJECTS['pageTitle'] = "Create new Permission Group";
}
else if (isset($_GET['id'])) {
  $group = $FACTORIES::getRightGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid permission group!");
  }
  else {
    $OBJECTS['group'] = $group;
  
    // TODO: load stuff
    $constants = DAccessControl::getConstants();
    
    $TEMPLATE = new Template("access/detail");
    $OBJECTS['pageTitle'] = "Details of Permission Group " . htmlentities($group->getGroupName(), ENT_QUOTES, "UTF-8");
  }
}
else {
  // determine members and agents
  $groups = $FACTORIES::getRightGroupFactory()->filter(array());
  
  $users = array();
  foreach ($groups as $group) {
    $users[$group->getId()] = 0;
  }
  
  $allUsers = $FACTORIES::getUserFactory()->filter(array());
  foreach ($allUsers as $user) {
    $users[$user->getRightGroupId()]++;
  }
  
  $OBJECTS['users'] = new DataSet($users);
  $OBJECTS['groups'] = $groups;
  $OBJECTS['pageTitle'] = "Permission Groups";
}

echo $TEMPLATE->render($OBJECTS);




