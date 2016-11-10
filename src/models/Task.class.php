<?php

class Task extends AbstractModel {
  private $modelName = "Task";
  
  // Modelvariables
  private $taskId;
  private $taskName;
  private $attackCmd;
  private $hashlistId;
  private $chunkTime;
  private $statusTimer;
  private $autoAdjust;
  private $keyspace;
  private $progress;
  private $priority;
  private $color;
  private $isSmall;
  private $isCpuTask;
  
  
  function __construct($taskId, $taskName, $attackCmd, $hashlistId, $chunkTime, $statusTimer, $autoAdjust, $keyspace, $progress, $priority, $color, $isSmall, $isCpuTask) {
    $this->taskId = $taskId;
    $this->taskName = $taskName;
    $this->attackCmd = $attackCmd;
    $this->hashlistId = $hashlistId;
    $this->chunkTime = $chunkTime;
    $this->statusTimer = $statusTimer;
    $this->autoAdjust = $autoAdjust;
    $this->keyspace = $keyspace;
    $this->progress = $progress;
    $this->priority = $priority;
    $this->color = $color;
    $this->isSmall = $isSmall;
    $this->isCpuTask = $isCpuTask;
    
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskId'] = $this->taskId;
    $dict['taskName'] = $this->taskName;
    $dict['attackCmd'] = $this->attackCmd;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['chunkTime'] = $this->chunkTime;
    $dict['statusTimer'] = $this->statusTimer;
    $dict['autoAdjust'] = $this->autoAdjust;
    $dict['keyspace'] = $this->keyspace;
    $dict['progress'] = $this->progress;
    $dict['priority'] = $this->priority;
    $dict['color'] = $this->color;
    $dict['isSmall'] = $this->isSmall;
    $dict['isCpuTask'] = $this->isCpuTask;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "taskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->taskId;
  }
  
  function getId() {
    return $this->taskId;
  }
  
  function setId($id) {
    $this->taskId = $id;
  }
  
  function getTaskName() {
    return $this->taskName;
  }
  
  function setTaskName($taskName) {
    $this->taskName = $taskName;
  }
  
  function getAttackCmd() {
    return $this->attackCmd;
  }
  
  function setAttackCmd($attackCmd) {
    $this->attackCmd = $attackCmd;
  }
  
  function getHashlistId() {
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId) {
    $this->hashlistId = $hashlistId;
  }
  
  function getChunkTime() {
    return $this->chunkTime;
  }
  
  function setChunkTime($chunkTime) {
    $this->chunkTime = $chunkTime;
  }
  
  function getStatusTimer() {
    return $this->statusTimer;
  }
  
  function setStatusTimer($statusTimer) {
    $this->statusTimer = $statusTimer;
  }
  
  function getAutoAdjust() {
    return $this->autoAdjust;
  }
  
  function setAutoAdjust($autoAdjust) {
    $this->autoAdjust = $autoAdjust;
  }
  
  function getKeyspace() {
    return $this->keyspace;
  }
  
  function setKeyspace($keyspace) {
    $this->keyspace = $keyspace;
  }
  
  function getProgress() {
    return $this->progress;
  }
  
  function setProgress($progress) {
    $this->progress = $progress;
  }
  
  function getPriority() {
    return $this->priority;
  }
  
  function setPriority($priority) {
    $this->priority = $priority;
  }
  
  function getColor() {
    return $this->color;
  }
  
  function setColor($color) {
    $this->color = $color;
  }
  
  function getIsSmall() {
    return $this->isSmall;
  }
  
  function setIsSmall($isSmall) {
    $this->isSmall = $isSmall;
  }
  
  function getIsCpuTask() {
    return $this->isCpuTask;
  }
  
  function setIsCpuTask($isCpuTask) {
    $this->isCpuTask = $isCpuTask;
  }
}
