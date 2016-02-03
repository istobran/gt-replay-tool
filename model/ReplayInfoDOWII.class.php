<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class ReplayInfoDOWII
{
	public $mapName;
	public $playTime;
	public $gameLength;
	public $gameLengthString;//字符串表示的游戏时间
	public $players = array(
		'id'=>array(),
		'side'=>array(),
		'team'=>array(),
	);
  public $version = "2.2.0";
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
 ?>
