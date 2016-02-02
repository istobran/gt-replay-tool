<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

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

function getExt($filename) {
	return addslashes(trim(substr(strrchr($filename, '.'), 1, 10)));
}
 ?>
