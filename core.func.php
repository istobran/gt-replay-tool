<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

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

  // $html_processed = preg_replace_callback(
  //     '~\{\$(.*?)\}~si',
  //     function($match) use ($replacement_array)
  //     {
  //         return str_replace($match[0], isset($replacement_array[$match[1]]) ? $replacement_array[$match[1]] : $match[0], $match[0]);
  //     },
  //     $html);

  // foreach ($variable as $key => $value) {
  //   # code...
  // }

  return $html;
}

function resolve_replay($attachmentid, $attach, $aidencode, $is_archive)
{
  global $_G;
  //import resources
  define('PLUGIN_ROOT',
    DISCUZ_ROOT.'source'.
    DIRECTORY_SEPARATOR.'plugin'.
    DIRECTORY_SEPARATOR.'replay_tool'.
    DIRECTORY_SEPARATOR
  );
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

  $replayinfo_g = get_replayinfo($attachmentid);
  $time_len = get_game_lentime($replayinfo_g['length']);
  $data = get_replayplayerinfo($attachmentid);

  debug(build_html($replayinfo_g, $time_len, $data, $attach, $aidencode, $is_archive));
  //return build_html();
}
?>
