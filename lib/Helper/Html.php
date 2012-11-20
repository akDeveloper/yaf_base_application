<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Helper;

use Yaf\ViewInterface;

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

        self::$encoding = 'utf-8';

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

    /**
     * Appends a javascript file from current view.
     *
     * Some javascript files are required to load only in specific action 
     * views. You can add them with this method.
     *
     * Notice that you have to add a line to your layout file like:
     * <code>
     * <?= $this->javascripts ?>
     * </code>
     * This is the place where additional javascript files would appear.
     * 
     * @param View_Interface $view The view instance
     * @param string|array        $args An array of javascript paths or a 
     *                                  string with javascript path to load.
     */
    public static function appendJavascript(ViewInterface $view, $args)
    {
        if ( !isset($view->javascripts)) {
            $view->assign('javascripts', '');
        }
        
        if ( !is_array($args)) {
            $args = array($args);
        }
        
        foreach ($args as $arg) {
            $url = strpos($arg, 'http') === 0 || strpos($arg, '/') === 0
                ? $arg 
                : '/javascripts/'.$arg;
            $view->javascripts .= self::javascript($url . '.js');
        }
    }

    /**
     * Appends a stylesheet file from current view.
     *
     * Some stylesheet files are required to load only in specific action 
     * views. You can add them with this method.
     *
     * Notice that you have to add a line to your layout file like:
     * <code>
     * <?= $this->css ?>
     * </code>
     * This is the place where additional stylesheet files would appear.
     * 
     * @param View_Interface $view The view instance
     * @param string|array        $args An array of stylesheet paths or a 
     *                                  string with stylesheet path to load.
     */
    public static function appendCss(ViewInterface $view, $args)
    {
        if ( !isset($view->stylesheets)) {
            $view->assign('stylesheets', '');
        }

        if ( !is_array($args)) {
            $args = array($args);
        }

        foreach ($args as $arg) {
            $url = strpos($arg, 'http') === 0 || strpos($arg, '/') === 0
                ? $arg 
                : '/stylesheets/'.$arg;
            $view->stylesheets .= self::css($url.'.css');
        } 
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
