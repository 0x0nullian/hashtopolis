<?php
use Bricky\Template;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 5) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("chunks");
$MENU->setActive("chunks");

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT chunks.*,assignments.speed,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,agents.name AS aname,tasks.name AS tname FROM chunks JOIN tasks ON chunks.task=tasks.id LEFT JOIN agents ON chunks.agent=agents.id LEFT JOIN assignments ON assignments.task=tasks.id AND assignments.agent=agents.id ORDER BY chunks.dispatchtime DESC");
$res = $res->fetchAll();
$chunks = array();
foreach ($res as $chunk) {
  $set = new DataSet();
  $set->setValues($chunk);
  $chunks[] = $set;
}

$OBJECTS['chunks'] = $chunks;

echo $TEMPLATE->render($OBJECTS);




