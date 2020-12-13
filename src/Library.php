<?php


namespace D946;


class Library
{

    //---------------------------------------------------------------------------------------------------------
    public function calcATR(array $atrs)
    {
        sort($atrs);
        $atrs = array_slice($atrs, 2, 14);
        $sum = 0;
        $cnt = 0;
        foreach ($atrs as $el) {
            $sum += $el;
            $cnt++;
        }
        return (($cnt > 0) ? $sum / $cnt : 0);
    }

    //---------------------------------------------------------------------------------------------------------
    public function find_First_Min_Max(array $dtL, &$afPrice, &$minA, &$maxA, &$price, $keyPrice = '4', $cntA = 100000)
    {
        $i = 0;
        $minA = 100000;
        $maxA = 0;
        unset($fPrice);
        foreach ($dtL as $item) {
            $item[$keyPrice] = str_replace(',', '.', $item[$keyPrice]);
            $price = floatval($item[$keyPrice]);
            if ($price < $minA) {
                $minA = $price;
            }
            if ($price > $maxA) {
                $maxA = $price;
            }
            if (!isset($fPrice)) {
                $fPrice = $price;
            }
            $i++;
            if ($i > $cntA) {
                break;
            }
        }
        $afPrice = $fPrice;
        return [$fPrice, $minA, $maxA, $price];
    }

