<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 20) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("files/index");
$MENU->setActive("files");
$message = "";

//catch actions here...
if (isset($_POST['action'])) {
  $fileHandler = new FileHandler();
  $fileHandler->handle($_POST['action']);
}

$view = "dict";
if(isset($_GET['view']) && in_array($_GET['view'], array('dict', 'rule'))){
  $view = $_GET['view'];
}


$qF = new QueryFilter("fileType", array_search($view, array('dict', 'rule')), "=");
$oF = new OrderFilter("filename", "ASC");
$OBJECTS['fileType'] = ($view == "dict")?"Wordlists":"Rules";
$OBJECTS['view'] = $view;
$OBJECTS['files'] = $FACTORIES::getFileFactory()->filter(array('filter' => $qF, 'order' => $oF));;
$OBJECTS['impfiles'] = Util::scanImportDirectory();
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




