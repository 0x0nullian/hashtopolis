<?php

/*
 * TODO: This file should be merged into superhashlists.php and later deleted
 */

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 20) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("new/superhashlist");
$MENU->setActive("lists_snew");
$message = "";

//catch agents actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    case 'newsuperhashlistp':
      // new superhashlist creator
      $hlistar = $_POST["hlist"];
      for ($i = 0; $i < count($hlistar); $i++) {
        if (intval($hlistar[$i]) <= 0) {
          unset($hlistar[$i]);
        }
      }
      $DB = $FACTORIES::getagentsFactory()->getDB();
      $allok = false;
      $message = "<div class='alert alert-neutral'>";
      if (count($hlistar) > 0) {
        $hlisty = implode(",", $hlistar);
        $res = $DB->query("SELECT DISTINCT format, hashtype FROM hashlists WHERE id IN ($hlisty)");
        $res = $res->fetch();
        if ($res) {
          $DB->exec("SET autocommit = 0");
          $DB->exec("START TRANSACTION");
          $message .= "Creating superhashlist in the DB...<br>";
          $name = $DB->quote(htmlentities($_POST["name"], false, "UTF-8"));
          if ($name == "''") {
            $name = "SHL_" . $res['hashtype'];
          }
          $res = $DB->exec("INSERT INTO hashlists (name,format,hashtype,hashcount,cracked) SELECT $name,3," . $res["hashtype"] . ",SUM(hashlists.hashcount),SUM(hashlists.cracked) FROM hashlists WHERE hashlists.id IN ($hlisty)");
          if ($res) {
            $id = $DB->lastInsertId();
            $message .= "Inserting hashlists...<br>";
            $res = $DB->exec("INSERT INTO superhashlists (id,hashlist) SELECT $id,hashlists.id FROM hashlists WHERE hashlists.id IN ($hlisty)");
            if ($res) {
              $DB->exec("COMMIT");
              $allok = true;
              $message .= "Done.<br>";
            }
            else {
              $message .= "Could not insert hashes to superhashlist";
            }
          }
          else {
            $message .= "Could not create superhashlist";
          }
          $DB->exec("SET autocommit = 1");
        }
        else {
          $message .= "Hashlists must be the same format and hash type to create a superhashlist.";
        }
      }
      else {
        $message .= "No valid hashlists provided.";
      }
      if (!$allok) {
        $DB->exec("ROLLBACK");
      }
      $message .= "</div>";
      break;
  }
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT id,name,hashtype FROM hashlists WHERE format!=3 ORDER BY hashtype ASC, id ASC");
$res = $res->fetchAll();
$lists = array();
foreach ($res as $list) {
  $set = new DataSet();
  $set->setValues($list);
  $lists[] = $set;
}

$OBJECTS['lists'] = $lists;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




