<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_replay_tool {
    function __construct() {
      global $_G;
      $this->css = '<link rel="stylesheet" type="text/css" href="'.$_G['siteurl'].'/source/plugin/replay_tool/css/replay.css" />';
    }
}

class plugin_replay_tool_forum extends plugin_replay_tool {
    function viewthread_top() {
      return $this->css;
    }
}
