<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::API_VIEW_PERM);

$TEMPLATE = new Template("api/index");
$MENU->setActive("users_api");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $apiHandler = new ApiHandler();
  $apiHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if(isset($_GET['id'])){
  // TODO:
}
else {
  // determine keys and groups
  $groups = $FACTORIES::getApiGroupFactory()->filter(array());

  $apis = array();
  foreach ($groups as $group) {
    $apis[$group->getId()] = 0;
  }

  $allApiKeys = $FACTORIES::getApiKeyFactory()->filter(array());
  foreach ($allApiKeys as $apiKey) {
    $apis[$apiKey->getApiGroupId()]++;
  }

  $OBJECTS['apis'] = new DataSet($apis);
  $OBJECTS['groups'] = $groups;
  $OBJECTS['pageTitle'] = "Api Groups";
}

echo $TEMPLATE->render($OBJECTS);




