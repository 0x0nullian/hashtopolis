<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class RegVoucher extends AbstractModel {
  private $regVoucherId;
  private $voucher;
  private $time;
  
  function __construct($regVoucherId, $voucher, $time) {
    $this->regVoucherId = $regVoucherId;
    $this->voucher = $voucher;
    $this->time = $time;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['regVoucherId'] = $this->regVoucherId;
    $dict['voucher'] = $this->voucher;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "regVoucherId";
  }
  
  function getPrimaryKeyValue() {
    return $this->regVoucherId;
  }
  
  function getId() {
    return $this->regVoucherId;
  }
  
  function setId($id) {
    $this->regVoucherId = $id;
  }
  
  function getVoucher(){
    return $this->voucher;
  }
  
  function setVoucher($voucher){
    $this->voucher = $voucher;
  }
  
  function getTime(){
    return $this->time;
  }
  
  function setTime($time){
    $this->time = $time;
  }

  public const REG_VOUCHER_ID = "regVoucherId";
  public const VOUCHER = "voucher";
  public const TIME = "time";
}
