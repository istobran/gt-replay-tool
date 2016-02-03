<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.2 - www.gametotal.org
|| # ---------------------------------------------------------------- # ||
|| # Copyright ?000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

//summer-->

//sql语句单引号
function quotesql($sql)
{
	return str_replace("'","''",$sql);
}

//stupid bithost
function ffopen($filename,$type)
{
	return fopen($filename,$type);
}

function ffread($fp,$filesize)
{
	return fread($fp,$filesize);
}

//高低位互换
function ChangeLowHigh($str)
{
	if(strlen($str) != 4)
		return $str;
	$data = $str[2].$str[3].$str[0].$str[1];
	return $data;
}

function hex2bin($data) {
  $len = strlen($data);
  for($i=0;$i<$len;$i+=2) {
      $newdata .= pack("C",hexdec(substr($data,$i,2)));
  }
  return $newdata;
}

//该函数将16位进制数字码转换为对应的utf-8字符串
function   hex2gbk($str){
	$len = strlen($str);
	if($len<4)
		return hex2bin($str);
	else{
		for($i=0;$i<$len;$i+=4){
			$code = substr($str, $i, 4);//单独的字符，高低位未进行转换，类似'小'的字符现在是0f53，ASCII字符为xx00
			if(strlen($code)<4)//如果小于4，转换为ASCII
				$newData .= hex2bin($code);
			else{
				$newCode = ChangeLowHigh($code);//高低位转换
		   	$newData .= iconv( "UCS-2",   "UTF-8",   pack( "H4",   $newCode));//编码
		  }
	  }
	}
	return $newData;
}

function get_replay_type($data, $fileExtension)
{
	if(strcasecmp($fileExtension, "cnc4replay") == 0)
		return "cnc4replay";	
	if(strcasecmp($fileExtension, "ra3replay") == 0)
		return "ra3replay";
	if(strcasecmp($fileExtension,"kwreplay") == 0)
		return "kwreplay";
	if(strcasecmp($fileExtension,"cnc3replay") == 0)
		return "cnc3replay";
	if(strcasecmp($fileExtension,"rep") == 0)
		return "rep";
	if(strcasecmp($fileExtension,"rec") == 0)
	{
		$hexdata = bin2hex($data);
		//先判断是否英雄连，第五个字节后为coh
		$cohToken = substr($hexdata, 8, 6);
		$cohToken = hex2bin($cohToken);
		if($cohToken == "COH")
			return "rec";//是英雄连
		$dowToken = substr($hexdata, 24, 8);
		$dowToken = hex2bin($dowToken);
		if($dowToken == "DOW2")
			return "dow2";
	}

}

