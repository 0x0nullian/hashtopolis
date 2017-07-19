<?php

namespace DBA;

class AgentError extends AbstractModel {
  private $agentErrorId;
  private $agentId;
  private $taskId;
  private $time;
  private $error;
  
  function __construct($agentErrorId, $agentId, $taskId, $time, $error) {
    $this->agentErrorId = $agentErrorId;
    $this->agentId = $agentId;
    $this->taskId = $taskId;
    $this->time = $time;
    $this->error = $error;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentErrorId'] = $this->agentErrorId;
    $dict['agentId'] = $this->agentId;
    $dict['taskId'] = $this->taskId;
    $dict['time'] = $this->time;
    $dict['error'] = $this->error;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "agentErrorId";
  }
  
  function getPrimaryKeyValue() {
    return $this->agentErrorId;
  }
  
  function getId() {
    return $this->agentErrorId;
  }
  
  function setId($id) {
    $this->agentErrorId = $id;
  }
  
  function getAgentId(){
    return $this->agentId;
  }
  
  function setAgentId($agentId){
    $this->agentId = $agentId;
  }
  
  function getTaskId(){
    return $this->taskId;
  }
  
  function setTaskId($taskId){
    $this->taskId = $taskId;
  }
  
  function getTime(){
    return $this->time;
  }
  
  function setTime($time){
    $this->time = $time;
  }
  
  function getError(){
    return $this->error;
  }
  
  function setError($error){
    $this->error = $error;
  }

  const AGENT_ERROR_ID = "agentErrorId";
  const AGENT_ID = "agentId";
  const TASK_ID = "taskId";
  const TIME = "time";
  const ERROR = "error";
}
