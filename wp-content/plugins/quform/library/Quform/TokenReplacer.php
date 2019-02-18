<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_TokenReplacer
{
    /**
     * Newline for email content
     *
     * @var string
     */
    const NEWLINE = "\r\n";

    /**
     * @var Quform_Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param Quform_Options $options
     */
    public function __construct(Quform_Options $options)
    {
        $this->options = $options;
    }

    /**
     * Replace variables in the given text that are unaffected by form submission
     *
     * @param   string       $text
     * @param   string       $format
     * @param   Quform_Form  $form
     * @return  string
     */
    public function replaceVariablesPreProcess($text, $format = 'text', Quform_Form $form)
    {
        if ( ! Quform::isNonEmptyString($text)) {
            return '';
        }

        if (strpos($text, '{') === false) {
            return $text;
        }

        $this->format = $format;
        $this->form = $form;

        return preg_replace_callback('/({(.+?)})/', array($this, 'replaceVariablePreProcess'), $text);
    }

    /**
     * Handle the callback for replaceVariablesPreProcess
     *
     * @param   array   $matches
     * @return  string
     */
    protected function replaceVariablePreProcess($matches)
    {
        $replaced = $matches[1];
        $token = $this->parseToken($matches[2]);

        switch ($token['name']) {
            case 'site_title':
                $replaced = get_bloginfo('name');
                break;
            case 'site_tagline':
                $replaced = get_bloginfo('description');
                break;
            case 'ip':
                $replaced = Quform::getClientIp();
                break;
            case 'post':
                $replaced = Quform::getPostProperty(count($token['params']) ? key($token['params']) : 'ID');
                break;
            case 'custom_field':
                $replaced = Quform::getPostMeta(count($token['params']) ? key($token['params']) : '');
                break;
            case 'url':
                $replaced = Quform::getCurrentUrl();
                break;
            case 'user':
                $replaced = Quform::getUserProperty(count($token['params']) ? key($token['params']) : 'display_name');
                break;
            case 'referring_url': // TODO should this use entry source url if set
                $replaced = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                break;
            case 'user_agent':
                $replaced = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                break;
            case 'date':
                if (isset($token['params']['format'])) {
                    $format = $token['params']['format'];
                } else {
                    $locale = Quform::getLocale($this->form->config('locale'));
                    $format = $locale['dateFormat'];
                }
                $replaced = date_i18n($format);
                break;
            case 'time':
                if (isset($token['params']['format'])) {
                    $format = $token['params']['format'];
                } else {
                    $locale = Quform::getLocale($this->form->config('locale'));
                    $format = $locale['timeFormat'];
                }
                $replaced = date_i18n($format);
                break;
        }

        $replaced = apply_filters('quform_replace_variables_pre_process', $replaced, $token, $this->format);

        if ($this->format == 'url') {
            $replaced = urlencode($replaced);
        } elseif ($this->format == 'rawurl') {
            $replaced = rawurlencode($replaced);
        }

        return $replaced;
    }

    /**
     * Replace all variables in the given text
     *
     * @param                $text
     * @param   string       $format
     * @param   Quform_Form  $form
     * @return  string
     */
    public function replaceVariables($text, $format = 'text', Quform_Form $form)
    {
        if ( ! Quform::isNonEmptyString($text)) {
            return '';
        }

        if (strpos($text, '{') === false) {
            return $text;
        }

        $this->format = $format;
        $this->form = $form;

        return preg_replace_callback('/({(.+?)})/', array($this, 'replaceVariable'), $text);
    }

    /**
     * Handle the callback for replaceVariables
     *
     * @param   array   $matches
     * @return  string
     */
    protected function replaceVariable($matches)
    {
        $replaced = $original = $matches[1];
        $token = $this->parseToken($matches[2]);

        switch ($token['name']) {
            case 'post':
                $replaced = Quform::getPostProperty(count($token['params']) ? key($token['params']) : 'ID', Quform::get($_POST, 'post_id'));
                break;
            case 'custom_field':
                $replaced = Quform::getPostMeta(count($token['params']) ? key($token['params']) : '', Quform::get($_POST, 'post_id'));
                break;
            case 'referring_url':
                $replaced = Quform::get($_POST, 'referring_url');
                break;
            case 'default_email_address':
                $replaced = $this->options->get('defaultEmailAddress');
                break;
            case 'default_email_name':
                $replaced = $this->options->get('defaultEmailName');
                break;
            case 'default_from_email_address':
                $replaced = $this->options->get('defaultFromEmailAddress');
                break;
            case 'default_from_email_name':
                $replaced = $this->options->get('defaultFromEmailName');
                break;
            case 'admin_email':
                $replaced = get_bloginfo('admin_email');
                break;
            case 'element':
                $replaced = $this->replaceElement($token);
                break;
            case 'form_name':
                $replaced = $this->form->config('name');
                break;
            case 'entry_id':
                $replaced = $this->form->getEntryId();
                break;
            case 'all_form_data':
                $replaced = $this->replaceAllSubmittedData($token);
                break;
        }

        if ($replaced === $original) {
            $replaced = $this->replaceVariablesPreProcess($original, $this->format, $this->form);
        } else {
            $replaced = apply_filters('quform_replace_variables', $replaced, $token, $this->format, $this->form);

            if ($this->format == 'url') {
                $replaced = urlencode($replaced);
            } elseif ($this->format == 'rawurl') {
                $replaced = rawurlencode($replaced);
            }
        }

        return $replaced;
    }

    /**
     * Replace the element placeholder with its value
     *
     * @param   array   $token     The token parts
     * @return  string
     */
    protected function replaceElement($token)
    {
        $value = '';

        if (isset($token['params']['id'])) {
            $element = $this->form->getElementById($token['params']['id']);

            if ($element instanceof Quform_Element_Field && ! $element->isConditionallyHidden()) {
                $format = isset($token['params']['format']) ? $token['params']['format'] : $this->format;

                if ($format == 'html') {
                    $value = $element->getValueHtml();
                } else {
                    $separator = isset($token['params']['separator']) ? $token['params']['separator'] : ', ';
                    $value = $element->getValueText($separator);
                }
            }
        }

        return $value;
    }

    /**
     * Replace the token to display all submitted form data
     *
     * @param   array   $token
     * @return  string
     */
    protected function replaceAllSubmittedData($token)
    {
        $showEmptyFields = isset($token['params']['showEmptyFields']) && $token['params']['showEmptyFields'] === 'true' ? true : false;

        if ($this->format == 'html') {
            $content = $this->renderAllSubmittedDataHtml($showEmptyFields);
        } else {
            $content = $this->renderAllSubmittedDataText($showEmptyFields);
        }

        return $content;
    }

    /**
     * Render the submitted form data in plain text
     *
     * @param   bool    $showEmptyFields
     * @return  string
     */
    protected function renderAllSubmittedDataText($showEmptyFields)
    {
        $content = '';

        foreach ($this->form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                continue;
            }

            // Skip hidden elements
            if ($element->isHidden()) {
                continue;
            }

            // Skip empty elements
            if ($element->isEmpty() && ! $showEmptyFields) {
                continue;
            }

            if ($element instanceof Quform_Element_Html) {
                if ($element->config('showInEmail')) {
                    $content .= $element->getContent();
                    $content .= self::NEWLINE . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Group) {
                if ($element->config('showLabelInEmail') && Quform::isNonEmptyString($element->config('label'))) {
                    $content .= str_repeat('=', 25) . self::NEWLINE;
                    $content .= $element->config('label') . self::NEWLINE;
                    $content .= str_repeat('=', 25);
                    $content .= self::NEWLINE . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Field) {
                if ($element->config('showInEmail')) {
                    $content .= $element->getAdminLabel() . self::NEWLINE;
                    $content .= str_repeat('-', 25) . self::NEWLINE;
                    $content .= $element->getValueText(self::NEWLINE);
                    $content .= self::NEWLINE . self::NEWLINE;
                }
            }
        }

        return $content;
    }

    /**
     * Render the HTML for the table containing all submitted form data
     *
     * @param   bool    $showEmptyFields
     * @return  string
     */
    protected function renderAllSubmittedDataHtml($showEmptyFields)
    {
        $content = '<table width="100%" cellpadding="0" cellspacing="0" border="0">' . self::NEWLINE;
        $content .= '<tr><td valign="top" style="padding: 15px;">' . self::NEWLINE;
        $content .= '<table width="100%" cellpadding="10" cellspacing="0" border="0" style="background: #ffffff; border-bottom: 1px solid #d4d4d4; box-shadow: 0 2px 7px 0 rgba(0, 0, 0, 0.07);">' . self::NEWLINE;

        foreach ($this->form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                continue;
            }

            // Skip hidden elements
            if ($element->isHidden()) {
                continue;
            }

            // Skip empty elements
            if ($element->isEmpty() && ! $showEmptyFields) {
                continue;
            }

            if ($element instanceof Quform_Element_Html) {
                if ($element->config('showInEmail')) {
                    $content .= '<tr><td colspan="2" valign="top" style="font-family: Helvetica, Arial, sans-serif; font-size: 16px; color: #282828; line-height: 130%; padding-top: 15px; padding-bottom: 10px; background-color: #c73412; color: #fff;">' . $element->getContent() . '</td></tr>' . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Page) {
                if ($element->config('showLabelInEmail') && Quform::isNonEmptyString($element->config('label'))) {
                    $content .= '<tr><td colspan="2" valign="top" style="font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: bold; color: #282828; padding-top: 15px; padding-bottom: 10px; background-color: #c73412; color: #fff;">' . esc_html($element->config('label')) . '</td></tr>' . self::NEWLINE;
                }
            }  else if ($element instanceof Quform_Element_Group) {
                if ($element->config('showLabelInEmail') && Quform::isNonEmptyString($element->config('label'))) {
                    $content .= '<tr><td colspan="2" valign="top" style="font-family: Helvetica, Arial, sans-serif; font-size: 17px;  color: #282828; padding-top: 15px; padding-bottom: 10px; background-color: #c73412; color: #fff;">' . esc_html($element->config('label')) . '</td></tr>' . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Field) {
                if ($element->config('showInEmail')) {
                    $content .= '<tr bgcolor="#efefef"><td valign="top" style="font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: bold; color: #282828; border-top: 1px solid #d4d4d4; border-left: 1px solid #d4d4d4; border-right: 1px solid #d4d4d4;">' . esc_html($element->getAdminLabel()) . '</td></tr>' . self::NEWLINE;
                    $content .= '<tr bgcolor="#fcfcfc"><td valign="top" style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #282828; line-height: 130%; border-top: 1px solid #d4d4d4; border-bottom: 1px solid #fff; border-left: 1px solid #d4d4d4; border-right: 1px solid #d4d4d4;">' . $element->getValueHtml() . '</td></tr>' . self::NEWLINE;
                }
            }
        }

        $content .= '</table>' . self::NEWLINE;
        $content .= '</td></tr>' . self::NEWLINE;
        $content .= '</table>';

        return $content;
    }

    /**
     * Parse a single token into an array with the name and parameters
     *
     * @param   string  $token
     * @return  array
     */
    protected function parseToken($token)
    {
        $parts = explode('|', $token);

        // Remove token name and leave params in $parts
        $name = trim(array_shift($parts));

        // Build the params array
        $params = array();
        foreach ($parts as $part) {
            $paramParts = explode(':', $part, 2);
            $params[$paramParts[0]] = isset($paramParts[1]) ? $paramParts[1] : true; // A parameter without a value is just "true"
        }

        return array(
            'name' => $name,
            'params' => $params
        );
    }
}
