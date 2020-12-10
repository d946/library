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

}