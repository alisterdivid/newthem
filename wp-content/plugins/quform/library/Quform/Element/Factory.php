<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Factory
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param  Quform_Options     $options
     * @param  Quform_Session     $session
     * @param  Quform_Repository  $repository
     */
    public function __construct(Quform_Options $options, Quform_Session $session, Quform_Repository $repository)
    {
        $this->options = $options;
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * Create and configure a form element according to the given config
     *
     * @param   array                $config  The element configuration
     * @param   Quform_Form          $form    The form instance
     * @return  Quform_Element|null           The element instance or null if the config is invalid
     */
    public function create(array $config, Quform_Form $form)
    {
        if (isset($config['type'])) {
            $type = $config['type'];

            $method = 'create' . ucfirst($type) . 'Element';
            if (method_exists($this, $method)) {
                return call_user_func_array(array($this, $method), array($config, $form));
            }

            $element = apply_filters('quform_create_element_' . $type, null, $config, $form, $this);

            if ( ! $element instanceof Quform_Element) {
                throw new InvalidArgumentException(sprintf("Method not found to create element of type '%s'", $type));
            }
        }

        return null;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Text
     */
    protected function createTextElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Text($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Textarea
     */
    protected function createTextareaElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Textarea($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @return  Quform_Element_Email
     */
    protected function createEmailElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Email($config['id'], $form);

        $this->configureField($element, $config, $form);

        $options = array();
        $invalidEmailMessage = $this->getConfigValue($config, 'messageEmailAddressInvalidFormat', $element);
        if (Quform::isNonEmptyString($invalidEmailMessage)) {
            $options['messages'][Quform_Validator_Email::INVALID_FORMAT] = $invalidEmailMessage;
        }

        $element->addValidator(new Quform_Validator_Email($options));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Select
     */
    protected function createSelectElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Select($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Checkbox
     */
    protected function createCheckboxElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Checkbox($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @return  Quform_Element_Radio
     */
    protected function createRadioElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Radio($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                       $config
     * @param   Quform_Form                 $form
     * @return  Quform_Element_Multiselect
     */
    protected function createMultiselectElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Multiselect($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                   $config
     * @param   Quform_Form             $form
     * @return  Quform_Element_Captcha
     */
    protected function createCaptchaElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Captcha($config['id'], $form, $this->session);

        $element->addValidator(new Quform_Validator_Required());

        $options = array(
            'session' => $this->session,
            'sessionKey' => $form->getSessionKey() . '.captcha.' . $element->getName()
        );

        $notMatchMessage = $this->getConfigValue($config, 'messageCaptchaNotMatch', $element);
        if (Quform::isNonEmptyString($notMatchMessage)) {
            $options['messages'][Quform_Validator_Captcha::NOT_MATCH] = $notMatchMessage;
        }

        $element->addValidator(new Quform_Validator_Captcha($options));

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Page
     */
    protected function createPageElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Page($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @return  Quform_Element_Group
     */
    protected function createGroupElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Group($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array               $config
     * @param   Quform_Form         $form
     * @return  Quform_Element_Row
     */
    protected function createRowElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Row($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Column
     */
    protected function createColumnElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Column($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Submit
     */
    protected function createSubmitElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Submit($config['id'], $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_File
     */
    protected function createFileElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_File($config['id'], $form);

        $this->configureField($element, $config, $form);

        $options = array(
            'name' => $element->getName(),
            'required' => $this->getConfigValue($config, 'required', $element),
            'allowAllFileTypes' => $this->options->get('allowAllFileTypes')
        );

        $allowedExtensions = array();
        $allowedExtensionsStr = $this->getConfigValue($config, 'allowedExtensions', $element);

        if (Quform::isNonEmptyString($allowedExtensionsStr)) {
            $allowedExtensions = explode(',', $allowedExtensionsStr);
            $allowedExtensions = array_map('trim', array_map('strtolower', $allowedExtensions));
        }

        $options['allowedExtensions'] = $allowedExtensions;

        $maximumFileSize = $this->getConfigValue($config, 'maximumFileSize', $element);
        if (is_numeric($maximumFileSize)) {
            $options['maximumFileSize'] = $maximumFileSize * 1048576;
        }

        $minimumNumberOfFiles = $this->getConfigValue($config, 'minimumNumberOfFiles', $element);
        if (is_numeric($minimumNumberOfFiles)) {
            $options['minimumNumberOfFiles'] = (int) $minimumNumberOfFiles;

            if ($options['minimumNumberOfFiles'] > 0) {
                $options['required'] = true;
            }
        }

        $maximumNumberOfFiles = $this->getConfigValue($config, 'maximumNumberOfFiles', $element);
        if (is_numeric($maximumNumberOfFiles)) {
            $options['maximumNumberOfFiles'] = (int) $maximumNumberOfFiles;
        }

        $messageMap = array(
            'messageFileUploadRequired' => Quform_Validator_FileUpload::REQUIRED,
            'messageFileNumRequired' => Quform_Validator_FileUpload::NUM_REQUIRED,
            'messageFileTooMany' => Quform_Validator_FileUpload::TOO_MANY,
            'messageFileTooBigFilename' => Quform_Validator_FileUpload::TOO_BIG_FILENAME,
            'messageFileTooBig' => Quform_Validator_FileUpload::TOO_BIG,
            'messageNotAllowedTypeFilename' => Quform_Validator_FileUpload::NOT_ALLOWED_TYPE_FILENAME,
            'messageNotAllowedType' => Quform_Validator_FileUpload::NOT_ALLOWED_TYPE
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = $this->getConfigValue($config, $configKey, $element);

            if (Quform::isNonEmptyString($message)) {
                $options['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_FileUpload($options));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                     $config
     * @param   Quform_Form               $form
     * @return  Quform_Element_Recaptcha
     */
    protected function createRecaptchaElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Recaptcha($config['id'], $form);

        $element->addValidator(new Quform_Validator_Required());

        $options = array(
            'secretKey' => $this->options->get('recaptchaSecretKey')
        );

        $messageMap = array(
            'messageRecaptchaMissingInputSecret' => Quform_Validator_Recaptcha::MISSING_INPUT_SECRET,
            'messageRecaptchaInvalidInputSecret' => Quform_Validator_Recaptcha::INVALID_INPUT_SECRET,
            'messageRecaptchaMissingInputResponse' => Quform_Validator_Recaptcha::MISSING_INPUT_RESPONSE,
            'messageRecaptchaInvalidInputResponse' => Quform_Validator_Recaptcha::INVALID_INPUT_RESPONSE,
            'messageRecaptchaError' => Quform_Validator_Recaptcha::ERROR
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = Quform::get($config, $configKey);

            if (Quform::isNonEmptyString($message)) {
                $options['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_Recaptcha($options));

        $this->configureField($element, $config, $form);

        $config['recaptchaSiteKey'] = $this->options->get('recaptchaSiteKey');

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Html
     */
    protected function createHtmlElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Html($config['id'], $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Date
     */
    protected function createDateElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Date($config['id'], $form);

        $this->configureField($element, $config, $form);

        $dateLocale = $this->getConfigValue($config, 'dateLocale', $element);
        $config['dateLocale'] = Quform::isNonEmptyString($dateLocale) ? $dateLocale : $form->getLocale();

        $config['dateFormat'] = $this->getConfigValue($config, 'dateFormat', $element);
        if ( ! Quform::isNonEmptyString($config['dateFormat'])) {
            $config['dateFormat'] = $this->options->get('dateFormat');

            if ( ! Quform::isNonEmptyString($config['dateFormat'])) {
                $locale = Quform::getLocale($config['dateLocale']);
                $config['dateFormat'] = $locale['dateFormat'];
            }
        }

        $validatorOptions = array('format' => $config['dateFormat']);

        $dateMin = $this->getConfigValue($config, 'dateMin', $element);
        if (Quform::isNonEmptyString($dateMin)) {
            $validatorOptions['min'] = $dateMin;
        }

        $dateMax = $this->getConfigValue($config, 'dateMax', $element);
        if (Quform::isNonEmptyString($dateMax)) {
            $validatorOptions['max'] = $dateMax;
        }

        $messageMap = array(
            'messageDateInvalidDate' => Quform_Validator_Date::INVALID_DATE,
            'messageDateTooEarly' => Quform_Validator_Date::TOO_EARLY,
            'messageDateTooLate' => Quform_Validator_Date::TOO_LATE
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = Quform::get($config, $configKey);

            if (Quform::isNonEmptyString($message)) {
                $validatorOptions['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_Date($validatorOptions));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Time
     */
    protected function createTimeElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Time($config['id'], $form);

        $this->configureField($element, $config, $form);

        $timeLocale = $this->getConfigValue($config, 'timeLocale', $element);
        $config['timeLocale'] = Quform::isNonEmptyString($timeLocale) ? $timeLocale : $form->getLocale();

        $config['timeFormat'] = $this->getConfigValue($config, 'timeFormat', $element);
        if ( ! Quform::isNonEmptyString($config['timeFormat'])) {
            $config['timeFormat'] = $this->options->get('timeFormat');

            if ( ! Quform::isNonEmptyString($config['timeFormat'])) {
                $locale = Quform::getLocale($config['timeLocale']);
                $config['timeFormat'] = $locale['timeFormat'];
            }
        }

        $validatorOptions = array(
            'format' => $config['timeFormat']
        );

        $config['timeMin'] = $this->getConfigValue($config, 'timeMin', $element);
        if (Quform::isNonEmptyString($config['timeMin'])) {
            $validatorOptions['min'] = $config['timeMin'];
        }

        $config['timeMax'] = $this->getConfigValue($config, 'timeMax', $element);
        if (Quform::isNonEmptyString($config['timeMax'])) {
            $validatorOptions['max'] = $config['timeMax'];
        }

        $interval = $this->getConfigValue($config, 'timeInterval', $element);
        if (Quform::isNonEmptyString($interval)) {
            $interval = (string) Quform::clamp($interval, 1, 60);

            $config['timeInterval'] = $interval;
            $validatorOptions['interval'] = $interval;
        } else {
            $config['timeInterval'] = '30';
            $validatorOptions['interval'] = '30';
        }

        $messageMap = array(
            'messageTimeInvalidTime' => Quform_Validator_Time::INVALID_TIME,
            'messageTimeTooEarly' => Quform_Validator_Time::TOO_EARLY,
            'messageTimeTooLate' => Quform_Validator_Time::TOO_LATE
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = Quform::get($config, $configKey);

            if (Quform::isNonEmptyString($message)) {
                $validatorOptions['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_Time($validatorOptions));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Hidden
     */
    protected function createHiddenElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Hidden($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Honeypot
     */
    protected function createHoneypotElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Honeypot($config['id'], $form);

        $element->addValidator(new Quform_Validator_Honeypot());

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Password
     */
    protected function createPasswordElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Password($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Name
     */
    protected function createNameElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Name($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        if ($element->config('prefixEnabled')) {
            $prefixElement = $this->createSelectElement(array(
                'id' => 1,
                'label' => '',
                'required' => $element->config('prefixRequired'),
                'options' => $element->config('prefixOptions'),
                'noneOption' => $element->config('prefixNoneOption'),
                'noneOptionText' => $element->config('prefixNoneOptionText'),
                'subLabel' => $element->config('prefixSubLabel'),
                'defaultValue' => $element->config('prefixDefaultValue'),
                'customClass' => $element->config('customClass')
            ), $form);

            $prefixElement->setBelongsTo($element);

            $element->setPart('1', $prefixElement);
        }

        if ($element->config('firstEnabled')) {
            $firstNameElement = $this->createTextElement(array(
                'id' => 2,
                'label' => '',
                'required' => $element->config('firstRequired'),
                'placeholder' => $element->config('firstPlaceholder'),
                'subLabel' => $element->config('firstSubLabel'),
                'customClass' => $element->config('customClass')
            ), $form);

            $firstNameElement->setBelongsTo($element);

            $element->setPart('2', $firstNameElement);
        }

        if ($element->config('middleEnabled')) {
            $middleNameElement = $this->createTextElement(array(
                'id' => 3,
                'label' => '',
                'required' => $element->config('middleRequired'),
                'placeholder' => $element->config('middlePlaceholder'),
                'subLabel' => $element->config('middleSubLabel'),
                'customClass' => $element->config('customClass')
            ), $form);

            $middleNameElement->setBelongsTo($element);

            $element->setPart('3', $middleNameElement);
        }

        if ($element->config('lastEnabled')) {
            $lastNameElement = $this->createTextElement(array(
                'id' => 4,
                'label' => '',
                'required' => $element->config('lastRequired'),
                'placeholder' => $element->config('lastPlaceholder'),
                'subLabel' => $element->config('lastSubLabel'),
                'customClass' => $element->config('customClass')
            ), $form);

            $lastNameElement->setBelongsTo($element);

            $element->setPart('4', $lastNameElement);
        }

        if ($element->config('suffixEnabled')) {
            $suffixElement = $this->createTextElement(array(
                'id' => 5,
                'label' => '',
                'required' => $element->config('suffixRequired'),
                'placeholder' => $element->config('suffixPlaceholder'),
                'subLabel' => $element->config('suffixSubLabel'),
                'customClass' => $element->config('customClass')
            ), $form);

            $suffixElement->setBelongsTo($element);

            $element->setPart('5', $suffixElement);
        }

        return $element;
    }

    /**
     * Configure container elements (Group, Page)
     *
     * @param   Quform_Element_Container  $container
     * @param   array                     $config
     * @param   Quform_Form               $form
     * @return  array
     */
    protected function configureContainer(Quform_Element_Container $container, array $config, Quform_Form $form)
    {
        $elements = $this->getConfigValue($config, 'elements', $container);

        if (is_array($elements)) {
            foreach ($elements as $eConfig) {
                $element = $this->create($eConfig, $form);

                if ($element instanceof Quform_Element) {
                    if (in_array($form->config('environment'), array('viewEntry', 'editEntry', 'listEntry')) && in_array(get_class($element), array('Quform_Element_Captcha', 'Quform_Element_Recaptcha'))) {
                        // Ignore captcha when interacting with entries
                        continue;
                    }

                    $container->addElement($element);
                }
            }
        }

        return $config;
    }

    /**
     * Configure common field settings
     *
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @param   Quform_Element_Field  $element
     */
    public function configureField(Quform_Element_Field $element, array $config, Quform_Form $form)
    {
        if ( ! in_array(get_class($element), array('Quform_Element_Captcha', 'Quform_Element_Recaptcha', 'Quform_Element_File'))) { // Captcha always have a required validator, file has it's own
            if ($this->getConfigValue($config, 'required', $element)) {
                $element->addValidator(new Quform_Validator_Required());
            }
        }

        if ($element->hasValidator('required')) {
            $requiredMessage = $this->getConfigValue($config, 'messageRequired', $element);

            if ( ! Quform::isNonEmptyString($requiredMessage)) {
                $requiredMessage = $form->config('messageRequired');
            }

            if (Quform::isNonEmptyString($requiredMessage)) {
                $element->getValidator('required')->setConfig('messages.' . Quform_Validator_Required::REQUIRED, $requiredMessage);
            }
        }

        $defaultValue = $this->getConfigValue($config, 'defaultValue', $element);
        if ($defaultValue !== null) {
            $element->setDefaultValue($defaultValue);
            $element->setValue($element->getDefaultValue());
        }

        if ($this->getConfigValue($config, 'dynamicDefaultValue', $element)) {
            $dynamicKey = $this->getConfigValue($config, 'dynamicKey', $element);
            if (Quform::isNonEmptyString($dynamicKey)) {
                $element->setDynamicDefaultValue($dynamicKey);
            }
        }

        $maxLength = $this->getConfigValue($config, 'maxLength', $element);
        if (is_numeric($maxLength)) {
            $lengthValidator = new Quform_Validator_Length(array(
                'max' => $maxLength
            ));

            $lengthValidatorMessage = $this->getConfigValue($config, 'messageLengthTooLong', $element);
            if (Quform::isNonEmptyString($lengthValidatorMessage)) {
                $lengthValidator->setConfig('messages.' . Quform_Validator_Length::TOO_LONG, $lengthValidatorMessage);
            }

            $element->addValidator($lengthValidator);
        }

        $filters = $this->getConfigValue($config, 'filters', $element);
        if (is_array($filters)) {
            foreach ($filters as $fConfig) {
                if (isset($fConfig['type'])) {
                    $fClass = 'Quform_Filter_' . ucfirst($fConfig['type']);
                    if (class_exists($fClass)) {
                        $element->addFilter(new $fClass($fConfig));
                    }
                }
            }
        }

        $validators = $this->getConfigValue($config, 'validators', $element);
        if (is_array($validators)) {
            foreach ($validators as $vConfig) {
                if (isset($vConfig['type'])) {
                    $vClass = 'Quform_Validator_' . ucfirst($vConfig['type']);
                    if (class_exists($vClass)) {
                        if ($vClass == 'Quform_Validator_Email') {
                            $vConfig['charset'] = $form->getCharset();
                        } else if ($vClass == 'Quform_Validator_Duplicate') {
                            $vConfig['element'] = $element;
                            $vConfig['repository'] = $this->repository;
                        }

                        $element->addValidator(new $vClass($vConfig));
                    }
                }
            }
        }
    }

    /**
     * @param   Quform_Element_Multi  $element
     * @param   array                 $config
     * @return  array
     */
    protected function configureMultiOptions(Quform_Element_Multi $element, array $config)
    {
        $options = $this->getConfigValue($config, 'options', $element);
        if (is_array($options)) {
            $element->addOptions($options);
        }

        if ($this->getConfigValue($config, 'inArrayValidator', $element)) {
            $haystack = array();

            foreach ($element->getOptions() as $option) {
                if (isset($option['options'])) {
                    foreach ($option['options'] as $optgroupOption) {
                        $haystack[] = $optgroupOption['value'];
                    }
                } else {
                    $haystack[] = $option['value'];
                }
            }

            if ($element instanceof Quform_Element_Checkbox || $element instanceof Quform_Element_Multiselect) {
                $element->addValidator(new Quform_Validator_Array(array(
                    'validator' => new Quform_Validator_InArray(array(
                        'haystack' => $haystack
                    ))
                )));
            } else {
                $element->addValidator(new Quform_Validator_InArray(array(
                    'haystack' => $haystack
                )));
            }
        }

        return $config;
    }

    /**
     * Remove redundant data from the $config array and set the remaining data as the element config
     *
     * @param  Quform_Element  $element
     * @param  array           $config
     */
    public function setConfig(Quform_Element $element, array $config)
    {
        unset($config['elements'], $config['filters'], $config['validators']);

        $element->setConfig($config);
    }

    /**
     * Get the config value with the given key, if it doesn't exist it will get the default config value from the element
     *
     * @param   array           $config
     * @param                   $key
     * @param   Quform_Element  $element
     * @return  mixed
     */
    public function getConfigValue(array $config, $key, Quform_Element $element)
    {
        $value = Quform::get($config, $key);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($element), 'getDefaultConfig')), $key);
        }

        return $value;
    }
}
