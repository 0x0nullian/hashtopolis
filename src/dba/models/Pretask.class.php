<?php

namespace DBA;

class Pretask extends AbstractModel {
  private $pretaskId;
  private $taskName;
  private $attackCmd;
  private $chunkTime;
  private $statusTimer;
  private $color;
  private $isSmall;
  private $isCpuTask;
  private $useNewBench;
  private $priority;
  private $isMaskImport;
  private $isHidden;
  
  function __construct($pretaskId, $taskName, $attackCmd, $chunkTime, $statusTimer, $color, $isSmall, $isCpuTask, $useNewBench, $priority, $isMaskImport, $isHidden) {
    $this->pretaskId = $pretaskId;
    $this->taskName = $taskName;
    $this->attackCmd = $attackCmd;
    $this->chunkTime = $chunkTime;
    $this->statusTimer = $statusTimer;
    $this->color = $color;
    $this->isSmall = $isSmall;
    $this->isCpuTask = $isCpuTask;
    $this->useNewBench = $useNewBench;
    $this->priority = $priority;
    $this->isMaskImport = $isMaskImport;
    $this->isHidden = $isHidden;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['pretaskId'] = $this->pretaskId;
    $dict['taskName'] = $this->taskName;
    $dict['attackCmd'] = $this->attackCmd;
    $dict['chunkTime'] = $this->chunkTime;
    $dict['statusTimer'] = $this->statusTimer;
    $dict['color'] = $this->color;
    $dict['isSmall'] = $this->isSmall;
    $dict['isCpuTask'] = $this->isCpuTask;
    $dict['useNewBench'] = $this->useNewBench;
    $dict['priority'] = $this->priority;
    $dict['isMaskImport'] = $this->isMaskImport;
    $dict['isHidden'] = $this->isHidden;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "pretaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->pretaskId;
  }
  
  function getId() {
    return $this->pretaskId;
  }
  
  function setId($id) {
    $this->pretaskId = $id;
  }
  
  function getTaskName(){
    return $this->taskName;
  }
  
  function setTaskName($taskName){
    $this->taskName = $taskName;
  }
  
  function getAttackCmd(){
    return $this->attackCmd;
  }
  
  function setAttackCmd($attackCmd){
    $this->attackCmd = $attackCmd;
  }
  
  function getChunkTime(){
    return $this->chunkTime;
  }
  
  function setChunkTime($chunkTime){
    $this->chunkTime = $chunkTime;
  }
  
  function getStatusTimer(){
    return $this->statusTimer;
  }
  
  function setStatusTimer($statusTimer){
    $this->statusTimer = $statusTimer;
  }
  
  function getColor(){
    return $this->color;
  }
  
  function setColor($color){
    $this->color = $color;
  }
  
  function getIsSmall(){
    return $this->isSmall;
  }
  
  function setIsSmall($isSmall){
    $this->isSmall = $isSmall;
  }
  
  function getIsCpuTask(){
    return $this->isCpuTask;
  }
  
  function setIsCpuTask($isCpuTask){
    $this->isCpuTask = $isCpuTask;
  }
  
  function getUseNewBench(){
    return $this->useNewBench;
  }
  
  function setUseNewBench($useNewBench){
    $this->useNewBench = $useNewBench;
  }
  
  function getPriority(){
    return $this->priority;
  }
  
  function setPriority($priority){
    $this->priority = $priority;
  }
  
  function getIsMaskImport(){
    return $this->isMaskImport;
  }
  
  function setIsMaskImport($isMaskImport){
    $this->isMaskImport = $isMaskImport;
  }
  
  function getIsHidden(){
    return $this->isHidden;
  }
  
  function setIsHidden($isHidden){
    $this->isHidden = $isHidden;
  }

  const PRETASK_ID = "pretaskId";
  const TASK_NAME = "taskName";
  const ATTACK_CMD = "attackCmd";
  const CHUNK_TIME = "chunkTime";
  const STATUS_TIMER = "statusTimer";
  const COLOR = "color";
  const IS_SMALL = "isSmall";
  const IS_CPU_TASK = "isCpuTask";
  const USE_NEW_BENCH = "useNewBench";
  const PRIORITY = "priority";
  const IS_MASK_IMPORT = "isMaskImport";
  const IS_HIDDEN = "isHidden";
}