class ReplayInfoCNC4
{
	var $mapName;
	var $mapFileName;
	var $playTime;//游戏日期
	var $gameLength;//游戏时长
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
		'sidenumber'=>array(),
		'ishuman'=>array(),
	);
	var $sides = array('9','8');//有效阵营
	var $version;//版本号
	var $prefix = 'cnc4';

	public function parseCNC4File($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseCNC4Data($file_data);
	}

	public function parseCNC4Data($data)
	{
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		//先解析地图
		//先找结尾
		$mapEnd = strpos($hexdata, "3b4d433d");//;MC=
		$mapString = substr($hexdata, 0, $mapEnd);
		$mapStart = strrpos($mapString, "0000")+4;		//地图开头之前是0000
		$mapName = substr($mapString, $mapStart);
		$this->mapName = hex2gbk($mapName);
		//地图名
		//先寻找/official
		$flag = "/official/";
		$fileStart = strpos($hexdata, bin2hex($flag))+ strlen($flag)*2;
		$fileEnd = strpos($hexdata, "3b4d", $fileStart);
		$mapFileName = substr($hexdata, $fileStart, $fileEnd - $fileStart);
		$this->mapFileName = hex2bin($mapFileName);
		//玩家信息,"S=H"之后
		$index = 0;
		$playerStart = strpos($hexdata, "533d48", $fileEnd)+6;//
		$playerTotalEnd = strpos($hexdata, "3a3b", $playerStart)+2;
		$playerString = substr($hexdata, $playerStart, $playerTotalEnd - $playerStart);
		//需要扫描两种标准，第一种玩家，第二种电脑
		$playerStart = 0;
		//$players = preg_split("/313a|2c3a|293a/", $playerString);
		$players = preg_split("/2c/", $playerString);
		$ccount = count($players);
		$step = 11;
		for($pindex=0;$pindex < $ccount;$pindex+=$step)
		{
			$nameclip = $players[$pindex+0];
			$nameStart = strpos($nameclip, "3a48");
			if($nameStart !== false || $pindex == 0)//第一个一定是人
			{
				//是人
				$step = 11;
				if($pindex != 0)
				{
					$nameStart += 4;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
				}else $name = $players[0];
				if(array_search(hex2bin($players[6+$pindex]), $this->sides) !== false)
				{
						array_push($this->players['id'], hex2bin($name));
						array_push($this->players['side'], $this->prefix.hex2bin($players[6+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[6+$pindex]));
						array_push($this->players['team'], hex2bin($players[6+$pindex]));
						array_push($this->players['ishuman'], '1');
				}
			}else{
				$nameStart = strpos($players[$pindex + 0], "3a43");
				if($nameStart !== false)
				{
					$nameStart += 4;
					//是机器
					$step = 7;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
					if(array_search(hex2bin($players[3+$pindex]), $this->sides) !== false)
					{
						switch($name)
						{
							case '45':
								array_push($this->players['id'], 'ra3CE');//简单电脑
								break;
							case '4d':
								array_push($this->players['id'], 'ra3CM');//中等电脑
								break;
							case '48':
								array_push($this->players['id'], 'ra3CH');//困难电脑
								break;
							case '42':
								array_push($this->players['id'], "ra3CB");//凶残电脑
								break;
							default:
								array_push($this->players['id'], "ra3CU");//未知电脑
								break;
						}
						array_push($this->players['side'], $this->prefix.hex2bin($players[3+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[3+$pindex]));
						array_push($this->players['team'], hex2bin($players[3+$pindex]));
						array_push($this->players['ishuman'], '0');
					}
				}
			}
		}//end for
		//寻找版本号
		$verEnd = strpos($hexdata, "000000000002", $playerTotalEnd);//先找结尾，F3C748
		$verData = substr($hexdata, $playerTotalEnd, $verEnd-$playerTotalEnd);
		//var_dump($verData);
		$verStart = strrpos($verData,"00")+2;
		$verString = substr($verData, $verStart, 6);//只取3位版本
		$this->version = hex2bin($verString);//只用了2字节，所以用hex2bin
		//寻找日期
		//从版本号回退40字节
		$dateStart = $verStart-40+$playerTotalEnd;
		$dateString = substr($hexdata, $dateStart, 24);//固定长度
		//解析年
		$year = substr($dateString,0,4);
		$year = ChangeLowHigh($year);
		$year = intval($year, 16);
		//解析月
		$month = substr($dateString, 4, 2);
		$month = intval($month, 16);
		//解析日
		$day = substr($dateString, 12, 2);
		$day = intval($day, 16);
		//时
		$hour = substr($dateString, 16, 2);
		$hour = intval($hour, 16);
		//分
		$min = substr($dateString, 20, 2);
		$min = intval($min, 16);
		$min = $min<10?"0".$min:$min;
		//时间字符串
		$this->playTime = "${year}-${month}-${day} ${hour}:$min";
		//时长
		$flag = "FOOTER";
		$timeStart = strpos($hexdata, bin2hex($flag))+strlen($flag)*2;
		$timeSpan = substr($hexdata, $timeStart, 4);
		$timeSpan = ChangeLowHigh($timeSpan);
		$timeNumber = (int)(intval($timeSpan,16)/15);
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
		$this->gameLengthString = "未知";
	}
}//end of cnc3 class


class ReplayInfoCOH
{
	var $mapName;
	var $playTime;
	var $gameLength;
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
	);
  var $version;

	public function parseCOHFile($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseCOHData($file_data);
	}

	public function parseCOHData($data)
	{
		$newVersion = "2.601";//最新的版本号统一在这里修改
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		$hexdataLen = strlen($hexdata);
		//寻找日期
		$start = strpos($hexdata, "4543")+4;
		$end = strpos($hexdata, "0000", $start)+2;//只所以向后加2是因为会有00是有用的情况
		$replayTime = substr($hexdata, $start, $end-$start);
		$this->playTime = hex2gbk($replayTime);
		//寻找地图
		$start = strpos($hexdata, "6d705c")+6;//为“mp\”
		$end = strpos($hexdata, "0000", $start)-8;//结尾是找到四个0后，再往前8个字节
		$maplen = $end-$start;
		if($maplen != ((int)($maplen/2))*2)
			$maplen += 1;
		$replayMap = substr($hexdata, $start, $maplen);
		$replayMap = hex2bin($replayMap);
		if(strpos($replayMap, "\\") !== false)
		{
			$version = $newVersion;
			$realMapIndex = strrpos($replayMap, "\\");
			$replayMap = substr($replayMap, $realMapIndex+1, strlen($replayMap)-$realMapIndex);
		}
		$this->mapName = $replayMap;
		//寻找玩家
		//DATAINFO后50个字节开始
		$index = 0;
		$searchIndex = 0;
		while(true)
		{
			$datainfo = "44415441494e46";
			$start = strpos($hexdata, $datainfo, $searchIndex);//寻找DATAINFO
			if($start === false)//没有了，退出
				break;
			$start = $start+strlen($datainfo)+50;	//地址前移自身长度与48个字节是玩家信息的开始
			$lenStart = $start - 8;
			$lenString = substr($hexdata, $lenStart, 2);
			$len = intval($lenString, 16);//玩家名字长度
			$replayPlayer = substr($hexdata, $start, $len*4);//截取玩家信息
			$replayPlayer = hex2gbk($replayPlayer);
			//截取阵营信息，从玩家结尾前进24个字节
			$sideStart = $start + $len*4 + 24;
			//阵营结束，第一个00
			$sideEnd = strpos($hexdata, "00", $sideStart);
			$playerSide = substr($hexdata, $sideStart, $sideEnd - $sideStart);
			$playerSide = hex2bin($playerSide);
			array_push($this->players['id'], $replayPlayer);
			array_push($this->players['side'], $playerSide);
			$searchIndex = $end;
			$index++;
			if($index>16)
				break;
			$searchIndex = $start+1;
		}
		//时间，文件结尾00 00 00 20后第二个字段，长度4字节
		//读取文件最后的部分

		$timeFlag = "00000020";
		$timeStart = strrpos($hexdata, $timeFlag);
		$resist = 0;
		while(($timeStart & 1)==1 && $resist<10)
		{
			$timeStart = strrpos($hexdata, $timeFlag, $timeStart-$hexdataLen-1);
			$resist++;
		}
		$timeStart += strlen($timeFlag);
		if($version != $newVersion)
		{//检查第9-10位是否为0
			$versionCheck = substr($hexdata, $timeStart+8, 2);
			//$vcheck2 =
			if($versionCheck == "00")
			{
				$timeStart += 2;//是老版本
				$this->version = "2.301";
			}
			else
				$this->version = "2.400";//是新版本
		}
		else $this->version = $version;
		//$timeStart = strlen($hexdata)-24;
		$time = substr($hexdata, $timeStart, 4);
		//var_dump($timeStart);
		//对调高低位
		$newtime = ChangeLowHigh($time);
		//var_dump($newtime);
		$timeNumber = (int)(intval($newtime,16)/8);
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
	}
}//end of coh class




