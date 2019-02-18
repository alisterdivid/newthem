<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Column extends Quform_Element_Container
{
    /**
     * Render the column and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $context = $this->prepareContext($context);

        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getContainerClasses())));

        foreach ($this->elements as $key => $element) {
            $output .= $element->render($context);
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the classes for the outermost column wrapper
     *
     * @return array
     */
    protected function getContainerClasses()
    {
        return array(
            'quform-element',
            'quform-element-column',
            sprintf('quform-element-%s', $this->getIdentifier())
        );
    }

    /**
     * Render the CSS for this element and its children
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        if (Quform::isNonEmptyString($this->config('width'))) {
            $css .= sprintf('.quform-element-row > .quform-element-column.quform-element-%s { width:%s%%;%s }',
                $this->getIdentifier(),
                $this->config('width'),
                Quform::get($context, 'columnSize') == 'float' ? 'max-width: 100%;' : ''
            );
        }

        $css .= parent::renderCss($context);

        return $css;
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_column', array(
            'width' => '',
            'elements' => array()
        ));

        $config['type'] = 'column';

        return $config;
    }
}