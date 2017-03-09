<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 14:00
 */
class HashtopussyNotificationEmail extends HashtopussyNotification {
  protected     $receiver;
  public static $name = "Email";
  
  function getTemplateName() {
    return "notifications/email";
  }
  
  function getObjects() {
    $obj = array();
    $obj['username'] = Util::getUsernameById($this->notification->getUserId());
    return $obj;
  }
  
  function sendMessage($message) {
    Util::sendMail($this->receiver, "Hashtopussy Notification", $message);
  }
}

$NOTIFICATIONS['Email'] = new HashtopussyNotificationEmail();

