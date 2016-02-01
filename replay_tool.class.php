<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define('MODEL_FOLDER', DISCUZ_ROOT.'./source/plugin/replay_tool/model/');

class plugin_replay_tool {
    function __construct(){

    }

    public function resolve_replay()
    {
      //import resources
      $class_resources = array(
        'CNC4' => MODEL_FOLDER.'ReplayInfoCNC4.php',
        'CNC3' => MODEL_FOLDER.'ReplayInfoCNC3.php',
        'COH' => MODEL_FOLDER.'ReplayInfoCOH.php',
        'DOWII' => MODEL_FOLDER.'ReplayInfoDOWII.php',
        'KW' => MODEL_FOLDER.'ReplayInfoKW.php',
        'RA3' => MODEL_FOLDER.'ReplayInfoRA3.php',
        'ZH' => MODEL_FOLDER.'ReplayInfoZH.php'
      );
      foreach ($class_resources as $value) {
        @include_once $value;
      }

    }

    function global_footer(){
        return '<script>alert("插件我来了")</script>';
    }

}
