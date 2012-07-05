<?php

class Lycan_Helper_Number
{
    public static $DEFAULT_CURRENCY_VALUES = array(
        'format'          => "%u%n",
        'negative_format' => "-%u%n",
        'unit'            => "$",
        'separator'       => ".",
        'delimiter'       => ",",
        'precision'       => 2,
        'significant'     => false,
        'strip_insignificant_zeros' => false
    );

    public static function numberToPhone($number, $options = array())
    {
        if ( null === $number ) return null;

        $number = trim($number);
        $area_code = isset($options['area_code']) ? $options['area_code'] : null;
        $delimiter = isset( $options['delimiter'] ) ? $options['delimiter'] : '-';
        $extension = isset($options['extension']) ? $options['extension'] : null;
        $country_code = isset($options['country_code']) ? $options['country_code'] : null;

        if ( $area_code ){
            $number = preg_replace('/(\d{1,3})(\d{3})(\d{4}$)/',"(\\1) \\2{$delimiter}\\3",$number);
        } else {
            $number = preg_replace('/(\d{0,3})(\d{3})(\d{4})$/',"\\1{$delimiter}\\2{$delimiter}\\3",$number);
        }

        $str = "";
        if( $country_code) $str .= "+{$country_code}{$delimiter}";
        $str .= $number;
        if ($extension) $str .= " x {$extension}";

        return $str;
    }

    /**
     *
     *  'locale'  - Sets the locale to be used for formatting (defaults to current locale).
     *  'precision' - Sets the level of precision (defaults to 2).
     *  'unit' - Sets the denomination of the currency (defaults to '$').
     *  'separator' - Sets the separator between the units (defaults to '.').
     *  'delimiter' - Sets the thousands delimiter (defaults to ',').
     *  'format' - Sets the format for non-negative numbers (defaults to '%u%n').
     */
    public static function numberToCurrency($number, $options = array())
    {
        if ( null === $number ) return null;

        $params = array(
            'default' => array()
        );

        if ( isset($options['locale']) )
            $params = array_merge( $params, array('locale' => $options['locale']) );

        $defaults = Lycan_Locale_i18n::translate("number.format", $params);
        #print_r($defaults);
        /**
         * Should return
         * array(
         *  'precision' => 2,
         *  'separator' => '.',
         *  'delimiter' => ','
         * )
         */
        $currency = Lycan_Locale_i18n::translate("number.currency.format", $params);
        /**
         * Should return
         * array(
         *  'unit' => '$',
         *  'precision' => 2,
         *  'format' => '%u %n'
         * )
         */
        $defaults = array_merge(self::$DEFAULT_CURRENCY_VALUES, $defaults, $currency);

        if ( isset($options['format']) )
            $defaults['negative_format'] = "-" + $options['format'];

        $options = array_merge($defaults, $options);

        $unit = $options['unit'];
        unset($options['unit']);
        $format = $options['format'];
        unset($options['format']);

        if ( floatval($number) < 0 ) {
            $format = $options['negative_format'];
            unset($options['negative_format']);
            $number = abs($number);
        }

        $value = self::numberWithPrecision($number, array_merge($options, array('raise'=>true)));

        $find = array("%n", "%u"); $replace = array($value, $unit);
        $format = str_replace($find, $replace, $format);

        return $format;
    }

    public static function numberToPercentage($number, $options = array())
    {
        if ( null === $number ) return null;
        $params = array(
            'default' => array()
        );
        $defaults = Lycan_Locale_i18n::translate("number.format", $params);
        $percentage = Lycan_Locale_i18n::translate("number.percentage.format");

        $defaults = array_merge($defaults, $percentage);
        $options = array_merge($defaults, $options);

        return self::numberWithPrecision($number, array_merge($options, array('raise' => true))) . "%";

    }

