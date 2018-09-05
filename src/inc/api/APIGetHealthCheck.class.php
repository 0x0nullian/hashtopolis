<?php
use DBA\Factory;

class APIGetHealthCheck extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryGetHealthCheck::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_HEALTH_CHECK, "Invalid get health check query!");
    }
    $this->checkToken(PActions::GET_HEALTH_CHECK, $QUERY);
    $this->updateAgent(PActions::GET_HEALTH_CHECK);

    $healthCheckAgent = HealthUtils::checkNeeded($this->agent);
    if($healthCheckAgent == null){
      // for whatever reason there is no check available anymore
      $this->sendErrorResponse(PActions::GET_HEALTH_CHECK, "No health check available for this agent!");
    }
    $healthCheck = Factory::getHealthCheckFactory()->get($healthCheckAgent->getHealthCheckId());

    $cmd = SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)." -a 3 -1 ?l?u?d ?1?1?1?1?1";
    $hashes = file_get_contents(dirname(__FILE__)."/../../tmp/health-check-".$healthCheck->getId().".txt");
    $hashes = explode("\n", $hashes);

    $this->sendResponse([
      PResponseGetHealthCheck::ACTION => PActions::GET_HEALTH_CHECK,
      PResponseGetHealthCheck::RESPONSE => PValues::SUCCESS,
      PResponseGetHealthCheck::ATTACK => $cmd,
      PResponseGetHealthCheck::CRACKER_BINARY_ID => (int)$healthCheck->getCrackerBinaryId(),
      PResponseGetHealthCheck::HASHES => $hashes,
      PResponseGetHealthCheck::CHECK_ID => (int)$healthCheck->getId()
    ]);
  }
}