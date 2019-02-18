<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Validator_Date extends Quform_Validator_Abstract
{
    const INVALID = 'dateInvalid';
    const INVALID_DATE = 'dateInvalidDate';
    const TOO_EARLY = 'dateTooEarly';
    const TOO_LATE = 'dateTooLate';

    /**
     * Returns true if the value is a valid date, false otherwise
     *
     * @param   array    $value
     * @return  boolean
     */
    public function isValid($value)
    {
        $this->reset();

        if ( ! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value, $matches) && checkdate($matches[2], $matches[3], $matches[1])) {
            $date = new DateTime($value);

            if (Quform::isNonEmptyString($this->config('min'))) {
                $min = $this->config('min') == '{today}' ? new DateTime(date('Y-m-d')) : new DateTime($this->config('min'));

                if ($date < $min) {
                    $this->error(self::TOO_EARLY, array(
                        'min' => $min->format($this->config('format')),
                        'value' => $date->format($this->config('format'))
                    ));

                    return false;
                }
            }

            if (Quform::isNonEmptyString($this->config('max'))) {
                $max = $this->config('max') == '{today}' ? new DateTime(date('Y-m-d')) : new DateTime($this->config('max'));

                if ($date > $max) {
                    $this->error(self::TOO_LATE, array(
                        'max' => $max->format($this->config('format')),
                        'value' => $date->format($this->config('format'))
                    ));

                    return false;
                }
            }

            return true;
        }

        $this->error(self::INVALID_DATE);
        return false;
    }

    /**
     * Get all message templates or the single message with the given key
     *
     * @param   string|null   $key
     * @return  array|string
     */
    public static function getMessageTemplates($key = null)
    {
        $messageTemplates = array(
            self::INVALID => __('Invalid data type, string expected',  'quform'),
            self::INVALID_DATE => __('Please enter a valid date',  'quform'),
            self::TOO_EARLY => __('The date must not be earlier than %min%', 'quform'),
            self::TOO_LATE => __('The date must not be later than %max%', 'quform')
        );

        if (is_string($key)) {
            return array_key_exists($key, $messageTemplates) ? $messageTemplates[$key] : null;
        }

        return $messageTemplates;
    }

    /**
     * Get the default config for this validator
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_validator_date', array(
            'min' => '',
            'max' => '',
            'format' => 'n/j/Y',
            'messages' => array(
                self::INVALID => '',
                self::INVALID_DATE => '',
                self::TOO_EARLY => '',
                self::TOO_LATE => ''
            )
        ));

        $config['type'] = 'date';

        return $config;
    }
}
