<?php

namespace D946;

//!-----------------------------------------------------пїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅ------
class Graph
{
    private $im;
    public $header_height = 20;
    public $height;
    public $params;
    public $width;
    public $inparams = [
        'width_bar' => 10
    ];
    public $colors = [
        'cl_background' => ['2b2c2c'],
        'cl_bull' => ['5ca885'],
        'cl_bear' => ['e05853'],
        'cl_linemirror' => ['c8c8c8'],
        'cl_linehigh' => ['f4a460'],
        'cl_linelow' => ['1e90ff'],
        'cl_gray' => ['c8c8c8'],
        'cl_white' => ['ffffff'],
        'cl_black' => ['000000'],
        'cl_cross' => ['00bcd4']
    ];

    function __construct($width, $height)
    {
        $this->width = ($width + 5) * $this->inparams['width_bar'];
        $this->height = $height;
        return $this;
    }

    public function init()
    {
        $this->im = imagecreatetruecolor($this->width + 50, $this->height + $this->params['atr_maxn_height'] + $this->header_height);
        foreach ($this->colors as &$color) {
            $hexcolor = str_split($color[0], 2);

            $bincolor[0] = hexdec("0x{$hexcolor[0]}");
            $bincolor[1] = hexdec("0x{$hexcolor[1]}");
            $bincolor[2] = hexdec("0x{$hexcolor[2]}");

            $color['colorlink'] = imagecolorallocate($this->im, $bincolor[0], $bincolor[1], $bincolor[2]);
        }
        $this->colors['cl_gray_alpha']['colorlink'] = imagecolorexactalpha($this->im, 0xC8, 0xC8, 0xC8, 90);
        imagefill($this->im, 0, 0, $this->colors['cl_background']['colorlink']);
        return $this;
    }

    private function drawBar($i, $o, $h, $l, $c, $color)
    {
        imagelinethick($this->im, $i * $this->inparams['width_bar'], $h,
            $i * $this->inparams['width_bar'], $l, $color, 2);
        imagelinethick($this->im, $i * $this->inparams['width_bar'] - 4, $o,
            $i * $this->inparams['width_bar'], $o, $color, 2);
        imagelinethick($this->im, $i * $this->inparams['width_bar'], $c,
            $i * $this->inparams['width_bar'] + 4, $c, $color, 2);
    }

    private function drawRectangle($i, $h, $l, $color)
    {
        imagerectangle($this->im, $i * $this->inparams['width_bar'] - 4, $h, $i * $this->inparams['width_bar'] + 4, $l, $color);
    }

    private function drawCandle($i, $o, $h, $l, $c, $color)
    {
        imagelinethick($this->im, $i * $this->inparams['width_bar'], $h,
            $i * $this->inparams['width_bar'], $l, $color, 2);
        //imagelinethick ( $this->im , $i * 10 - 4 ,$o, $i * 10 , $o , $color,2 );
        //imagelinethick ( $this->im , $i * 10 ,$c, $i * 10 + 4 , $c , $color,2 );
        if ($o > $c) {
            imagelinethick($this->im, $i * $this->inparams['width_bar'], $o - 4,
                $i * $this->inparams['width_bar'], $c + 4, $color, 8);
        } else {
            imagelinethick($this->im, $i * $this->inparams['width_bar'], $c - 4,
                $i * $this->inparams['width_bar'], $o + 4, $color, 8);
        }
    }

    private function drawLine($i1, $i2, $level, $color)
    {
        imagelinethick($this->im, $i1 * $this->inparams['width_bar'], $level,
            $i2 * $this->inparams['width_bar'], $level, $color, 3);
        imageellipse($this->im, $i1 * $this->inparams['width_bar'],
            $level, 25, 25, $color);
        imageellipse($this->im, $i2 * $this->inparams['width_bar'],
            $level, 25, 25, $color);
    }

    private function drawLevel($i1, $i2, $level, $color)
    {
        imagelinethick($this->im, $i1 * $this->inparams['width_bar'], $level,
            $i2 * $this->inparams['width_bar'], $level, $color, 1);
        //imageellipse( $this->im, $i1 * 10 ,$level, 25, 25, $color);
        //imageellipse( $this->im, $i2 * 10 ,$level, 25, 25, $color);
    }

