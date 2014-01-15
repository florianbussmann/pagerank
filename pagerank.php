<?php
function strToNum($str, $check, $magic){
	$int_32_unit = 4294967296; // 2^32
	$length = strlen($str);
	
	for($i = 0; $i < $length; $i++) {
		$check *= $magic;
		
		if($check >= $int_32_unit) {
			$check = ($check - $int_32_unit * (int) ($check / $int_32_unit));
			$check = ($check < -2147483648) ? ($check + $int_32_unit) : $check;
		}
		
		$check += ord($str{$i});
	}
	return $check;
}

function getPageHash($str){
	$check1 = strToNum($str, 0x1505, 0x21);
	$check2 = strToNum($str, 0, 0x1003F);
	
	$check1 >>= 2;
	$check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
	$check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
	$check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);
	$t1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) <<2 ) | ($check2 & 0xF0F );
	$t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000 );
	
	$hashNum = ($t1 | $t2);
	$check_byte = 0;
	$flag = 0;
	$hash_str = sprintf("%u", $hashNum);
	$length = strlen($hash_str);
	
	for($i = $length - 1; $i >= 0; $i--) {
		$re = $hash_str{$i};
		
		if(1 === ($flag % 2)) {
			$re += $re;
			$re = (int)($re / 10) + ($re % 10);
		}
		
		$check_byte += $re;
		$flag ++;
	}
	
	$check_byte %= 10;
	
	if(0 !== $check_byte) {
		$check_byte = 10 - $check_byte;
		
		if(1 === ($flag % 2) ) {
			if(1 === ($check_byte % 2))
				$check_byte += 9;
			
			$check_byte >>= 1;
		}
	}
	
	return "7".$check_byte.$hash_str;
}

function getPageRank($url, $server = 'toolbarqueries.google.com') {
	$checksum = getPageHash($url);

	$request_url = sprintf(
		'http://%s/tbr?client=navclient-auto&ch=%s&ie=UTF-8&oe=UTF-8&features=Rank&q=info:%s',
		$server,
		$checksum,
		urlencode($url)
	);
	
	if(($c = @file_get_contents($request_url)) === false)
		return false;
	else if(empty($c))
		return -1;
	else
		return intval(substr($c, strrpos($c, ':') + 1));
}
?>