class ReplayInfoRA3
{
	var $mapName;
	var $mapFileName;
	var $playTime;//游戏日期
	var $gameLength;//游戏时长
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
		'sidenumber'=>array(),
		'ishuman'=>array(),
	);
	var $sides = array('1','2','4','8','7');//有效阵营
	var $sideDesp = array('1'=>'ra31','2'=>'ra32','4'=>'ra34','8'=>'ra38', '7'=>'ra37');
	var $version;//版本号

	public function parseRA3File($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseRA3Data($file_data);
	}

	public function parseRA3Data($data)
	{
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		//先解析地图
		//先找结尾
		$mapEnd = strpos($hexdata, "0000460061006b006500");//f.a.k.e
		$mapString = substr($hexdata, 0, $mapEnd);
		$mapStart = strrpos($mapString, "0000")+4;		//地图开头之前是0000
		$mapName = substr($mapString, $mapStart);
		$this->mapName = hex2gbk($mapName);
		//地图名
		//先寻找/official
		$flag = "/official/";
		$fileStart = strpos($hexdata, bin2hex($flag))+ strlen($flag)*2;
		$fileEnd = strpos($hexdata, "3b4d", $fileStart);
		$mapFileName = substr($hexdata, $fileStart, $fileEnd - $fileStart);
		$this->mapFileName = hex2bin($mapFileName);
		//玩家信息,"S=H"之后
		$index = 0;
		$playerStart = strpos($hexdata, "533d48", $fileEnd)+6;//
		$playerTotalEnd = strpos($hexdata, "3a3b", $playerStart)+2;
		$playerString = substr($hexdata, $playerStart, $playerTotalEnd - $playerStart);
		//需要扫描两种标准，第一种玩家，第二种电脑
		$playerStart = 0;
		//$players = preg_split("/313a|2c3a|293a/", $playerString);
		$players = preg_split("/2c/", $playerString);
		$ccount = count($players);
		$step = 11;
		for($pindex=0;$pindex < $ccount;$pindex+=$step)
		{
			$nameclip = $players[$pindex+0];
			$nameStart = strpos($nameclip, "3a48");
			if($nameStart !== false || $pindex == 0)//第一个一定是人
			{
				//是人
				$step = 11;
				if($pindex != 0)
				{
					$nameStart += 4;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
				}else $name = $players[0];
				if(array_search(hex2bin($players[5+$pindex]), $this->sides) !== false)
				{
						array_push($this->players['id'], hex2bin($name));
						array_push($this->players['side'], $this->sideDesp[hex2bin($players[5+$pindex])]);
						array_push($this->players['sidenumber'], hex2bin($players[5+$pindex]));
						array_push($this->players['team'], hex2bin($players[7+$pindex]));
						array_push($this->players['ishuman'], '1');
				}
			}else{
				$nameStart = strpos($players[$pindex + 0], "3a43");
				if($nameStart !== false)
				{
					$nameStart += 4;
					//是机器
					$step = 6;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
					if(array_search(hex2bin($players[2+$pindex]), $this->sides) !== false)
					{
						switch($name)
						{
							case '45':
								array_push($this->players['id'], 'ra3CE');//简单电脑
								break;
							case '4d':
								array_push($this->players['id'], 'ra3CM');//中等电脑
								break;
							case '48':
								array_push($this->players['id'], 'ra3CH');//困难电脑
								break;
							case '42':
								array_push($this->players['id'], "ra3CB");//凶残电脑
								break;
							default:
								array_push($this->players['id'], "ra3CU");//未知电脑
								break;
						}
						array_push($this->players['side'], $this->sideDesp[hex2bin($players[2+$pindex])]);
						array_push($this->players['sidenumber'], hex2bin($players[2+$pindex]));
						array_push($this->players['team'], hex2bin($players[4+$pindex]));
						array_push($this->players['ishuman'], '0');
					}
				}
			}
		}//end for
		//寻找时间
		$dateStart = $playerTotalEnd+20;//寻找到字符串“last replay”的开头，也就是该字符串的字符数，因为不同语言版本的字符串长度不同
		$lastReplayStringNumber = substr($hexdata, $dateStart, 2);
		$lastReplayStringNumber = intval($lastReplayStringNumber, 16);
		$dateStart += $lastReplayStringNumber*4;
		$dateStart += 8; //加上三个0字节和字符数占据的长度
		$dateString = substr($hexdata, $dateStart, 24);//固定长度
		//解析年
		$year = substr($dateString,0,4);
		$year = ChangeLowHigh($year);
		$year = intval($year, 16);
		//解析月
		$month = substr($dateString, 4, 2);
		$month = intval($month, 16);
		//解析日
		$day = substr($dateString, 12, 2);
		$day = intval($day, 16);
		//时
		$hour = substr($dateString, 16, 2);
		$hour = intval($hour, 16);
		//分
		$min = substr($dateString, 20, 2);
		$min = intval($min, 16);
		$min = $min<10?"0".$min:$min;
		//时间字符串
		$this->playTime = "${year}-${month}-${day} ${hour}:$min";
		//寻找版本号
	  $verStart = $dateStart + 40;
		$verEnd = strpos($hexdata, "2e", $verStart+4);
		$verData = substr($hexdata, $verStart, $verEnd-$verStart);
		$this->version = hex2bin($verData);//只用了2字节，所以用hex2bin
		//时长，共8位，需要后4位颠倒后和前4位颠倒后再颠倒 AA BB CC DD最后为DD CC BB AA
		$flag = "FOOTER";
		$timeStart = strpos($hexdata, bin2hex($flag))+strlen($flag)*2;
		$timeSpanlow = substr($hexdata, $timeStart, 4);
		$timeSpanlow = ChangeLowHigh($timeSpanlow);
		$timeSpanhigh = substr($hexdata, $timeStart+4, 4);
		$timeSpanhigh = ChangeLowHigh($timeSpanhigh);
		$timeSpan = $timeSpanhigh.$timeSpanlow;
		$timeNumber = (int)(intval($timeSpan,16)/15);
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
	}
}//end of RA3 class