    private function drawHGrid($i1, $i2, $level, $color)
    {
        imageMyDashedLine($this->im, $i1 * $this->inparams['width_bar'], $level,
            $i2 * $this->inparams['width_bar'], $level, $color, $this->colors['cl_background']['colorlink']);
        //imagepatternedline( $this->im , $i1 * 10 ,$level, $i2 * 10 , $level , $color, $this->colors['cl_background']['colorlink']);
        //imagelinethick ( $this->im , $i1 * 10 ,$level, $i2 * 10 , $level , $color, 1 );
        //imageellipse( $this->im, $i1 * 10 ,$level, 25, 25, $color);
        //imageellipse( $this->im, $i2 * 10 ,$level, 25, 25, $color);
    }

    private function drawVGrid($i1, $color)
    {
        imageMyDashedLine($this->im, $i1 * $this->inparams['width_bar'], 0 + $this->header_height,
            $i1 * $this->inparams['width_bar'], $this->height + $this->header_height,
            $color, $this->colors['cl_background']['colorlink']);
        //imagepatternedline( $this->im , $i1 * 10 ,$level, $i2 * 10 , $level , $color, $this->colors['cl_background']['colorlink']);
        //imagelinethick ( $this->im , $i1 * 10 ,$level, $i2 * 10 , $level , $color, 1 );
        //imageellipse( $this->im, $i1 * 10 ,$level, 25, 25, $color);
        //imageellipse( $this->im, $i2 * 10 ,$level, 25, 25, $color);
    }

    private function drawAtr($i, $v, $nowATR, $color)
    {
        imagelinethick($this->im, $i * $this->inparams['width_bar'], $this->height + ($this->params['atr_maxn_height'] + $this->header_height - $v),
            $i * $this->inparams['width_bar'], $this->height + $this->params['atr_maxn_height'] + $this->header_height, $this->colors['cl_gray']['colorlink'], 6);
        imagelinethick($this->im, $i * $this->inparams['width_bar'], $this->height + ($this->params['atr_maxn_height'] + $this->header_height - $nowATR),
            $i * $this->inparams['width_bar'], $this->height + $this->params['atr_maxn_height'] + $this->header_height, $color, 3);
        return $this;
    }

    private function mPrice($price)
    {
        return $this->height - map($price, $this->params['minPrice'], $this->params['maxPrice'], 0, 500) + $this->header_height;
    }

    function showBar($days)
    {
        foreach ($days as $key => $item) {
            $this->drawBar($key + 1, $this->mPrice($item['O']),
                $this->mPrice($item['H']),
                $this->mPrice($item['L']),
                $this->mPrice($item['C']),
                ($item['O'] < $item['C'] ? $this->colors['cl_bull']['colorlink'] : $this->colors['cl_bear']['colorlink']));
        }
        return $this;
    }

    function showFuture($days)
    {
        $i = count($days);
        $item = $days[$i - 1];
        $atr = $item['atrs'];
        $this->drawRectangle($i + 1, $this->mPrice($item['H']), $this->mPrice($item['H'] + $atr), $this->colors['cl_bull']['colorlink']);
        $this->drawRectangle($i + 1, $this->mPrice($item['L'] - $atr), $this->mPrice($item['L']), $this->colors['cl_bear']['colorlink']);
        $this->drawRectangle($i + 2, $this->mPrice($item['H'] - $atr), $this->mPrice($item['H']), $this->colors['cl_bear']['colorlink']);
        $this->drawRectangle($i + 3, $this->mPrice($item['L'] + $atr), $this->mPrice($item['L']), $this->colors['cl_bear']['colorlink']);
        return $this;
    }

