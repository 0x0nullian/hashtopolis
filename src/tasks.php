<?php
require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}

$TEMPLATE = new Template("tasks/index");
$MENU->setActive("tasks_list");

//catch agents actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    //TODO: implement task handler
    case 'agentbench':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // adjust agent benchmark
      $agid = intval($_POST["agent"]);
      $bench = floatval($_POST["bench"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE assignments SET benchmark=$bench WHERE agent=$agid");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not set benchmark!</div>";
      }
      break;
    case 'agentauto':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // enable agent benchmark autoadjust for its current assignment
      $agid = intval($_POST["agent"]);
      $auto = intval($_POST["auto"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE assignments SET autoadjust=$auto WHERE agent=$agid");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not change autoadjust!</div>";
      }
      break;
    case 'chunkabort':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // reset chunk state and progress to zero
      $chunk = intval($_POST["chunk"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE chunks SET state=10 WHERE id=$chunk");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not abort chunk!</div>";
      }
      break;
    case 'chunkreset':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // reset chunk state and progress to zero
      $chunk = intval($_POST["chunk"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE chunks SET state=0,progress=0,rprogress=0,dispatchtime=" . time() . ",solvetime=0 WHERE id=$chunk");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not reset chunk!</div>";
      }
      break;
    case 'taskpurge':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // delete all task chunks, forget its keyspace value and reset progress to zero
      $task = intval($_POST["task"]);
      $DB = $FACTORIES::getagentsFactory()->getDB();
      $DB->query("START TRANSACTION");
      $res1 = $DB->query("UPDATE assignments SET benchmark=0 WHERE task=$task");
      $res2 = $DB->query("UPDATE hashes SET chunk=NULL WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
      $res3 = $DB->query("UPDATE hashes_binary SET chunk=NULL WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
      $res4 = $DB->query("DELETE FROM zapqueue WHERE chunk IN (SELECT id FROM chunks WHERE task=$task)");
      $res5 = $DB->query("DELETE FROM chunks WHERE task=$task");
      $res6 = $DB->query("UPDATE tasks SET keyspace=0,progress=0 WHERE id=$task");
      if ($res1 && $res2 && $res3 && $res4 && $res5 && $res6) {
        $DB->exec("COMMIT");
      }
      else {
        $DB->exec("ROLLBACK");
        $message = "<div class='alert alert-danger'>Could not purge task!</div>";
      }
      break;
    case 'taskcolor':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // change task color
      $task = intval($_POST["task"]);
      $color = $_POST["color"];
      if (preg_match("/[0-9A-Za-z]{6}/", $color) == 1) {
        $color = "'$color'";
      }
      else {
        $color = "NULL";
      }
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE tasks SET color=$color WHERE id=$task");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not change color!</div>";
      }
      break;
    case 'taskauto':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // enable agent benchmark autoadjust for all subsequent agents added to this task
      $task = intval($_POST["task"]);
      $auto = intval($_POST["auto"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE tasks SET autoadjust=$auto WHERE id=$task");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not change autoadjust!</div>";
      }
      break;
    case 'taskchunk':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // update task chunk time
      $task = intval($_POST["task"]);
      $chunktime = intval($_POST["chunktime"]);
      $DB = $FACTORIES::getagentsFactory()->getDB();
      $DB->query("SET autocommit = 0");
      $res1 = $DB->query("UPDATE assignments JOIN tasks ON tasks.id=assignments.task SET assignments.benchmark=(assignments.benchmark/tasks.chunktime)*$chunktime WHERE assignments.task=$task");
      $res2 = $DB->query("UPDATE tasks SET chunktime=$chunktime WHERE id=$task");
      if ($res1 && $res2) {
        $DB->exec("COMMIT");
      }
      else {
        $DB->exec("ROLLBACK");
        $message = "<div class='alert alert-danger'>Could not update task chunk time!</div>";
      }
      break;
    case 'taskrename':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // change task name
      $task = intval($_POST["task"]);
      $name = $FACTORIES::getagentsFactory()->getDB()->quote(htmlentities($_POST["name"], false, "UTF-8"));
      $res = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE tasks SET name=$name WHERE id=$task");
      if (!$res) {
        $message = "<div class='alert alert-danger'>Could not rename task!</div>";
      }
      break;
    case "finishedtasksdelete":
      if ($LOGIN->getLevel() < 30) {
        break;
      }
      // delete finished tasks
      $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT tasks.id,hashlists.format,tasks.hashlist FROM tasks JOIN hashlists ON tasks.hashlist=hashlists.id JOIN (SELECT task,SUM(progress) AS sumprog FROM chunks WHERE rprogress=10000 GROUP BY task) chunks ON chunks.task=tasks.id WHERE (tasks.progress=tasks.keyspace AND chunks.sumprog=tasks.keyspace) OR hashlists.cracked=hashlists.hashcount");
      $res = $res->fetchAll();
      $tasks = array();
      // load the tasks first
      foreach ($res as $task) {
        $tasks[] = $task;
      }
      // then process them
      $message = "<div class='alert alert-neutral'>";
      foreach ($tasks as $entry) {
        $task = $entry["id"];
        $format = $entry["format"];
        $hlist = $entry["hashlist"];
        $FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
        if (Util::delete_task($task)) {
          $FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
          $message .= "Deleted task $task<br>";
        }
        else {
          $FACTORIES::getagentsFactory()->getDB()->exec("ROLLBACK");
          $message .= "Could not delete task $task!<br>";
        }
      }
      $message .= "</div>";
      break;
    case 'taskdelete':
      if ($LOGIN->getLevel() < 30) {
        break;
      }
      // delete a task
      $task = intval($_POST["task"]);
      $FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
      if (Util::delete_task($task)) {
        $FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
        if (isset($_POST['refer']) && $_POST['refer'] == 'pretask') {
          header("Location: pretasks.php");
          die();
        }
        header("Location: tasks.php");
        die();
      }
      else {
        $FACTORIES::getagentsFactory()->getDB()->exec("ROLLBACK");
        $message = "<div class='alert alert-danger'>Could not delete task!</div>";
      }
      break;
    case 'taskprio':
      if ($LOGIN->getLevel() < 20) {
        break;
      }
      // change task priority
      $task = intval($_POST["task"]);
      $pretask = false;
      if (isset($_GET['pre'])) {
        $pretask = true;
      }
      $prio = intval($_POST["priority"]);
      $res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT 1 FROM tasks WHERE tasks.priority=$prio AND tasks.id!=$task AND tasks.priority>0 AND SIGN(IFNULL(tasks.hashlist,0))=(SELECT SIGN(IFNULL(hashlist,0)) FROM tasks WHERE id=$task) LIMIT 1");
      if ($res->rowCount() == 1) {
        // must be unique
        $message = "<div class='alert alert-danger'>Each task has to have unique priority!</div>";
      }
      else {
        $res = $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE tasks SET priority=$prio WHERE id=$task");
        if (!$res) {
          $message = "<div class='alert alert-danger'>Could not change priority!</div>";
        }
        else {
          if ($pretask) {
            header("Location: pretasks.php");
          }
          else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
          }
          die();
        }
      }
      break;
    case 'newtaskp':
      // new task creator
      $DB = $FACTORIES::getagentsFactory()->getDB();
      $name = $DB->quote(htmlentities($_POST["name"], false, "UTF-8"));
      $cmdline = $DB->quote($_POST["cmdline"]);
      $autoadj = intval(@$_POST["autoadjust"]);
      $chunk = intval($_POST["chunk"]);
      $status = intval($_POST["status"]);
      $color = $_POST["color"];
      $message = "<div class='alert alert-neutral'>";
      $forward = "";
      if (preg_match("/[0-9A-Za-z]{6}/", $color) == 1) {
        $color = "'$color'";
      }
      else {
        $color = "NULL";
      }
      if (strpos($cmdline, $CONFIG->getVal('hashlistAlias')) === false) {
        $message .= "Command line must contain hashlist (" . $CONFIG->getVal('hashlistAlias') . ").";
      }
      else {
        if ($_POST["hashlist"] == "preconf") {
          // it will be a preconfigured task
          $hashlist = "NULL";
          if ($name == "''") {
            $name = "PC_" . date("Ymd_Hi");
          }
          $forward = "pretasks.php";
        }
        else {
          $thashlist = intval($_POST["hashlist"]);
          if ($thashlist > 0) {
            $hashlist = $thashlist;
          }
          if ($name == "''") {
            $name = "HL" . $hashlist . "_" . date("Ymd_Hi");
          }
          $forward = "tasks.php";
        }
        if ($hashlist != "") {
          if ($status > 0 && $chunk > 0 && $chunk > $status) {
            if ($hashlist != "NULL") {
              $res = $DB->query("SELECT * FROM hashlists WHERE id=" . $hashlist);
              $hl = $res->fetch();
              if ($hl['hexsalt'] == 1 && strpos($cmdline, "--hex-salt") === false) {
                $cmdline = "'--hex-salt " . substr($cmdline, 1, -1) . "'";
              }
            }
            $DB->exec("SET autocommit = 0");
            $DB->exec("START TRANSACTION");
            $message .= "Creating task in the DB...";
            $res = $DB->exec("INSERT INTO tasks (name, attackcmd, hashlist, chunktime, statustimer, autoadjust, color) VALUES ($name, $cmdline, $hashlist, $chunk, $status, $autoadj, $color)");
            if ($res) {
              // insert succeeded
              $id = $DB->lastInsertId();
              $message .= "OK (id: $id)<br>";
              // attach files
              $attachok = true;
              if (isset($_POST["adfile"])) {
                foreach ($_POST["adfile"] as $fid) {
                  if ($fid > 0) {
                    $message .= "Attaching file $fid...";
                    if ($DB->exec("INSERT INTO taskfiles (task,file) VALUES ($id, $fid)")) {
                      $message .= "OK";
                    }
                    else {
                      $message .= "ERROR!";
                      $attachok = false;
                    }
                    $message .= "<br>";
                  }
                }
              }
              if ($attachok == true) {
                $DB->exec("COMMIT");
                $message .= "Task created successfuly!";
                if ($forward) {
                  header("Location: $forward");
                  die();
                }
              }
              else {
                $DB->exec("ROLLBACK");
              }
            }
            else {
              $message .= "ERROR: " . $DB->errorInfo()[2];
            }
          }
          else {
            $message .= "Chunk time must be higher than status timer.";
          }
        }
        else {
          $message .= "Every task requires a hashlist, even if it should contain only one hash.";
        }
      }
      $message .= "</div>";
      break;
  }
}

//test if auto-reload is enabled
$autorefresh = 0;
if (isset($_COOKIE['autorefresh']) && $_COOKIE['autorefresh'] == '1') {
  $autorefresh = 10;
}
if (isset($_POST['toggleautorefresh'])) {
  if ($autorefresh != 0) {
    $autorefresh = 0;
    setcookie("autorefresh", "0", time() - 600);
  }
  else {
    $autorefresh = 10;
    setcookie("autorefresh", "1", time() + 3600 * 24);
  }
  Util::refresh();
}
if ($autorefresh > 0) { //renew cookie
  setcookie("autorefresh", "1", time() + 3600 * 24);
}
$OBJECTS['autorefresh'] = $autorefresh;

if (isset($_GET['id'])) {
  if($LOGIN->getLevel() < 5){
    $TEMPLATE = new Template("restricted");
    die($TEMPLATE->render($OBJECTS));
  }
  //TODO: update single task view
  $TEMPLATE = new Template("tasks/detail");
  
  // show task details
  $taskSet = new DataSet();
  $DB = $FACTORIES::getagentsFactory()->getDB();
  $task = intval($_GET["id"]);
  $filter = intval(isset($_GET["all"]) ? $_GET["all"] : "");
  
  $res = $DB->query("SELECT tasks.*,hashlists.name AS hname,hashlists.format,hashlists.hashtype AS htype,hashtypes.description AS htypename,ROUND(chunks.cprogress) AS cprogress,SUM(assignments.speed*IFNULL(achunks.working,0)) AS taskspeed,IF(chunks.lastact>" . (time() - $CONFIG->getVal('chunktimeout')) . ",1,0) AS active FROM tasks LEFT JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN hashtypes ON hashlists.hashtype=hashtypes.id LEFT JOIN (SELECT task,SUM(progress) AS cprogress,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN assignments ON assignments.task=tasks.id LEFT JOIN (SELECT DISTINCT agent,1 AS working FROM chunks WHERE task=$task AND GREATEST(dispatchtime,solvetime)>" . (time() - $CONFIG->getVal('chunktimeout')) . ") achunks ON achunks.agent=assignments.agent WHERE tasks.id=$task GROUP BY tasks.id");
  /*$res = $DB->query("SELECT * FROM tasks WHERE tasks.id=$task");
  $taskEntry = $res->fetch();
  $res = $DB->query("SELECT hashlists.name AS hname, hashlists.format, hashlists.hashtype AS htype, hashtypes.description AS htypename FROM hashlists LEFT JOIN hashtypes ON hashlists.hashtype=hashtypes.id WHERE hashlists.id=".$taskEntry['hashlist']);
  $taskEntry = array_merge($taskEntry, $res->fetch());
  $res = $DB->query("SELECT task,SUM(progress) AS cprogress,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=".$taskEntry['id']." GROUP BY task");
  $taskEntry = array_merge($taskEntry, $res->fetch());
  $res = $DB->query("SELECT * FROM assignments LEFT JOIN (SELECT DISTINCT agent, 1 AS working FROM chunks WHERE task=".$taskEntry['id']." AND GREATEST(dispatchtime,solvetime)>".(time()-$CONFIG->getVal('chunktimeout')).") achunks ON achunks.agent=assignments.agent WHERE assignments.task=".$taskEntry['id']." GROUP BY assignments.agent");
  $taskEntry = array_merge($taskEntry, $res->fetch());*/
  
  $taskEntry = $res->fetch();
  if ($taskEntry) {
    $taskSet->setValues($taskEntry);
    $taskSet->addValue("filter", $filter);
    $res = $DB->query("SELECT dispatchtime,solvetime FROM chunks WHERE task={$taskEntry['id']} AND solvetime>dispatchtime ORDER BY dispatchtime ASC");
    $intervaly = array();
    foreach ($res as $entry) {
      $interval = array();
      $interval["start"] = $entry["dispatchtime"];
      $interval["stop"] = $entry["solvetime"];
      $intervaly[] = $interval;
    }
    $soucet = 0;
    for ($i = 1; $i <= count($intervaly); $i++) {
      if (isset($intervaly[$i]) && $intervaly[$i]["start"] <= $intervaly[$i - 1]["stop"]) {
        $intervaly[$i]["start"] = $intervaly[$i - 1]["start"];
        if ($intervaly[$i]["stop"] < $intervaly[$i - 1]["stop"]) {
          $intervaly[$i]["stop"] = $intervaly[$i - 1]["stop"];
        }
      }
      else {
        $soucet += ($intervaly[$i - 1]["stop"] - $intervaly[$i - 1]["start"]);
      }
    }
    $taskSet->addValue("soucet", $soucet);
    
    $res = $DB->query("SELECT ROUND((tasks.keyspace-SUM(tchunks.length))/SUM(tchunks.length*tchunks.active/tchunks.time)) AS eta FROM (SELECT SUM(chunks.length*chunks.rprogress/10000) AS length,SUM(chunks.solvetime-chunks.dispatchtime) AS time,IF(MAX(solvetime)>=" . time() . "-" . $CONFIG->getVal('chunktimeout') . ",1,0) AS active FROM chunks WHERE chunks.solvetime>chunks.dispatchtime AND chunks.task={$taskEntry['id']} GROUP BY chunks.agent) tchunks CROSS JOIN tasks WHERE tasks.id={$taskEntry['id']}");
    $entry = $res->fetch();
    $taskSet->addValue('eta', $entry['eta']);
    
    $res = $DB->query("SELECT files.id,files.filename,files.size,files.secret FROM taskfiles JOIN files ON files.id=taskfiles.file WHERE task={$taskEntry['id']} ORDER BY filename");
    $res = $res->fetchAll();
    $attachFiles = array();
    foreach ($res as $file) {
      $set = new DataSet();
      $set->setValues($file);
      $attachFiles[] = $set;
    }
    $OBJECTS['attachFiles'] = $attachFiles;
    
    $agents = array();
    $res = $DB->query("SELECT agents.id,agents.active,agents.trusted,agents.name,assignments.benchmark,assignments.autoadjust,IF(chunks.lastact>=" . (time() - $CONFIG->getVal('chunktimeout')) . ",1,0) AS working,assignments.speed,IFNULL(chunks.lastact,0) AS time,IFNULL(chunks.searched,0) AS searched,chunks.spent,IFNULL(chunks.cracked,0) AS cracked FROM agents JOIN assignments ON agents.id=assignments.agent JOIN tasks ON tasks.id=assignments.task LEFT JOIN (SELECT agent,SUM(progress) AS searched,SUM(solvetime-dispatchtime) AS spent,SUM(cracked) AS cracked,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=$task AND solvetime>dispatchtime GROUP BY agent) chunks ON chunks.agent=agents.id WHERE assignments.task=$task GROUP BY agents.id ORDER BY agents.id");
    $res = $res->fetchAll();
    foreach ($res as $agent) {
      $set = new DataSet();
      $set->setValues($agent);
      $agents[] = $set;
    }
    $OBJECTS['agents'] = $agents;
    
    $allAgents = array();
    $res = $DB->query("SELECT agents.id,agents.active,agents.trusted,agents.name,IF(chunks.lastact>=" . (time() - $CONFIG->getVal('chunktimeout')) . ",1,0) AS working,IFNULL(chunks.lastact,0) AS time,IFNULL(chunks.searched,0) AS searched,chunks.spent,IFNULL(chunks.cracked,0) AS cracked FROM agents LEFT JOIN (SELECT agent,SUM(progress) AS searched,SUM(solvetime-dispatchtime) AS spent,SUM(cracked) AS cracked,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=$task AND solvetime>dispatchtime GROUP BY agent) chunks ON chunks.agent=agents.id WHERE spent IS NOT NULL GROUP BY agents.id ORDER BY agents.id");
    $res = $res->fetchAll();
    foreach ($res as $agent) {
      $set = new DataSet();
      $set->setValues($agent);
      $allAgents[] = $set;
    }
    $OBJECTS['allAgents'] = $allAgents;
    $showAll = false;
    if (isset($_GET['allagents'])) {
      $showAll = true;
    }
    $OBJECTS['showAllAgents'] = $showAll;
    
    $assignAgents = array();
    $res = $DB->query("SELECT agents.id,agents.name FROM agents LEFT JOIN assignments ON assignments.agent=agents.id WHERE IFNULL(assignments.task,0)!=$task ORDER BY agents.id ASC");
    $res = $res->fetchAll();
    foreach ($res as $agent) {
      $set = new DataSet();
      $set->setValues($agent);
      $assignAgents[] = $set;
    }
    $OBJECTS['assignAgents'] = $assignAgents;
    
    $add = "";
    if ($filter != '1') {
      $add = "AND progress<length ";
    }
    $chunks = array();
    $res = $DB->query("SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,agents.name FROM chunks LEFT JOIN agents ON chunks.agent=agents.id WHERE task=$task " . $add . "ORDER BY chunks.dispatchtime DESC LIMIT 100");
    $res = $res->fetchAll();
    foreach ($res as $chunk) {
      $set = new DataSet();
      $set->setValues($chunk);
      $active = (max($chunk['dispatchtime'], $chunk['solvetime']) > time() - $CONFIG->getVal('chunktimeout') && $chunk['progress'] < $chunk['length'] && $chunk["state"] < 4);
      $set->addValue('active', $active);
      $chunks[] = $set;
    }
    $OBJECTS['chunks'] = $chunks;
    $OBJECTS['task'] = $taskSet;
  }
}
else if (isset($_GET['new'])) {
  if($LOGIN->getLevel() < 5){
    $TEMPLATE = new Template("restricted");
    die($TEMPLATE->render($OBJECTS));
  }
  $TEMPLATE = new Template("tasks/new");
  $MENU->setActive("tasks_new");
  $orig = 0;
  $copy = new Task(0, "", "", null, $CONFIG->getVal("chunktime"), $CONFIG->getVal("statustimer"), 0, 0, 0, 0, "", 0, 0);
  if (isset($_GET["copy"])) {
    //copied from a task
    $copy = $FACTORIES::getTaskFactory()->get($_GET['copy']);
    if($copy != null){
      $orig = $copy->getId();
      $copy->setId(0);
      $copy->setTaskName($copy->getTaskName() . " (copy)");
    }
  }
  $OBJECTS['orig'] = $orig;
  $OBJECTS['copy'] = $copy;
  
  $lists = array();
  $set = new DataSet();
  $set->addValue('id', null);
  $set->addValue("name", "(pre-configured task)");
  $lists[] = $set;
  $res = $FACTORIES::getHashlistFactory()->filter(array());
  foreach ($res as $list) {
    $set = new DataSet();
    $set->addValue('id', $list->getId());
    $set->addValue('name', $list->getHashlistName());
    $lists[] = $set;
  }
  $OBJECTS['lists'] = $lists;
  
  $origFiles = array();
  if($orig > 0){
    $qF = new QueryFilter("taskId", $orig, "=");
    $ff = $FACTORIES::getTaskFileFactory()->filter(array('filter' => $qF));
    foreach($ff as $f) {
      $origFiles[] = $f->getId();
    }
  }
  $oF = new OrderFilter("filename", "ASC");
  $allFiles = $FACTORIES::getFileFactory()->filter(array('order' => $oF));
  $files = array();
  foreach($allFiles as $singleFile){
    $set = new DataSet();
    $set->addValue('checked', (in_array($singleFile->getId(), $origFiles))?"1":"0");
    $set->addValue('file', $singleFile);
    $files[] = $set;
  }
  $OBJECTS['files'] = $files;
}
else {
  $jF = new JoinFilter($FACTORIES::getHashlistFactory(), "hashlistId", "hashlistId");
  $oF1 = new OrderFilter("priority", "DESC");
  $oF2 = new OrderFilter("taskId", "ASC");
  $joinedTasks = $FACTORIES::getTaskFactory()->filter(array('join' => $jF, 'order' => array($oF1, $oF2)));
  $tasks = array();
  for($z=0;$z<sizeof($joinedTasks['Task']);$z++){
    $set = new DataSet();
    $set->addValue('Task', $joinedTasks['Task'][$z]);
    $set->addValue('Hashlist', $joinedTasks['Hashlist'][$z]);
    
    $task = $joinedTasks['Task'][$z];
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter'=> $qF));
    $progress = 0;
    $cracked = 0;
    $maxTime = 0;
    foreach($chunks as $chunk){
      $progress += $chunk->getProgress();
      $cracked += $chunk->getCracked();
      if($chunk->getDispatchTime() > $maxTime){
        $maxTime = $chunk->getDispatchTime();
      }
      if($chunk->getSolveTime() > $maxTime){
        $maxTime = $chunk->getSolveTime();
      }
    }
    
    $isActive = false;
    if(time() - $maxTime < $CONFIG->getVal('chunktimeout')){
      $isActive = true;
    }
    
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF));
    
    $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getTaskFileFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), "fileId", "fileId");
    $joinedFiles = $FACTORIES::getFileFactory()->filter(array('filter' => $qF, 'join' => $jF));
    $sizes = 0;
    $secret = false;
    for($x=0;$x<sizeof($joinedFiles['File']);$x++){
      $sizes += $joinedFiles['File'][$x]->getSize();
      if($joinedFiles['File'][$x]->getSecret() == '1'){
        $secret = true;
      }
    }
    
    $set->addValue('numFiles', sizeof($joinedFiles['File']));
    $set->addValue('filesSize', $sizes);
    $set->addValue('fileSecret', $secret);
    $set->addValue('numAssignments', sizeof($assignments));
    $set->addValue('isActive', $isActive);
    $set->addValue('sumprog', $progress);
    $set->addValue('cracked', $cracked);
    $set->addValue('numChunks', sizeof($chunks));
    
    $tasks[] = $set;
    $OBJECTS['tasks'] = $tasks;
  }
}

echo $TEMPLATE->render($OBJECTS);




