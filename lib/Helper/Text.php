<?php

class Lycan_Helper_Text
{

    public static function split_in_words($string, $length=30)
    {
        $explode = explode(" ", stripslashes(strip_tags($string)));
        $append = count($explode) <= $length ? '' : " ...";
        return implode(" ", array_slice($explode, 0, $length)) . $append;
    }

    public static function strip_text($string, $length=14)
    {
        $suffix = strlen($string) < $length ? '' : ' ...';
        if (function_exists('mb_substr') ){
            return mb_substr(stripslashes($string), 0, $length) . $suffix;
        } else {
            return substr(stripslashes($string), 0, $length) . $suffix;
        }
    }

}

?>
