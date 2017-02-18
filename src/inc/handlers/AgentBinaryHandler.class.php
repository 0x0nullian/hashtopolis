<?php
use DBA\AgentBinary;
use DBA\QueryFilter;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.02.17
 * Time: 14:48
 */

class AgentBinaryHandler implements Handler {
  
  public function __construct($id = null) {
    //nothing
  }
  
  public function handle($action) {
    switch($action){
      case 'new':
        $this->newBinary();
        break;
      case 'edit':
        $this->editBinary();
        break;
      case 'delete':
        $this->deleteBinary();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function deleteBinary(){
    global $FACTORIES;
    
    $id = $_POST['id'];
    $agentBinary = $FACTORIES::getAgentBinaryFactory()->get($id);
    if($agentBinary == null){
      UI::addMessage(UI::ERROR, "Binary does not exist!");
      return;
    }
    $FACTORIES::getAgentBinaryFactory()->delete($agentBinary);
    //unlink(dirname(__FILE__)."/../../static/".$agentBinary->getFilename()); //TODO: not sure if we should delete or not
    UI::addMessage(UI::SUCCESS, "Binary deleted successfully!");
  }
  
  private function editBinary(){
    global $FACTORIES;
    
    $id = $_POST['id'];
    $type = $_POST['type'];
    $os = $_POST['os'];
    $filename = $_POST['filename'];
    $version = $_POST['version'];
    if(strlen($version) == 0){
      UI::addMessage(UI::ERROR, "Version cannot be empty!");
      return;
    }
    else if(!file_exists(dirname(__FILE__)."/../../static/$filename")){
      UI::addMessage(UI::ERROR, "Provided filename does not exist!");
      return;
    }
    $agentBinary = $FACTORIES::getAgentBinaryFactory()->get($id);
    if($agentBinary == null){
      UI::addMessage(UI::ERROR, "Binary does not exist!");
      return;
    }
    
    $qF1 = new QueryFilter(AgentBinary::TYPE, $type, "=");
    $qF2 = new QueryFilter(AgentBinary::AGENT_BINARY_ID, $agentBinary->getId(), "<>");
    $result = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if($result != null){
      UI::addMessage(UI::ERROR, "You cannot have two binaries with the same type!");
      return;
    }
    
    $agentBinary->setType($type);
    $agentBinary->setOperatingSystems($os);
    $agentBinary->setFilename($filename);
    $agentBinary->setVersion($version);
    
    $FACTORIES::getAgentBinaryFactory()->update($agentBinary);
    UI::addMessage(UI::SUCCESS, "Binary was updated successfully!");
  }
  
  private function newBinary(){
    global $FACTORIES;
    
    $type = $_POST['type'];
    $os = $_POST['os'];
    $filename = $_POST['filename'];
    $version = $_POST['version'];
    if(strlen($version) == 0){
      UI::addMessage(UI::ERROR, "Version cannot be empty!");
      return;
    }
    else if(!file_exists(dirname(__FILE__)."/../../static/$filename")){
      UI::addMessage(UI::ERROR, "Provided filename does not exist!");
      return;
    }
    $qF = new QueryFilter(AgentBinary::TYPE, $type, "=");
    $result = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if($result != null){
      UI::addMessage(UI::ERROR, "You cannot have two binaries with the same type!");
      return;
    }
    $agentBinary = new AgentBinary(0, $type, $version, $os, $filename);
    $FACTORIES::getAgentBinaryFactory()->save($agentBinary);
    UI::addMessage(UI::SUCCESS, "Binary was added successfully!");
  }
}