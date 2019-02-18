<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Validator_Duplicate extends Quform_Validator_Abstract
{
    const IS_DUPLICATE = 'isDuplicate';

    /**
     * The element whose value should be checked
     *
     * @var Quform_Element_Field
     */
    protected $element;

    /**
     * The database repository
     *
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if ( ! array_key_exists('element', $options) || ! $options['element'] instanceof Quform_Element) {
            throw new InvalidArgumentException("Option 'element' is required and must be an instance of Quform_Element");
        }

        if ( ! array_key_exists('repository', $options) || ! $options['repository'] instanceof Quform_Repository) {
            throw new InvalidArgumentException("Option 'repository' is required and must be an instance of Quform_Repository");
        }

        $this->element = $options['element'];
        $this->repository = $options['repository'];
        unset($options['element'], $options['repository']);

        parent::__construct($options);
    }

    /**
     * Returns true if the value has not been previously submitted.
     * Return false otherwise.
     *
     * @param   $value
     * @return  boolean
     */
    public function isValid($value)
    {
        $this->reset();

        if ($this->repository->hasDuplicateEntry($this->element)) {
            $this->error(self::IS_DUPLICATE);
            return false;
        }

        return true;
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
            self::IS_DUPLICATE => __('This value is a duplicate of a previously submitted form',  'quform')
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
        $config = apply_filters('quform_default_config_validator_duplicate', array(
            'messages' => array(
                self::IS_DUPLICATE => ''
            )
        ));

        $config['type'] = 'duplicate';

        return $config;
    }
}
