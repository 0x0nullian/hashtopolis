<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class AssignmentFactory extends AbstractModelFactory {
  function getModelName() {
    return "Assignment";
  }
  
  function getModelTable() {
    return "Assignment";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return Assignment
   */
  function getNullObject() {
    $o = new Assignment(-1, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return Assignment
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Assignment($pk, $dict['taskId'], $dict['agentId'], $dict['benchmark']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Assignment|Assignment[]
   */
  function filter($options, $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if($single){
      if($join){
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Assignment::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      if($join){
        $models[] = $object;
      }
      else{
        $models[] = Util::cast($object, Assignment::class);
      }
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return Assignment
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Assignment::class);
  }

  /**
   * @param Assignment $model
   * @return Assignment
   */
  function save($model) {
    return Util::cast(parent::save($model), Assignment::class);
  }
}