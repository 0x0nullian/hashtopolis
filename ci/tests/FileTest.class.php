<?php

class FileTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType = HashtopolisTest::RUN_FAST;

  public function init($version){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing ".$this->getTestName()."...");
    parent::init($version);
  }

  public function run(){
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running ".$this->getTestName()."...");
    $this->testListFilesEmpty();
    $this->testCreatingInlineFile();
    $this->testCreatedInlineFile();
    $this->testCreatingFileTwice();
    $this->testGetFile();
    $this->testFileDownload();
    $this->testSecret();
    $this->testGetFile(false);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName()." completed");
  }

  private function testSecret(){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "setSecret",
      "isSecret" => false,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testSecret", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testSecret", "Response not OK");
    }
    else{
      $this->testSuccess("FileTest:testSecret");
    }
  }

  private function testListFilesEmpty(){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testListFilesEmpty", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testListFilesEmpty", "Response not OK");
    }
    else if(!is_array($response['files'])){
      $this->testFailed("FileTest:testListFilesEmpty", "Expected array but got non-array");
    }
    else if(sizeof($response['files']) > 0){
      $this->testFailed("FileTest:testListFilesEmpty", "Expected empty array but got larger one");
    }
    else{
      $this->testSuccess("FileTest:testListFilesEmpty");
    }
  }

  private function testCreatingInlineFile(){
    $testFile = base64_encode("This is a test file content!");
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => "test.txt",
      "fileType" => 0,
      "source" => "inline",
      "data" => $testFile,
      "accessGroupId" => 1,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testCreatingInlineFile", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testCreatingInlineFile", "Response not OK");
    }
    else{
      $this->testSuccess("FileTest:testCreatingInlineFile");
    }
  }

  private function testFileDownload(){
    $testContent = "This is a test file content!";
    $url = 'getFile.php?file=1&apiKey=mykey';
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE
    ));
    $response = curl_exec($ch);
    if($response != $testContent){
      HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_ERROR, $response);
      $this->testFailed("FileTest:testFileDownload", "File content does not match!");
    }
    else{
      $this->testSuccess("FileTest:testFileDownload");
    }
  }

  private function testGetFile($secret = true){
    $testContent = "This is a test file content!";
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "getFile",
      "fileId" => 1,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testGetFile($secret)", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testGetFile($secret)", "Response not OK");
    }
    else if($response['size'] != strlen($testContent)){
      $this->testFailed("FileTest:testGetFile($secret)", "File size not matching");
    }
    else if($response['url'] != 'getFile.php?file=1&apiKey=mykey'){
      $this->testFailed("FileTest:testGetFile($secret)", "Download url not correct");
    }
    else if($response['filename'] != 'test.txt'){
      $this->testFailed("FileTest:testGetFile($secret)", "Filename not matching");
    }
    else if($response['isSecret'] != $secret){
      $this->testFailed("FileTest:testGetFile($secret)", "Wrong isSecret value of file");
    }
    else{
      $this->testSuccess("FileTest:testGetFile($secret)");
    }
  }

  private function testCreatingFileTwice(){
    $testFile = base64_encode("This is a test file content!");
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "addFile",
      "filename" => "test.txt",
      "fileType" => 0,
      "source" => "inline",
      "data" => $testFile,
      "accessGroupId" => 1,
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testCreatingFileTwice", "Empty response");
    }
    else if($response['response'] != 'ERROR'){
      $this->testFailed("FileTest:testCreatingFileTwice", "Response not ERROR");
    }
    else{
      $this->testSuccess("FileTest:testCreatingFileTwice");
    }
  }

  private function testCreatedInlineFile(){
    $response = HashtopolisTestFramework::doRequest([
      "section" => "file",
      "request" => "listFiles",
      "accessKey" => "mykey"], HashtopolisTestFramework::REQUEST_UAPI);
    if($response === false){
      $this->testFailed("FileTest:testCreatedInlineFile", "Empty response");
    }
    else if($response['response'] != 'OK'){
      $this->testFailed("FileTest:testCreatedInlineFile", "Response not OK");
    }
    else if(!is_array($response['files'])){
      $this->testFailed("FileTest:testCreatedInlineFile", "Expected array but got non-array");
    }
    else{
      $found = false;
      foreach($response['files'] as $file){
        if($file['filename'] == "test.txt"){
          if($file['fileType'] != 0){
            $this->testFailed("FileTest:testCreatedInlineFile", "Created file does not have same fileType");
          }
          $found = true;
        }
      }
      if(!$found){
        $this->testFailed("FileTest:testCreatedInlineFile", "Created file is not in list");
      }
      else{
        $this->testSuccess("FileTest:testCreatedInlineFile");
      }
    }
  }

  public function getTestName(){
    return "File Test";
  }
}

HashtopolisTestFramework::register(new FileTest());