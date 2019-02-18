<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Honeypot extends Quform_Element_Field
{
    /**
     * Render this field and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $output = '<div class="quform-hidden"><label>';
        $output .= esc_html__('This field should be left blank', 'quform');
        $output .= $this->getFieldHtml($context);
        $output .= '</label></div>';

        return $output;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array $context
     * @return  string
     */
    protected function getFieldHtml(array $context = array())
    {
        return sprintf('<input type="text" name="%s" autocomplete="off" />', $this->getFullyQualifiedName());
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return apply_filters('quform_default_config_honeypot', array(
            'showInEmail' => false,
            'saveToDatabase' => false
        ));
    }
}
