<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function resolve_replay()
{
  global $_G;
  //import resources
  define('MODEL_FOLDER', DISCUZ_ROOT.'./source/plugin/replay_tool/model/');
  $class_resources = array(
    'CNC4' => MODEL_FOLDER.'ReplayInfoCNC4.class.php',
    'CNC3' => MODEL_FOLDER.'ReplayInfoCNC3.class.php',
    'COH' => MODEL_FOLDER.'ReplayInfoCOH.class.php',
    'DOWII' => MODEL_FOLDER.'ReplayInfoDOWII.class.php',
    'KW' => MODEL_FOLDER.'ReplayInfoKW.class.php',
    'RA3' => MODEL_FOLDER.'ReplayInfoRA3.class.php',
    'ZH' => MODEL_FOLDER.'ReplayInfoZH.class.php'
  );
  foreach ($class_resources as $value) {
    @include_once $value;
  }

}
 ?>
