<?php

class Assignment extends AbstractModel {
  private $modelName = "Assignment";
  
  // Modelvariables
  private $assignmentId;
  private $taskId;
  private $agentId;
  private $benchmark;
  private $autoAdjust;
  private $speed;
  
  
  function __construct($assignmentId, $taskId, $agentId, $benchmark, $autoAdjust, $speed) {
    $this->assignmentId = $assignmentId;
    $this->taskId = $taskId;
    $this->agentId = $agentId;
    $this->benchmark = $benchmark;
    $this->autoAdjust = $autoAdjust;
    $this->speed = $speed;
    
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['assignmentId'] = $this->assignmentId;
    $dict['taskId'] = $this->taskId;
    $dict['agentId'] = $this->agentId;
    $dict['benchmark'] = $this->benchmark;
    $dict['autoAdjust'] = $this->autoAdjust;
    $dict['speed'] = $this->speed;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "assignmentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->assignmentId;
  }
  
  function getId() {
    return $this->assignmentId;
  }
  
  function setId($id) {
    $this->assignmentId = $id;
  }
  
  function getTaskId() {
    return $this->taskId;
  }
  
  function setTaskId($taskId) {
    $this->taskId = $taskId;
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getBenchmark() {
    return $this->benchmark;
  }
  
  function setBenchmark($benchmark) {
    $this->benchmark = $benchmark;
  }
  
  function getAutoAdjust() {
    return $this->autoAdjust;
  }
  
  function setAutoAdjust($autoAdjust) {
    $this->autoAdjust = $autoAdjust;
  }
  
  function getSpeed() {
    return $this->speed;
  }
  
  function setSpeed($speed) {
    $this->speed = $speed;
  }
}
