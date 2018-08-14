<?php

use DBA\Chunk;
use DBA\OrderFilter;
use DBA\JoinFilter;
use DBA\Task;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::CHUNKS_VIEW_PERM);

$TEMPLATE = new Template("chunks");
$MENU->setActive("chunks");

$oF = null;
$OBJECTS['all'] = true;
$OBJECTS['pageTitle'] = "Chunk Activity";
if (!isset($_GET['show'])) {
  $page = 0;
  $PAGESIZE = 50;
  if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
  }
  $OBJECTS['page'] = $page;
  $numentries = $FACTORIES::getChunkFactory()->countFilter(array());
  $OBJECTS['maxpage'] = floor($numentries / $PAGESIZE);
  $limit = $page * $PAGESIZE;
  $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT $limit, $PAGESIZE", $FACTORIES::getChunkFactory());
  $OBJECTS['all'] = false;
  $OBJECTS['pageTitle'] = "Chunks Activity (page " . ($page + 1) . ")";
}

$jF = new JoinFilter($FACTORIES::getTaskFactory(), Chunk::TASK_ID, Task::TASK_ID);
$qF = new QueryFilter(Task::IS_ARCHIVED, 1, "<>", $FACTORIES::getTaskFactory());
if ($oF == null) {
  $joined = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
}
else {
  $joined = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::ORDER => $oF, $FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
}
$chunks = $joined[$FACTORIES::getChunkFactory()->getModelName()];
// TODO: also filter for tasks where access is forbidden because of files from specific group

$spent = new DataSet();
foreach ($chunks as $chunk) {
  $spent->addValue($chunk->getId(), max($chunk->getDispatchTime(), $chunk->getSolveTime()) - $chunk->getDispatchTime());
}
$OBJECTS['chunks'] = $chunks;
$OBJECTS['spent'] = $spent;

$tasks = $FACTORIES::getTaskFactory()->filter(array());
$taskNames = new DataSet();
foreach ($tasks as $task) {
  $taskNames->addValue($task->getId(), $task->getTaskName());
}
$OBJECTS['taskNames'] = $taskNames;

$agents = $FACTORIES::getAgentFactory()->filter(array());
$agentNames = new DataSet();
foreach ($agents as $agent) {
  $agentNames->addValue($agent->getId(), $agent->getAgentName());
}
$OBJECTS['agentNames'] = $agentNames;

echo $TEMPLATE->render($OBJECTS);




