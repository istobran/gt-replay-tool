<?php
interface ReplayInfo {
  public var $mapName;
  public var $players;
  public var $version;
  public function parseFile($filename);
  public function parseData($data);
}
 ?>
