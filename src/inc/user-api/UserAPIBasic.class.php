<?php
use DBA\User;

abstract class UserAPIBasic {
  /** @var User */
  protected $user = null;
  
  /**
   * @param array $QUERY input query sent to the API
   */
  public abstract function execute($QUERY = array());
  
  protected function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE);
    die();
  }
  
  protected function updateAgent($action) {
    global $FACTORIES;
    
    $this->agent->setLastIp(Util::getIP());
    $this->agent->setLastAct($action);
    $this->agent->setLastTime(time());
    $FACTORIES->getAgentFactory()->update($this->agent);
  }
  
  public function sendErrorResponse($action, $msg) {
    $ANS = array();
    $ANS[PResponseErrorMessage::ACTION] = $action;
    $ANS[PResponseErrorMessage::RESPONSE] = PValues::ERROR;
    $ANS[PResponseErrorMessage::MESSAGE] = $msg;
    header("Content-Type: application/json");
    echo json_encode($ANS, true);
    die();
  }
  
  public function checkToken($action, $QUERY) {
    global $FACTORIES;
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQuery::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($agent == null) {
      $this->sendErrorResponse($action, "Invalid token!");
    }
    $this->agent = $agent;
  }
}





















