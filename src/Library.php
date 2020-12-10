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

    //---------------------------------------------------------------------------------------------------------
    public function getFinamTick($dir, $tickerName, $start, $market = "BATS", $end = null)
    {
        if (!isset($ids)) {
            if (file_exists($dir . '/files/finamIds.json')) {
                $ids = json_decode(file_get_contents($dir . '/files/finamIds.json'), true);
                //$ids = $ids['BATS'];
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
        $url = $base . $ticker . "_" . date('ymj', $dstart) . "_" . date('ymj', $dend) . ".txt?" . implode('&', $fields);
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

            // DOCS: https://www.php.net/manual/en/function.stream-context-create.php
            $context = stream_context_create($opts);
            $file = file_get_contents($url, false, $context);
            if ($file !== false) {
                if (!is_dir(dirname($filename))) {
                    mkdir(dirname($filename), 0755, true);
                }
                if (date('ymj', $dstart) != date('ymj', time())) {
                    file_put_contents($filename, time() . gzcompress($file, 9));
                    //file_put_contents($filename, time() . $file);
                    //file_put_contents($filenamejson,json_encode($data));
                }
            }
            $data = $this->tickCSVtoData($file, $ticker);
        }
        return $data;
    }

}