<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Filter_Alpha extends Quform_Filter_Abstract
{
    /**
     * Filter everything from the given value except alphabet characters
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

        if (Quform::hasPcreUnicodeSupport()) {
            // Use native language alphabet
            $pattern = '/[^\p{L}' . $whiteSpace . ']/u';
        } else {
            $pattern = '/[^a-zA-Z' . $whiteSpace . ']/';
        }

        return preg_replace($pattern, '', $value);
    }

    /**
     * Get the default config for this filter
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_filter_alpha', array(
            'allowWhiteSpace' => false
        ));

        $config['type'] = 'alpha';

        return $config;
    }
}
