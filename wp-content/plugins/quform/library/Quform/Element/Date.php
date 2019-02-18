<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Date extends Quform_Element_Field implements Quform_Element_Editable
{
    /**
     * Get the value formatted in HTML
     *
     * @return  string
     */
    public function getValueHtml()
    {
        return Quform::escape($this->getValueText());
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        return $this->isEmpty() ? '' : date_i18n($this->config('dateFormat'), strtotime($this->getValue()));
    }

    /**
     * Is the given value valid for this element type
     *
     * @param   string  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_string($value)) {
            return false;
        }

        return $value == '{today}' || preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value);
    }

    /**
     * Prepare the dynamic default value
     *
     * Takes a date string e.g. 2017-12-1, if it's valid it will be converted into the format for the datepicker
     *
     * @param   string  $value
     * @return  string
     */
    public function prepareDynamicValue($value)
    {
        $parts = explode('-', $value);
        $value = '';

        if (isset($parts[0], $parts[1], $parts[2])) {
            $year = (int) $parts[0];
            $month = (int) $parts[1];
            $day = (int) $parts[2];

            if (checkdate($month, $day, $year)) {
                $value = date('Y-m-d', strtotime("$year-$month-$day"));
            }
        }

        return $value;
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
            'type' => 'text',
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context)),
            'placeholder' => 'YYYY-MM-DD',
            'data-options' => wp_json_encode($this->getDatepickerOptions())
        );

        if ($this->getValue() == '{today}') {
            $this->setValue(date('Y-m-d'));
        }

        if ( ! $this->isEmpty()) {
            $attributes['value'] = $this->getValue();
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
    }

    /**
     * Get the datepicker options for the field
     *
     * @return array
     */
    protected function getDatepickerOptions()
    {
        $options = array(
            'format' => $this->config('dateFormatJs'),
            'min' => $this->config('dateMin'),
            'max' => $this->config('dateMax'),
            'start' => $this->config('dateViewStart'),
            'depth' => $this->config('dateViewDepth'),
            'showFooter' => $this->config('dateShowFooter'),
            'locale' => $this->config('dateLocale'),
            'placeholder' => $this->config('placeholder'),
            'identifier' => $this->getIdentifier()
        );

        if ($this->getValue() == '{today}') {
            $options['today'] = true;
        }

        return $options;
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
            'quform-field-date',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($this->config('tooltip')) && Quform::get($context, 'tooltipType') == 'field') {
            $classes[] = sprintf('quform-tooltip-%s', Quform::get($context, 'tooltipEvent'));
        }

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

        $classes = apply_filters('quform_field_classes', $classes, $this, $this->form, $context);
        $classes = apply_filters('quform_field_classes_' . $this->getIdentifier(), $classes, $this, $this->form, $context);

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
        return Quform::getHtmlTag('input', $this->getFieldAttributes($context));
    }

    /**
     * Get the field HTML when editing
     *
     * @return string
     */
    public function getEditFieldHtml()
    {
        return $this->getFieldHtml();
    }

    /**
     * Render the CSS for this element
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = parent::renderCss($context);

        if ($context['fieldWidth'] == 'custom' && Quform::isNonEmptyString($context['fieldWidthCustom'])) {
            $css .= sprintf('.quform-input-date.quform-input-%s { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        return $css;
    }

    /**
     * Does the given logic rule match the given value?
     *
     * @param   mixed  $value
     * @param   array  $rule
     * @return  bool
     */
    protected function isLogicValueMatch($value, array $rule)
    {
        if ($rule['operator'] == 'gt') {
            return ! $this->isEmpty() && strtotime($value) > strtotime($rule['value']);
        } else if ($rule['operator'] == 'lt') {
            return ! $this->isEmpty() && strtotime($value) < strtotime($rule['value']);
        }

        return parent::isLogicValueMatch($value, $rule);
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_date', array(
            'label' => __('Date', 'quform'),
            'placeholder' => '',
            'subLabel' => '',
            'description' => '',
            'descriptionAbove' => '',
            'required' => false,
            'defaultValue' => '',
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'tooltip' => '',
            'labelIcon' => '',
            'fieldIconLeft' => '',
            'fieldIconRight' => 'qicon-calendar',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'customClass' => '',
            'adminLabel' => '',
            'dateMin' => '',
            'dateMax' => '',
            'dateViewStart' => 'month',
            'dateViewDepth' => 'month',
            'dateShowFooter' => false,
            'dateFormatJs' => '',
            'dateFormat' => '',
            'dateLocale' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,
            'labelPosition' => 'inherit',
            'labelWidth' => '',
            'tooltipType' => 'inherit',
            'tooltipEvent' => 'inherit',
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'messageRequired' => '',
            'messageDateInvalidDate' => '',
            'messageDateTooEarly' => '',
            'messageDateTooLate' => '',
            'styles' => array(),
            'visibility' => '',
            'validators' => array()
        ));

        $config['type'] = 'date';

        return $config;
    }
}
