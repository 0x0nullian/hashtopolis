<?php

use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\LikeFilter;
use DBA\QueryFilter;

class SearchHandler implements Handler {
  public function __construct($id = null) {
    // nothing
  }
  
  public function handle($action) {
    global $ACCESS_CONTROL;
    
    try{
      switch ($action) {
        case DSearchAction::SEARCH:
          $ACCESS_CONTROL->checkPermission(DSearchAction::SEARCH_PERM);
          $this->search();
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch(HTException $e){
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @throws HTException 
   */
  private function search() {
    global $FACTORIES, $OBJECTS;
    
    $query = $_POST['search'];
    if (strlen($query) == 0) {
      throw new HTException("Search query cannot be empty!");
    }
    $query = str_replace("\r\n", "\n", $query);
    $query = explode("\n", $query);
    $resultEntries = array();
    $hashlists = new DataSet();
    foreach ($query as $queryEntry) {
      if (strlen($queryEntry) == 0) {
        continue;
      }
      
      // test if hash contains salt
      if (strpos($queryEntry, ":") !== false) {
        $split = explode(":", $queryEntry);
        $hash = $split[0];
        unset($split[0]);
        $salt = implode(":", $split);
      }
      else {
        $hash = $queryEntry;
        $salt = "";
      }
      
      // TODO: add option to select if exact match or like match
      
      $filters = array();
      $filters[] = new LikeFilter(Hash::HASH, "%" . $hash . "%");
      if (strlen($salt) > 0) {
        $filters[] = new QueryFilter(Hash::SALT, $salt, "=");
      }
      $jF = new JoinFilter($FACTORIES::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID);
      $joined = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => $filters, $FACTORIES::JOIN => $jF));
      
      $qF = new LikeFilter(Hash::PLAINTEXT, "%" . $queryEntry . "%");
      $joined2 = $FACTORIES::getHashFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
      for ($i = 0; $i < sizeof($joined2[$FACTORIES::getHashFactory()->getModelName()]); $i++) {
        $joined[$FACTORIES::getHashFactory()->getModelName()][] = $joined2[$FACTORIES::getHashFactory()->getModelName()][$i];
        $joined[$FACTORIES::getHashlistFactory()->getModelName()][] = $joined2[$FACTORIES::getHashlistFactory()->getModelName()][$i];
      }
      
      $resultEntry = new DataSet();
      if (sizeof($joined[$FACTORIES::getHashFactory()->getModelName()]) == 0) {
        $resultEntry->addValue("found", false);
        $resultEntry->addValue("query", $queryEntry);
      }
      else {
        $resultEntry->addValue("found", true);
        $resultEntry->addValue("query", $queryEntry);
        $matches = array();
        for ($i = 0; $i < sizeof($joined[$FACTORIES::getHashFactory()->getModelName()]); $i++) {
          /** @var $hash Hash */
          $hash = $joined[$FACTORIES::getHashFactory()->getModelName()][$i];
          $matches[] = $hash;
          if ($hashlists->getVal($hash->getHashlistId()) == false) {
            $hashlists->addValue($hash->getHashlistId(), $joined[$FACTORIES::getHashlistFactory()->getModelName()][$i]);
          }
        }
        $resultEntry->addValue("matches", $matches);
      }
      $resultEntries[] = $resultEntry;
    }
    $OBJECTS['resultEntries'] = $resultEntries;
    $OBJECTS['hashlists'] = $hashlists;
    $OBJECTS['result'] = true;
    UI::addMessage(UI::SUCCESS, "Searched for " . sizeof($resultEntries) . " entries!");
  }
}