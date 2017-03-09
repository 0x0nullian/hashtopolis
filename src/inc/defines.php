<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.01.17
 * Time: 19:02
 */

/*
 * All define classes should start with 'D'
 */

// hashcat status numbers
class DHashcatStatus {
  const INIT                   = 0;
  const AUTOTUNE               = 1;
  const RUNNING                = 2;
  const PAUSED                 = 3;
  const EXHAUSTED              = 4;
  const CRACKED                = 5;
  const ABORTED                = 6;
  const QUIT                   = 7;
  const BYPASS                 = 8;
  const ABORTED_CHECKPOINT     = 9;
  const STATUS_ABORTED_RUNTIME = 10;
}

// operating systems
class DOperatingSystem {
  const LINUX   = 0;
  const WINDOWS = 1;
  const OSX     = 2;
}

// hashlist formats
class DHashlistFormat {
  const PLAIN         = 0;
  const WPA           = 1;
  const BINARY        = 2;
  const SUPERHASHLIST = 3;
}

// access levels for user groups
class DAccessLevel { // if you change any of them here, you need to check if this is consistent with the database
  const VIEW_ONLY     = 1;
  const READ_ONLY     = 5;
  const USER          = 20;
  const SUPERUSER     = 30;
  const ADMINISTRATOR = 50;
}

// used config values
class DConfig {
  const BENCHMARK_TIME    = "benchtime";
  const CHUNK_DURATION    = "chunktime";
  const CHUNK_TIMEOUT     = "chunktimeout";
  const AGENT_TIMEOUT     = "agenttimeout";
  const HASHES_PAGE_SIZE  = "pagingSize";
  const FIELD_SEPARATOR   = "fieldseparator";
  const HASHLIST_ALIAS    = "hashlistAlias";
  const STATUS_TIMER      = "statustimer";
  const BLACKLIST_CHARS   = "blacklistChars";
  const NUMBER_LOGENTRIES = "numLogEntries";
  const TIME_FORMAT       = "timefmt";
  
  /**
   * Gives the format which a config input should have. Default is string if it's not a known config.
   * @param $config string
   * @return string
   */
  public static function getConfigType($config) {
    switch ($config) {
      case DConfig::BENCHMARK_TIME:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_DURATION:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::AGENT_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASHES_PAGE_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::FIELD_SEPARATOR:
        return DConfigType::STRING_INPUT;
      case DConfig::HASHLIST_ALIAS:
        return DConfigType::STRING_INPUT;
      case DConfig::STATUS_TIMER:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BLACKLIST_CHARS:
        return DConfigType::STRING_INPUT;
      case DConfig::NUMBER_LOGENTRIES:
        return DConfigType::NUMBER_INPUT;
      case DConfig::TIME_FORMAT:
        return DConfigType::STRING_INPUT;
    }
    return DConfigType::STRING_INPUT;
  }
  
  /**
   * @param $config string
   * @return string
   */
  public static function getConfigDescription($config) {
    switch ($config) {
      case DConfig::BENCHMARK_TIME:
        return "Time in seconds an agent should benchmark a task";
      case DConfig::CHUNK_DURATION:
        return "How long an agent approximately should need for completing one chunk";
      case DConfig::CHUNK_TIMEOUT:
        return "How long an agent must not respond until it is treated as timed out and the chunk will get shipped to other agents";
      case DConfig::AGENT_TIMEOUT:
        return "How long an agent must not respond until he is treated as not active anymore";
      case DConfig::HASHES_PAGE_SIZE:
        return "How many hashes are showed at once in the hashes view (Page size)";
      case DConfig::FIELD_SEPARATOR:
        return "What separator should be used to separate hash and plain (or salt)";
      case DConfig::HASHLIST_ALIAS:
        return "What string is used as hashlist alias when creating a task";
      case DConfig::STATUS_TIMER:
        return "After how many seconds the agent should send it's progress and cracks to the server";
      case DConfig::BLACKLIST_CHARS:
        return "Chars which are not allowed to be used in attack command inputs";
      case DConfig::NUMBER_LOGENTRIES:
        return "How many log entries should be saved. When this number is exceeded by 120%, the oldest ones will get deleted";
      case DConfig::TIME_FORMAT:
        return "Set the formatting of time displaying. Use syntax for PHPs date() method.";
    }
    return $config;
  }
}

class DNotificationObjectType {
  const HASHLIST = "Hashlist";
  const AGENT = "Agent";
  const USER = "User";
  const TASK = "Task";
  
  const NONE = "NONE";
}

class DNotificationType {
  const TASK_COMPLETE = "taskComplete";
  const AGENT_ERROR   = "agentError";
  const OWN_AGENT_ERROR = "ownAgentError"; //difference to AGENT_ERROR is that this can be configured by owners
  const LOG_ERROR     = "logError";
  
  public static function getAll(){
    return array(
      DNotificationType::TASK_COMPLETE,
      DNotificationType::AGENT_ERROR,
      DNotificationType::OWN_AGENT_ERROR,
      DNotificationType::LOG_ERROR,
    );
  }
  
  /**
   * @param $notificationType string
   * @return int access level
   */
  public static function getRequiredLevel($notificationType) {
    switch($notificationType){
      case DNotificationType::TASK_COMPLETE:
        return DAccessLevel::USER;
      case DNotificationType::AGENT_ERROR:
        return DAccessLevel::SUPERUSER;
      case DNotificationType::OWN_AGENT_ERROR:
        return DAccessLevel::USER;
      case DNotificationType::LOG_ERROR:
        return DAccessLevel::ADMINISTRATOR;
    }
    return DAccessLevel::ADMINISTRATOR;
  }
  
  public static function getObjectType($notificationType) {
    switch($notificationType){
      case DNotificationType::TASK_COMPLETE:
        return DNotificationObjectType::TASK;
      case DNotificationType::AGENT_ERROR:
        return DNotificationObjectType::AGENT;
      case DNotificationType::OWN_AGENT_ERROR:
        return DNotificationObjectType::AGENT;
      case DNotificationType::LOG_ERROR:
        return DNotificationObjectType::NONE;
    }
    return DNotificationObjectType::NONE;
  }
}

class DPayloadKeys {
  const TASK        = "task";
  const AGENT       = "agent";
  const AGENT_ERROR = "agentError";
  const LOG_ENTRY   = "logEntry";
}

class DConfigType {
  const STRING_INPUT = "string";
  const NUMBER_INPUT = "number";
}

// log entry types
class DLogEntry {
  const WARN  = "warning";
  const ERROR = "error";
  const FATAL = "fatal error";
  const INFO  = "information";
}

class DLogEntryIssuer {
  const API  = "API";
  const USER = "User";
}


