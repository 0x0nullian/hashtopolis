<?php

namespace DBA;

class HashlistAgent extends AbstractModel {
  private $hashlistAgentId;
  private $hashlistId;
  private $agentId;
  
  function __construct($hashlistAgentId, $hashlistId, $agentId) {
    $this->hashlistAgentId = $hashlistAgentId;
    $this->hashlistId = $hashlistId;
    $this->agentId = $agentId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashlistAgentId'] = $this->hashlistAgentId;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['agentId'] = $this->agentId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "hashlistAgentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashlistAgentId;
  }
  
  function getId() {
    return $this->hashlistAgentId;
  }
  
  function setId($id) {
    $this->hashlistAgentId = $id;
  }
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }
  
  function getAgentId(){
    return $this->agentId;
  }
  
  function setAgentId($agentId){
    $this->agentId = $agentId;
  }

  const HASHLIST_AGENT_ID = "hashlistAgentId";
  const HASHLIST_ID = "hashlistId";
  const AGENT_ID = "agentId";
}
