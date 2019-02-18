<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Name extends Quform_Element_Field implements Quform_Element_Editable
{
    /**
     * @var array
     */
    static $partKeys = array(
        1 => 'prefix',
        2 => 'first',
        3 => 'middle',
        4 => 'last',
        5 => 'suffix'
    );

    /**
     * @var Quform_Element_Field[]
     */
    protected $parts = array();

    /**
     * @var array
     */
    protected $value = array();

    /**
     * Prepare the dynamic default value
     *
     * TODO handle URL param to value
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
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);

        $value = $this->getValue();

        foreach (self::$partKeys as $key => $slug) {
            if (array_key_exists($key, $this->parts) && array_key_exists($key, $value)) {
                $this->parts[$key]->setValue($value[$key]);
            }
        }
    }

    /**
     * Get the filtered value
     *
     * @return array The filtered value
     */
    public function getValue()
    {
        $value = $this->value;

        $this->filterValueRecursive($value);

        return $value;
    }

    /**
     * Get the value formatted in HTML
     *
     * @return string
     */
    public function getValueHtml()
    {
        return Quform::escape($this->getValueText());
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator  The separator for array types (not used here)
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        $nonEmptyParts = array();

        foreach ($this->getValue() as $value) {
            if (Quform::isNonEmptyString($value)) {
                $nonEmptyParts[] = $value;
            }
        }

        return join(' ', $nonEmptyParts);
    }

    /**
     * Is the given value valid for this element type
     *
     * @param   array  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $val) {
            if ( ! array_key_exists($key, self::$partKeys) || ! parent::isValidValue($val)) {
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
        return $this->getValueText() === $value;
    }

    /**
     * @return array
     */
    public function getEmptyValue()
    {
        return array();
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
     * @return bool
     */
    public function isRequired()
    {
        $required = false;

        foreach ($this->parts as $part) {
            if ($part->isRequired()) {
                $required = true;
            }
        }

        return $required;
    }

    /**
     * If the value is not an array or is an empty array it's empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->hasValue('');
    }

    /**
     * Set a part by name
     *
     * @param  $name
     * @param  Quform_Element_Field  $element
     */
    public function setPart($name, Quform_Element_Field $element)
    {
        $this->parts[$name] = $element;
    }

    /**
     * Get a part by name
     *
     * @param   string  $name  The name of the part
     * @return  Quform_Element_Field|null
     */
    public function getPart($name)
    {
        return isset($this->parts[$name]) ? $this->parts[$name] : null;
    }

    /**
     * Is this element valid?
     *
     * TODO need to check if it works inside conditional logic hidden, the parts might not be flagged as hidden
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        if ( ! parent::isValid()) {
            $valid = false;
        }

        // TODO need skip validation checks , or not visible (e.g. admin only)

        foreach ($this->parts as $part) {
            if ( ! $part->isValid()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        $hasError = false;

        if (parent::hasError()) {
            $hasError = true;
        }

        foreach ($this->parts as $part) {
            if ($part->hasError()) {
                $hasError = true;
            }
        }

        return $hasError;
    }

    /**
     * @return array
     */
    public function getErrorArray()
    {
        $errors = array();

        if (parent::hasError()) {
            $errors[$this->getIdentifier()] = $this->getError();
        }

        foreach ($this->parts as $part) {
            if ($part->hasError()) {
                $errors[$part->getIdentifier()] = $part->getError();
            }
        }

        return $errors;
    }

    /**
     * Get the HTML for the element label
     *
     * @param   array        $context
     * @param   string|bool  $forAttribute  Set the "for" attribute to the element unique ID
     * @param   bool         $id            Add a unique ID to the label
     * @return  string
     */
    protected function getLabelHtml(array $context = array(), $forAttribute = true, $id = false)
    {
        return parent::getLabelHtml($context, false, true);
    }

    /**
     * Get the HTML for the element input wrapper
     *
     * @param   array   $context
     * @return  string
     */
    protected function getInputHtml(array $context = array())
    {
        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getInputClasses($context))));
        $output .= sprintf(
            '<div class="quform-element-row %s quform-%d-columns">',
            Quform::isNonEmptyString($context['fieldWidth']) ? 'quform-element-row-size-float' : 'quform-element-row-size-fixed', // For non-100% input widths use float class
            count($this->parts)
        );
        $output .= $this->getFieldHtml($context);
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array   $context
     * @return  string
     */
    protected function getFieldHtml(array $context = array())
    {
        $output = '';

        foreach (self::$partKeys as $key => $slug) {
            if ($this->config($slug . 'Enabled') && ($part  = $this->getPart($key)) instanceof Quform_Element) {
                $ariaLabelledby = array();

                if (Quform::isNonEmptyString($this->config('label'))) {
                    $ariaLabelledby[] = $this->getUniqueId() . '_label';
                }

                if (Quform::isNonEmptyString($part->config('subLabel'))) {
                    $ariaLabelledby[] = $part->getUniqueId() . '_sub_label';
                }

                if (count($ariaLabelledby)) {
                    $part->setConfig('aria-labelledby', join(' ', $ariaLabelledby));
                }

                $output .= sprintf('<div class="quform-element-column">%s</div>', $part->render($context));
            }
        }

        return $output;
    }

    /**
     * Get the field HTML when editing
     *
     * @return string
     */
    public function getEditFieldHtml()
    {
        $output = sprintf('<div class="qfb-edit-name-row qfb-edit-name-row-%d">', count($this->parts));

        foreach (self::$partKeys as $key => $slug) {
            if ($this->config($slug . 'Enabled') && $this->getPart($key) instanceof Quform_Element_Editable) {
                $part = $this->getPart($key);

                $output .= sprintf(
                    '<div class="qfb-edit-name-column"><div class="qfb-edit-element qfb-edit-element-%1$s"><div class="qfb-edit-input qfb-edit-input-%1$s">%2$s</div></div></div>',
                    $part->getIdentifier(),
                    $part->getEditFieldHtml()
                );
            }
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render the CSS for this field
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = parent::renderCss($context);

        if ($context['fieldWidth'] == 'custom' && Quform::isNonEmptyString($context['fieldWidthCustom'])) {
            $css .= sprintf('.quform-input-name.quform-input-%s { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-input-name.quform-input-%s .quform-inner > .quform-input { width: 100%% !important; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        foreach ($this->parts as $part) {
            $css .= $part->getCss($context);
        }

        return $css;
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
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_name', array(
            'label' => __('Name', 'quform'),
            'subLabel' => '',
            'description' => '',
            'descriptionAbove' => '',
            'prefixEnabled' => false,
            'prefixRequired' => false,
            'prefixOptions' => array(
                array('id' => 1, 'label' => __('Mr', 'quform'), 'value' => __('Mr', 'quform')),
                array('id' => 2, 'label' => __('Mrs', 'quform'), 'value' => __('Mrs', 'quform')),
                array('id' => 3, 'label' => __('Ms', 'quform'), 'value' => __('Ms', 'quform')),
                array('id' => 4, 'label' => __('Miss', 'quform'), 'value' => __('Miss', 'quform')),
                array('id' => 5, 'label' => __('Dr', 'quform'), 'value' => __('Dr', 'quform')),
            ),
            'prefixNextOptionId' => 6,
            'prefixDefaultValue' => '',
            'prefixCustomiseValues' => false,
            'prefixNoneOption' => true,
            'prefixNoneOptionText' => '',
            'prefixSubLabel' => __('Prefix', 'quform'),
            'firstEnabled' => true,
            'firstRequired' => false,
            'firstPlaceholder' => '',
            'firstSubLabel' => __('First', 'quform'),
            'middleEnabled' => false,
            'middleRequired' => false,
            'middlePlaceholder' => '',
            'middleSubLabel' => __('Middle', 'quform'),
            'lastEnabled' => true,
            'lastRequired' => false,
            'lastPlaceholder' => '',
            'lastSubLabel' => __('Last', 'quform'),
            'suffixEnabled' => false,
            'suffixRequired' => false,
            'suffixPlaceholder' => '',
            'suffixSubLabel' => __('Suffix', 'quform'),
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'tooltip' => '',
            'labelIcon' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'customClass' => '',
            'adminLabel' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,
            'labelPosition' => 'inherit',
            'labelWidth' => '',
            'tooltipType' => 'icon',
            'tooltipEvent' => 'inherit',
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'messageRequired' => '',
            'styles' => array(),
            'visibility' => '',
            'validators' => array()
        ));

        $config['type'] = 'name';

        return $config;
    }
}
