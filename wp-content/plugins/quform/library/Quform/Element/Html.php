<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Html extends Quform_Element
{
    /**
     * Render this element and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $output = '';

        if ($this->isVisible()) {
            $output .= sprintf('<div class="quform-element quform-element-html quform-element-%s quform-cf">', $this->getIdentifier());
            $output .= sprintf('<div class="quform-spacer">%s</div>', $this->getContent());
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the HTML content
     */
    public function getContent()
    {
        $content = $this->config('content');

        if ($this->config('autoFormat')) {
            $content = nl2br($content);
        }

        $content = $this->form->replaceVariablesPreProcess($content);

        $content = do_shortcode($content);

        return $content;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getContent() === '';
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_html', array(
            'label' => __('HTML', 'quform'),
            'content' => '',
            'autoFormat' => false,
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'showInEmail' => false,
            'showInEntry' => false,
            'visibility' => ''
        ));

        $config['type'] = 'html';

        return $config;
    }
}
