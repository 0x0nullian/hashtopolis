<?php

class TaskFile extends AbstractModel {
  private $modelName = "TaskFile";
  
  // Modelvariables
  private $taskFileId;
  private $taskId;
  private $fileId;
  
  
  function __construct($taskFileId, $taskId, $fileId) {
    $this->taskFileId = $taskFileId;
    $this->taskId = $taskId;
    $this->fileId = $fileId;
    
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskFileId'] = $this->taskFileId;
    $dict['taskId'] = $this->taskId;
    $dict['fileId'] = $this->fileId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "taskFileId";
  }
  
  function getPrimaryKeyValue() {
    return $this->taskFileId;
  }
  
  function getId() {
    return $this->taskFileId;
  }
  
  function setId($id) {
    $this->taskFileId = $id;
  }
  
  function getTaskId() {
    return $this->taskId;
  }
  
  function setTaskId($taskId) {
    $this->taskId = $taskId;
  }
  
  function getFileId() {
    return $this->fileId;
  }
  
  function setFileId($fileId) {
    $this->fileId = $fileId;
  }
}
