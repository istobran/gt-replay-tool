<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class ReplayInfoZH
{
	public $mapName;
	public $mapFileName;
	public $playTime;//游戏日期
	public $gameLength;//游戏时长
	public $gameLengthString;//字符串表示的游戏时间
	public $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
		'sidenumber'=>array(),
		'ishuman'=>array(),
	);
	public $sides = array('-1','-2','2','3','4','5','6','7','8','9','10','11','12','13');//有效阵营
	public $version;//版本号
	public $prefix = 'zh';

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
 ?>
