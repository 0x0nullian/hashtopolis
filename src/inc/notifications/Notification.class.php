<?php
use DBA\NotificationSetting;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 13:38
 */
abstract class HashtopussyNotification {
  public static $name;
  protected     $receiver;
  
  /** @var  $notification NotificationSetting */
  protected $notification;
  
  /**
   * @param $notificationType string
   * @param $payload DataSet
   * @param $receiver string Contains the value where the message can be sent to. This can for example be an URL, an email address, etc.
   * @param $notification NotificationSetting
   */
  public function execute($notificationType, $payload, $notification) {
    /** @var $CONFIG DataSet */
    global $CONFIG;
    
    $this->receiver = $notification->getReceiver();
    $this->notification = $notification;
    $template = new Template($this->getTemplateName());
    $obj = $this->getObjects();
    switch ($notificationType) {
      case DNotificationType::TASK_COMPLETE:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $obj['message'] = "Task '" . $task->getTaskName() . "' (" . $task->getId() . ") is completed!";
        $obj['html'] = "Task <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "'>" . $task->getTaskName() . "</a> is completed!";
        $obj['simplified'] = "Task <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "|" . $task->getTaskName() . "> is completed!";
        break;
      case DNotificationType::AGENT_ERROR:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $obj['message'] = "Agent '" . $agent->getAgentName() . "' (" . $agent->getId() . ") errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        $obj['html'] = "Agent <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "'>" . $agent->getAgentName() . "</a> errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        $obj['simplified'] = "Agent <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/agents.php?id=" . $agent->getId() . "|" . $agent->getAgentName() . "> errored: " . $payload->getVal(DPayloadKeys::AGENT_ERROR);
        break;
      case DNotificationType::LOG_ERROR:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level ERROR occured by '".$logEntry->getIssuer()."-".$logEntry->getIssuerId()."': " . $logEntry->getMessage() . "!";
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
      case DNotificationType::NEW_TASK:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $obj['message'] = "New Task '" . $task->getTaskName() . "' (" . $task->getId() . ") was created";
        $obj['html'] = "New Task <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "'>" . $task->getTaskName() . "</a> was created";
        $obj['simplified'] = "New Task <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/tasks.php?id=" . $task->getId() . "|" . $task->getTaskName() . "> was created";
        break;
      case DNotificationType::NEW_HASHLIST:
        $hashlist = $payload->getVal(DPayloadKeys::HASHLIST);
        $obj['message'] = "New Hashlist '" . $hashlist->getHashlistName() . "' (" . $hashlist->getId() . ") was created";
        $obj['html'] = "New Hashlist <a href='" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/hashlists.php?id=" . $hashlist->getId() . "'>" . $hashlist->getHashlistName() . "</a> was created";
        $obj['simplified'] = "New Hashlist <" . Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/hashlists.php?id=" . $hashlist->getId() . "|" . $hashlist->getHashlistName() . "> was created";
        break;
      case DNotificationType::HASHLIST_ALL_CRACKED:
        //TODO:
        break;
      case DNotificationType::HASHLIST_CRACKED_HASH:
        //TODO:
        break;
      case DNotificationType::USER_CREATED:
        //TODO:
        break;
      case DNotificationType::USER_DELETED:
        //TODO:
        break;
      case DNotificationType::USER_LOGIN_FAILED:
        //TODO:
        break;
      case DNotificationType::NEW_AGENT:
        //TODO:
        break;
      case DNotificationType::DELETE_TASK:
        //TODO:
        break;
      case DNotificationType::DELETE_HASHLIST:
        //TODO:
        break;
      case DNotificationType::DELETE_AGENT:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        $obj['message'] = "Agent '" . $agent->getAgentName() . "' (" . $agent->getId() . ") got deleted";
        $obj['html'] = "Agent <a href='#'>" . $agent->getAgentName() . "</a> got deleted";
        $obj['simplified'] = "Agent '" . $agent->getAgentName() . "' got deleted";
        break;
      case DNotificationType::LOG_WARN:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level WARN occured by '".$logEntry->getIssuer()."-".$logEntry->getIssuerId()."': " . $logEntry->getMessage() . "!";
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
      case DNotificationType::LOG_FATAL:
        $logEntry = $payload->getVal(DPayloadKeys::LOG_ENTRY);
        $obj['message'] = "Log level FATAL occured by '".$logEntry->getIssuer()."-".$logEntry->getIssuerId()."': " . $logEntry->getMessage() . "!";
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
      default:
        $obj['message'] = "Notification for unknown type: " . print_r($payload->getAllValues(), true);
        $obj['html'] = $obj['message'];
        $obj['simplified'] = $obj['message'];
        break;
    }
    $this->sendMessage($template->render($obj));
  }
  
  abstract function getTemplateName();
  
  abstract function getObjects();
  
  abstract function sendMessage($message);
}
