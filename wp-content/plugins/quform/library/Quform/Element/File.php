<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_File extends Quform_Element_Field implements Quform_Attachable, Quform_Element_Editable
{
    /**
     * The value which is an array of any uploaded files
     * @var array
     */
    protected $value = array();

    /**
     * @var bool
     */
    protected $isMultiple = true;

    /**
     * Is the uploaded file valid?
     *
     * @return boolean True if valid, false otherwise
     */
    public function isValid()
    {
        $this->clearError();
        $skipValidation = false;
        $valid = true;

        // Skip validating if there are no uploaded files and the element is not required
        if ( ! $this->isRequired()) {
            if ( ! array_key_exists($this->getName(), $_FILES)) {
                $skipValidation = true;
            }
        }

        // Skip validation if the element is conditionally hidden, or not visible (e.g. admin only)
        if ($this->isConditionallyHidden() || ! $this->isVisible()) {
            $skipValidation = true;
        }

        if ( ! $skipValidation) {
            $value = $this->getValue();

            foreach ($this->getValidators() as $validator) {
                if ($validator->isValid($value)) {
                    continue;
                }

                $this->error = $validator->getMessage();
                $valid = false;
                break;
            }

            $valid = apply_filters('quform_element_valid', $valid, $value, $this);
            $valid = apply_filters('quform_element_valid_' . $this->getIdentifier(), $valid, $value, $this);
        }

        return $valid;
    }

    /**
     * @return Quform_Validator_FileUpload
     */
    public function getFileUploadValidator()
    {
        return $this->getValidator('fileUpload');
    }

    /**
     * Gets whether the element is required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->getFileUploadValidator()->config('required');
    }

    /**
     * @return array
     */
    public function getEmptyValue()
    {
        return array();
    }

    /**
     * Get the value of this element
     *
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the value formatted in HTML
     *
     * @return  string
     */
    public function getValueHtml()
    {
        $output = '';

        if ( ! $this->isEmpty()) {
            $output = '<ul style="margin:0;">';
            foreach ($this->getValue() as $file) {
                if (isset($file['url'])) {
                    $output .= '<li><a href="' . esc_url($file['url']) . '">' . Quform::escape($file['name']) . '</a></li>';
                } else {
                    $output .= '<li>' . Quform::escape($file['name']) . '</li>';
                }
            }
            $output .= '</ul>';
        }

        return $output;
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator  The separator between values
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        $output = '';

        if ( ! $this->isEmpty()) {
            $arr = array();

            foreach ($this->getValue() as $file) {
                if (isset($file['url'])) {
                    $arr[] = $file['url'];
                } else {
                    $arr[] = $file['name'];
                }
            }

            $output = join($separator, $arr);
        }

        return $output;
    }

    /**
     * Add a file upload to the value
     *
     * @param array
     */
    public function addFile($file)
    {
        if ($this->isValidFile($file)) {
            $this->value[] = $file;
        }
    }

    /**
     * @param   array  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_array($value)) {
            return;
        }

        foreach ($value as $val) {
            if ( ! $this->isValidFile($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if the given file is a valid file array, false otherwise
     *
     * @param   array  $file
     * @return  bool
     */
    protected function isValidFile($file)
    {
        if ( ! is_array($file)) {
            return false;
        }

        foreach ($file as $data) {
            if ( ! is_string($data) && ! is_int($data)) {
                return false;
            }
        }

        return true;
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
     * Get the HTML attributes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldAttributes(array $context = array())
    {
        $attributes = array(
            'type' => 'file',
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context)),
            'tabindex' => '-1' // TODO set in JS?
        );

        $validator = $this->getFileUploadValidator();

        if ($validator->config('maximumNumberOfFiles') !== 1) {
            $attributes['multiple'] = true;
        }

        if (count($validator->config('allowedExtensions'))) {
            $attributes['accept'] = $this->convertExtensionsToAccept($validator->config('allowedExtensions'));
        }

        if ($this->form->config('ajax') && $this->config('enhancedUploadEnabled')) {
            $attributes['data-config'] = wp_json_encode($this->getUploaderConfig());
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
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
            'quform-field-file',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->form->config('ajax') && $this->config('enhancedUploadEnabled')) {
            $classes[] = 'quform-field-file-enhanced';
        }

        $classes = apply_filters('quform_field_classes', $classes, $this, $this->form, $context);
        $classes = apply_filters('quform_field_classes_' . $this->getIdentifier(), $classes, $this, $this->form, $context);

        return $classes;
    }

    /**
     * Get the enhanced upload config options
     *
     * @return array
     */
    protected function getUploaderConfig()
    {
        $validator = $this->getFileUploadValidator();

        $config = array(
            'id' => $this->getId(),
            'identifier' => $this->getIdentifier(),
            'name' => $this->getName(),
            'max' => $validator->config('maximumNumberOfFiles'),
            'size' => $validator->config('maximumFileSize'),
            'allowedExtensions' => $validator->config('allowedExtensions'),
            'notAllowedTypeWithFilename' => $validator->createMessage(Quform_Validator_FileUpload::NOT_ALLOWED_TYPE_FILENAME),
            'tooBigWithFilename' => $validator->createMessage(Quform_Validator_FileUpload::TOO_BIG_FILENAME),
            'tooMany' => $validator->createMessage(Quform_Validator_FileUpload::TOO_MANY),
            'buttonType' => $this->config('enhancedUploadStyle'),
            'buttonText' => $this->getTranslation('browseText', _x('Browse...', 'for a file to upload', 'quform')),
            'buttonIcon' => $this->config('buttonIcon'),
            'buttonIconPosition' => $this->config('buttonIconPosition')
        );

        return $config;
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
     * Get the classes for the element inner wrapper
     *
     * @param   array  $context
     * @return  array
     */
    protected function getInnerClasses(array $context = array())
    {
        $classes = parent::getInnerClasses($context);

        if (Quform::isNonEmptyString($this->config('uploadListLayout'))) {
            $classes[] = sprintf('quform-upload-files-%s', $this->config('uploadListLayout'));
        }

        if (Quform::isNonEmptyString($this->config('uploadListSize'))) {
            $classes[] = sprintf('quform-upload-files-size-%s', $this->config('uploadListSize'));
        }

        return $classes;
    }

    /**
     * Get the classes for the element input wrapper
     *
     * @param   array  $context
     * @return  array
     */
    protected function getInputClasses(array $context = array())
    {
        $classes = parent::getInputClasses($context);

        if (Quform::isNonEmptyString($context['buttonStyle'])) {
            $classes[] = sprintf('quform-button-style-%s', $context['buttonStyle']);
        }

        if (Quform::isNonEmptyString($context['buttonSize'])) {
            $classes[] = sprintf('quform-button-size-%s', $context['buttonSize']);
        }

        if (Quform::isNonEmptyString($context['buttonWidth']) && $context['buttonWidth'] != 'custom') {
            $classes[] = sprintf('quform-button-width-%s', $context['buttonWidth']);
        }

        return $classes;
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
        $output .= $this->getFieldHtml();
        $output .= '</div>';

        if ( ! $this->isEmpty()) {
            $output .= '<div class="quform-upload-files quform-cf">';

            foreach ($this->getValue() as $file) {
                $output .= sprintf(
                    '<div class="quform-upload-file"><span class="quform-upload-file-name">%s</span></div>',
                    Quform::escape($file['name'])
                );
            }

            $output .= '</div>';
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
        $output = sprintf('<div class="qfb-edit-file-uploads%s">', count($this->getValue()) ? '' : ' qfb-hidden');

        foreach ($this->getValue() as $file) {
            $output .= sprintf(
                '<div class="qfb-edit-file-upload qfb-cf" data-quform-upload-uid="%s"><div class="qfb-edit-file-upload-inner">',
                $file['quform_upload_uid']
            );

            $output .= sprintf(
                '<span class="qfb-edit-file-upload-name">%s</span>',
                Quform::escape($file['name'])
            );

            $output .= sprintf(
                '<span class="qfb-edit-file-upload-remove" title="%s"><i class="fa fa-trash"></i></span>',
                esc_attr__('Remove this file')
            );

            $output .= '</div></div>';
        }

        $output .= '</div>';

        $output .= $this->getFieldHtml();

        return $output;
    }

    /**
     * Convert the array of extensions into an input file accept string
     *
     * @param   array   $extensions
     * @return  string
     */
    protected function convertExtensionsToAccept(array $extensions)
    {
        foreach ($extensions as $key => $value) {
            $extensions[$key] = '.' . $value;
        }

        $accept = join(',', $extensions);

        return $accept;
    }

    /**
     * @return bool
     */
    public function hasAttachments()
    {
        return count($this->getAttachments()) > 0;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        $attachments = array();

        foreach ($this->getValue() as $file) {
            $attachments[] = $file['path'];
        }

        return $attachments;
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
            $css .= sprintf('.quform-input-file.quform-input-%1$s, .quform-enhanced-upload .quform-input-file.quform-input-%1$s .quform-upload-dropzone { width: %2$s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        if ($context['buttonWidth'] == 'custom' && Quform::isNonEmptyString($context['buttonWidthCustom'])) {
            $css .= sprintf('.quform-input-file.quform-input-%s > .quform-upload-button { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['buttonWidthCustom']));
        }

        return $css;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return parent::getCssSelectors() + array(
            'uploadButton' => '%s .quform-input-%s .quform-upload-button',
            'uploadButtonHover' => '%s .quform-input-%s .quform-upload-button:hover',
            'uploadButtonActive' => '%s .quform-input-%s .quform-upload-button:active',
            'uploadButtonText' => '%s .quform-input-%s .quform-upload-button .quform-upload-button-text',
            'uploadButtonTextHover' => '%s .quform-input-%s .quform-upload-button:hover .quform-upload-button-text',
            'uploadButtonTextActive' => '%s .quform-input-%s .quform-upload-button:active .quform-upload-button-text',
            'uploadButtonIcon' => '%s .quform-input-%s .quform-upload-button .quform-upload-button-icon',
            'uploadButtonIconHover' => '%s .quform-input-%s .quform-upload-button:hover .quform-upload-button-icon',
            'uploadButtonIconActive' => '%s .quform-input-%s .quform-upload-button:active .quform-upload-button-icon'
        );
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
        $config = apply_filters('quform_default_config_file', array(
            'label' => __('Upload', 'quform'),
            'subLabel' => '',
            'description' => '',
            'descriptionAbove' => '',
            'required' => false,
            'multiple' => false,
            'minimumNumberOfFiles' => '0',
            'maximumNumberOfFiles' => '1',
            'tooltip' => '',
            'labelIcon' => '',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'buttonStyle' => 'inherit',
            'buttonSize' => 'inherit',
            'buttonWidth' => 'inherit',
            'buttonWidthCustom' => '',
            'buttonIcon' => 'qicon-file_upload',
            'buttonIconPosition' => 'right',
            'uploadListLayout' => '',
            'uploadListSize' => '',
            'adminLabel' => '',
            'showInEmail' => true,
            'saveToDatabase' => true,
            'labelPosition' => 'inherit',
            'labelWidth' => '',
            'tooltipType' => 'icon',
            'tooltipEvent' => 'inherit',
            'saveToServer' => true,
            'enhancedUploadEnabled' => true,
            'enhancedUploadStyle' => 'button',
            'allowedExtensions' => 'jpg, jpeg, png, gif',
            'maximumFileSize' => '10',
            'savePath' => 'quform/{form_id}/{year}/{month}/',
            'browseText' => '',
            'messageFileUploadRequired' => '',
            'messageFileNumRequired' => '',
            'messageFileTooMany' => '',
            'messageFileTooBigFilename' => '',
            'messageFileTooBig' => '',
            'messageNotAllowedTypeFilename' => '',
            'messageNotAllowedType' => '',
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'styles' => array(),
            'visibility' => ''
        ));

        $config['type'] = 'file';

        return $config;
    }
}
