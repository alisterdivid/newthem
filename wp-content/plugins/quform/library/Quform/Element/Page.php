<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Page extends Quform_Element_Group
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
        $output .= $this->getTitleDescriptionHtml();
        $output .= '<div class="quform-child-elements">';

        foreach ($this->elements as $key => $element) {
            $output .= $element->render($context);
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Get the classes for the outermost page wrapper
     *
     * @return array
     */
    protected function getContainerClasses()
    {
        $classes = array(
            'quform-element',
            'quform-element-page',
            sprintf('quform-page-%s', $this->getId()),
            sprintf('quform-page-%s', $this->getIdentifier()),
            'quform-cf',
            sprintf('quform-group-style-%s', $this->config('groupStyle'))
        );

        if ($this->isFirstPage()) {
            $classes[] = 'quform-first-page';
        }

        if ($this->isLastPage()) {
            $classes[] = 'quform-last-page';
        }

        if ($this->isCurrentPage()) {
            $classes[] = 'quform-current-page';
        }

        return $classes;
    }

    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->form->getFirstPage() == $this;
    }

    /**
     * @return bool
     */
    public function isLastPage()
    {
        return $this->form->getLastPage() == $this;
    }

    /**
     * @return bool
     */
    public function isCurrentPage()
    {
        return $this->form->getCurrentPage() == $this;
    }

    /**
     * Is this page valid
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        foreach ($this->getRecursiveIterator() as $element) {
            if ( ! $element instanceof Quform_Element_Field) {
                continue;
            }

            if ( ! $element->isValid()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Get the validation errors that exist on this page
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = array();

        foreach ($this->getRecursiveIterator() as $element) {
            if ( ! $element instanceof Quform_Element_Field) {
                continue;
            }

            if ($element->hasError()) {
                foreach ($element->getErrorArray() as $identifier => $message) {
                    $errors[$identifier] = $message;
                }
            }
        }

        return $errors;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return array(
            'page' => '%s .quform-page-%s',
            'pageTitle' => '%s .quform-page-%s .quform-page-title',
            'pageDescription' => '%s .quform-page-%s .quform-page-description',
            'pageElements' => '%s .quform-page-%s > .quform-child-elements'
        );
    }

    /**
     * Get the default element configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = apply_filters('quform_default_config_page', array(
            'label' => __('Untitled page', 'quform'),
            'title' => '',
            'titleTag' => 'h3',
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

        $config['type'] = 'page';

        return $config;
    }
}
