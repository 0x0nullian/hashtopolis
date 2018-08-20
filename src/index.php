<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!$INSTALL) {
  header("Location: install/");
  die("Forward to <a href='install'>Install</a>");
}

AccessControl::getInstance()->checkPermission(DViewControl::INDEX_VIEW_PERM);

$TEMPLATE = new Template("static/index");
$OBJECTS['pageTitle'] = "Welcome";
$message = "";

if (isset($_GET['err'])) {
  $err = $_GET['err'];
  $time = substr($err, 1);
  if (time() - $time < 10) {
    switch ($err[0]) {
      case '1':
        $message = "<div class='alert alert-danger'>Invalid form submission!</div>";
        break;
      case '2':
        $message = "<div class='alert alert-danger'>You need to fill in both fields!</div>";
        break;
      case '3':
        $message = "<div class='alert alert-danger'>Wrong username/password/OTP!</div>";
        break;
      case '4':
        $message = "<div class='alert alert-warning'>You need to be logged in to view this! Please log in again.</div>";
        break;
    }
  }
}
else if (isset($_GET['logout'])) {
  $logout = $_GET['logout'];
  $time = substr($logout, 1);
  if (time() - $time < 10) {
    $message = "<div class='alert alert-success'>You logged out successfully!</div>";
  }
}

$OBJECTS['message'] = $message;
$fw = "";
if (isset($_GET['fw'])) {
  $fw = $_GET['fw'];
}
$OBJECTS['fw'] = htmlentities($fw, ENT_QUOTES, "UTF-8");

echo $TEMPLATE->render($OBJECTS);




