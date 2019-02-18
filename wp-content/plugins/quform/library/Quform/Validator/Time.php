<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Validator_Time extends Quform_Validator_Abstract
{
    const INVALID = 'timeInvalid';
    const INVALID_TIME = 'timeInvalidTime';
    const TOO_EARLY = 'timeTooEarly';
    const TOO_LATE = 'timeTooLate';
    const BAD_INTERVAL = 'timeBadInterval';

    /**
     * Checks whether the given value is a valid time. Also sets the error message if not.
     *
     * @param   array    $value  The value to check
     * @return  boolean          True if valid false otherwise
     */
    public function isValid($value)
    {
        $this->reset();

        if ( ! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if (preg_match('/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', $value)) {
            $time = strtotime($value);

            if (Quform::isNonEmptyString($this->config('min'))) {
                $min = $this->config('min') == '{now}' ? strtotime(date('H:i')) : strtotime($this->config('min'));

                if ($time < $min) {
                    $this->error(self::TOO_EARLY, array(
                        'min' => date($this->config('format'), $min),
                        'value' => date($this->config('format'), $time)
                    ));

                    return false;
                }
            }

            if (Quform::isNonEmptyString($this->config('max'))) {
                $max = $this->config('max') == '{now}' ? strtotime(date('H:i')) : strtotime($this->config('max'));

                if ($time > $max) {
                    $this->error(self::TOO_LATE, array(
                        'max' => date($this->config('format'), $max),
                        'value' => date($this->config('format'), $time)
                    ));

                    return false;
                }
            }

            if (Quform::isNonEmptyString($this->config('interval'))) {
                $interval = (int) $this->config('interval');
                $parts = explode(':', $value);
                $minutes = (int) $parts[1];

                if ($minutes % $interval !== 0) {
                    $this->error(self::BAD_INTERVAL, array(
                        'interval' => $interval,
                        'value' => date($this->config('format'), $time)
                    ));

                    return false;
                }
            }

            return true;
        }

        $this->error(self::INVALID_TIME);
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
            self::INVALID_TIME => __('Please enter a valid time',  'quform'),
            self::TOO_EARLY => __('The time must not be earlier than %min%', 'quform'),
            self::TOO_LATE => __('The time must not be later than %max%', 'quform'),
            self::BAD_INTERVAL => __('The minutes must be a multiple of %interval%', 'quform')
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
        $config = apply_filters('quform_default_config_validator_time', array(
            'format' => 'g:i A',
            'min' => '',
            'max' => '',
            'interval' => '',
            'messages' => array(
                self::INVALID => '',
                self::INVALID_TIME => '',
                self::TOO_EARLY => '',
                self::TOO_LATE => ''
            )
        ));

        $config['type'] = 'time';

        return $config;
    }
}
