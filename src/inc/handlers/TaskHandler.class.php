<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:21
 */

class TaskHandler implements Handler {
  private $task;
  
  public function __construct($taskId = null) {
    global $FACTORIES;
    
    if ($taskId == null) {
      $this->task = null;
      return;
    }
  
    $this->task = $FACTORIES::getAgentFactory()->get($taskId);
    if ($this->task == null) {
      UI::printError("FATAL", "Task with ID $taskId not found!");
    }
  }
  
  public function handle($action) {
    global $LOGIN;
    
    switch ($action) {
      case 'agentbench':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->adjustBenchmark();
        break;
      case 'agentauto':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->toggleAutoadjust();
        break;
      case 'chunkabort':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->abortChunk();
        break;
      case 'chunkreset':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->resetChunk();
        break;
      case 'taskpurge':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->purgeTask();
        break;
      case 'taskcolor':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->updateColor();
        break;
      case 'taskauto':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->toggleTaskAutoadjust();
        break;
      case 'taskchunk':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeChunkTime();
        break;
      case 'taskrename':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename();
        break;
      case "finishedtasksdelete":
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->deleteFinished();
        break;
      case 'taskdelete':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete();
        break;
      case 'taskprio':
        if ($LOGIN->getLevel() < 20) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->updatePriority();
        break;
      case 'newtaskp':
        $this->create();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private function create(){
    global $FACTORIES, $CONFIG;
  
    // new task creator
    $name = htmlentities($_POST["name"], false, "UTF-8");
    $cmdline = $_POST["cmdline"];
    $autoadjust = intval(@$_POST["autoadjust"]);
    $chunk = intval($_POST["chunk"]);
    $status = intval($_POST["status"]);
    $color = $_POST["color"];
    if (preg_match("/[0-9A-Za-z]{6}/", $color) != 1) {
      $color = null;
    }
    if (strpos($cmdline, $CONFIG->getVal('hashlistAlias')) === false) {
      UI::addMessage("danger", "Command line must contain hashlist (" . $CONFIG->getVal('hashlistAlias') . ")!");
      return;
    }
    if ($_POST["hashlist"] == null) {
      // it will be a preconfigured task
      $hashlistId = null;
      if (strlen($name) == 0) {
        $name = "PC_" . date("Ymd_Hi");
      }
      $forward = "pretasks.php";
    }
    else {
      $hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
      if ($hashlist <= 0) {
        UI::addMessage("danger", "Invalid hashlist!");
        return;
      }
      $hashlistId = $hashlist->getId();
      if (strlen($name) == 0) {
        $name = "HL" . $hashlistId . "_" . date("Ymd_Hi");
      }
      $forward = "tasks.php";
    }
    if($chunk < 0 || $status < 0 || $chunk < $status){
      UI::addMessage("danger", "Chunk time must be higher than status timer!");
      return;
    }
    if ($hashlistId != null && $hashlist->getHexSalt() == 1 && strpos($cmdline, "--hex-salt") === false) {
      $cmdline = "--hex-salt $cmdline"; // put the --hex-salt if the user was not clever enough to put it there :D
    }
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $task = new Task(0, $name, $cmdline, $hashlistId, $chunk, $status, $autoadjust, 0, 0, 0, $color, 0, 0);
    $task = $FACTORIES::getTaskFactory()->save($task);
    if (isset($_POST["adfile"])) {
      foreach ($_POST["adfile"] as $fileId) {
        $taskFile = new TaskFile(0, $task->getId(), $fileId);
        $FACTORIES::getTaskFileFactory()->save($taskFile);
      }
    }
    AbstractModelFactory::getDB()->query("COMMIT");
    header("Location: $forward");
    die();
  }
  
  private function updatePriority(){
    global $FACTORIES;
  
    // change task priority
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    $pretask = false;
    if (isset($_GET['pre'])) {
      $pretask = true;
    }
    $priority = intval($_POST["priority"]);
    $qF1 = new QueryFilter("priority", $priority, "=");
    $qF2 = new QueryFilter("priority", $priority, ">");
    $qF3 = new QueryFilter("taskId", $task->getId(), "<>");
    $qF4 = new QueryFilter("hashlistId", null, "<>");
    $check = $FACTORIES::getTaskFactory()->filter(array('filter' => array($qF1, $qF2, $qF3, $qF4)), true);
    if($check != null){
      UI::addMessage("danger", "Priorities must be unique!");
      return;
    }
    $task->setPriority($priority);
    $FACTORIES::getTaskFactory()->update($task);
    if($pretask){
      header("Location: pretasks.php");
    }
    else{
      header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    }
    die();
  }
  
  private function delete(){
    global $FACTORIES;
  
    // delete a task
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $this->deleteTask($task);
    AbstractModelFactory::getDB()->query("COMMIT");
    if($task->getHashlistId() == null){
      header("pretasks.php");
      die();
    }
  }
  
  private function deleteTask($task){
    global $FACTORIES;
  
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => array($qF)));
    $FACTORIES::getAgentErrorFactory()->massDeletion(array('filter' => array($qF)));
    $FACTORIES::getTaskFileFactory()->massDeletion(array('filter' => array($qF)));
    $uS = new UpdateSet("chunkId", null);
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
    $chunkIds = array();
    foreach($chunks as $chunk){
      $chunkIds[] = $chunk->getId();
    }
    if(sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter("chunkId", $chunkIds);
      $FACTORIES::getHashFactory()->massUpdate(array('filter' => $qF2, 'update' => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array('filter' => $qF2, 'update' => $uS));
    }
    $FACTORIES::getChunkFactory()->massDeletion(array('filter' => $qF));
    $FACTORIES::getTaskFactory()->delete($task);
  }
  
  private function deleteFinished(){
    global $FACTORIES;
  
    // delete finished tasks
    $qF = new QueryFilter("rprogress", 10000, "=");
    $tasks = $FACTORIES::getTaskFactory()->filter(array('filter' => $qF));
  
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    foreach($tasks as $task){
      $this->deleteTask($task);
    }
    AbstractModelFactory::getDB()->query("COMMIT");
  }
  
  private function rename(){
    global $FACTORIES;
  
    // change task name
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    $name = htmlentities($_POST["name"], false, "UTF-8");
    $task->setTaskName($name);
    $FACTORIES::getTaskFactory()->update($task);
  }
  
  private function changeChunkTime(){
    global $FACTORIES;
  
    // update task chunk time
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    $chunktime = intval($_POST["chunktime"]);
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $qF = new QueryFilter("taskId", $task->getId(), "=", $FACTORIES::getTaskFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFactory(), "taskId", "taskId");
    $join = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF, 'join' => $jF));
    for($i=0;$i<sizeof($join['Task']);$i++){
      $assignment = $join['Assignment'][$i];
      $assignment->setBenchmark($assignment->getBenchmark()/$task->getChunkTime()*$chunktime);
      $FACTORIES::getAssignmentFactory()->update($assignment);
    }
    $task->setChunkTime($chunktime);
    $FACTORIES::getTaskFactory()->update($task);
    AbstractModelFactory::getDB()->query("COMMIT");
  }
  
  private function toggleTaskAutoadjust(){
    global $FACTORIES;
  
    // enable agent benchmark autoadjust for all subsequent agents added to this task
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    $auto = intval(@$_POST["auto"]);
    $task->setAutoAdjust($auto);
    $FACTORIES::getTaskFactory()->update($task);
  }
  
  private function updateColor(){
    global $FACTORIES;
  
    // change task color
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    $color = $_POST["color"];
    if (preg_match("/[0-9A-Za-z]{6}/", $color) == 0) {
      $color = null;
    }
    $task->setColor($color);
    $FACTORIES::getTaskFactory()->update($task);
  }
  
  private function abortChunk(){
    global $FACTORIES;
  
    // reset chunk state and progress to zero
    $chunk = $FACTORIES::getChunkFactory()->get($_POST['chunk']);
    if($chunk == null){
      UI::addMessage("danger", "No such chunk!");
      return;
    }
    $chunk->setState(10);
    $FACTORIES::getChunkFactory()->update($chunk);
  }
  
  private function resetChunk(){
    global $FACTORIES;
  
    // reset chunk state and progress to zero
    $chunk = $FACTORIES::getChunkFactory()->get($_POST['chunk']);
    if($chunk == null){
      UI::addMessage("danger", "No such chunk!");
      return;
    }
    $chunk->setState(0);
    $chunk->setProgress(0);
    $chunk->setRprogress(0);
    $chunk->setDispatchTime(time());
    $chunk->setSolveTime(0);
    $FACTORIES::getChunkFactory()->update($chunk);
  }
  
  private function purgeTask(){
    global $FACTORIES;
    
    // delete all task chunks, forget its keyspace value and reset progress to zero
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if($task == null){
      UI::addMessage("danger", "No such task!");
      return;
    }
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    $qF = new QueryFilter("taskId", $task->getId(), "=");
    $uS = new UpdateSet("benchmark", 0);
    $FACTORIES::getAssignmentFactory()->massUpdate(array('filter' => $qF, 'update' => $uS));
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
    $chunkIds = array();
    foreach($chunks as $chunk){
      $chunkIds[] = $chunk->getId();
    }
    if(sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter("chunkId", $chunkIds);
      $uS = new UpdateSet("chunkId", null);
      $FACTORIES::getHashFactory()->massUpdate(array('filter' => $qF2, 'update' => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array('filter' => $qF2, 'update' => $uS));
    }
    $FACTORIES::getChunkFactory()->massDeletion(array('filter' => $qF));
    $task->setKeyspace(0);
    $task->setProgress(0);
    $FACTORIES::getTaskFactory()->update($task);
    AbstractModelFactory::getDB()->query("COMMIT");
  }
  
  private function toggleAutoadjust(){
    global $FACTORIES;
  
    // enable agent benchmark autoadjust for its current assignment
    $qF = new QueryFilter("agentId", $_POST['agent'], "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF), true);
    if($assignment == null){
      UI::addMessage("danger", "No assignment for this agent!");
      return;
    }
    $auto = intval($_POST["auto"]);
    $assignment->setAutoAdjust($auto);
    $FACTORIES::getAssignmentFactory()->update($assignment);
  }
  
  private function adjustBenchmark(){
    global $FACTORIES;
  
    // adjust agent benchmark
    $qF = new QueryFilter("agentId", $_POST['agent'], "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array('filter' => $qF), true);
    if($assignment == null){
      UI::addMessage("danger", "No assignment for this agent!");
      return;
    }
    $bench = floatval($_POST["bench"]);
    $assignment->setBenchmark($bench);
    $FACTORIES::getAssignmentFactory()->update($assignment);
  }
}