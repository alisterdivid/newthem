<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Group extends Quform_Element_Container
{
    /**
     * Render this group and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $context = $this->prepareContext($context);

        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getContainerClasses())));
        $output .= '<div class="quform-spacer">';
        $output .= $this->getTitleDescriptionHtml();
        $output .= '<div class="quform-child-elements">';

        foreach ($this->elements as $key => $element) {
            $output .= $element->render($context);
        }

        $output .= '</div></div></div>';

        return $output;
    }

    /**
     * Get the classes for the outermost group wrapper
     *
     * @return array
     */
    protected function getContainerClasses()
    {
        return array(
            'quform-element',
            'quform-element-group',
            sprintf('quform-element-%s', $this->getIdentifier()),
            'quform-cf',
            sprintf('quform-group-style-%s', $this->config('groupStyle'))
        );
    }

    /**
     * Render the CSS for this group and its children
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        if ($this->config('groupStyle') == 'bordered' && ($this->config('borderColor') || $this->config('backgroundColor'))) {
            $css .= sprintf('.quform .quform-group-style-bordered.quform-element-%1$s > .quform-spacer > .quform-child-elements,
                 .quform .quform-group-style-bordered.quform-page-%1$s > .quform-child-elements {', $this->getIdentifier());

            if ($this->config('borderColor')) {
                $css .= 'border-color: ' . esc_attr($this->config('borderColor')) . '!important;';
            }
            if ($this->config('backgroundColor')) {
                $css .= 'background-color: ' . esc_attr($this->config('backgroundColor')) . '!important;';
            }

            $css .= '}';
        }

        $css .= parent::renderCss($context);

        return $css;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return array(
            'group' => '%s .quform-element-%s',
            'groupTitle' => '%s .quform-element-%s .quform-group-title',
            'groupDescription' => '%s .quform-element-%s .quform-group-description',
            'groupElements' => '%s .quform-element-%s > .quform-child-elements'
        );
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_group', array(
            'label' => __('Untitled group', 'quform'),
            'title' => '',
            'titleTag' => 'h4',
            'description' => '',
            'descriptionAbove' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'groupStyle' => 'plain',
            'borderColor' => '',
            'backgroundColor' => '',
            'labelPosition' => 'inherit',
            'labelWidth' => '',
            'showLabelInEmail' => false,
            'showLabelInEntry' => false,
            'tooltipType' => 'inherit',
            'tooltipEvent' => 'inherit',
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'styles' => array(),
            'elements' => array()
        ));

        $config['type'] = 'group';

        return $config;
    }
}
