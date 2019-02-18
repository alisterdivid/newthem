<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Multiselect extends Quform_Element_Select
{
    /**
     * @var array
     */
    protected $value = array();

    /**
     * @var bool
     */
    protected $isMultiple = true;

    /**
     * Prepare the dynamic default value
     *
     * @param   string  $value
     * @return  array
     */
    public function prepareDynamicValue($value)
    {
        return explode(',', $value);
    }

    /**
     * Set the value
     *
     * @param   array  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_array($value)) {
            return false;
        }

        foreach ($value as $val) {
            if ( ! parent::isValidValue($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Does this element have the given value?
     *
     * @param   mixed    $value
     * @return  boolean
     */
    public function hasValue($value)
    {
        return in_array($value, $this->getValue(), true);
    }

    /**
     * @return string
     */
    public function getEmptyValue()
    {
        return array();
    }

    /**
     * Get the filtered value
     *
     * @return array
     */
    public function getValue()
    {
        $value = $this->value;

        $this->filterValueRecursive($value);

        return $value;
    }

    /**
     * Get the value formatted for HTML
     *
     * @return string
     */
    public function getValueHtml()
    {
        $value = '';

        if ( ! $this->isEmpty()) {
            $value = '<ul style="margin:0;">';

            foreach ($this->getValue() as $option) {
                $value .= sprintf('<li>%s</li>', Quform::escape($option));
            }

            $value .= '</ul>';
        }

        return $value;
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator  The separator
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        return join($separator, $this->getValue());
    }

    /**
     * Get the value for storage in the database
     *
     * @return string
     */
    public function getValueForStorage()
    {
        return serialize($this->getValue());
    }

    /**
     * Set the value from storage
     *
     * @param string $value
     */
    public function setValueFromStorage($value)
    {
        $this->setValue(is_serialized($value) ? unserialize($value) : $this->getEmptyValue());
    }

    /**
     * If the value is not an array or is an empty array it's empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return ! count($this->getValue());
    }

    /**
     * Get the HTML attributes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldAttributes(array $context = array())
    {
        $attributes = array(
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context)),
            'multiple' => true
        );

        if ($this->config('enhancedSelectEnabled')) {
            $attributes['data-options'] = wp_json_encode(array(
                'rtl' => $this->form->isRtl(),
                'search' => ! $this->config('enhancedSelectSearch'),
                'placeholder' => $this->getTranslation('enhancedSelectPlaceholder', __('Please select', 'quform')),
                'noResultsFound' => $this->getTranslation('enhancedSelectNoResultsFound', __('No results found.', 'quform')),
            ));

            $attributes['style'] = 'width: 100%;';
        }

        if (Quform::isNonEmptyString($this->config('aria-labelledby')))  {
            $attributes['aria-labelledby'] = $this->config('aria-labelledby');
        }

        if (Quform::isNonEmptyString($this->config('sizeAttribute'))) {
            if ($this->config('sizeAttribute') == 'auto') {
                $attributes['size'] = $this->getOptionsCount();
            } else {
                $attributes['size'] = $this->config('sizeAttribute');
            }
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
    }

    /**
     * Get the total number of options, including optgroup options
     *
     * @return int
     */
    protected function getOptionsCount()
    {
        $count = count($this->getOptions());

        foreach ($this->getOptions() as $option) {
            if (isset($option['options'])) {
                $count += count($option['options']);
            }
        }

        return $count;
    }

    /**
     * Get the classes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldClasses(array $context = array())
    {
        $classes = array(
            'quform-field',
            'quform-field-multiselect',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->config('enhancedSelectEnabled')) {
            $classes[] = 'quform-field-multiselect-enhanced';
        }

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

        return $classes;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array   $context
     * @return  string
     */
    protected function getFieldHtml(array $context = array())
    {
        return Quform::getHtmlTag('select', $this->getFieldAttributes($context), $this->getOptionsHtml());
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $options = array();
        $defaults = array(__('Option 1', 'quform'), __('Option 2', 'quform'), __('Option 3', 'quform'));

        foreach ($defaults as $key => $value) {
            $option = self::getDefaultOptionConfig();
            $option['id'] = $key + 1;
            $option['label'] = $option['value'] = $value;
            $options[] = $option;
        }

        $config = apply_filters('quform_default_config_multi_select', array(
            'label' => __('Untitled', 'quform'),
            'subLabel' => '',
            'description' => '',
            'descriptionAbove' => '',
            'required' => false,
            'options' => $options,
            'nextOptionId' => 4,
            'customiseValues' => false,
            'defaultValue' => array(),
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'tooltip' => '',
            'labelIcon' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'customClass' => '',
            'sizeAttribute' => '',
            'enhancedSelectEnabled' => false,
            'enhancedSelectSearch' => true,
            'enhancedSelectPlaceholder' => '',
            'enhancedSelectNoResultsFound' => '',
            'adminLabel' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,
            'labelPosition' => 'inherit',
            'labelWidth' => '',
            'tooltipType' => 'icon',
            'tooltipEvent' => 'inherit',
            'inArrayValidator' => true,
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'messageRequired' => '',
            'styles' => array(),
            'visibility' => '',
            'filters' => array(),
            'validators' => array()
        ));

        $config['type'] = 'multiselect';

        return $config;
    }
}