class ReplayInfoKW
{
	var $mapName;
	var $mapFileName;
	var $playTime;//游戏日期
	var $gameLength;//游戏时长
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
		'sidenumber'=>array(),
		'ishuman'=>array(),
	);
	var $sides = array('1','2','6','7','8','9','10','11','12','13','14');//有效阵营
	var $version;//版本号

	public function parseKWFile($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseKWData($file_data);
	}

	public function parseKWData($data)
	{
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		//先解析地图
		//先找结尾
		$mapEnd = strpos($hexdata, "0000460061006b006500");//f.a.k.e
		$mapString = substr($hexdata, 0, $mapEnd);
		$mapStart = strrpos($mapString, "0000")+4;		//地图开头之前是0000
		$mapName = substr($mapString, $mapStart);
		$this->mapName = hex2gbk($mapName);
		//地图名
		//先寻找/official
		$flag = "/official/";
		$fileStart = strpos($hexdata, bin2hex($flag))+ strlen($flag)*2;
		$fileEnd = strpos($hexdata, "3b4d", $fileStart);
		$mapFileName = substr($hexdata, $fileStart, $fileEnd - $fileStart);
		$this->mapFileName = hex2bin($mapFileName);
		//玩家信息,"S=H"之后
		$index = 0;
		$playerStart = strpos($hexdata, "533d48", $fileEnd)+6;//
		$playerTotalEnd = strpos($hexdata, "3a3b", $playerStart)+2;
		$playerString = substr($hexdata, $playerStart, $playerTotalEnd - $playerStart);
		//需要扫描两种标准，第一种玩家，第二种电脑
		$playerStart = 0;
		//$players = preg_split("/313a|2c3a|293a/", $playerString);
		$players = preg_split("/2c/", $playerString);
		$ccount = count($players);
		$step = 11;
		for($pindex=0;$pindex < $ccount;$pindex+=$step)
		{
			$nameclip = $players[$pindex+0];
			$nameStart = strpos($nameclip, "3a48");
			if($nameStart !== false || $pindex == 0)//第一个一定是人
			{
				//是人
				$step = 11;
				if($pindex != 0)
				{
					$nameStart += 4;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
				}else $name = $players[0];
				if(array_search(hex2bin($players[5+$pindex]), $this->sides) !== false)
				{
						array_push($this->players['id'], hex2bin($name));
						array_push($this->players['side'], 'kw'.hex2bin($players[5+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[5+$pindex]));
						array_push($this->players['team'], hex2bin($players[7+$pindex]));
						array_push($this->players['ishuman'], '1');
				}
			}else{
				$nameStart = strpos($players[$pindex + 0], "3a43");
				if($nameStart !== false)
				{
					$nameStart += 4;
					//是机器
					$step = 6;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
					if(array_search(hex2bin($players[2+$pindex]), $this->sides) !== false)
					{
						switch($name)
						{
							case '45':
								array_push($this->players['id'], 'ra3CE');//简单电脑
								break;
							case '4d':
								array_push($this->players['id'], 'ra3CM');//中等电脑
								break;
							case '48':
								array_push($this->players['id'], 'ra3CH');//困难电脑
								break;
							case '42':
								array_push($this->players['id'], "ra3CB");//凶残电脑
								break;
							default:
								array_push($this->players['id'], "ra3CU");//未知电脑
								break;
						}
						array_push($this->players['side'], 'kw'.hex2bin($players[2+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[2+$pindex]));
						array_push($this->players['team'], hex2bin($players[4+$pindex]));
						array_push($this->players['ishuman'], '0');
					}
				}
			}
		}//end for
		//寻找版本号
		$verEnd = strpos($hexdata, "000000000002", $playerTotalEnd);//先找结尾，F3C748
		$verData = substr($hexdata, $playerTotalEnd, $verEnd-$playerTotalEnd);
		//var_dump($verData);
		$verStart = strrpos($verData,"00")+2;
		$verString = substr($verData, $verStart, 6);//只取3位版本
		$this->version = hex2bin($verString);//只用了2字节，所以用hex2bin
		//寻找日期
		//从版本号回退40字节
		$dateStart = $verStart-40+$playerTotalEnd;
		$dateString = substr($hexdata, $dateStart, 24);//固定长度
		//解析年
		$year = substr($dateString,0,4);
		$year = ChangeLowHigh($year);
		$year = intval($year, 16);
		//解析月
		$month = substr($dateString, 4, 2);
		$month = intval($month, 16);
		//解析日
		$day = substr($dateString, 12, 2);
		$day = intval($day, 16);
		//时
		$hour = substr($dateString, 16, 2);
		$hour = intval($hour, 16);
		//分
		$min = substr($dateString, 20, 2);
		$min = intval($min, 16);
		$min = $min<10?"0".$min:$min;
		//时间字符串
		$this->playTime = "${year}-${month}-${day} ${hour}:$min";
		//时长
		$flag = "FOOTER";
		$timeStart = strpos($hexdata, bin2hex($flag))+strlen($flag)*2;
		$timeSpan = substr($hexdata, $timeStart, 4);
		$timeSpan = ChangeLowHigh($timeSpan);
		$timeNumber = (int)(intval($timeSpan,16)/15);
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
	}
}//end of kw class

class ReplayInfoCNC3
{
	var $mapName;
	var $mapFileName;
	var $playTime;//游戏日期
	var $gameLength;//游戏时长
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
		'sidenumber'=>array(),
		'ishuman'=>array(),
	);
	var $sides = array('1','2','6','7','8');//有效阵营
	var $version;//版本号
	var $prefix = 'cnc3';

	public function parseCNC3File($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseCNC3Data($file_data);
	}

	public function parseCNC3Data($data)
	{
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		//先解析地图
		//先找结尾
		$mapEnd = strpos($hexdata, "0000460061006b006500");//f.a.k.e
		$mapString = substr($hexdata, 0, $mapEnd);
		$mapStart = strrpos($mapString, "0000")+4;		//地图开头之前是0000
		$mapName = substr($mapString, $mapStart);
		$this->mapName = hex2gbk($mapName);
		//地图名
		//先寻找/official
		$flag = "/official/";
		$fileStart = strpos($hexdata, bin2hex($flag))+ strlen($flag)*2;
		$fileEnd = strpos($hexdata, "3b4d", $fileStart);
		$mapFileName = substr($hexdata, $fileStart, $fileEnd - $fileStart);
		$this->mapFileName = hex2bin($mapFileName);
		//玩家信息,"S=H"之后
		$index = 0;
		$playerStart = strpos($hexdata, "533d48", $fileEnd)+6;//
		$playerTotalEnd = strpos($hexdata, "3a3b", $playerStart)+2;
		$playerString = substr($hexdata, $playerStart, $playerTotalEnd - $playerStart);
		//需要扫描两种标准，第一种玩家，第二种电脑
		$playerStart = 0;
		//$players = preg_split("/313a|2c3a|293a/", $playerString);
		$players = preg_split("/2c/", $playerString);
		$ccount = count($players);
		$step = 11;
		for($pindex=0;$pindex < $ccount;$pindex+=$step)
		{
			$nameclip = $players[$pindex+0];
			$nameStart = strpos($nameclip, "3a48");
			if($nameStart !== false || $pindex == 0)//第一个一定是人
			{
				//是人
				$step = 11;
				if($pindex != 0)
				{
					$nameStart += 4;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
				}else $name = $players[0];
				if(array_search(hex2bin($players[5+$pindex]), $this->sides) !== false)
				{
						array_push($this->players['id'], hex2bin($name));
						array_push($this->players['side'], $this->prefix.hex2bin($players[5+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[5+$pindex]));
						array_push($this->players['team'], hex2bin($players[7+$pindex]));
						array_push($this->players['ishuman'], '1');
				}
			}else{
				$nameStart = strpos($players[$pindex + 0], "3a43");
				if($nameStart !== false)
				{
					$nameStart += 4;
					//是机器
					$step = 6;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
					if(array_search(hex2bin($players[2+$pindex]), $this->sides) !== false)
					{
						switch($name)
						{
							case '45':
								array_push($this->players['id'], 'ra3CE');//简单电脑
								break;
							case '4d':
								array_push($this->players['id'], 'ra3CM');//中等电脑
								break;
							case '48':
								array_push($this->players['id'], 'ra3CH');//困难电脑
								break;
							case '42':
								array_push($this->players['id'], "ra3CB");//凶残电脑
								break;
							default:
								array_push($this->players['id'], "ra3CU");//未知电脑
								break;
						}
						array_push($this->players['side'], $this->prefix.hex2bin($players[2+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[2+$pindex]));
						array_push($this->players['team'], hex2bin($players[4+$pindex]));
						array_push($this->players['ishuman'], '0');
					}
				}
			}
		}//end for
		//寻找版本号
		$verEnd = strpos($hexdata, "000000000002", $playerTotalEnd);//先找结尾，F3C748
		$verData = substr($hexdata, $playerTotalEnd, $verEnd-$playerTotalEnd);
		//var_dump($verData);
		$verStart = strrpos($verData,"00")+2;
		$verString = substr($verData, $verStart, 6);//只取3位版本
		$this->version = hex2bin($verString);//只用了2字节，所以用hex2bin
		//寻找日期
		//从版本号回退40字节
		$dateStart = $verStart-40+$playerTotalEnd;
		$dateString = substr($hexdata, $dateStart, 24);//固定长度
		//解析年
		$year = substr($dateString,0,4);
		$year = ChangeLowHigh($year);
		$year = intval($year, 16);
		//解析月
		$month = substr($dateString, 4, 2);
		$month = intval($month, 16);
		//解析日
		$day = substr($dateString, 12, 2);
		$day = intval($day, 16);
		//时
		$hour = substr($dateString, 16, 2);
		$hour = intval($hour, 16);
		//分
		$min = substr($dateString, 20, 2);
		$min = intval($min, 16);
		$min = $min<10?"0".$min:$min;
		//时间字符串
		$this->playTime = "${year}-${month}-${day} ${hour}:$min";
		//时长
		$flag = "FOOTER";
		$timeStart = strpos($hexdata, bin2hex($flag))+strlen($flag)*2;
		$timeSpan = substr($hexdata, $timeStart, 4);
		$timeSpan = ChangeLowHigh($timeSpan);
		$timeNumber = (int)(intval($timeSpan,16)/15);
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
	}
}//end of cnc3 class

class ReplayInfoZH
{
	var $mapName;
	var $mapFileName;
	var $playTime;//游戏日期
	var $gameLength;//游戏时长
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
		'sidenumber'=>array(),
		'ishuman'=>array(),
	);
	var $sides = array('-1','-2','2','3','4','5','6','7','8','9','10','11','12','13');//有效阵营
	var $version;//版本号
	var $prefix = 'zh';

	public function parseZHFile($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseZHData($file_data);
	}

	public function parseZHData($data)
	{
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		//先解析地图
		//地图名
		$mapEnd = strpos($hexdata, "3b4d43");//;MC
		$mapString = substr($hexdata, 0, $mapEnd);
		$mapStart = strrpos($mapString, "3b4d3d")+6;		//地图开头之前是;M=
		$mapStart = strrpos($mapString, "2f", $mapStart)+2;
		$mapName = substr($mapString, $mapStart);
		$this->mapName = hex2bin($mapName);
		$this->mapFileName = preg_replace("/\s/", "", $this->mapName);
		//玩家信息,"S=H"之后
		$index = 0;
		$playerStart = strpos($hexdata, "533d48", $fileEnd)+6;//
		$playerTotalEnd = strpos($hexdata, "3a3b", $playerStart)+2;
		$playerString = substr($hexdata, $playerStart, $playerTotalEnd - $playerStart);
		//需要扫描两种标准，第一种玩家，第二种电脑
		$playerStart = 0;
		//$players = preg_split("/313a|2c3a|293a/", $playerString);
		$players = preg_split("/2c/", $playerString);
		$ccount = count($players);
		for($pindex=0;$pindex < $ccount;$pindex+=$step)
		{
			$nameclip = $players[$pindex+0];
			$nameStart = strpos($nameclip, "3a48");
			if($nameStart !== false || $pindex == 0)//第一个一定是人
			{
				//是人
				$step = 8;
				if($pindex != 0)
				{
					$nameStart += 4;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
				}else $name = $players[0];
				if(array_search(hex2bin($players[5+$pindex]), $this->sides) !== false)
				{
						array_push($this->players['id'], hex2bin($name));
						array_push($this->players['side'], $this->prefix.hex2bin($players[5+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[5+$pindex]));
						array_push($this->players['team'], hex2bin($players[7+$pindex]));
						array_push($this->players['ishuman'], '1');
				}
			}else{
				$nameStart = strpos($players[$pindex + 0], "3a43");
				if($nameStart !== false)
				{
					$nameStart += 4;
					//是机器
					$step = 4;
					$name = substr($players[$pindex + 0], $nameStart, strlen($players[$pindex+0])-$nameStart);
					if(array_search(hex2bin($players[2+$pindex]), $this->sides) !== false)
					{
						switch($name)
						{
							case '45':
								array_push($this->players['id'], 'ra3CE');//简单电脑
								break;
							case '4d':
								array_push($this->players['id'], 'ra3CM');//中等电脑
								break;
							case '48':
								array_push($this->players['id'], 'ra3CH');//困难电脑
								break;
							default:
								array_push($this->players['id'], "ra3CU");//未知电脑
								break;
						}
						array_push($this->players['side'], $this->prefix.hex2bin($players[2+$pindex]));
						array_push($this->players['sidenumber'], hex2bin($players[2+$pindex]));
						$team = hex2bin($players[4+$pindex]);
						array_push($this->players['team'], substr($team, 0, 1));
						array_push($this->players['ishuman'], '0');
					}
				}
			}
		}//end for
		//寻找版本号
		$verStart = strpos($hexdata, "002e00")-2;//先找.，F3C748
		$verEnd = strpos($hexdata, "000000", $verStart)+2;//前面0是版本号的一部分
		$verData = substr($hexdata, $verStart, $verEnd-$verStart);
//		var_dump($verData);
		$this->version = hex2gbk($verData);
		//寻找日期
		//第一个00后推12个00，再次寻找到连续的两个00（英文版三个00）
		$dateStart = strpos($hexdata,"00");
		$dateStart += 26;
		$dateStart = strpos($hexdata, "0000", $dateStart)+4;
		$testchar = substr($hexdata, $dateStart, 2);
		if($testchar == "00")
			$dateStart += 2;
		$dateString = substr($hexdata, $dateStart, 24);//固定长度
		//解析年
		$year = substr($dateString,0,4);
		$year = ChangeLowHigh($year);
		$year = intval($year, 16);
		//解析月
		$month = substr($dateString, 4, 2);
		$month = intval($month, 16);
		//解析日
		$day = substr($dateString, 12, 2);
		$day = intval($day, 16);
		//时
		$hour = substr($dateString, 16, 2);
		$hour = intval($hour, 16);
		//分
		$min = substr($dateString, 20, 2);
		$min = intval($min, 16);
		$min = $min<10?"0".$min:$min;
		//时间字符串
		$this->playTime = "${year}-${month}-${day} ${hour}:$min";

		//游戏时长
		//12位到20位是开始时间
		$timeStart1 = ChangeLowHigh(substr($hexdata, 12, 4));
		$timeStart2 = ChangeLowHigh(substr($hexdata, 16, 4));
		$timeStart = intval($timeStart2.$timeStart1, 16);
		//21到29是结束时间
		$timeEnd1 = ChangeLowHigh(substr($hexdata, 20, 4));
		$timeEnd2 = ChangeLowHigh(substr($hexdata, 24, 4));
		$timeEnd = intval($timeEnd2.$timeEnd1, 16);
		$timeNumber = $timeEnd - $timeStart;
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
	}
}//end of zh class

class ReplayInfoDOWII
{
	var $mapName;
	var $playTime;
	var $gameLength;
	var $gameLengthString;//字符串表示的游戏时间
	var $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
	);
  var $version = "2.2.0";
	public function parseDOWIIFile($filename)
	{
		$fp = fopen($filename,"rb");
		$file_data=fread($fp,filesize($filename));
		$this->parseDOWIIData($file_data);
	}

	public function parseDOWIIData($data)
	{
		if(strlen($data) == 0)
			return;
		$hexdata = bin2hex($data);
		$hexdataLen = strlen($hexdata);
		//寻找日期
		$start = strpos($hexdata, "4543")+4;
		$end = strpos($hexdata, "0000", $start)+2;//只所以向后加2是因为会有00是有用的情况
		$replayTime = substr($hexdata, $start, $end-$start);
		$this->playTime = hex2gbk($replayTime);
		//寻找地图
		$start = strpos($hexdata, "7076705c")+8;//为“pvp\”
		$end = strpos($hexdata, "0000", $start)-8;//结尾是找到四个0后，再往前8个字节
		$maplen = $end-$start;
		if($maplen != ((int)($maplen/2))*2)
			$maplen += 1;
		$replayMap = substr($hexdata, $start, $maplen);
		$this->mapName = hex2bin($replayMap);
		//寻找玩家
		//DATAINFO后50个字节开始
		$index = 0;
		$searchIndex = 0;
		while(true)
		{
			$datainfo = "44415441494e46";
			$start = strpos($hexdata, $datainfo, $searchIndex);//寻找DATAINFO
			if($start === false)//没有了，退出
				break;
			$start = $start+strlen($datainfo)+50;	//地址前移自身长度与48个字节是玩家信息的开始
			$lenStart = $start - 8;
			$lenString = substr($hexdata, $lenStart, 2);
			$len = intval($lenString, 16);//玩家名字长度
			$replayPlayer = substr($hexdata, $start, $len*4);//截取玩家信息
			$replayPlayer = hex2gbk($replayPlayer);
			//截取阵营信息，从玩家结尾前进16个字节
			//阵营信息长度
			$lenStart = $start + $len*4 + 16;
			$lenString = substr($hexdata, $lenStart, 2);
			$len = intval($lenString, 16);
			//阵营开头，前进8个字节
			$sideStart = $lenStart+8;
			$playerSide = substr($hexdata, $sideStart, $len*2);
			$playerSide = hex2bin($playerSide);
			if(strlen($replayPlayer)>0)
			{
				array_push($this->players['id'], $replayPlayer);
				array_push($this->players['side'], $playerSide);
				array_push($this->players['team'], $index);
			}
			$searchIndex = $end;
			$index++;
			if($index>16)
				break;
			$searchIndex = $start+1;
		}
		//重新划定队伍，上半区为0，下半区为1
		$separator = $index/2;
		$count = count($this->players['id']);
		for($ti=0;$ti<$count;$ti++)
			if($this->players['team'][$ti]<$separator)
				$this->players['team'][$ti] = 0;
			else $this->players['team'][$ti] = 1;
		//时间，文件结尾00 00 00 20后第一个字段，长度8字节
		//读取文件最后的部分
		$timeFlag = "00000020";
		$timeStart = strrpos($hexdata, $timeFlag);
		$resist = 0;
		while($timeStart != ((int)($timeStart/2))*2 && $resist<10)
		{
			$timeStart = strrpos($hexdata, $timeFlag, $timeStart-$hexdataLen-1);
			$resist++;
		}
		$timeStart += strlen($timeFlag);
		//$timeStart = strlen($hexdata)-24;
		$time1 = ChangeLowHigh(substr($hexdata, $timeStart, 4));
		$time2 = ChangeLowHigh(substr($hexdata, $timeStart+4, 4));
		//对调高低位
		$newtime = $time2.$time1;
//		var_dump($newtime);
		$timeNumber = (int)(intval($newtime,16)/8);
		$this->gameLength = $timeNumber;
		$timeMin = (int)($timeNumber/60);
		$timeSec = $timeNumber - $timeMin*60;
		$timeHour = (int)($timeMin/60);
		if($timeHour != 0)
			$timeMin = $timeMin - $timeHour*60;
		//$this->gameLengthString = "用时：";
		if($timeHour>0)
			$this->gameLengthString .= $timeHour."时";
		if($timeMin>0)
			$this->gameLengthString .= $timeMin."分";
		if($timeSec>0)
			$this->gameLengthString .= $timeSec."秒";
	}
}//end of dowii class

function getExt($filename) {
	return addslashes(trim(substr(strrchr($filename, '.'), 1, 10)));
}
//以下为保存录像信息
$logStr = '';
function writeReplayInfo($attachmentid,$table_att){
	global $_G;
	
	$exits_attach =DB::fetch_first("select attachmentid from replayinfo where attachmentid=".$attachmentid);
	if (empty($exits_attach))
	{
		//建立recobject
		
		$attachinfo = DB::fetch_first("SELECT * FROM ".$table_att." a WHERE aid=".$attachmentid);
		$logStr.= "SELECT * FROM ".$table_att." a WHERE aid=".$attachmentid;
		$logStr.= "==";
		$filename = DISCUZ_ROOT.'./data/attachment/forum/'.$attachinfo['attachment'];
		$logStr.= $filename;
		$logStr.="++";
		file_put_contents('./log.txt',$logStr,FILE_APPEND);
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
//	die();
}
//<--summer
//<--summer
/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 16413 $
|| ####################################################################
\*======================================================================*/
?>