    function showFutureNow($days)
    {
        $i = count($days);
        $item = $days[$i - 1];
        $atr = $item['atrs'];
        //$this->drawRectangle($i + 1, $this->mPrice($item['H']),$this->mPrice($item['H']+$atr),$this->colors['cl_bull']['colorlink']);
        //$this->drawRectangle($i + 1, $this->mPrice($item['L']-$atr),$this->mPrice($item['L']),$this->colors['cl_bear']['colorlink']);
        $this->drawRectangle($i + 2, $this->mPrice($item['H'] - $atr), $this->mPrice($item['H']), $this->colors['cl_bear']['colorlink']);
        $this->drawRectangle($i + 3, $this->mPrice($item['L'] + $atr), $this->mPrice($item['L']), $this->colors['cl_bear']['colorlink']);
        return $this;
    }

    function showCandle($days)
    {
        foreach ($days as $key => $item) {
            $this->drawCandle($key + 1, $this->mPrice($item['O']),
                $this->mPrice($item['H']),
                $this->mPrice($item['L']),
                $this->mPrice($item['C']),
                ($item['O'] < $item['C'] ? $this->colors['cl_bull']['colorlink'] : $this->colors['cl_bear']['colorlink']));
        }
        return $this;
    }

    function showLine(array $lines, $color)
    {
        foreach ($lines as $key => $line) {
            $this->drawLine($line['begin'] + 1, $line['end']['val'] + 1, $this->mPrice($key), $this->colors[$color]['colorlink']);
        }
        return $this;
    }

    function showLevel(array $lines, $max, $color)
    {
        foreach ($lines as $key => $line) {
            $this->drawLevel(0, $max, $this->mPrice($line), $this->colors[$color]['colorlink']);
        }
        return $this;
    }

    function showMirrorLine(array $lines, $color)
    {
        foreach ($lines as $key => $line) {
            $this->drawLine($line['begin'] + 1, $line['end'] + 1, $this->mPrice($key), $this->colors[$color]['colorlink']);
        }
        return $this;
    }

    function showSLine($a1, $a2, $b1, $b2, $color)
    {
        imageline($this->im, $a1, $a2, $b1, $b2, $this->colors[$color]['colorlink']);
        return $this;
    }

    function showGrid(array $days, $minPrice, $maxPrice, $step, $color)
    {
        $price = $minPrice + $step;
        $price = ((int)($price / $step)) * $step;
        while ($price < $maxPrice) {
            $this->drawHGrid(1, count($days) + 5, $this->mPrice($price), $this->colors[$color]['colorlink']);
            imagestring($this->im, 4, (count($days) + 5) * $this->inparams['width_bar'] + 15, $this->mPrice($price) - 5,
                $price, $this->colors['cl_white']['colorlink']);
            $price += $step;
        }
        foreach ($days as $id => $day) {
            $mon = date("m", $day['dt']);
            if (!isset($oldmon)) {
                $oldmon = $mon;
            } else {
                if ($oldmon != $mon) {
                    $oldmon = $mon;
                    $this->drawVGrid($id, $this->colors[$color]['colorlink']);
                }
            }
        }
        return $this;
    }

    function showAtr(array $days, $maxatr)
    {
        foreach ($days as $key => $item) {
            if (isset($item['atrs'])) {
                $this->drawAtr($key + 1, map($item['atrs'], 0, $maxatr, 0, 45), map($item['ATR'], 0, $maxatr, 0, 45),
                    ($days[$key]['O'] < $days[$key]['C'] ? $this->colors['cl_bull']['colorlink'] : $this->colors['cl_bear']['colorlink']));
            }
        }
        return $this;
    }

    function showText($text)
    {
        //$font_file = './arial.ttf';
        //imagefttext($this->im , 0 , 0, 100, 100, $this->colors['cl_white']['colorlink'], $font_file, 'PHP Manual');
        imagestring($this->im, 4, 15, 5, $text, $this->colors['cl_white']['colorlink']);
        return $this;
    }

    function save($filename)
    {
        $path = dirname($filename);
        @mkdir($path,0777,true);
        imagepng($this->im, $filename);
        imagedestroy($this->im);
        return $this;
    }

    function get()
    {
        ob_start();
        imagepng($this->im, null, -1);
        $blob = ob_get_contents();
        ob_end_clean();	
        imagedestroy($this->im);		
        return $blob;
    }
	
}