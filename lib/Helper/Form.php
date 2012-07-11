<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Helper;

/**
 * Description of Form Helper
 *
 * @author Andreas Kollaros
 */

class Form
{
    public static function label($tag, $for=null, $attrs=array())
    {
        $attrs['for'] = $for;
        return '<label'.Html::attributes($attrs).'>'.$tag.'</label>'.PHP_EOL;
    }

    public static function textarea($name, $value=null, $attrs=array())
    {
        $attrs['name'] = $name;

        return '<textarea'.Html::attributes($attrs).'>'.Html::encode($value).'</textarea>'.PHP_EOL;
    }

    public static function select($name, $options = array(), $selected = null, 
        $attrs = array(), $add_empty = true
    ) {
        $attrs['name'] = $name;
        
        $html = array();

        if (true == $add_empty) {
            $html[] = self::option(null,null,null);
        }

        foreach ($options as $label => $value) {

            if (is_array($value)) {
                $html[] = self::optgroup($label, $value, $selected);
            } else {
                $html[] = self::option($label, $value, $selected);
            }

        }

        return '<select'.Html::attributes($attrs).'>'.implode('',$html).'</select>'.PHP_EOL;
    }

    public static function option($label, $value, $selected)
    {
        $value = is_bool($value) ? (int) $value : $value;

        $selected = ((string) $value == (string) $selected) ? 'selected' : null;
        $attrs = array('value' => Html::encode($value), 'selected'=>$selected);

        return '<option'.Html::attributes($attrs).'>'
            .Html::encode($label).'</option>'.PHP_EOL;
    }

    public static function optgroup($label, $options, $selected)
    {
        $html = array();

        foreach ($options as $option=>$value){
            $html[] = self::option($option,$value,$selected);
        }

        return '<optgroup label="'.Html::encode($label).'">'.implode('',$html).'</optgroup>'.PHP_EOL;
    }

    public static function checkbox($name, $value, $attrs = array(),
        $checked_value = 1, $unchecked_value = 0
    ) {

        ((string) $value == (string) $checked_value) 
            ? $attrs['checked'] = 'checked'
            : null;

        return self::input_html('checkbox', $name, $value, $attrs); 
    }

    public static function radio($name, $value, $checked = false, $attrs = array())
    {
        if (true == $checked) {
            $attrs['checked'] = 'checked';
        }

        return self::input_html('hidden', $name, $value, $attrs);
    }

    public static function hidden($name, $value = null, $attrs = array())
    {
        return self::input_html('hidden', $name, $value, $attrs);
    }
    
    public static function text($name, $value = null, $attrs=array())
    {
        return self::input_html('text', $name, $value, $attrs);
    }

    public static function file($name, $value = null, $attrs = array())
    {
        return self::input_html('file', $name, $value, $attrs);
    }

    public static function password($name, $value = null, $attrs = array())
    {
        return self::input_html('password', $name, $value, $attrs);
    }

    public static function input_html($type="submit", $name=null, $value=null, 
        $attrs=array()
    ) {
        
        return '<input type="'.$type.'"'.Html::attributes($attrs).' name="'.$name
            .'" value="'.Html::encode($value).'" />'.PHP_EOL;
    }

    public static function delete_button($url, $token, $options=array())
    {

        if ( isset($options['name']) ){
            $name = $options['name'];
            unset($options['name']);
        } else {
            $name = '';
        }
        $html_options = array();
        $html_options['token'] = $token;
        if ( isset($options['confirm']) ){
            $html_options['confirm'] = $options['confirm'];
            unset($options['confirm']);
        } else {
            $html_options['confirm'] = 'Do you want to delete this entry?';
        }
        $html_options['method'] = 'delete';
        $options['url'] = $url;
        return self::button_to($name, $options, $html_options);
    }

    public static function button_to($name, $options=array(), $html_options=array())
    {
        $token = isset($html_options['token']) ? $html_options['token'] : null;
        $method = null;
        if ( isset($html_options['method']) ) {
            switch ($html_options['method']) {
                case 'delete':
                    $method = 'DELETE';
                    break;
                case 'put':
                    $method = 'PUT';
                    break;
            }
        }
        $confirm = null;
        if ( isset($html_options['confirm']) ){
            $confirm = $html_options['confirm'];
            unset($html_options['confirm']);
        }
        $image = null;
        if ( isset($options['image']) ) {
            $image ='<span class="icon"><img src="' . $options['image'] . '" alt="'.$name.'"/></span>';
            unset($options['image']);
        }

        $url = isset($options['url']) ? $options['url'] : null;
        if ($options['url']) unset($options['url']);
        $js = "";
        if ($confirm ) $js .= "if (confirm('$confirm')) {"; 
        $js .= "var f = document.createElement('form');f.style.display = 'none'; this.parentNode.appendChild(f);f.method = 'POST';f.action = this.href;";
        if ( $method ) $js .= "var m = document.createElement('input');m.setAttribute('type', 'hidden');m.setAttribute('name', '_method');m.setAttribute('value', '$method');f.appendChild(m);";
        if ( $token )  $js .= "var s = document.createElement('input');s.setAttribute('type', 'hidden');s.setAttribute('name', 'authenticity_token');s.setAttribute('value', '$token');f.appendChild(s);";
        $js .="f.submit();";
        if ( $confirm) $js .= "};";
        $js .= "return false;";
        
        return '<a ' . self::attributes($options) . ' onclick="' . $js . '" href="' . $url . '">' . $image . $name . '</a>';
    }
}
