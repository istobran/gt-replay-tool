<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define('PLUGIN_ROOT',
  DISCUZ_ROOT.'source'.
  DIRECTORY_SEPARATOR.'plugin'.
  DIRECTORY_SEPARATOR.'replay_tool'.
  DIRECTORY_SEPARATOR
);

function get_replayinfo($aid){
	$replay_g = DB::fetch_first("select * from replayinfo where attachmentid=".$aid);
	return $replay_g;
}

function get_game_lentime($gameLen){
	$timeNumber = intval($gameLen);
	$timeMin = (int)($timeNumber/60);
	$timeSec = $timeNumber - $timeMin*60;
	$timeHour = (int)($timeMin/60);
	$replaylengthstring = "";
	if($timeHour != 0)
		$timeMin = $timeMin - $timeHour*60;
	if($timeHour>0)
		$replaylengthstring .= $timeHour.'小时';
	if($timeMin>0)
		$replaylengthstring .= $timeMin.'分钟';
	if($timeSec>0)
		$replaylengthstring .= $timeSec.'秒';
	return $replaylengthstring;
}

// 获取 录像信息
function get_replayplayerinfo($aid) {
	$data = array();
	$player = '';
	$replayinfo = DB::fetch_first("SELECT gametype, length, mapname, time, attachmentid, version, mapdispname from replayinfo where attachmentid = '".$aid."'");
	switch($replayinfo['gametype'])
				{
					//summer-->
					case 'dow2':

						$mapname = $replayinfo['mapname'];
						if($mapname)
							$mapimage = "./static/image/replays/dow2/maps/${mapname}.jpg";
						else $mapimage = "./static/image/replays/dow2/maps/unknownmap.gif";
						if(!file_exists($mapimage))
							$mapimage = "./static/image/replays/dow2/maps/unknownmap.gif";
						$mapname_html = $replayinfo['mapdispname'];
						$replayPlayers = DB::query("SELECT name, side, attachmentid, team from replayplayerinfo where attachmentid = '".$aid."'");
						$player = "<ul class='team_players'>";

						$allies = array();
						$axis = array();
						while ($playerSingle = DB::fetch($replayPlayers))
						{
							$playerString = "<ul class='team_players'><li><img src = './static/image/replays/dow2/factions/".$playerSingle['side'].".jpg'alt= '' title= ''/><span><b>";
							$playerString .= $playerSingle['name']."</b></span></li></ul>";
							if($playerSingle['team'] == "0")
	             				array_push($allies, $playerString);
							else
	                        	array_push($axis,$playerString);
						}
						foreach($allies as $pString)
							$player .= $pString;
						$player .= "<div class='versus'>".$vbphrase['replayversus']."</div>";
						foreach($axis as $pString)
							$player .= $pString;

						$datetimestring = $replayinfo['time']." | ";
						$versionstring = $replayinfo['version'];

						$replaylengthstring="";
						$timeNumber = intval($replayinfo['length']);
						$timeMin = (int)($timeNumber/60);
						$timeSec = $timeNumber - $timeMin*60;
						$timeHour = (int)($timeMin/60);
						$replaylengthstring = "";
						if($timeHour != 0)
							$timeMin = $timeMin - $timeHour*60;
						if($timeHour>0)
							$replaylengthstring .= $timeHour.'小时';
						if($timeMin>0)
							$replaylengthstring .= $timeMin.'分钟';
						if($timeSec>0)
							$replaylengthstring .= $timeSec.'秒';

						$show['replayattachment'] = true;
					break;

					case 'ZHReplay':

						$mapname = $replayinfo['mapname'];
						if($mapname)
							$mapimage = "./static/image/replays/zh/maps/${mapname}.gif";
						else $mapimage = "./static/image/replays/zh/maps/unknownmap.gif";
						if(!file_exists($mapimage))
							$mapimage = "./static/image/replays/zh/maps/unknownmap.gif";
						$mapname_html = $replayinfo['mapdispname'];
						$replayPlayers = DB::query("SELECT name, side, team, sidenumber, attachmentid from replayplayerinfo where attachmentid = '".$aid."'");
						$player = "<ul class='team_players'>";

						$teams = array();
						$observerteam = "";
						$freeIndex = 100;
						while ($playerSingle = DB::fetch($replayPlayers))
						{
							if($playerSingle['sidenumber'] == -2)
								$observerteam .= "<ul class='team_players'><li><img src = './static/image/replays/zh/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
							.$playerSingle['side']."'/><span><b>".$playerSingle['name']."</b></span></li></ul>";
							else{
								$playerString = "<ul class='team_players'><li><img src = './static/image/replays/zh/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
								.$playerSingle['side']."'/><span><b>";
								$playerString .= $playerSingle['name']."</b></span></li></ul>";
								if($playerSingle['team'] == -1)
								{
									$teams[$freeIndex] .= $playerString;
									$freeIndex++;
								}
								else{
									$teams[$playerSingle['team']] .=$playerString;
								}
							}
						}
						$player = implode("<div class='versus'>".$vbphrase['replayversus']."</div>", $teams);
						if(strlen($observerteam) != 0)
							$player = $player."<div class='versus'>".$vbphrase['replayobserver']."</div>".$observerteam;

						$datetimestring = $replayinfo['time']." | ";
						$versionstring = $replayinfo['version'];

						$replaylengthstring = "";
						$timeNumber = intval($replayinfo['length']);
						$timeMin = (int)($timeNumber/60);
						$timeSec = $timeNumber - $timeMin*60;
						$timeHour = (int)($timeMin/60);
						if($timeHour != 0)
							$timeMin = $timeMin - $timeHour*60;
						if($timeHour>0)
							$replaylengthstring .= $timeHour.'小时';
						if($timeMin>0)
							$replaylengthstring .= $timeMin.'分钟';
						if($timeSec>0)
							$replaylengthstring .= $timeSec.'秒';

						$show['replayattachment'] = true;
						break;
					case 'CNC3Replay':
						$mapname = $replayinfo['mapname'];
						if($mapname)
							$mapimage = "./static/image/replays/cc3/maps/${mapname}.gif";
						else $mapimage = "./static/image/replays/cc3/maps/unknownmap.gif";
						$mapname_html = $replayinfo['mapdispname'];
						$replayPlayers = DB::query("SELECT name, side, team, sidenumber, attachmentid from replayplayerinfo where attachmentid = '".$aid."'");
						$player = "<ul class='team_players'>";

						$teams = array();
						$observerteam = "";
						$freeIndex = 100;
						while ($playerSingle = DB::fetch($replayPlayers))
						{
							if($playerSingle['sidenumber'] == 2)
								$observerteam .= "<ul class='team_players'><li><img src = './static/image/replays/cc3/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
							.$playerSingle['side']."'/><span><b>".$playerSingle['name']."</b></span></li></ul>";
							else{
								$playerString = "<ul class='team_players'><li><img src = './static/image/replays/cc3/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
								.$playerSingle['side']."'/><span><b>";
								$playerString .= $playerSingle['name']."</b></span></li></ul>";
								if($playerSingle['team'] == -1)
								{
									$teams[$freeIndex] .= $playerString;
									$freeIndex++;
								}
								else{
									$teams[$playerSingle['team']] .=$playerString;
								}
							}
						}
						$player = implode("<div class='versus'>".$vbphrase['replayversus']."</div>", $teams);
						if(strlen($observerteam) != 0)
							$player = $player."<div class='versus'>".$vbphrase['replayobserver']."</div>".$observerteam;

						$datetimestring = $replayinfo['time']." | ";
						$versionstring = $replayinfo['version'];

						$replaylengthstring = "";
						$timeNumber = intval($replayinfo['length']);
						$timeMin = (int)($timeNumber/60);
						$timeSec = $timeNumber - $timeMin*60;
						$timeHour = (int)($timeMin/60);
						if($timeHour != 0)
							$timeMin = $timeMin - $timeHour*60;
						if($timeHour>0)
							$replaylengthstring .= $timeHour.'小时';
						if($timeMin>0)
							$replaylengthstring .= $timeMin.'分钟';
						if($timeSec>0)
							$replaylengthstring .= $timeSec.'秒';

						$show['replayattachment'] = true;
						break;

						case 'CNC4Replay':
							$mapname = $replayinfo['mapname'];
							if($mapname)
								$mapimage = "./static/image/replays/cc4/maps/${mapname}.gif";
							else $mapimage = "./static/image/replays/cc4/maps/unknownmap.gif";
							if(!file_exists($mapimage))
								$mapimage = "./static/image/replays/cc4/maps/unknownmap.gif";
							$mapname_html = $replayinfo['mapdispname'];
							$replayPlayers = DB::query("SELECT name, side, team, sidenumber, attachmentid from replayplayerinfo where attachmentid = '".$aid."'");
							$player = "<ul class='team_players'>";

							$teams = array();
							$observerteam = "";
							$freeIndex = 100;
							while ($playerSingle = DB::fetch($replayPlayers))
							{
								if($playerSingle['sidenumber'] == 2)
									$observerteam .= "<ul class='team_players'><li><img src = './static/image/replays/cc4/factions/".$playerSingle['sidenumber'].".jpg' alt= '' title= '"
								.$playerSingle['side']."'/><span><b>".$playerSingle['name']."</b></span></li></ul>";
								else{
									$playerString = "<ul class='team_players'><li><img src = './static/image/replays/cc4/factions/".$playerSingle['sidenumber'].".jpg' alt= '' title= '"
									.$playerSingle['side']."'/><span><b>";
									$playerString .= $playerSingle['name']."</b></span></li></ul>";
									if($playerSingle['team'] == -1)
									{
										$teams[$freeIndex] .= $playerString;
										$freeIndex++;
									}
									else{
										$teams[$playerSingle['team']] .=$playerString;
									}
								}
							}
							$player = implode("<div class='versus'>".$vbphrase['replayversus']."</div>", $teams);
							if(strlen($observerteam) != 0)
								$player = $player."<div class='versus'>".$vbphrase['replayobserver']."</div>".$observerteam;

							$datetimestring = $replayinfo['time']." | ";
							$versionstring = $replayinfo['version'];

							$replaylengthstring = "";
							$timeNumber = intval($replayinfo['length']);
							$timeMin = (int)($timeNumber/60);
							$timeSec = $timeNumber - $timeMin*60;
							$timeHour = (int)($timeMin/60);
							if($timeHour != 0)
								$timeMin = $timeMin - $timeHour*60;
							if($timeHour>0)
								$replaylengthstring .= $timeHour.'小时';
							if($timeMin>0)
								$replaylengthstring .= $timeMin.'分钟';
							if($timeSec>0)
								$replaylengthstring .= $timeSec.'秒';

							$show['replayattachment'] = true;
							break;
					case 'KWReplay':
						$mapname = $replayinfo['mapname'];
						if($mapname)
							$mapimage = "./static/image/replays/cc3kw/maps/${mapname}.gif";
						else $mapimage = "./static/image/replays/cc3kw/maps/unknownmap.gif";
						if(!file_exists($mapimage))
							$mapimage = "./static/image/replays/cc3kw/maps/unknownmap.gif";
						$mapname_html = $replayinfo['mapdispname'];
						$replayPlayers = DB::query("SELECT name, side, team, sidenumber, attachmentid from replayplayerinfo where attachmentid = '".$aid."'");
						$player = "<ul class='team_players'>";

						$teams = array();
						$observerteam = "";
						$freeIndex = 100;
						while ($playerSingle = DB::fetch($replayPlayers))
						{
							if($playerSingle['sidenumber'] == 2)
								$observerteam .= "<ul class='team_players'><li><img src = './static/image/replays/cc3kw/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
							.$playerSingle['side']."'/><span><b>".$playerSingle['name']."</b></span></li></ul>";
							else{
								$playerString = "<ul class='team_players'><li><img src = './static/image/replays/cc3kw/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
								.$playerSingle['side']."'/><span><b>";
								$playerString .= $playerSingle['name']."</b></span></li></ul>";
								if($playerSingle['team'] == -1)
								{
									$teams[$freeIndex] .= $playerString;
									$freeIndex++;
								}
								else{
									$teams[$playerSingle['team']] .=$playerString;
								}
							}
						}
						$player = implode("<div class='versus'>".$vbphrase['replayversus']."</div>", $teams);
						if(strlen($observerteam) != 0)
							$player = $player."<div class='versus'>".$vbphrase['replayobserver']."</div>".$observerteam;

						$datetimestring = $replayinfo['time']." | ";
						$versionstring = $replayinfo['version'];

						$replaylengthstring = "";
						$timeNumber = intval($replayinfo['length']);
						$timeMin = (int)($timeNumber/60);
						$timeSec = $timeNumber - $timeMin*60;
						$timeHour = (int)($timeMin/60);
						if($timeHour != 0)
							$timeMin = $timeMin - $timeHour*60;
						if($timeHour>0)
							$replaylengthstring .= $timeHour.'小时';
						if($timeMin>0)
							$replaylengthstring .= $timeMin.'分钟';
						if($timeSec>0)
							$replaylengthstring .= $timeSec.'秒';

						$show['replayattachment'] = true;
						break;
					case 'RA3Replay':
						$mapname = $replayinfo['mapname'];
						if($mapname)
							$mapimage = "./static/image/replays/ra3/maps/${mapname}.gif";
						else $mapimage = "./static/image/replays/ra3/maps/unknownmap.gif";
						if(!file_exists($mapimage))
							$mapimage = "./static/image/replays/ra3/maps/unknownmap.gif";
						$mapname_html = $replayinfo['mapdispname'];
						$replayPlayers = DB::query("SELECT name, side, team, sidenumber, attachmentid from replayplayerinfo where attachmentid = '".$aid."'");
						$player = "<ul class='team_players'>";

						$teams = array();
						$observerteam = "";
						$freeIndex = 100;
						while ($playerSingle = DB::fetch($replayPlayers))
						{
							if($playerSingle['sidenumber'] == 1)
								$observerteam .= "<ul class='team_players'><li><img src = './static/image/replays/ra3/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
							.$playerSingle['side']."'/><span><b><a href=\"www.sina.com\">".$playerSingle['name']."</a></b></span></li></ul>";
							else{
								$playerString = "<ul class='team_players'><li><img src = './static/image/replays/ra3/factions/".$playerSingle['sidenumber'].".gif' alt= '' title= '"
								.$playerSingle['side']."'/><span><b><a href=\"http://portal.commandandconquer.com/portal/site/cnc/stats?persona=CNC%3a".$playerSingle['name']."\">";
								$playerString .= $playerSingle['name']."</a></b></span></li></ul>";
								if($playerSingle['team'] == -1)
								{
									$teams[$freeIndex] .= $playerString;
									$freeIndex++;
								}
								else{
									$teams[$playerSingle['team']] .=$playerString;
								}
							}
						}
						$player = implode("<div class='versus'>".$vbphrase['replayversus']."</div>", $teams);
						if(strlen($observerteam) != 0)
							$player = $player."<div class='versus'>".$vbphrase['replayobserver']."</div>".$observerteam;

						$datetimestring = $replayinfo['time']." | ";
						$versionstring = $replayinfo['version'];

						$replaylengthstring = "";
						$timeNumber = intval($replayinfo['length']);
						$timeMin = (int)($timeNumber/60);
						$timeSec = $timeNumber - $timeMin*60;
						$timeHour = (int)($timeMin/60);
						if($timeHour != 0)
							$timeMin = $timeMin - $timeHour*60;
						if($timeHour>0)
							$replaylengthstring .= $timeHour.'小时';
						if($timeMin>0)
							$replaylengthstring .= $timeMin.'分钟';
						if($timeSec>0)
							$replaylengthstring .= $timeSec.'秒';

						$show['replayattachment'] = true;
						break;

					case 'rec':
						$mapname = $replayinfo['mapname'];
						if($mapname)
							$mapimage = "./static/image/replays/coh/maps/${mapname}.gif";
						else $mapimage = "./static/image/replays/coh/maps/unknownmap.gif";
						if(!file_exists($mapimage))
							$mapimage = "./static/image/replays/coh/maps/unknownmap.gif";
						$mapname_html = $replayinfo['mapdispname'];
						$replayPlayers = DB::query("SELECT name, side, attachmentid from replayplayerinfo where attachmentid = '".$aid."'");
						$player = "<ul class='team_players'>";

						$allies = array();
						$axis = array();
						while ($playerSingle = DB::fetch($replayPlayers))
						{
							$playerString = "<ul class='team_players'><li><img src = './static/image/replays/coh/factions/".$playerSingle['side'].".gif'alt= '' title= ''/><span><b>";
							$playerString .= $playerSingle['name']."</b></span></li></ul>";
							switch($playerSingle['side'])
							{
								case 'allies_commonwealth':
								case 'allies':
									array_push($allies, $playerString);
									break;
								case 'axis':
								case 'axis_panzer_elite':
									array_push($axis, $playerString);
									break;
							}
						}
						foreach($allies as $pString)
							$player .= $pString;
						$player .= "<div class='versus'>".$vbphrase['replayversus']."</div>";
						foreach($axis as $pString)
							$player .= $pString;

						$datetimestring = $replayinfo['time']." | ";
						$versionstring = $replayinfo['version'];

						$replaylengthstring="";
						$timeNumber = intval($replayinfo['length']);
						$timeMin = (int)($timeNumber/60);
						$timeSec = $timeNumber - $timeMin*60;
						$timeHour = (int)($timeMin/60);
						$replaylengthstring = "";
						if($timeHour != 0)
							$timeMin = $timeMin - $timeHour*60;
						if($timeHour>0)
							$replaylengthstring .= $timeHour.'小时';
						if($timeMin>0)
							$replaylengthstring .= $timeMin.'分钟';
						if($timeSec>0)
							$replaylengthstring .= $timeSec.'秒';

						$show['replayattachment'] = true;
						break;
		}
		$data['mapimage'] = $mapimage;
		$data['player'] = $player;
		return $data;
}

