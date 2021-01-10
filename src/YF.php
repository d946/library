<?php

namespace D946;

class YF
{
	
	//------------------------------------------------------------------------------
	function getDay($ticker, $year, $mon, $day, &$items)
	{
		$items = [];
		$year = sprintf("%02d", $year);
		$mon = sprintf("%02d", $mon);
		$day = sprintf("%02d", $day);
		//$tm = strtotime('20'.$year.'-'.$mon.'-'.$day);
		//$date->add(new DateInterval('P10D'));
		$period1 = new DateTime('20' . $year . '-' . $mon . '-' . $day);
		$period2 = new DateTime('20' . $year . '-' . $mon . '-' . $day);
		$period1->add(new DateInterval('P1D'));
		$period2->add(new DateInterval('P2D'));

		$period1 = $period1->getTimestamp();
		$period2 = $period2->getTimestamp();

		$url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . $ticker . '?region=US&lang=en-US&includePrePost=false&interval=1m&period1=' . $period1 . '&' . 'period2=' . $period2;
		$dir = 'yf/' . $ticker[0] . '/' . $ticker . '/' . $year . '/' . $mon;
		$fn = $dir . '/' . $ticker . '_20' . $year . '-' . $mon . '-' . $day . '.json';
		if (file_exists($fn)) {
			$file = @file_get_contents($fn);
			if ($file === false) {
				return 'Not read file ' . $fn;
			}
			$json = json_decode($file, true);
			if (json_last_error() != JSON_ERROR_NONE) {
				return 'Error parse json from file' . json_last_error_msg;
			}
		} else {
			global $reqcnt;
			$reqcnt++;
			$file = @file_get_contents($url);
			if ($file === false) {
				return 'Not get url ' . $url;
			}
			$json = json_decode($file, true);
			if (json_last_error() != JSON_ERROR_NONE) {
				return 'Error parse json from url ' . json_last_error_msg;
			}
			$json['rqtime'] = time();
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
		}
		$data = $json;
		if (!isset($data['chart']['result'][0]['timestamp'])) {
			print_r($ticker);
		}
		foreach ($data['chart']['result'][0]['timestamp'] as $key => $item) {
			$tmp = ['dt' => $item,
				'dtf' => date("Y-m-d H:i:s", $item),
				'O' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['open'][$key], 2, ".", ""),
				'H' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['high'][$key], 2, ".", ""),
				'L' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['low'][$key], 2, ".", ""),
				'C' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['close'][$key], 2, ".", ""),
				'V' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['volume'][$key], 2, ".", ""),
				'ATR' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['high'][$key] - $data['chart']['result'][0]['indicators']['quote'][0]['low'][$key], 2, ".", "")
			];
			$items[] = $tmp;
		}
		return '';
	}	
	
	//------------------------------------------------------------------------------
	function getMonth($ticker, $year, $mon, &$items)
	{
		$items = [];
		global $first,$log;
		$year = sprintf("%02d", $year);
		$mon = sprintf("%02d", $mon);
		$tm = strtotime('20' . $year . '-' . $mon . '-23');
		$period1str = date("Y-m-01", $tm);
		$period2 = date("Y-m-t", $tm);

		$period1 = strtotime($period1str);
		$period2 = strtotime($period2);
		if (isset($first[$ticker])){
		  $fr = strtotime(date("Y-m-01", $first[$ticker]));
		}else{
		  $fr=$period1;
		}
		if ($fr>$period1){
		  $log[$ticker] = ($log[$ticker]??'')."S";
		  return [];
		}
		$url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . $ticker . '?region=US&lang=en-US&includePrePost=false&interval=1d&period1=' . $period1 . '&' . 'period2=' . $period2;
		$dir = 'yf/' . $ticker[0] . '/' . $ticker . '/' . $year;
		$shortname = $ticker . '_20' . $year . '-' . $mon;
		$fn = $dir . '/' . $shortname . '.zip';

		if (file_exists($fn)) {
			$log[$ticker] = ($log[$ticker]??'')."V";
			$file = zfile_get_contents($fn, $comment);
			if ($file === false) {
				return 'Not read file ' . $fn;
			}
			$json = json_decode($file, true);
			if (json_last_error() != JSON_ERROR_NONE) {
				return 'Error parse json from file' . json_last_error_msg;
			}
			$now = time();
			$ldr = date("Y-m-01", $comment);
			$ldn = date("Y-m-01", $now);
			if ($period1str==$ldn){
			  //echo "$period1str $ldr $ldn\r\n";
			  if ($ldr == $ldn){
				 $ldr = date("Y-m-d", $comment);
				 $ldn = date("Y-m-d", $now);
				 //echo "# $ldr $ldn\r\n";
				 if ($ldr != $ldn){
					unset($json);
				 }
			  }
			}
		}
		if (!isset($json)) {
			$log[$ticker] = ($log[$ticker]??'')."G";
			global $reqcnt;
			$reqcnt++;
			$file = @file_get_contents($url);
			if ($file === false) {
				return 'Not get url ' . $url;
			}
			echo "G";
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
			if (1) {//if (date('ymj', $dstart) != date('ymj', time())) {
				$zip = new ZipArchive();
				if ($zip->open($fn, ZipArchive::CREATE) !== TRUE) {
					exit("РќРµРІРѕР·РјРѕР¶РЅРѕ РѕС‚РєСЂС‹С‚СЊ <$fn>\n");
				}
				$zip->addFromString($shortname . ".txt", $file);
				$zip->setArchiveComment(time());
				$zip->close();
			}

			$json = json_decode($file, true);
			if (json_last_error() != JSON_ERROR_NONE) {
				return 'Error parse json from url ' . json_last_error_msg;
			}
		}
		$data = $json;
		if (!isset($data['chart']['result'][0]['timestamp'])) {
			print_r($ticker);
		}
		if (!isset($data['chart']['result'][0]['timestamp'])){
		  var_dump($ticker);
		}
		foreach ($data['chart']['result'][0]['timestamp'] as $key => $item) {
			$tmp = ['dt' => $item,
				'dtf' => date("Y-m-d H:i:s", $item),
				'O' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['open'][$key], 2, ".", ""),
				'H' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['high'][$key], 2, ".", ""),
				'L' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['low'][$key], 2, ".", ""),
				'C' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['close'][$key], 2, ".", ""),
				'V' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['volume'][$key], 2, ".", ""),
				'ATR' => number_format($data['chart']['result'][0]['indicators']['quote'][0]['high'][$key] - $data['chart']['result'][0]['indicators']['quote'][0]['low'][$key], 2, ".", "")
			];
			$items[] = $tmp;
		}
		global $first;
		if (!isset($first[$ticker])){
		   $first[$ticker]=$data['chart']['result'][0]['meta']['firstTradeDate'];
		}
		return '';
	}	
	
}