    public static function numberWithPrecision($number, $options = array())
    {
        $params = array( 'default' => array() );

        if ( isset($options['locale']) )
            $params = array_merge( $params, array('locale' => $options['locale']) );

        $defaults = Lycan_Locale_i18n::translate("number.format", $params);
        $precision_defaults = Lycan_Locale_i18n::translate('number.precision.format', $params);

        $defaults = array_merge($defaults, $precision_defaults);
        $options = array_merge($defaults, $options);

        $precision = isset($options['precision']) ? $options['precision'] : null;
        unset($options['precision']);
        $significant = isset($options['significant']) ? $options['significant'] : null;
        unset($options['significant']);
        $strip_insignificant_zeros = isset($options['strip_insignificant_zeros'])
            ? $options['strip_insignificant_zeros']
            :null;
        unset($options['strip_insignificant_zeros']);

        if ( $significant and $precision > 0){
            if (0 == $number){
                $digits = 1;
                $rounded_number = 0;
            } else {
                $digits = floor( log10( abs($number) ) + 1);
                $rounded_number = round( $number /  pow(10, $digits - $precision) * pow(10, $digits - $precision));
                $digits = floor( log10( abs($rounded_number) ) + 1 );
            }

            $precision = $precision - $digits;
            $precision = $precision > 0 ? $precision : 0;

        } else {
            $rounded_number = round($number, $precision);
        }

        #var_dump( sprintf("%01.{$precision}f", $rounded_number));

        $formatted_number = self::numberWithDelimiter(sprintf("%01.{$precision}f", $rounded_number), $options);
        if ( $strip_insignificant_zeros ){
            $escaped_separator = "\\" . $options['separator'];
            $formatted_number = preg_replace("/({$escaped_separator})(\d*[1-9])?0+\z/",'\1\2', $formatted_number);
            $formatted_number = preg_replace("/{$escaped_separator}\z/",'', $formatted_number);
        }

        return $formatted_number;
    }

    public static function numberWithDelimiter($number, $options = array())
    {

        $defaults = array(
            'separator' => ".",
            'delimiter' => ",",
            'precision' => 3,
            'significant' => false,
            'strip_insignificant_zeros' => false
        );

        $options = array_merge($defaults, $options);

        $parts = explode('.', $number);
        $pattern = '/(\d)(?=(\d\d\d)+(?!\d))/';
        $replace = "\\1{$options['delimiter']}";

        $parts[0] = preg_replace($pattern, $replace, $parts[0]);
        return implode($options['separator'], $parts);

    }

    protected static $STORAGE_UNITS = array(
        'byte',
        'kb',
        'mb',
        'gb',
        'tb',
        'pb',
        'eb',
        'zb'
    );

    public static function numberToHumanSize($number, $options = array())
    {
        if (null === $number ) return null;

        $human = array(
            'delimiter' => '',
            'precision' => 3,
            'significant' => true,
            'strip_insignificant_zeros' => true
        );

        $defaults = array(
            'separator' => ".",
            'delimiter' => ",",
            'precision' => 3,
            'significant' => false,
            'strip_insignificant_zeros' => false
        );

        $defaults = array_merge($defaults, $human);

        $options = array_merge($defaults, $options);

        if ( !isset($options['strip_insignificant_zeros']) ) $options['strip_insignificant_zeros'] = true;

        $storage_units_format = "%n %u";

        $base = isset($options['prefix']) && $options['prefix'] == 'si' ? 1000 : 1024;

        if ( $number < $base ) {
            $unit = 'bytes';
            $find = array("%n", "%u"); $replace = array($number, $unit);
            return str_replace($find, $replace, $storage_units_format);
        } else {
            $max_exp = count( self::$STORAGE_UNITS ) - 1;
            $exponent = intval( log($number) / log($base) );
            if ( $exponent > $max_exp ) $exponent = $max_exp;
            $number = $number / pow($base, $exponent);
        }

        $unit_key = self::$STORAGE_UNITS[$exponent];
        $units = array(
          'byte' => "Bytes",
          'kb' =>  "KB",
          'mb' => "MB",
          'gb' => "GB",
          'tb' => "TB",
          'pb' => 'PB',
          'eb' => 'EB',
          'zb' => 'ZB'
        );

        $unit = $units[$unit_key];
        $formatted_number = self::numberWithPrecision($number, $options);
        $find = array("%n", "%u"); $replace = array($formatted_number, $unit);
        return str_replace($find, $replace, $storage_units_format);
    }

    protected static $DECIMAL_UNITS = array(
        0 => 'unit',
        1 => 'ten',
        2 => 'hundred',
        3 => 'thousand',
        6 => 'million',
        9 => 'billion',
        12 => 'trillion',
        15 => 'quadrillion',
        -1 => 'deci',
        -2 => 'centi',
        -3 => 'mili',
        -6 => 'micro',
        -9 => 'nano',
        -12 => 'pico',
        -15 => 'femto'
    );

    public static function numberToHuman($number, $options = array())
    {
        if ( null === $number) return null;


    }
}
?>
