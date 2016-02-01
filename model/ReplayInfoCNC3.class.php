<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

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
 ?>
