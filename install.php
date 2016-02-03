<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
CREATE TABLE IF NOT EXISTS replaydictionary (
  `key` varchar(255) NOT NULL COMMENT '索引值',
  `value` mediumtext NOT NULL COMMENT '显示值'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用于保持录像中需要使用的汉字';

CREATE TABLE IF NOT EXISTS replayinfo (
 `infoid` int(10) NOT NULL auto_increment,
 `gametype` varchar(100) NOT NULL COMMENT '游戏类型',
 `length` int(10) unsigned NOT NULL COMMENT '游戏时长',
 `mapname` mediumtext NOT NULL COMMENT '游戏地图',
 `time` varchar(255) NOT NULL COMMENT '游戏时间',
 `attachmentid` int(10) NOT NULL COMMENT '附件id',
 `version` varchar(255) NOT NULL COMMENT '版本号',
 `mapdispname` varchar(255) NOT NULL COMMENT '地图的显示名',
 PRIMARY KEY  (`infoid`),
 KEY `attachmentid` (`attachmentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS replaymapname (
 `filename` varchar(512) character set latin1 NOT NULL COMMENT '文件名',
 `name` varchar(512) NOT NULL COMMENT '地图名',
 `gameid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS replayplayerinfo (
 `attachmentid` int(10) NOT NULL COMMENT '录像的id',
 `name` varchar(255) NOT NULL,
 `side` varchar(100) NOT NULL COMMENT '阵营',
 `team` varchar(16) NOT NULL COMMENT '组别',
 `sideNumber` varchar(32) NOT NULL COMMENT '阵营的数字索引',
 `ishuman` varchar(200) NOT NULL,
 `replayinfoid` int(10) NOT NULL,
 KEY `attachmentid` (`attachmentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;

?>
