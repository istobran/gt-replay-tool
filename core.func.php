<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function resolve_replay()
{
  global $_G;
  //import resources
  define('MODEL_FOLDER',
    DISCUZ_ROOT.'source'.
    DIRECTORY_SEPARATOR.'plugin'.
    DIRECTORY_SEPARATOR.'replay_tool'.
    DIRECTORY_SEPARATOR.'model'.
    DIRECTORY_SEPARATOR
  );
  $class_resources = array(
    'CNC4' => MODEL_FOLDER.'ReplayInfoCNC4.class.php',
    'CNC3' => MODEL_FOLDER.'ReplayInfoCNC3.class.php',
    'COH' => MODEL_FOLDER.'ReplayInfoCOH.class.php',
    'DOWII' => MODEL_FOLDER.'ReplayInfoDOWII.class.php',
    'KW' => MODEL_FOLDER.'ReplayInfoKW.class.php',
    'RA3' => MODEL_FOLDER.'ReplayInfoRA3.class.php',
    'ZH' => MODEL_FOLDER.'ReplayInfoZH.class.php'
  );
  include $class_resources['CNC4'];
  include $class_resources['CNC3'];
  include $class_resources['COH'];
  include $class_resources['DOWII'];
  include $class_resources['KW'];
  include $class_resources['RA3'];
  include $class_resources['ZH'];
  include 'tool.func.php';
  foreach ($class_resources as $value) {
    // $arr[] = $value.' : '.include_once($value);
    $arr[] = $value.' : '.file_exists($value);
  }
  debug(implode("\n", $arr));
}
 ?>
