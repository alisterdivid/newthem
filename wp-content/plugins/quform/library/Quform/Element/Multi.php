<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
abstract class Quform_Element_Multi extends Quform_Element_Field
{
    /**
     * Element options
     * @var array
     */
    protected $options = array();

    /**
     * Add an element option
     *
     * @param string $option
     * @param string $value
     */
    public function addOption($option, $value = '')
    {
        $option = (string) $option;
        $this->options[$option] = $value;
    }

    /**
     * Add multiple element options
     *
     * @param array $options
     */
    public function addOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (is_array($value)
                && array_key_exists('key', $value)
                && array_key_exists('value', $value)
            ) {
                $this->addOption($value['key'], $value['value']);
            } else {
                $this->addOption($option, $value);
            }
        }
    }

    /**
     * Set multiple element options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->clearOptions();
        $this->addOptions($options);
    }

    /**
     * Clear multiple element options
     */
    public function clearOptions()
    {
        $this->options = array();
    }

    /**
     * Get the options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return parent::getCssSelectors() + array(
            'options' => '%s .quform-input-%s .quform-options',
            'option' => '%s .quform-input-%s .quform-option',
            'optionLabel' => '%s .quform-input-%s .quform-option .quform-option-label',
            'optionLabelSelected' => '%s .quform-input-%s .quform-option .quform-field:checked + .quform-option-label',
            'optionIcon' => '%s .quform-input-%s .quform-option .quform-option-icon',
            'optionIconSelected' => '%s .quform-input-%s .quform-option .quform-option-icon-selected',
            'optionText' => '%s .quform-input-%s .quform-option .quform-option-text',
            'optionTextSelected' => '%s .quform-input-%s .quform-option .quform-field:checked + .quform-option-label .quform-option-text'
        );
    }

    /**
     * Does the given logic rule match the current value?
     *
     * @param   array  $rule
     * @return  bool
     */
    public function isLogicRuleMatch(array $rule)
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $match = false;

            foreach ($value as $val) {
                if ($this->isLogicValueMatch($val, $rule)) {
                    $match = true;
                    break;
                }
            }

            return $match;
        }

        return $this->isLogicValueMatch($value, $rule);
    }

    /**
     * Inherit settings from this element into the context
     *
     * @param   array  $context
     * @return  array
     */
    protected function prepareContext(array $context = array())
    {
        $context = parent::prepareContext($context);

        // Inside labels are not possible so set it above
        if ( ! in_array($context['labelPosition'], array('', 'left'), true)) {
            $context['labelPosition'] = '';
        }

        // Icon is the only possible tooltip type for this element
        $context['tooltipType'] = 'icon';

        return $context;
    }

    /**
     * Get the value of the given $key from the given $option or return the default of it does not exist
     *
     * @param   array   $option
     * @param   string  $key
     * @return  string
     */
    public function getOptionValue(array $option, $key)
    {
        $value = Quform::get($option, $key);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultOptionConfig')), $key);
        }

        return $value;
    }

    /**
     * Get the default option config
     *
     * @return array
     */
    public static function getDefaultOptionConfig()
    {
        return array(
            'label' => '',
            'value' => ''
        );
    }
}
