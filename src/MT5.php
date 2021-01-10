<?php

namespace D946;

class MT5
{

	//------------------------------------------------------------------------------
	function getDays($server,$ticker, $dt, &$days)
	{
		//localhost:8080
		$dstart = strtotime($dt);
		$year = date('Y', $dstart);
		$mon = date('m', $dstart);
		$day = date('j', $dstart);
		$days = [];
		$data = json_decode(file_get_contents('http://'.$server.'/data?t='.$ticker.'&y='.$year.'&m='.$mon.'&d='.$day.'&c=180'),true);

		foreach($data['data'] as $item){
		  $days[]=[
			 'dt'=>$item[1],
			 'dtf'=>date("Y-m-d H:i:s", $item[1]),
			 'O'=>number_format($item[2], 2, ".", ""),
			 'H'=>number_format($item[3], 2, ".", ""),
			 'L'=>number_format($item[4], 2, ".", ""),
			 'C'=>number_format($item[5], 2, ".", ""),
			 'V'=>'',
			 'ATR'=>number_format($item[3]-$item[4], 2, ".", "")];
		}
		return $days;
	}	
	
}