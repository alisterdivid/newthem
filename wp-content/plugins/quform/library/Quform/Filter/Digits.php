<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Filter_Digits extends Quform_Filter_Abstract
{
    /**
     * Filter everything from the given value except digits
     *
     * If the value provided is not a string, the value will remain unfiltered
     *
     * @param   string  $value  The value to filter
     * @return  string          The filtered value
     */
    public function filter($value)
    {
        if ( ! is_string($value)) {
            return $value;
        }

        $whiteSpace = $this->config('allowWhiteSpace') ? '\s' : '';

        $pattern = '/[^0-9' . $whiteSpace . ']/';

        return preg_replace($pattern, '', $value);
    }

    /**
     * Get the default config for this filter
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_filter_digits', array(
            'allowWhiteSpace' => false
        ));

        $config['type'] = 'digits';

        return $config;
    }
}
