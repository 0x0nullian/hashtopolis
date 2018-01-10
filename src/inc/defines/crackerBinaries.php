<?php

class CrackerBinaryAction {
  const DELETE_BINARY_TYPE = "deleteBinaryType";
  const DELETE_BINARY      = "deleteBinary";
}

class DPlatforms {
  const LINUX   = "linux";
  const WINDOWS = "win";
  const MAC_OSX = "osx";
  
  public static function getName($type) {
    switch ($type) {
      case DPlatforms::LINUX:
        return "Linux";
      case DPlatforms::MAC_OSX:
        return "Max OSX";
      case DPlatforms::WINDOWS:
        return "Windows";
      default:
        return "Unknown";
    }
  }
}