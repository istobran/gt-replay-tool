<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class ReplayInfoCOH
{
	public $mapName;
	public $playTime;
	public $gameLength;
	public $gameLengthString;//字符串表示的游戏时间
	public $players = array(
		'id'=>array(),
		'side'=>array(),
	);
  public $version;

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
 ?>