function build_html($replayinfo_g, $time_len, $data, $attach, $aidencode, $is_archive) {
  $html = file_get_contents(PLUGIN_ROOT.'template'.DIRECTORY_SEPARATOR.'template.html');

  $replacement_array = array(
      'replayinfo_g[\'mapname\']' => $replayinfo_g['mapname'],
      'replayinfo_g[\'gametype\']' => $replayinfo_g['gametype'],
      'replayinfo_g[\'mapdispname\']' => $replayinfo_g['mapdispname'],
      'replayinfo_g[\'version\']' => $replayinfo_g['version'],
      'replayinfo_g[\'time\']' => $replayinfo_g['time'],
      'data[\'mapimage\']' => $data['mapimage'],
      'data[\'player\']' => $data['player'],
      'attach[\'attachsize\']' => $attach['attachsize'],
      'attach[\'downloads\']' => $attach['downloads'],
      'time_len' => $time_len,
      'aidencode' => $aidencode,
      'is_archive' => $is_archive
  );

  preg_match_all('~\{\$(.*?)\}~si', $html, $matches);

  foreach ($replacement_array as $key => $value) {
    $index = array_search($key, $matches[1]);
    $html = str_replace($matches[0][$index], $value, $html);
  }

  return $html;
}

function build_replay_info($attachmentid, $table_att) {
  //import resources
  $class_resources = array(
    'CNC4' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoCNC4.class.php',
    'CNC3' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoCNC3.class.php',
    'COH' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoCOH.class.php',
    'DOWII' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoDOWII.class.php',
    'KW' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoKW.class.php',
    'RA3' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoRA3.class.php',
    'ZH' => PLUGIN_ROOT.'model'.DIRECTORY_SEPARATOR.'ReplayInfoZH.class.php'
  );
  foreach ($class_resources as $value) {
    include_once $value;
  }
  include_once 'tool.func.php';
  $logStr = '';
  //建立recobject
  $attachinfo = DB::fetch_first("SELECT * FROM ".$table_att." a WHERE aid=".$attachmentid);
  $logStr.= "SELECT * FROM ".$table_att." a WHERE aid=".$attachmentid;
  $logStr.= "==";
  $filename = DISCUZ_ROOT.'./data/attachment/forum/'.$attachinfo['attachment'];
  $logStr.= $filename;
  $logStr.="++";
  file_put_contents(PLUGIN_ROOT.'log.txt', $logStr, FILE_APPEND);
  $fp = ffopen($filename,"rb");
  $fileData =ffread($fp,filesize($filename));
  $fileExtension = getExt($attachinfo['filename']);
      $gametype = get_replay_type($fileData, $fileExtension);
      $gametype = strtolower($gametype);
  switch($gametype)
  {
    case "dow2":
    //dow2
      $recObject = new ReplayInfoDOWII();
      $recObject->parseDOWIIData($fileData);
      //插入录像信息
                 //取得地图名
                 $mapChinesename = DB::fetch_first("SELECT name from replaymapname where filename = '$recObject->mapName'");
                 $mapname = $mapChinesename['name'];
                 $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'dow2', '$recObject->gameLength', '$recObject->mapName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$mapname')";
                 DB::query($query);
                 $replayinfo = DB::fetch_first("SELECT infoid FROM replayinfo WHERE attachmentid = $attachmentid");
                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 //$debug = print_r($recObject->players, true);
                 for($index=0;$index<$playercount;$index++)
                 {
                     $query = "INSERT INTO replayplayerinfo (
                                     `attachmentid` ,
                                     `name` ,
                                     `side` ,
                                     `team`,
                                     `replayinfoid`
                                     )
                                     VALUES (
                     '$attachmentid', '".quotesql($recObject->players['id'][$index])."', '".$recObject->players['side'][$index]."', '".$recObject->players['team'][$index]."','".
                     $replayinfo['infoid']."')";
                     DB::query($query);
                 }
                 break;
      break;
    //zh
    case "rep":
              $recObject = new ReplayInfoZH();
              $recObject->parseZHData($fileData);
              //插入录像信息
              //先将地图名称转换为安全sql语句
              $repMap = str_replace("'", "''", $recObject->mapName);
              $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'ZHReplay', '$recObject->gameLength', '$recObject->mapFileName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$repMap')";
                 DB::query($query);

                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 for($index=0;$index<$playercount;$index++)
                 {
                     //取得阵营信息
              $sidedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['side'][$index]."'");
              if($recObject->players['ishuman'][$index] == '0')
              {
                //取得电脑名字
                $namedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['id'][$index]."'");
                $playername = $namedata['value'];
              }else quotesql($playername = $recObject->players['id'][$index]);
                     $query = "INSERT INTO replayplayerinfo (
                                     `attachmentid` ,
                                     `name` ,
                                     `side` ,
                                     `team`,
                                     `sidenumber`,
                                     `ishuman`
                                     )
                                     VALUES (
                     '$attachmentid', '".$playername."', '".$sidedata['value']."', '"
                     .$recObject->players['team'][$index]."', '".$recObject->players['sidenumber'][$index]."', '".$recObject->players['ishuman'][$index]."'    )";
                     DB::query($query);
                 }
                 break;
    //CNC3
    case "cnc3replay":
              $recObject = new ReplayInfoCNC3();
              $recObject->parseCNC3Data($fileData );
              //插入录像信息
              //先将地图名称转换为安全sql语句
              $cc3Map = str_replace("'", "''", $recObject->mapName);
              $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'CNC3Replay', '$recObject->gameLength', '$recObject->mapFileName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$cc3Map')";
                 DB::query($query);
                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 for($index=0;$index<$playercount;$index++)
                 {
                     //取得阵营信息
              $sidedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['side'][$index]."'");
              if($recObject->players['ishuman'][$index] == '0')
              {
                //取得电脑名字
                $namedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['id'][$index]."'");
                $playername = $namedata['value'];
              }else $playername = quotesql($recObject->players['id'][$index]);
                     $query = "INSERT INTO replayplayerinfo (
                                     `attachmentid` ,
                                     `name` ,
                                     `side` ,
                                     `team`,
                                     `sidenumber`,
                                     `ishuman`
                                     )
                                     VALUES (
                     '$attachmentid', '".$playername."', '".$sidedata['value']."', '"
                     .$recObject->players['team'][$index]."', '".$recObject->players['sidenumber'][$index]."', '".$recObject->players['ishuman'][$index]."'    )";
                     DB::query($query);
                 }
                 break;
                 //CNC4
    case "cnc4replay":
              $recObject = new ReplayInfoCNC4();
              $recObject->parseCNC4Data($fileData );
              //插入录像信息
              //先将地图名称转换为安全sql语句
              $mapChinesename = DB::fetch_first("SELECT name from replaymapname where filename = '$recObject->mapFileName'");
                  $cc4Map = $mapChinesename['name'];
              $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'CNC4Replay', '$recObject->gameLength', '$recObject->mapFileName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$cc4Map')";
                 DB::query($query);
                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 for($index=0;$index<$playercount;$index++)
                 {
                     //取得阵营信息
              $sidedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['side'][$index]."'");
              if($recObject->players['ishuman'][$index] == '0')
              {
                //取得电脑名字
                $namedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['id'][$index]."'");
                $playername = $namedata['value'];
              }else $playername = quotesql($recObject->players['id'][$index]);
                     $query = "INSERT INTO replayplayerinfo (
                                     `attachmentid` ,
                                     `name` ,
                                     `side` ,
                                     `team`,
                                     `sidenumber`,
                                     `ishuman`
                                     )
                                     VALUES (
                     '$attachmentid', '".$playername."', '".$sidedata['value']."', '"
                     .$recObject->players['team'][$index]."', '".$recObject->players['sidenumber'][$index]."', '".$recObject->players['ishuman'][$index]."'    )";
                     DB::query($query);
                 }
                 break;
    //凯恩之怒
    case "kwreplay":
              $recObject = new ReplayInfoKW();
              $recObject->parseKWData($fileData);
              //插入录像信息
              //先将地图名称转换为安全sql语句
              $kwMap = str_replace("'", "''", $recObject->mapName);
              $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'KWReplay', '$recObject->gameLength', '$recObject->mapFileName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$kwMap')";
                 DB::query($query);
                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 for($index=0;$index<$playercount;$index++)
                 {
                     //取得阵营信息
                    $sidedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['side'][$index]."'");
                    if($recObject->players['ishuman'][$index] == '0')
                    {
                      //取得电脑名字
                      $namedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['id'][$index]."'");
                      $playername = $namedata['value'];
                    }else $playername = quotesql($recObject->players['id'][$index]);
                    $query = "INSERT INTO replayplayerinfo (
                                          `attachmentid` ,
                                          `name` ,
                                          `side` ,
                                          `team`,
                                          `sidenumber`,
                                          `ishuman`
                                          )
                                          VALUES (
                          '$attachmentid', '".$playername."', '".$sidedata['value']."', '"
                          .$recObject->players['team'][$index]."', '".$recObject->players['sidenumber'][$index]."', '".$recObject->players['ishuman'][$index]."'    )";
                          DB::query($query);
                }
                 break;
    case 'ra3replay':
              //红色警戒3
              $recObject = new ReplayInfoRA3();
              $recObject->parseRA3Data($fileData);
              //先将地图名称转换为安全sql语句
              $ra3Map = str_replace("'", "''", $recObject->mapName);
              //插入录像信息
              $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'RA3Replay', '$recObject->gameLength', '$recObject->mapFileName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$ra3Map')";
                 DB::query($query);
                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 for($index=0;$index<$playercount;$index++)
                 {
                     //取得阵营信息
              $sidedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['side'][$index]."'");
              if($recObject->players['ishuman'][$index] == '0')
              {
                //取得电脑名字
                $namedata = DB::fetch_first("SELECT `key`, `value` from replayDictionary where `key`='".$recObject->players['id'][$index]."'");
                $playername = $namedata['value'];
              }else $playername = quotesql($recObject->players['id'][$index]);
                     $query = "INSERT INTO replayplayerinfo (
                                     `attachmentid` ,
                                     `name` ,
                                     `side` ,
                                     `team`,
                                     `sidenumber`,
                                     `ishuman`
                                     )
                                     VALUES (
                     '$attachmentid', '".$playername."', '".$sidedata['value']."', '"
                     .$recObject->players['team'][$index]."', '".$recObject->players['sidenumber'][$index]."', '".$recObject->players['ishuman'][$index]."'    )";
                     DB::query($query);
                 }
                 break;

    case "rec":
              //英雄连
                 $recObject = new ReplayInfoCOH();
                 //解析录像文件
                 $recObject->parseCOHData($fileData );
                 //插入录像信息
                 //取得地图名
                 $mapChinesename = DB::fetch_first("SELECT name from replaymapname where filename = '$recObject->mapName'");
                 $mapname = $mapChinesename['name'];
                 $query = "INSERT INTO replayinfo (
                                 `infoid` ,
                                 `gametype` ,
                                 `length` ,
                                 `mapname` ,
                                 `time` ,
                                 `attachmentid`,
                                 `version`,
                                 `mapdispname`
                                 )
                                 VALUES (
                                 NULL , 'rec', '$recObject->gameLength', '$recObject->mapName', '$recObject->playTime',
                                 '$attachmentid', '$recObject->version', '$mapname')";
                 DB::query($query);
                 //插入玩家信息
                 $playercount = count($recObject->players['id']);
                 //$debug = print_r($recObject->players, true);
                 for($index=0;$index<$playercount;$index++)
                 {
                     $query = "INSERT INTO replayplayerinfo (
                                     `attachmentid` ,
                                     `name` ,
                                     `side` ,
                                     `team`
                                     )
                                     VALUES (
                     '$attachmentid', '".quotesql($recObject->players['id'][$index])."', '".$recObject->players['side'][$index]."', -1
                     )";
                     DB::query($query);
                 }
                 break;
  }
}

function resolve_replay($attachmentid, $attach, $aidencode, $is_archive)
{
  global $_G;
  // check if replay info is already in database
  $exits_attach = DB::fetch_first("select attachmentid from replayinfo where attachmentid=".$attachmentid);
  if (empty($exits_attach)) {
    $table_att = DB::table(getattachtablebyaid($attachmentid));
    build_replay_info($attachmentid, $table_att);
  }

  $replayinfo_g = get_replayinfo($attachmentid);
  $time_len = get_game_lentime($replayinfo_g['length']);
  $data = get_replayplayerinfo($attachmentid);

  return build_html($replayinfo_g, $time_len, $data, $attach, $aidencode, $is_archive);
}
?>
