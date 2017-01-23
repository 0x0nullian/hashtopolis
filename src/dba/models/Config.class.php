<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class Config extends AbstractModel {
  private $configId;
  private $item;
  private $value;
  
  function __construct($configId, $item, $value) {
    $this->configId = $configId;
    $this->item = $item;
    $this->value = $value;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['configId'] = $this->configId;
    $dict['item'] = $this->item;
    $dict['value'] = $this->value;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "configId";
  }
  
  function getPrimaryKeyValue() {
    return $this->configId;
  }
  
  function getId() {
    return $this->configId;
  }
  
  function setId($id) {
    $this->configId = $id;
  }
  
  function getItem(){
    return $this->item;
  }
  
  function setItem($item){
    $this->item = $item;
  }
  
  function getValue(){
    return $this->value;
  }
  
  function setValue($value){
    $this->value = $value;
  }

  const CONFIG_ID = "configId";
  const ITEM = "item";
  const VALUE = "value";
}