    //---------------------------------------------------------------------------------------------------------
    public function getDayYF($dir, $tickerName, $start)
    {
        $dstart = strtotime($start);
        //$tm = (time() / 86400) * 86400;
        $url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . $tickerName . '?symbol=' . $tickerName . '&period1=' . $dstart . '&period2=' . ($dstart + 86400) . '&interval=1d';
        $filename = $dir . '/cache/yf/' . $tickerName . '/' . date('ymd', $dstart) . '_day.txt';//../../repo/saved/
        if (file_exists($filename)) {
            $file = file_get_contents($filename);
            //$ftime = substr($file, 0, 10);
            //$ftime = strtotime($ftime_text);
            $file = substr($file, 10);
            $answer = $file;
            //if (((int)($ftime + 86400)) < time()) {
            //unset($file);
            //echo 'unset '.$tickerName;
            //} else {
            //echo ($ftime + 86400).' | '.time().' # ';
            //}
        }
        if (!isset($file)) {
            $file = file_get_contents($url);
            $file = json_decode($file, true);
            $answer = $file['chart']['result'][0]['indicators']['quote'][0]['close'][0];
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0755, true);
            }
            if (date('ymj', $dstart) != date('ymj', time())) {
                file_put_contents($filename, time() . $answer);
            }
            //  file_put_contents($filename."_", json_encode($file));
        }
        //var_dump($file['chart']['result'][0]['indicators']['quote'][0]['close'][0]);
        return ['close' => $answer];
    }

    //---------------------------------------------------------------------------------------------------------
    private function tickCSVtoData($file, $ticker)
    {
        $csv = explode("\r\n", $file);
        unset($csv[0]);
        $data = [];
        foreach ($csv as $line) {
            $line = trim($line);
            if ($line == '') {
                continue;
            }
            $data[] = explode(',', "$ticker,0," . $line);
        }
        return $data;
    }

    public function getTickDB($dir, $tickerName, $start, $market = "BATS", $end = null)
    {
        $dsn = "mysql:host=localhost;dbname=test;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, 'root', '1', $opt);

        $data = [];
        $result = $pdo->query('select * from dde where TICKER="$tickerName" order by id')->fetchAll();
        if (count($result) > 0) {
            foreach ($result as $item) {
                $data[] = [$item['TICKER'], 0, "20201211", str_replace(':', '', $item['TLABEL']), $item['PRICE'], $item['CNT'] * 10, $item['UID'], ($item['OPER'] == "Продажа" ? "S" : ($item['OPER'] == "Купля" ? "B" : ""))];
            }
        }
        return $data;
    }

    //---------------------------------------------------------------------------------------------------------
    public function getFinamTick($dir, $tickerName, $start, $market = "BATS", $end = null)
    {
        if (!isset($ids)) {
            if (file_exists($dir . '/files/finamIds.json')) {
                $ids = json_decode(file_get_contents($dir . '/files/finamIds.json'), true);
            } else {
                $ids['BATS'] = [
                    'MSFT' => ['name' => 'US1.MSFT', 'market' => 25, 'em' => 19068],
                    'MA' => ['name' => 'US1.MA', 'market' => 25, 'em' => 489007],
                    'GOOG' => ['name' => 'US2.GOOG', 'market' => 25, 'em' => 20590],
                    'KIM' => ['name' => 'US2.KIM', 'market' => 25, 'em' => 874515],
                    'VZ' => ['name' => 'US1.VZ', 'market' => 25, 'em' => 18137],
                    'WMT' => ['name' => 'US1.VZ', 'market' => 25, 'em' => 18146],
                    'SPY' => ['name' => 'ETF.IVV', 'market' => 28, 'em' => 19117],
                    'V' => ['name' => 'SPBEX.V', 'market' => 517, 'em' => 419855],
                    'AFKS.MM' => ['name' => 'AFKS.MM', 'market' => 1, 'em' => 19715],

                ];
            }
        }
        $ticker = $ids[$market][$tickerName]['name'];

        if (!isset($end)) {
            $end = $start;
        }
        $base = 'http://export.finam.ru/';

        $dstart = strtotime($start);

        $dend = strtotime($end);

        $params = [
            'market' => $ids[$market][$tickerName]['market'],
            'em' => $ids[$market][$tickerName]['em'],
            'code' => 'US1.MSFT',
            'apply' => 0,
            'df' => date('j', $dstart),
            'mf' => date('m', $dstart) - 1,
            'yf' => date('Y', $dstart),
            'from' => date('d.m.Y', $dstart),
            'dt' => date('j', $dend),
            'mt' => date('m', $dend) - 1,
            'yt' => date('Y', $dend),
            'to' => date('d.m.Y', $dend),
            'p' => 1,
            'f' => $ticker . '_' . date('ymj', $dstart) . '_' . date('ymj', $dend),
            'e' => '.txt',
            'cn' => $ticker,
            'dtf' => 1,
            'tmf' => 1,
            'MSOR' => 1,
            'mstime' => 'on',
            'mstimever' => 1,
            'sep' => 1,
            'sep2' => 1,
            'datf' => 12,
            'at' => 1
        ];
        foreach ($params as $k => $v) {
            $fields[] = $k . '=' . $v;
        }
        $url = $base . "export9.out?" . implode('&', $fields);
        //	export9.out
        $filename = $dir . '/cache/ft/' . $tickerName . '/' . date('ymd', $dstart) . '.txt';//../../repo/saved/
        $filenamejson = $dir . '/cache/ft/' . $tickerName . '/' . date('ymd', $dstart) . '.json';//../../repo/saved/
        if (file_exists($filename)) {
            $file = file_get_contents($filename);
            $ftime = strtotime(substr($file, 0, 10));
            $file = substr($file, 10);
            $file = gzuncompress($file);
            if (($ftime + 86400) > time()) {
                unset($file);
            } else {
                $data = $this->tickCSVtoData($file, $ticker);
                /*
                if (file_exists($filenamejson)){
                    $data = json_decode(file_get_contents($filenamejson),true);
                }else{
                  $data = tickCSVtoData($file,$ticker);
                  file_put_contents($filenamejson,json_encode($data));
                }*/
            }
        }
        if (!isset($file)) {
            $opts = [
                "http" => [
                    'timeout' => 120,
                    "method" => "GET",
                    "header" =>
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n" .
                        "Accept-Encoding: gzip, deflate\r\n" .
                        "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6\r\n" .
                        "Cache-Control: no-cache\r\n" .
                        "Connection: keep-alive\r\n" .
                        "Host: export.finam.ru\r\n" .
                        "Pragma: no-cache\r\n" .
                        "Upgrade-Insecure-Requests: 1\r\n" .
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n"
                ]
            ];

            $context = stream_context_create($opts);
            $file = file_get_contents($url, false, $context);
            if ($file !== false) {
                if (!is_dir(dirname($filename))) {
                    mkdir(dirname($filename), 0755, true);
                }
                if (date('ymj', $dstart) != date('ymj', time())) {
                    file_put_contents($filename, time() . gzcompress($file, 9));
                }
            }
            $data = $this->tickCSVtoData($file, $ticker);
        }
        return $data;
    }

    //---------------------------------------------------------------------------------------------------------
    public function findPosByStep(array $dtL, $step, $keyPrice = '4')
    {
        $i = 0;
        $cntA = 0;
        unset($newprice);
        foreach ($dtL as $item) {
            $item[$keyPrice] = str_replace(',', '.', $item[$keyPrice]);
            $price = floatval($item[$keyPrice]);
            if (!isset($newprice)) {
                $newprice = $price;
            } else if ($price != $newprice) {
                $newprice = $price;
                $i++;
            }
            $cntA++;
            if ($i > $step) {
                break;
            }
        }
        return $cntA;
    }

    //---------------------------------------------------------------------------------------------------------
    public function calc_Vstep_and_BS($atr, $ticker, &$vstep, &$brickSize, $cnt = 12)
    {
        if (file_exists('bs.json')) {
            $custom = json_decode(file_get_contents('bs.json'), true);
        } else {
            $custom = [];
        }
        if (isset($custom[$ticker])) {
            $brickSize = $custom[$ticker][1];
            $vstep = $custom[$ticker][0];
            return;
        } else {
            $vstep = (int)(($atr * 100.0) / 50.0);
            if ($vstep < 1) {
                $vstep = 1;
            }
            $brickSize = $vstep * 0.05;
            $brickSize = number_format($brickSize, 2, '.', '');
            $custom[$ticker][1] = $brickSize;
            $custom[$ticker][0] = $vstep;
            file_put_contents('bs.json', json_encode($custom));
        }
        /*
        $custom=[
          'SBER'=>[ 0.25, 10],
          'GAZP'=>[ 0.25, 10],
          'YNDX'=>[ 20.0, 250],
          'ROSN'=>[ 0.5, 15],
          'MOEX'=>[ 0.25, 10],
          'TATN'=>[ 1.0, 50],
          'AFKS'=>[ 0.05, 2],
          'WELL'=>[ 0.05, 3],
          'GMKN'=>[ 20.0, 500],
          'NVTK'=>[ 2.0, 50],
        ];
        $info=[
          [0     ,   0.5 , 0.01, 2],
          [0.50  ,   1.0 , 0.02, 5],
          [1.0   ,   2.5 , 0.05, 10],
          [2.5   ,   5.0 , 0.25, 10],
          [5.0   ,  10.0 , 0.50, 10],
          [10.0   , 50.0 , 1.00, 10],
          [50.0  , 100.0 , 1.00, 10],
          [100.0 , 150.0 , 4.00, 200],
          [150.0 , 2000.0 , 10.00, 200],
        ];
        if (isset($custom[$ticker])){
            $brickSize = $custom[$ticker][0];
            $vstep = $custom[$ticker][1];
            return;
        }
        foreach($info as $item){
            if (($atr>$item[0])&&($atr<=$item[1])){
                $bs = $item[2];
                $vstep = $item[3];
            }
        }
        if (!isset($bs)){
            $bs = 0.05;
            $vstep = 1;
        }
        $brickSize = $bs;*/
        /*
        $step = $atr / $cnt;
        if ($step < 0.05) {
            $brickSize = 0.01;
            $vstep = 1;
        } else if ($step < 0.10) {
            $brickSize = 0.05;
            $vstep = 1;
        } else if ($step < 0.20)
            $brickSize = 0.10;
            $vstep = 5;
        else if ($step < 0.30)
            $brickSize = 0.25;
            $vstep = 5;
        else if ($step < 0.50)
            $brickSize = 0.25;
            $vstep = 5;
        else if ($step < 0.75)
            $brickSize = 0.50;
            $vstep = 5;
        else {
            $brickSize = 5.0;
            $vstep = 200;
        }*/
    }

    //---------------------------------------------------------------------------------------------------------
    function getSimpleVolumes(array $dtL, $keyPrice = '4', $keyVolume = '5', $keyOper = '7', $cntA = 100000)
    {
        $dtrd = [];
        $i = 0;
        foreach ($dtL as $item) {
            $item[$keyPrice] = str_replace(',', '.', $item[$keyPrice]);
            $price = floatval($item[$keyPrice]);
            $kprice = number_format($price + 0.0005, 2, '.', '');
            if (isset($dtrd[$kprice][$item[$keyOper]])) {
                $dtrd[$kprice][$item[$keyOper]] += $item[$keyVolume];
            } else {
                $dtrd[$kprice][$item[$keyOper]] = $item[$keyVolume];
                $dtrd[$kprice][$item[$keyOper]] += 0;
            }
            $i++;
            if ($i > $cntA) {
                break;
            }

        }
        return $dtrd;
    }

    //---------------------------------------------------------------------------------------------------------
    function getGroupVolumes(array $dtrd, $vstep, $fPrice, $minA, $maxA)
    {
        krsort($dtrd);
        $vs = 0.01 * ($vstep);
        $dtrdd = [];
        $cntUp = (int)((($maxA - $minA) + $vs * 3) / $vs);
        for ($dd = $cntUp; $dd > -$cntUp; $dd--) {
            $basePriceInt = (int)((($fPrice * 100.0) + 0.001) + ($dd * $vstep));
            $basePrice = number_format($basePriceInt / 100.0 + 0.001, 2, '.', '');

            $ssum = 0;
            $ssumB = 0;
            $ssumS = 0;
            if (isset($dtrd[$basePrice])) {
                if (isset($dtrd[$basePrice]['B'])) {
                    $dtrdd[$basePrice]['detail'][$basePrice]['B'] = $dtrd[$basePrice]['B'];
                    $ssum += $dtrd[$basePrice]['B'];
                    $ssumB += $dtrd[$basePrice]['B'];
                }

                if (isset($dtrd[$basePrice]['S'])) {
                    $dtrdd[$basePrice]['detail'][$basePrice]['S'] = $dtrd[$basePrice]['S'];
                    $ssum += $dtrd[$basePrice]['S'];
                    $ssumS += $dtrd[$basePrice]['S'];
                }
            }
            for ($i = 1; $i < $vstep; $i++) {
                $basePriceNInt = (int)($basePriceInt - $i);
                $basePriceN = number_format($basePriceNInt / 100.0 + 0.001, 2, '.', '');
                if (isset($dtrd[$basePriceN])) {
                    if (isset($dtrd[$basePriceN]['B'])) {
                        $dtrdd[$basePrice]['detail'][$basePriceN]['B'] = $dtrd[$basePriceN]['B'];
                        $ssum += $dtrd[$basePriceN]['B'];
                        $ssumB += $dtrd[$basePriceN]['B'];
                    }
                    if (isset($dtrd[$basePriceN]['S'])) {
                        $dtrdd[$basePrice]['detail'][$basePriceN]['S'] = $dtrd[$basePriceN]['S'];
                        $ssum += $dtrd[$basePriceN]['S'];
                        $ssumS += $dtrd[$basePriceN]['S'];
                    }
                }
            }
            if ($ssum > 0) {
                $dtrdd[$basePrice]['v'] = $ssum;
                $dtrdd[$basePrice]['b'] = $ssumB;
                $dtrdd[$basePrice]['s'] = $ssumS;
            }
        }
        krsort($dtrdd);
        return $dtrdd;
    }

    //---------------------------------------------------------------------------------------------------------
    function getGaps(array $dtL, $vstep, $keyPrice = '4', $keyDate = '3', $keyVolume = '5', $keyOper = '7', $cntA = 100000)
    {
        $i = 0;
        $gap = [];
        foreach ($dtL as $item) {
            $item[$keyPrice] = str_replace(',', '.', $item[$keyPrice]);
            $price = floatval($item[$keyPrice]);
            if (isset($sv)) {
                if (abs($price - $sv) >= ($vstep * 0.01 * 3)) {
                    if (substr($item[$keyDate], 0, 4) != '1000') {
                        $gap[] = [$item[$keyDate], $sv, $price, number_format($price - $sv, 2), $item[$keyOper], $item[$keyVolume]];
                    }
                }
                $sv = $price;
            } else {
                $sv = $price;
            }
            $i++;
            if ($i > $cntA) {
                break;
            }
        }
        return $gap;
    }

    //---------------------------------------------------------------------------------------------------------
    function getBricks(array $dtL, $brickSize, $keyPrice = '4', $keyDate = '3', $cntA = 100000)
    {
        $dtb = [];
        unset($oldValue);
        $i = 0;
        foreach ($dtL as $item) {
            $item[$keyPrice] = str_replace(',', '.', $item[$keyPrice]);
            $price = floatval($item[$keyPrice]);
            if (!isset($oldValue)) {
                $oldValue = $price;
                $min = $oldValue;
                $max = $oldValue;
            } else {
                $difference = $price - $oldValue;
                if (($difference > 0) && ($difference > $brickSize)) {

                    for ($j = 0; $j < (int)($difference / $brickSize); $j++) {
                        $newValue = $oldValue + $brickSize;
                        if ($j == 0) {
                            $dtb[] = [$oldValue, $newValue, $min < $oldValue ? $min : $oldValue, $max > $newValue ? $max : $newValue, $item[$keyDate]];
                        } else {
                            $dtb[] = [$oldValue, $newValue, $oldValue, $newValue, $item[$keyDate]];
                        }
                        $oldValue = $newValue;
                    }
                    $min = $oldValue;
                    $max = $oldValue;

                } else if (($difference < 0) && (abs($difference) > $brickSize)) {
                    for ($j = 0; $j < (int)(abs($difference) / $brickSize); $j++) {
                        $newValue = $oldValue - $brickSize;
                        if ($j == 0) {
                            $dtb[] = [$oldValue, $newValue, $min < $newValue ? $min : $newValue, $max > $oldValue ? $max : $oldValue, $item[$keyDate]];
                        } else {
                            $dtb[] = [$oldValue, $newValue, $newValue, $oldValue, $item[$keyDate]];
                        }
                        $oldValue = $newValue;
                    }
                    $min = $oldValue;
                    $max = $oldValue;

                } else {
                    if ($price > $max) {
                        $max = $price;
                    }
                    if ($price < $min) {
                        $min = $price;
                    }

                }
            }
            $i++;
            if ($i > $cntA) {
                break;
            }
        }
        return $dtb;
    }

    function mode_fTick($dir, $ticker, $dt, $market, $step, $fTick = 'getFinamTick')
    {

        $dstart = strtotime($dt);

        $starttime = microtime(true);

        if ($fTick == 'getFinamTick') {
            $dataTick = $this->getFinamTick($dir, $ticker, $dt, $market);// получение тиковых данных
        }else{
            $dataTick = $this->getTickDB($dir, $ticker, $dt, $market);// получение тиковых данных
        }

        $endtime = microtime(true);
        $timediff = $endtime - $starttime;

        // получение дневных данных
        $filename = $dir . '/cache/ld/' . $ticker . '/' . date('ymd', $dstart) . '_day.txt';//../../repo/saved/
        if (file_exists($filename)) {
            $file = file_get_contents($filename);
            $dataDay = json_decode(file_get_contents($filename), true);
        }
        if (!isset($file)) {
            $dataDay = json_decode(file_get_contents("http://localhost:8080/data?t=$ticker" . ($market == "MOEX" ? ".MM" : "") . "&y=" . date('Y', $dstart) . "&m=" . date('m', $dstart) . "&d=" . date('j', $dstart) . "&c=60"), true)['data'];
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0755, true);
            }
            file_put_contents($filename, json_encode($dataDay));
        }
        // получение массива atrs
        foreach (array_slice($dataDay, -19, 18) as $el) {
            $atrs[] = $el[3] - $el[4];
        }

        $atr = $this->calcATR($atrs);// расчет atr

        $prevPrice = array_slice($dataDay, -2);

        $cntTickInStep = $this->findPosByStep($dataTick, $step, '4');// получение кол-ва первичных данных

        $this->find_First_Min_Max($dataTick, $startPrice, $minInRange, $maxInRange, $price, '4', $cntTickInStep);// получение мин и мах

        $this->calc_Vstep_and_BS($atr, $ticker, $vstep, $brickSize);

        $sVolume = $this->getSimpleVolumes($dataTick, '4', '5', '7', $cntTickInStep);// получение объемов с минимальным шагом

        $tmp['info'] = [];
        $tmp['d'] = $dataDay;
        $tmp['vdd'] = $this->getGroupVolumes($sVolume, $vstep, $startPrice, $minInRange, $maxInRange); // получение объемов с заданным шагом
        $tmp['b'] = $this->getBricks($dataTick, $brickSize, '4', '3', $cntTickInStep); // расчет bricks
        $tmp['gap'] = $this->getGaps($dataTick, $vstep, '4', '3', '5', '7', $cntTickInStep); // расчет гепов

        $endtime = microtime(true);
        $timediffAll = $endtime - $starttime;
        $tmp['info'] = [
            'StartPrice' => $startPrice,
            'ClosePrice' => $price,
            'ATR' => number_format($atr + 0.005, 2),
            'PrevClose' => $prevPrice[0][5],
            'bs' => $brickSize,
            'vs' => $vstep,
            'isLast' => ($cntTickInStep == count($dataTick)),
            'getTime' => $timediff,
            'getTimeAll' => $timediffAll,
            'label' => date('h:i:s d/m/y'),
            'ticker' => $ticker,
            'min' => $minInRange,
            'max' => $maxInRange];
        return $tmp;
    }

    function secondsToTime($s)
    {
        $h = floor($s / 3600);
        $s -= $h * 3600;
        $m = floor($s / 60);
        $s -= $m * 60;
        return $h . ':' . sprintf('%02d', $m) . ':' . sprintf('%02d', $s);
    }

}