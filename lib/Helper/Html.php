<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Helper;

/**
 * Description of Html Helper
 *
 * @author Andreas Kollaros
 */

class Html
{

    protected static $encoding;

    public static function encode($value)
    {
        return htmlentities($value, ENT_QUOTES, self::getEncoding(), false);
    }

    public static function decode($value)
    {
        return html_entity_decode($value, ENT_QUOTES, self::getEncoding());
    }

    public static function specialchars($value)
    {

        return htmlspecialchars($value, ENT_QUOTES, self::getEncoding(), false);
    }

    public static function image($url, $alt=null, $attrs = array())
    {
        
        $attrs['alt'] = $alt ?: basename($url);

        return '<img src="' . $url . '"' . self::attributes($attrs) . ' />';
    }

    public static function linkTo($text, $url, $attrs = array())
    {
        return '<a href="' . $url . '"' . self::attributes($attrs) . '>' . $text . '</a>';
    }

    public static function span($content, $attrs=array())
    {
        return '<span'.self::attributes($attrs).'>'.self::encode($content)."</span>";
    }

    public static function getEncoding()
    {
        if (self::$encoding) {
            return self::$encoding;
        }

        self::$encoding = \Yaf\Application::app()->getConfig()
            ->get('application')
            ->get('encoding');

        return self::$encoding;
    }

    public static function validUrl($url)
    {
        if (null === $url || "" == $url)
            return null;
        
        return substr($url, 0, 4) == "http" ? $url : "http://" . $url;
    }

    public static function javascript($url)
    {
        return '<script src="'.$url.'" type="text/javascript"></script>' . PHP_EOL;
    }

    public static function css($url, $attrs=array())
    {
        $defaults = array('media'=>'screen', 'type'=>'text/css', 'rel'=>'stylesheet');
        
        $attrs = array_merge($attrs, $defaults);

        return '<link href="' . $url . '" '.self::attributes($attrs).'/>' . PHP_EOL;
    }

    public static function attributes($array)
    {
        if (empty($array)) return null;

        $o = "";
        foreach ($array as $k => $v) {
             if ( null !== $v )
                $o .= ' ' . $k . '="' . self::encode($v) . '"';
        }
        return ' '.$o;
    }

}
