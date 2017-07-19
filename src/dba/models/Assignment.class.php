<?php

namespace DBA;

class Assignment extends AbstractModel {
  private $assignmentId;
  private $taskId;
  private $agentId;
  private $benchmark;
  
  function __construct($assignmentId, $taskId, $agentId, $benchmark) {
    $this->assignmentId = $assignmentId;
    $this->taskId = $taskId;
    $this->agentId = $agentId;
    $this->benchmark = $benchmark;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['assignmentId'] = $this->assignmentId;
    $dict['taskId'] = $this->taskId;
    $dict['agentId'] = $this->agentId;
    $dict['benchmark'] = $this->benchmark;
    
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
  
  function getTaskId(){
    return $this->taskId;
  }
  
  function setTaskId($taskId){
    $this->taskId = $taskId;
  }
  
  function getAgentId(){
    return $this->agentId;
  }
  
  function setAgentId($agentId){
    $this->agentId = $agentId;
  }
  
  function getBenchmark(){
    return $this->benchmark;
  }
  
  function setBenchmark($benchmark){
    $this->benchmark = $benchmark;
  }

  const ASSIGNMENT_ID = "assignmentId";
  const TASK_ID = "taskId";
  const AGENT_ID = "agentId";
  const BENCHMARK = "benchmark";
}
