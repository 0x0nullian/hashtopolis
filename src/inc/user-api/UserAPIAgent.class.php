<?php
use DBA\QueryFilter;
use DBA\RegVoucher;

class UserAPIAgent extends UserAPIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;

    switch($QUERY[UQuery::REQUEST]){
      case USectionAgent::CREATE_VOUCHER:
        $this->createVoucher($QUERY);
        break;
      case USectionAgent::GET_BINARIES:
        $this->getBinaries($QUERY);
        break;
      case USectionAgent::DELETE_VOUCHER:
        $this->deleteVoucher($QUERY);
        break;
      case USectionAgent::LIST_VOUCHERS:
        $this->listVouchers($QUERY);
        break;
      case USectionAgent::LIST_AGENTS:
        // TODO:
        break;
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
  }

  private function deleteVoucher($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::VOUCHER])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid delete voucher query!");
    }
    $qF = new QueryFilter(RegVoucher::VOUCHER, $QUERY['voucher'], "=");
    $voucher = $FACTORIES::getRegVoucherFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if($voucher == null){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Voucher not found!");
    }
    $FACTORIES::getRegVoucherFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::DELETE_VOUCHER,
        UResponseAgent::RESPONSE => UValues::OK
      )
    );
  }

  private function listVouchers($QUERY){
    global $FACTORIES;

    $vouchers = $FACTORIES::getRegVoucherFactory()->filter(array());
    $arr = [];
    foreach($vouchers as $voucher){
      $arr[] = $voucher->getVoucher();
    }
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::GET_BINARIES,
        UResponseAgent::RESPONSE => UValues::OK,
        UResponseAgent::VOUCHERS => $arr
      )
    );
  }

  private function getBinaries($QUERY){
    global $FACTORIES;

    $url = explode("/", $_SERVER['PHP_SELF']);
    unset($url[sizeof($url) - 1]);
    $agentUrl = Util::buildServerUrl() . implode("/", $url) . "/api/server.php";
    $baseUrl = Util::buildServerUrl() . implode("/", $url) . "/agents.php?download=";
    $response = array(
      UResponseAgent::SECTION => USection::AGENT,
      UResponseAgent::REQUEST => USectionAgent::GET_BINARIES,
      UResponseAgent::RESPONSE => UValues::OK,
      UResponseAgent::AGENT_URL => $agentUrl
    );

    $arr = [];
    $binaries = $FACTORIES::getAgentBinaryFactory()->filter(array());
    foreach($binaries as $binary){
      $arr[] = array(
        UResponseAgent::BINARIES_NAME => $binary->getType(),
        UResponseAgent::BINARIES_OS => $binary->getOperatingSystems(),
        UResponseAgent::BINARIES_URL => $baseUrl . $binary->getId(),
        UResponseAgent::BINARIES_VERSION => $binary->getVersion(),
        UResponseAgent::BINARIES_FILENAME => $binary->getFilename()
      );
    }
    $response[UResponseAgent::BINARIES] = $arr;
    $this->sendResponse($response);
  }

  private function createVoucher($QUERY){
    $handler = new AgentHandler();

    $voucher = Util::randomString(10);
    if(isset($QUERY[UQueryAgent::VOUCHER])){
      $voucher = $QUERY[UQueryAgent::VOUCHER];
    }
    $handler->createVoucher($voucher);
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::CREATE_VOUCHER,
        UResponseAgent::RESPONSE => UValues::OK,
        UResponseAgent::VOUCHER => $voucher
      )
    );
  }
}