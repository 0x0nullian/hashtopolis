<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */
class AccountHandler implements Handler {
  private $user;
  
  public function __construct($userId = null) {
    global $FACTORIES;
    
    if ($userId == null) {
      $this->user = null;
      return;
    }
    
    $this->user = $FACTORIES::getUserFactory()->get($userId);
    if ($this->user == null) {
      UI::printError("FATAL", "User with ID $userId not found!");
    }
  }
  
  public function handle($action) {
    global $LOGIN, $OBJECTS;
    
    switch ($action) {
      case 'setemail':
        $this->setEmail();
        break;
      case 'updatelifetime':
        $this->updateLifetime();
        break;
      case 'changepass':
        $this->changePassword();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
    
    $LOGIN->setUser($this->user);
    $OBJECTS['user'] = $this->user;
  }
  
  private function changePassword(){
    global $FACTORIES;
    
    $oldPassword = $_POST['oldpass'];
    $newPassword = $_POST['newpass'];
    $repeatedPassword = $_POST['reppass'];
    if (!Encryption::passwordVerify($this->user->getUsername(), $oldPassword, $this->user->getPasswordSalt(), $this->user->getPasswordHash())) {
      UI::addMessage("danger", "Your old password is wrong!");
      return;
    }
    else if (strlen($newPassword) < 4) {
      UI::addMessage("danger", "Your password is too short!");
      return;
    }
    else if ($newPassword != $repeatedPassword) {
      UI::addMessage("danger", "Your new passwords do not match!");
      return;
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($this->user->getUsername(), $newPassword, $newSalt);
    $this->user->setPasswordHash($newHash);
    $this->user->setPasswordSalt($newSalt);
    $this->user->setIsComputedPassword(0);
    $FACTORIES::getUserFactory()->update($this->user);
    UI::addMessage("success", "Password was updated successfully!");
  }
  
  private function updateLifetime(){
    global $FACTORIES;
    
    $lifetime = intval($_POST['lifetime']);
    if ($lifetime < 60 || $lifetime > 24 * 3600) {
      UI::addMessage("danger", "Lifetime must be larger than 1 minute and smaller than 2 days!");
      return;
    }
    
    $this->user->setSessionLifetime($lifetime);
    $FACTORIES::getUserFactory()->update($this->user);
    UI::addMessage("success", "Updated session lifetime successfully!");
  }
  
  private function setEmail(){
    global $FACTORIES;
    
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      UI::addMessage("danger", "Invalid email address!");
      return;
    }
    
    $this->user->setEmail($email);
    $FACTORIES::getUserFactory()->update($this->user);
    UI::addMessage("success", "Email updated successfully!");
  }
}