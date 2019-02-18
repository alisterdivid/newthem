<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Builder
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Themes
     */
    protected $themes;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     * @param  Quform_Options       $options
     * @param  Quform_Themes        $themes
     * @param  Quform_ScriptLoader  $scriptLoader
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory, Quform_Options $options,
                                Quform_Themes $themes, Quform_ScriptLoader $scriptLoader)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->options = $options;
        $this->themes = $themes;
        $this->scriptLoader = $scriptLoader;
    }

    /**
     * Get the localisation / variables to pass to the builder JS
     *
     * @return array
     */
    public function getScriptL10n()
    {
        $data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => Quform::url(),
            'saveFormNonce' => wp_create_nonce('quform_save_form'),
            'formSaved' => __('Form saved', 'quform'),
            'confirmRemoveElement' => __('Are you sure you want to remove this element? Any previously submitted form data for this element will no longer be accessible.', 'quform'),
            'confirmRemoveGroup' => __('Are you sure you want to remove this group? All elements inside this group will also be removed. Any previously submitted form data for elements inside this group will no longer be accessible.', 'quform'),
            'confirmRemovePage' => __('Are you sure you want to remove this page? All elements inside this page will also be removed. Any previously submitted form data for elements inside this page will no longer be accessible.', 'quform'),
            'confirmRemoveRow' => __('Are you sure you want to remove this row? All elements inside this row will also be removed. Any previously submitted form data for elements inside this row will no longer be accessible.', 'quform'),
            'confirmRemoveColumn' => __('Are you sure you want to remove this column? All elements inside this column will also be removed. Any previously submitted form data for elements inside this column will no longer be accessible.', 'quform'),
            'confirmRemoveOptgroup' => __('Are you sure you want to remove this optgroup? Any options inside of it will also be removed.', 'quform'),
            'confirmRemoveSubmit' => __('Are you sure you want to remove this submit button?', 'quform'),
            'nestingOptgroupError' => __('Nested optgroups are not supported.', 'quform'),
            'errorSavingForm' => __('Error saving the form', 'quform'),
            'atLeastOneToCcBccRequired' => __('At least one To, Cc or Bcc address is required', 'quform'),
            'correctHighlightedFields' => __('Please correct the highlighted fields and save the form again', 'quform'),
            'inherit' => __('Inherit', 'quform'),
            'field' => __('Field', 'quform'),
            'icon' => __('Icon', 'quform'),
            'above' => __('Above', 'quform'),
            'left' => __('Left', 'quform'),
            'inside' => __('Inside', 'quform'),
            'atLeastOnePage' => __('The form must have at least one page', 'quform'),
            'loadedPreviewLocales' => $this->getLoadedPreviewLocales(),
            'exampleTooltip' => __('This is an example tooltip!', 'quform'),
            'remove' => _x('Remove', 'delete', 'quform'),
            'selectOptionHtml' => $this->getOptionHtml('select'),
            'checkboxOptionHtml' => $this->getOptionHtml('checkbox'),
            'radioOptionHtml' => $this->getOptionHtml('radio'),
            'multiselectOptionHtml' => $this->getOptionHtml('multiselect'),
            'optgroupHtml' => $this->getOptgroupHtml(),
            'bulkOptions' => $this->getBulkOptions(),
            'defaultOptions' => $this->getDefaultOptions(),
            'defaultOptgroups' => $this->getDefaultOptgroups(),
            'logicRuleHtml' => $this->getLogicRuleHtml(),
            'noLogicElements' => __('There are no elements available to use for logic rules.', 'quform'),
            'noLogicRules' => __('There are no logic rules yet, click "Add logic rule" to add one.', 'quform'),
            'logicSourceTypes' => $this->getLogicSourceTypes(),
            'thisFieldMustBePositiveNumberOrZero' => __('This field must be a positive number or zero', 'quform'),
            'atLeastOneLogicRuleRequired' => __('At least one logic rule is required', 'quform'),
            'showThisGroup' => __('Show this group', 'quform'),
            'hideThisGroup' => __('Hide this group', 'quform'),
            'showThisField' => __('Show this field', 'quform'),
            'hideThisField' => __('Hide this field', 'quform'),
            'showThisPage' => __('Show this page', 'quform'),
            'hideThisPage' => __('Hide this page', 'quform'),
            'useThisConfirmationIfAll' => __('Use this confirmation if all of these rules match', 'quform'),
            'useThisConfirmationIfAny' => __('Use this confirmation if any of these rules match', 'quform'),
            'sendToTheseRecipientsIfAll' => __('Send to these recipients if all of these rules match', 'quform'),
            'sendToTheseRecipientsIfAny' => __('Send to these recipients if any of these rules match', 'quform'),
            'ifAllOfTheseRulesMatch' => __('if all of these rules match', 'quform'),
            'ifAnyOfTheseRulesMatch' => __('if any of these rules match', 'quform'),
            'addRecipient' => __('Add recipient', 'quform'),
            'addLogicRule' => __('Add logic rule', 'quform'),
            'noConditionals' => __('There are no conditionals yet, click "Add conditional" to add one.', 'quform'),
            'is' => __('is', 'quform'),
            'isNot' => __('is not', 'quform'),
            'isEmpty' => __('is empty', 'quform'),
            'isNotEmpty' => __('is not empty', 'quform'),
            'greaterThan' => __('is greater than', 'quform'),
            'lessThan' => __('is less than', 'quform'),
            'contains' => __('contains', 'quform'),
            'startsWith' => __('starts with', 'quform'),
            'endsWith' => __('ends with', 'quform'),
            'enterValue' => __('Enter a value', 'quform'),
            'unsavedChanges' => __('You have unsaved changes.', 'quform'),
            'previewError' => __('An error occurred loading the preview', 'quform'),
            'untitled' =>  __('Untitled', 'quform'),
            'pageTabNavHtml' => $this->getPageTabNavHtml(),
            'elements' => $this->getElements(),
            'elementHtml' => $this->getDefaultElementHtml('text'),
            'groupHtml' => $this->getDefaultElementHtml('group'),
            'pageHtml' => $this->getDefaultElementHtml('page'),
            'rowHtml' => $this->getDefaultElementHtml('row'),
            'columnHtml' => $this->getDefaultElementHtml('column'),
            'styles' => $this->getStyles(),
            'styleHtml' => $this->getStyleHtml(),
            'globalStyles' => $this->getGlobalStyles(),
            'globalStyleHtml' => $this->getGlobalStyleHtml(),
            'visibleStyles' => $this->getVisibleStyles(),
            'filters' => $this->getFilters(),
            'filterHtml' => $this->getFilterHtml(),
            'visibleFilters' => $this->getVisibleFilters(),
            'validators' => $this->getValidators(),
            'validatorHtml' => $this->getValidatorHtml(),
            'visibleValidators' => $this->getVisibleValidators(),
            'notification' => Quform_Notification::getDefaultConfig(),
            'notificationHtml' => $this->getNotificationHtml(),
            'notificationConfirmRemove' => __('Are you sure you want to remove this notification?', 'quform'),
            'sendThisNotification' => __('Send this notification', 'quform'),
            'doNotSendThisNotification' => __('Do not send this notification', 'quform'),
            'recipientHtml' => $this->getRecipientHtml(),
            'popupTriggerText' => __('Change this to the text or HTML that will trigger the popup', 'quform'),
            'attachmentHtml' => $this->getAttachmentHtml(),
            'selectFiles' => __('Select Files', 'quform'),
            'selectElement' => __('Select an element', 'quform'),
            'attachmentSourceTypes' => $this->getAttachmentSourceTypes(),
            'noAttachmentSourcesFound' => __('No attachment sources found', 'quform'),
            'noAttachments' => __('There are no attachments yet, click "Add attachment" to add one.', 'quform'),
            'selectOneFile' => __('Select at least one file', 'quform'),
            'confirmation' => Quform_Confirmation::getDefaultConfig(),
            'confirmationHtml' => $this->getConfirmationHtml(),
            'cannotRemoveDefaultConfirmation' => __('The default confirmation cannot be removed', 'quform'),
            'confirmationConfirmRemove' => __('Are you sure you want to remove this confirmation?', 'quform'),
            'dbPasswordHtml' => $this->getDbPasswordHtml(),
            'dbColumnHtml' => $this->getDbColumnHtml(),
            'areYouSure' => __('Are you sure?', 'quform'),
            'emailRemoveBrackets' => __('Please remove the brackets from the email address', 'quform'),
            'themes' => $this->getThemes(),
            'collapse' => __('Collapse', 'quform'),
            'expand' => __('Expand', 'quform'),
            'noIcon' => __('No icon', 'quform'),
            'columnNumber' => __('Column %d', 'quform'),
            'columnWidthMustBeNumeric' => __('Column width must be numeric', 'quform'),
            'columnWidthTotalTooHigh' => __('Total of column widths must not be higher than 100', 'quform'),
            'pageSettings' => __('Page settings', 'quform'),
            'groupSettings' => __('Group settings', 'quform'),
            'rowSettings' => __('Row settings', 'quform'),
            'elementSettings' => __('Element settings', 'quform'),
            'pleaseSelect' => __('Please select', 'quform'),
            'buttonIcon' => __('Button icon', 'quform'),
            'buttonIconPosition' => __('Button icon position', 'quform'),
            'dropzoneIcon' => __('Dropzone icon', 'quform'),
            'dropzoneIconPosition' => __('Dropzone icon position', 'quform'),
            'pages' => Quform::getPages(),
            'posts' => Quform::getPosts(),
            'pagesText' => __('Pages', 'quform'),
            'postsText' => __('Posts', 'quform'),
            'displayAMessage' => __('Display a message', 'quform'),
            'redirectTo' => __('Redirect to', 'quform'),
            'reloadThePage' => __('Reload the page', 'quform'),
            'enableCustomizeValuesToChange' => __('Enable the "Customize values" setting to change the value', 'quform'),
            'everyone' => __('Everyone', 'quform'),
            'adminOnly' => __('Admin only', 'quform'),
            'loggedInUsersOnly' => __('Logged in users only', 'quform'),
            'loggedOutUsersOnly' => __('Logged out users only', 'quform')
        );

        $params = array(
            'l10n_print_after' => 'quformBuilderL10n = ' . wp_json_encode($data)
        );

        return $params;
    }

    /**
     * Get the HTML for an option for a select element
     *
     * @param   string  $type  The element type, 'select', 'radio', 'checkbox' or 'multiselect'
     * @return  string
     */
    protected function getOptionHtml($type)
    {
        $output = sprintf('<div class="qfb-option qfb-option-type-%s qfb-box qfb-cf">', $type);

        $output .= '<div class="qfb-option-left"><div class="qfb-option-left-inner">';
        $output .= '<div class="qfb-settings-row qfb-settings-row-2">';
        $output .= '<div class="qfb-settings-column">';
        $output .= sprintf('<input class="qfb-option-label" type="text" placeholder="%s">', esc_attr__('Label', 'quform'));
        $output .= '</div>';
        $output .= '<div class="qfb-settings-column">';
        $output .= sprintf('<input class="qfb-option-value" type="text" placeholder="%s">', esc_attr__('Value', 'quform'));
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div></div>';

        $output .= '<div class="qfb-option-right">';

        $output .= '<div class="qfb-option-actions">';

        $output .= sprintf('<span class="qfb-option-action-set-default" title="%s"><i class="fa fa-check"></i></span>', esc_attr__('Default value', 'quform'));
        $output .= '<span class="qfb-option-action-add"><i class="fa fa-plus"></i></span>';
        $output .= '<span class="qfb-option-action-duplicate"><i class="mdi mdi-content_copy"></i></span>';
        $output .= '<span class="qfb-option-action-remove"><i class="fa fa-trash"></i></span>';
        if ($type == 'radio' || $type == 'checkbox') {
            $output .= '<span class="qfb-option-action-settings"><i class="mdi mdi-settings"></i></span>';
        }
        $output .= '<span class="qfb-option-action-move"><i class="fa fa-arrows"></i></span>';

        $output .= '</div>';
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for an optgroup for a select element
     *
     * @return  string
     */
    protected function getOptgroupHtml()
    {
        $output = '<div class="qfb-optgroup qfb-box qfb-cf"><div class="qfb-optgroup-top qfb-cf">';
        $output .= '<div class="qfb-optgroup-left"><div class="qfb-optgroup-left-inner">';
        $output .= sprintf('<input class="qfb-optgroup-label" type="text" placeholder="%s">', esc_attr__('Optgroup label', 'quform'));
        $output .= '</div></div>';
        $output .= '<div class="qfb-optgroup-right">';
        $output .= '<div class="qfb-optgroup-actions">';
        $output .= '<span class="qfb-optgroup-action-add"><i class="fa fa-plus"></i></span>';
        $output .= '<span class="qfb-optgroup-action-remove"><i class="fa fa-trash"></i></span>';
        $output .= '<span class="qfb-optgroup-action-move"><i class="fa fa-arrows"></i></span>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div></div>';

        return $output;
    }

    /**
     * Get the default option config for each element type
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            'select' => Quform_Element_Select::getDefaultOptionConfig(),
            'checkbox' => Quform_Element_Checkbox::getDefaultOptionConfig(),
            'radio' => Quform_Element_Radio::getDefaultOptionConfig(),
            'multiselect' => Quform_Element_Multiselect::getDefaultOptionConfig()
        );
    }

    /**
     * Get the default optgroup config for each element type
     *
     * @return array
     */
    protected function getDefaultOptgroups()
    {
        return array(
            'select' => Quform_Element_Select::getDefaultOptgroupConfig(),
            'multiselect' => Quform_Element_Multiselect::getDefaultOptgroupConfig()
        );
    }

    /*
     * Get the predefined bulk options
     *
     * @return array
     */
    public function getBulkOptions()
    {
        return apply_filters('quform_bulk_options', array(
            'countries' => array(
                'name' => __('Countries', 'quform'),
                'options' => $this->getCountries()
            ),
            'usStates' => array(
                'name' => __('U.S. States', 'quform'),
                'options' => $this->getUsStates()
            ),
            'canadianProvinces' => array(
                'name' => __('Canadian Provinces', 'quform'),
                'options' => $this->getCanadianProvinces()
            ),
            'ukCounties' => array(
                'name' => __('UK Counties', 'quform'),
                'options' => $this->getUkCounties()
            ),
            'germanStates' => array(
                'name' => __('German States', 'quform'),
                'options' => array('Baden-Wurttemberg', 'Bavaria', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hesse', 'Mecklenburg-West Pomerania', 'Lower Saxony', 'North Rhine-Westphalia', 'Rhineland-Palatinate', 'Saarland', 'Saxony', 'Saxony-Anhalt', 'Schleswig-Holstein', 'Thuringia')
            ),
            'dutchProvinces' => array(
                'name' => __('Dutch Provinces', 'quform'),
                'options' => array('Drente', 'Flevoland', 'Friesland', 'Gelderland', 'Groningen', 'Limburg', 'Noord-Brabant', 'Noord-Holland', 'Overijssel', 'Zuid-Holland', 'Utrecht', 'Zeeland')
            ),
            'continents' => array(
                'name' => __('Continents', 'quform'),
                'options' => array(__('Africa', 'quform'), __('Antarctica', 'quform'), __('Asia', 'quform'), __('Australia', 'quform'), __('Europe', 'quform'), __('North America', 'quform'), __('South America', 'quform'))
            ),
            'gender' => array(
                'name' => __('Gender', 'quform'),
                'options' => array(__('Male', 'quform'), __('Female', 'quform'))
            ),
            'age' => array(
                'name' => __('Age', 'quform'),
                'options' => array(__('Under 18', 'quform'), __('18-24', 'quform'), __('25-34', 'quform'), __('35-44', 'quform'), __('45-54', 'quform'), __('55-64', 'quform'), __('65 or over', 'quform'))
            ),
            'maritalStatus' => array(
                'name' => __('Marital Status', 'quform'),
                'options' => array(__('Single', 'quform'), __('Married', 'quform'), __('Divorced', 'quform'), __('Widowed', 'quform'))
            ),
            'income' => array(
                'name' => __('Income', 'quform'),
                'options' => array(__('Under $20,000', 'quform'), __('$20,000 - $30,000', 'quform'), __('$30,000 - $40,000', 'quform'), __('$40,000 - $50,000', 'quform'), __('$50,000 - $75,000', 'quform'), __('$75,000 - $100,000', 'quform'), __('$100,000 - $150,000', 'quform'), __('$150,000 or more', 'quform'))
            ),
            'days' => array(
                'name' => __('Days', 'quform'),
                'options' => array(__('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday'))
            ),
            'months' => array(
                'name' => __('Months', 'quform'),
                'options' => array_values($this->getAllMonths())
            )
        ));
    }

    /**
     * Returns an array of all countries
     *
     * @return array
     */
    protected function getCountries()
    {
        return apply_filters('quform_countries', array(
            __('Afghanistan', 'quform'), __('Albania', 'quform'), __('Algeria', 'quform'), __('American Samoa', 'quform'), __('Andorra', 'quform'), __('Angola', 'quform'), __('Anguilla', 'quform'), __('Antarctica', 'quform'), __('Antigua And Barbuda', 'quform'), __('Argentina', 'quform'), __('Armenia', 'quform'), __('Aruba', 'quform'), __('Australia', 'quform'), __('Austria', 'quform'), __('Azerbaijan', 'quform'), __('Bahamas', 'quform'), __('Bahrain', 'quform'), __('Bangladesh', 'quform'), __('Barbados', 'quform'), __('Belarus', 'quform'), __('Belgium', 'quform'),
            __('Belize', 'quform'), __('Benin', 'quform'), __('Bermuda', 'quform'), __('Bhutan', 'quform'), __('Bolivia', 'quform'), __('Bosnia And Herzegovina', 'quform'), __('Botswana', 'quform'), __('Bouvet Island', 'quform'), __('Brazil', 'quform'), __('British Indian Ocean Territory', 'quform'), __('Brunei Darussalam', 'quform'), __('Bulgaria', 'quform'), __('Burkina Faso', 'quform'), __('Burundi', 'quform'), __('Cambodia', 'quform'), __('Cameroon', 'quform'), __('Canada', 'quform'), __('Cape Verde', 'quform'), __('Cayman Islands', 'quform'), __('Central African Republic', 'quform'), __('Chad', 'quform'),
            __('Chile', 'quform'), __('China', 'quform'), __('Christmas Island', 'quform'), __('Cocos (Keeling) Islands', 'quform'), __('Colombia', 'quform'), __('Comoros', 'quform'), __('Congo', 'quform'), __('Congo, The Democratic Republic Of The', 'quform'), __('Cook Islands', 'quform'), __('Costa Rica', 'quform'), __('Cote D\'Ivoire', 'quform'), __('Croatia (Local Name: Hrvatska)', 'quform'), __('Cuba', 'quform'), __('Cyprus', 'quform'), __('Czech Republic', 'quform'), __('Denmark', 'quform'), __('Djibouti', 'quform'), __('Dominica', 'quform'), __('Dominican Republic', 'quform'), __('East Timor', 'quform'), __('Ecuador', 'quform'),
            __('Egypt', 'quform'), __('El Salvador', 'quform'), __('Equatorial Guinea', 'quform'), __('Eritrea', 'quform'), __('Estonia', 'quform'), __('Ethiopia', 'quform'), __('Falkland Islands (Malvinas)', 'quform'), __('Faroe Islands', 'quform'), __('Fiji', 'quform'), __('Finland', 'quform'), __('France', 'quform'), __('France, Metropolitan', 'quform'), __('French Guiana', 'quform'), __('French Polynesia', 'quform'), __('French Southern Territories', 'quform'), __('Gabon', 'quform'), __('Gambia', 'quform'), __('Georgia', 'quform'), __('Germany', 'quform'), __('Ghana', 'quform'), __('Gibraltar', 'quform'),
            __('Greece', 'quform'), __('Greenland', 'quform'), __('Grenada', 'quform'), __('Guadeloupe', 'quform'), __('Guam', 'quform'), __('Guatemala', 'quform'), __('Guinea', 'quform'), __('Guinea-Bissau', 'quform'), __('Guyana', 'quform'), __('Haiti', 'quform'), __('Heard And Mc Donald Islands', 'quform'), __('Holy See (Vatican City State)', 'quform'), __('Honduras', 'quform'), __('Hong Kong', 'quform'), __('Hungary', 'quform'), __('Iceland', 'quform'), __('India', 'quform'), __('Indonesia', 'quform'), __('Iran (Islamic Republic Of)', 'quform'), __('Iraq', 'quform'), __('Ireland', 'quform'),
            __('Israel', 'quform'), __('Italy', 'quform'), __('Jamaica', 'quform'), __('Japan', 'quform'), __('Jordan', 'quform'), __('Kazakhstan', 'quform'), __('Kenya', 'quform'), __('Kiribati', 'quform'), __('Korea, Democratic People\'s Republic Of', 'quform'), __('Korea, Republic Of', 'quform'), __('Kuwait', 'quform'), __('Kyrgyzstan', 'quform'), __('Lao People\'s Democratic Republic', 'quform'), __('Latvia', 'quform'), __('Lebanon', 'quform'), __('Lesotho', 'quform'), __('Liberia', 'quform'), __('Libyan Arab Jamahiriya', 'quform'), __('Liechtenstein', 'quform'), __('Lithuania', 'quform'), __('Luxembourg', 'quform'),
            __('Macau', 'quform'), __('Macedonia, Former Yugoslav Republic Of', 'quform'), __('Madagascar', 'quform'), __('Malawi', 'quform'), __('Malaysia', 'quform'), __('Maldives', 'quform'), __('Mali', 'quform'), __('Malta', 'quform'), __('Marshall Islands', 'quform'), __('Martinique', 'quform'), __('Mauritania', 'quform'), __('Mauritius', 'quform'), __('Mayotte', 'quform'), __('Mexico', 'quform'), __('Micronesia, Federated States Of', 'quform'), __('Moldova, Republic Of', 'quform'), __('Monaco', 'quform'), __('Mongolia', 'quform'), __('Montserrat', 'quform'), __('Morocco', 'quform'), __('Mozambique', 'quform'),
            __('Myanmar', 'quform'), __('Namibia', 'quform'), __('Nauru', 'quform'), __('Nepal', 'quform'), __('Netherlands', 'quform'), __('Netherlands Antilles', 'quform'), __('New Caledonia', 'quform'), __('New Zealand', 'quform'), __('Nicaragua', 'quform'), __('Niger', 'quform'), __('Nigeria', 'quform'), __('Niue', 'quform'), __('Norfolk Island', 'quform'), __('Northern Mariana Islands', 'quform'), __('Norway', 'quform'), __('Oman', 'quform'), __('Pakistan', 'quform'), __('Palau', 'quform'), __('Panama', 'quform'), __('Papua New Guinea', 'quform'), __('Paraguay', 'quform'),
            __('Peru', 'quform'), __('Philippines', 'quform'), __('Pitcairn', 'quform'), __('Poland', 'quform'), __('Portugal', 'quform'), __('Puerto Rico', 'quform'), __('Qatar', 'quform'), __('Reunion', 'quform'), __('Romania', 'quform'), __('Russian Federation', 'quform'), __('Rwanda', 'quform'), __('Saint Kitts And Nevis', 'quform'), __('Saint Lucia', 'quform'), __('Saint Vincent And The Grenadines', 'quform'), __('Samoa', 'quform'), __('San Marino', 'quform'), __('Sao Tome And Principe', 'quform'), __('Saudi Arabia', 'quform'), __('Senegal', 'quform'), __('Seychelles', 'quform'), __('Sierra Leone', 'quform'),
            __('Singapore', 'quform'), __('Slovakia (Slovak Republic)', 'quform'), __('Slovenia', 'quform'), __('Solomon Islands', 'quform'), __('Somalia', 'quform'), __('South Africa', 'quform'), __('South Georgia, South Sandwich Islands', 'quform'), __('Spain', 'quform'), __('Sri Lanka', 'quform'), __('St. Helena', 'quform'), __('St. Pierre And Miquelon', 'quform'), __('Sudan', 'quform'), __('Suriname', 'quform'), __('Svalbard And Jan Mayen Islands', 'quform'), __('Swaziland', 'quform'), __('Sweden', 'quform'), __('Switzerland', 'quform'), __('Syrian Arab Republic', 'quform'), __('Taiwan', 'quform'), __('Tajikistan', 'quform'), __('Tanzania, United Republic Of', 'quform'),
            __('Thailand', 'quform'), __('Togo', 'quform'), __('Tokelau', 'quform'), __('Tonga', 'quform'), __('Trinidad And Tobago', 'quform'), __('Tunisia', 'quform'), __('Turkey', 'quform'), __('Turkmenistan', 'quform'), __('Turks And Caicos Islands', 'quform'), __('Tuvalu', 'quform'), __('Uganda', 'quform'), __('Ukraine', 'quform'), __('United Arab Emirates', 'quform'), __('United Kingdom', 'quform'), __('United States', 'quform'), __('United States Minor Outlying Islands', 'quform'), __('Uruguay', 'quform'), __('Uzbekistan', 'quform'), __('Vanuatu', 'quform'), __('Venezuela', 'quform'), __('Vietnam', 'quform'),
            __('Virgin Islands (British)', 'quform'), __('Virgin Islands (U.S.)', 'quform'), __('Wallis And Futuna Islands', 'quform'), __('Western Sahara', 'quform'), __('Yemen', 'quform'), __('Yugoslavia', 'quform'), __('Zambia', 'quform'), __('Zimbabwe', 'quform')
        ));
    }

    /**
     * Returns an array of US states
     *
     * @return array
     */
    protected function getUsStates()
    {
        return array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware',
            'District Of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas',
            'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
            'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
            'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island',
            'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
            'West Virginia', 'Wisconsin', 'Wyoming'
        );
    }

    /**
     * Returns an array of Canadian Provinces / Territories
     *
     * @return array
     */
    protected function getCanadianProvinces()
    {
        return array(
            'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick', 'Newfoundland & Labrador',
            'Northwest Territories', 'Nova Scotia', 'Nunavut','Ontario', 'Prince Edward Island', 'Quebec',
            'Saskatchewan', 'Yukon'
        );
    }

    /**
     * Returns an array of UK counties
     *
     * @return array
     */
    protected function getUkCounties()
    {
        return array(
            'Aberdeen City', 'Aberdeenshire', 'Angus', 'Antrim', 'Argyll and Bute', 'Armagh', 'Avon', 'Banffshire',
            'Bedfordshire', 'Berkshire', 'Blaenau Gwent', 'Borders', 'Bridgend', 'Bristol', 'Buckinghamshire',
            'Caerphilly', 'Cambridgeshire', 'Cardiff', 'Carmarthenshire', 'Ceredigion', 'Channel Islands', 'Cheshire',
            'Clackmannan', 'Cleveland', 'Conwy', 'Cornwall', 'Cumbria', 'Denbighshire', 'Derbyshire', 'Devon', 'Dorset',
            'Down', 'Dumfries and Galloway', 'Durham', 'East Ayrshire', 'East Dunbartonshire', 'East Lothian',
            'East Renfrewshire', 'East Riding of Yorkshire', 'East Sussex', 'Edinburgh City', 'Essex', 'Falkirk',
            'Fermanagh', 'Fife', 'Flintshire', 'Glasgow (City of)', 'Gloucestershire', 'Greater Manchester', 'Gwynedd',
            'Hampshire', 'Herefordshire', 'Hertfordshire', 'Highland', 'Humberside', 'Inverclyde', 'Isle of Anglesey',
            'Isle of Man', 'Isle of Wight', 'Isles of Scilly', 'Kent', 'Lancashire', 'Leicestershire', 'Lincolnshire',
            'London', 'Londonderry', 'Merseyside', 'Merthyr Tydfil', 'Middlesex', 'Midlothian', 'Monmouthshire',
            'Moray', 'Neath Port Talbot', 'Newport', 'Norfolk', 'North Ayrshire', 'North East Lincolnshire',
            'North Lanarkshire', 'North Yorkshire', 'Northamptonshire', 'Northumberland', 'Nottinghamshire',
            'Orkney', 'Oxfordshire', 'Pembrokeshire', 'Perthshire and Kinross', 'Powys', 'Renfrewshire',
            'Rhondda Cynon Taff', 'Roxburghshire', 'Rutland', 'Shetland', 'Shropshire', 'Somerset', 'South Ayrshire',
            'South Lanarkshire', 'South Yorkshire', 'Staffordshire', 'Stirling', 'Suffolk', 'Surrey', 'Swansea',
            'The Vale of Glamorgan', 'Torfaen', 'Tyne and Wear', 'Tyrone', 'Warwickshire', 'West Dunbartonshire',
            'West Lothian', 'West Midlands', 'West Sussex', 'West Yorkshire', 'Western Isles', 'Wiltshire',
            'Worcestershire', 'Wrexham'
        );
    }

    /**
     * Get all the months in the year
     *
     * @return array
     */
    protected function getAllMonths()
    {
        return apply_filters('quform_get_all_months', array(
            1  => __('January', 'quform'),
            2  => __('February', 'quform'),
            3  => __('March', 'quform'),
            4  => __('April', 'quform'),
            5  => __('May', 'quform'),
            6  => __('June', 'quform'),
            7  => __('July', 'quform'),
            8  => __('August', 'quform'),
            9  => __('September', 'quform'),
            10 => __('October', 'quform'),
            11 => __('November', 'quform'),
            12 => __('December', 'quform')
        ));
    }

    /**
     * Get the core form elements config
     *
     * @param   string|null  $type  The element type or null for all elements
     * @return  array
     */
    public function getElements($type = null)
    {
        $elements = array(
            'text' => array(
                'name' => _x('Text', 'text input field', 'quform'),
                'icon' => '<i class="fa fa-pencil"></i>',
                'config' => Quform_Element_Text::getDefaultConfig()
            ),
            'textarea' => array(
                'name' => _x('Textarea', 'textarea input field', 'quform'),
                'icon' => '<i class="fa fa-align-left"></i>',
                'config' => Quform_Element_Textarea::getDefaultConfig()
            ),
            'email' => array(
                'name' => _x('Email', 'email address field', 'quform'),
                'icon' => '<i class="fa fa-envelope"></i>',
                'config' => Quform_Element_Email::getDefaultConfig()
            ),
            'select' => array(
                'name' => _x('Select Menu', 'select menu field', 'quform'),
                'icon' => '<i class="fa fa-caret-square-o-down"></i>',
                'config' => Quform_Element_Select::getDefaultConfig()
            ),
            'checkbox' => array(
                'name' => _x('Checkboxes', 'checkboxes field', 'quform'),
                'icon' => '<i class="fa fa-check-square-o"></i>',
                'config' => Quform_Element_Checkbox::getDefaultConfig()
            ),
            'radio' => array(
                'name' => _x('Radio Buttons', 'radio buttons field', 'quform'),
                'icon' => '<i class="mdi mdi-radio_button_checked"></i>',
                'config' => Quform_Element_Radio::getDefaultConfig()
            ),
            'multiselect' => array(
                'name' => _x('Multi Select', 'multi select field', 'quform'),
                'icon' => '<i class="fa fa-list-ul"></i>',
                'config' => Quform_Element_Multiselect::getDefaultConfig()
            ),
            'file' => array(
                'name' => __('File Upload', 'quform'),
                'icon' => '<i class="fa fa-upload"></i>',
                'config' => Quform_Element_File::getDefaultConfig()
            ),
            'date' => array(
                'name' => _x('Date', 'date field', 'quform'),
                'icon' => '<i class="fa fa-calendar"></i>',
                'config' => Quform_Element_Date::getDefaultConfig()
            ),
            'time' => array(
                'name' => _x('Time', 'time field', 'quform'),
                'icon' => '<i class="fa fa-clock-o"></i>',
                'config' => Quform_Element_Time::getDefaultConfig()
            ),
            'name' => array(
                'name' => _x('Name', 'name field', 'quform'),
                'icon' => '<i class="fa fa-user"></i>',
                'config' => Quform_Element_Name::getDefaultConfig()
            ),
            'password' => array(
                'name' => _x('Password', 'password input field', 'quform'),
                'icon' => '<i class="fa fa-lock"></i>',
                'config' => Quform_Element_Password::getDefaultConfig()
            ),
            'html' => array(
                'name' => __('HTML', 'quform'),
                'icon' => '<i class="fa fa-code"></i>',
                'config' => Quform_Element_Html::getDefaultConfig()
            ),
            'hidden' => array(
                'name' => __('Hidden', 'quform'),
                'icon' => '<i class="fa fa-eye-slash"></i>',
                'config' => Quform_Element_Hidden::getDefaultConfig()
            ),
            'captcha' => array(
                'name' => _x('CAPTCHA', 'captcha field', 'quform'),
                'icon' => '<i class="fa fa-handshake-o"></i>',
                'config' => Quform_Element_Captcha::getDefaultConfig()
            ),
            'recaptcha' => array(
                'name' => __('reCAPTCHA', 'quform'),
                'icon' => '<i class="mdi mdi-face"></i>',
                'config' => Quform_Element_Recaptcha::getDefaultConfig()
            ),
            'submit' => array(
                'name' => _x('Submit', 'submit button element', 'quform'),
                'icon' => '<i class="fa fa-paper-plane"></i>',
                'config' => Quform_Element_Submit::getDefaultConfig()
            ),
            'page' => array(
                'name' => __('Page', 'quform'),
                'config' => Quform_Element_Page::getDefaultConfig()
            ),
            'group' => array(
                'name' => __('Group', 'quform'),
                'icon' => '<i class="fa fa-object-group"></i>',
                'config' => Quform_Element_Group::getDefaultConfig()
            ),
            'row' => array(
                'name' => __('Column Layout', 'quform'),
                'icon' => '<i class="fa fa-columns"></i>',
                'config' => Quform_Element_Row::getDefaultConfig()
            ),
            'column' => array(
                'name' => __('Column', 'quform'),
                'icon' => '<i class="fa fa-columns"></i>',
                'config' => Quform_Element_Column::getDefaultConfig()
            )
        );

        $elements = apply_filters('quform_admin_elements', $elements);

        if (is_string($type) && isset($elements[$type])) {
            return $elements[$type];
        }

        return $elements;
    }

    /**
     * Get the default config for the element of the given type
     *
     * @param   string  $type  The element type
     * @return  array          The default element config
     */
    protected function getDefaultElementConfig($type)
    {
        $element = $this->getElements($type);

        return $element['config'];
    }

    /**
     * Get the element styles data
     *
     * @return array
     */
    public function getStyles()
    {
        $styles = array(
            'element' => array('name' => __('Outer wrapper', 'quform')),
            'elementLabel' => array('name' => __('Label', 'quform')),
            'elementLabelText' => array('name' => __('Label text', 'quform')),
            'elementRequiredText' => array('name' => __('Element required text', 'quform')),
            'elementInner' => array('name' => __('Inner wrapper', 'quform')),
            'elementInput' => array('name' => __('Input wrapper', 'quform')),
            'elementText' => array('name' => __('Text input field', 'quform')),
            'elementTextHover' => array('name' => __('Text input field (hover)', 'quform')),
            'elementTextFocus' => array('name' => __('Text input field (focus)', 'quform')),
            'elementTextarea' => array('name' => __('Textarea field', 'quform')),
            'elementTextareaHover' => array('name' => __('Textarea field (hover)', 'quform')),
            'elementTextareaFocus' => array('name' => __('Textarea field (focus)', 'quform')),
            'elementSelect' => array('name' => __('Select field', 'quform')),
            'elementSelectHover' => array('name' => __('Select field (hover)', 'quform')),
            'elementSelectFocus' => array('name' => __('Select field (focus)', 'quform')),
            'elementIcon' => array('name' => __('Text input icons', 'quform')),
            'elementIconHover' => array('name' => __('Text input icons (hover)', 'quform')),
            'elementSubLabel' => array('name' => __('Sub label', 'quform')),
            'elementDescription' => array('name' => __('Description', 'quform')),
            'options' => array('name' => __('Options outer wrapper', 'quform')),
            'option' => array('name' => __('Option wrapper', 'quform')),
            'optionLabel' => array('name' => __('Option label', 'quform')),
            'optionLabelSelected' => array('name' => __('Option label (when selected)', 'quform')),
            'optionIcon' => array('name' => __('Option icon', 'quform')),
            'optionIconSelected' => array('name' => __('Option icon (when selected)', 'quform')),
            'optionText' => array('name' => __('Option text', 'quform')),
            'optionTextSelected' => array('name' => __('Option text (when selected)', 'quform')),
            'page' => array('name' => __('Page wrapper', 'quform')),
            'pageTitle' => array('name' => __('Page title', 'quform')),
            'pageDescription' => array('name' => __('Page description', 'quform')),
            'pageElements' => array('name' => __('Page elements wrapper', 'quform')),
            'group' => array('name' => __('Group wrapper', 'quform')),
            'groupTitle' => array('name' => __('Group title', 'quform')),
            'groupDescription' => array('name' => __('Group description', 'quform')),
            'groupElements' => array('name' => __('Group elements wrapper', 'quform')),
            'submit' => array('name' => __('Submit button outer wrapper', 'quform')),
            'submitInner' => array('name' => __('Submit button inner wrapper', 'quform')),
            'submitButton' => array('name' => __('Submit button', 'quform')),
            'submitButtonHover' => array('name' => __('Submit button (hover)', 'quform')),
            'submitButtonActive' => array('name' => __('Submit button (active)', 'quform')),
            'submitButtonText' => array('name' => __('Submit button text', 'quform')),
            'submitButtonTextHover' => array('name' => __('Submit button text (hover)', 'quform')),
            'submitButtonTextActive' => array('name' => __('Submit button text (active)', 'quform')),
            'submitButtonIcon' => array('name' => __('Submit button icon', 'quform')),
            'submitButtonIconHover' => array('name' => __('Submit button icon (hover)', 'quform')),
            'submitButtonIconActive' => array('name' => __('Submit button icon (active)', 'quform')),
            'backInner' => array('name' => __('Back button inner wrapper', 'quform')),
            'backButton' => array('name' => __('Back button', 'quform')),
            'backButtonHover' => array('name' => __('Back button (hover)', 'quform')),
            'backButtonActive' => array('name' => __('Back button (active)', 'quform')),
            'backButtonText' => array('name' => __('Back button text', 'quform')),
            'backButtonTextHover' => array('name' => __('Back button text (hover)', 'quform')),
            'backButtonTextActive' => array('name' => __('Back button text (active)', 'quform')),
            'backButtonIcon' => array('name' => __('Back button icon', 'quform')),
            'backButtonIconHover' => array('name' => __('Back button icon (hover)', 'quform')),
            'backButtonIconActive' => array('name' => __('Back button icon (active)', 'quform')),
            'uploadButton' => array('name' => __('Upload button', 'quform')),
            'uploadButtonHover' => array('name' => __('Upload button (hover)', 'quform')),
            'uploadButtonActive' => array('name' => __('Upload button (active)', 'quform')),
            'uploadButtonText' => array('name' => __('Upload button text', 'quform')),
            'uploadButtonTextHover' => array('name' => __('Upload button text (hover)', 'quform')),
            'uploadButtonTextActive' => array('name' => __('Upload button text (active)', 'quform')),
            'uploadButtonIcon' => array('name' => __('Upload button icon', 'quform')),
            'uploadButtonIconHover' => array('name' => __('Upload button icon (hover)', 'quform')),
            'uploadButtonIconActive' => array('name' => __('Upload button icon (active)', 'quform'))
        );

        foreach ($styles as $key => $style) {
            $styles[$key]['config'] = array('type' => $key, 'css' => '');
        }

        return apply_filters('quform_admin_styles', $styles);
    }

    /**
     * Get all available global styles
     *
     * @param  string  $key  Only get the style with this key
     * @return array
     */
    public function getGlobalStyles($key = null)
    {
        $styles = array(
            'formOuter' => array('name' => _x('Form outer wrapper', 'the outermost HTML wrapper around the form', 'quform')),
            'formInner' => array('name' => _x('Form inner wrapper', 'the inner HTML wrapper around the form', 'quform')),
            'formSuccess' => array('name' => __('Success message', 'quform')),
            'formTitle' => array('name' => __('Form title', 'quform')),
            'formDescription' => array('name' => __('Form description', 'quform')),
            'formElements' => array('name' => _x('Form elements wrapper', 'the HTML wrapper around the form elements', 'quform')),
            'formError' => array('name' => __('Form error message', 'quform')),
            'formErrorInner' => array('name' => __('Form error message inner wrapper', 'quform')),
            'formErrorTitle' => array('name' => __('Form error message title', 'quform')),
            'formErrorContent' => array('name' => __('Form error message content', 'quform')),
            'element' => array('name' => _x('Element outer wrapper', 'outermost wrapping HTML element around an element', 'quform')),
            'elementLabel' => array('name' => __('Element label', 'quform')),
            'elementLabelText' => array('name' => __('Element label text', 'quform')),
            'elementRequiredText' => array('name' => __('Element required text', 'quform')),
            'elementInner' => array('name' => _x('Element inner wrapper', 'the inner HTML wrapper around the element', 'quform')),
            'elementInput' => array('name' => _x('Element input wrapper', 'the HTML wrapper around just the input', 'quform')),
            'elementText' => array('name' => __('Text input fields', 'quform')),
            'elementTextHover' => array('name' => __('Text input fields (hover)', 'quform')),
            'elementTextFocus' => array('name' => __('Text input fields (focus)', 'quform')),
            'elementTextarea' => array('name' => __('Textarea fields', 'quform')),
            'elementTextareaHover' => array('name' => __('Textarea fields (hover)', 'quform')),
            'elementTextareaFocus' => array('name' => __('Textarea fields (focus)', 'quform')),
            'elementSelect' => array('name' => __('Select fields', 'quform')),
            'elementSelectHover' => array('name' => __('Select fields (hover)', 'quform')),
            'elementSelectFocus' => array('name' => __('Select fields (focus)', 'quform')),
            'elementIcon' => array('name' => __('Text input icons', 'quform')),
            'elementIconHover' => array('name' => __('Text input icons (hover)', 'quform')),
            'elementSubLabel' => array('name' => __('Element sub label', 'quform')),
            'elementDescription' => array('name' => __('Element description', 'quform')),
            'options' => array('name' => _x('Options outer wrapper', 'the wrapper around the list of options for checkboxes and radio buttons', 'quform')),
            'option' => array('name' => _x('Option wrappers', 'the wrapper around each option for checkboxes and radio buttons', 'quform')),
            'optionLabel' => array('name' => __('Option labels', 'quform')),
            'optionLabelSelected' => array('name' => __('Option labels (when selected)', 'quform')),
            'optionIcon' => array('name' => __('Option icons', 'quform')),
            'optionIconSelected' => array('name' => __('Option icons (when selected)', 'quform')),
            'optionText' => array('name' => __('Option text', 'quform')),
            'optionTextSelected' => array('name' => __('Option text (when selected)', 'quform')),
            'elementError' => array('name' => __('Element error', 'quform')),
            'elementErrorInner' => array('name' => __('Element error inner wrapper', 'quform')),
            'elementErrorText' => array('name' => __('Element error text', 'quform')),
            'page' => array('name' => __('Page wrapper', 'quform')),
            'pageTitle' => array('name' => __('Page title', 'quform')),
            'pageDescription' => array('name' => __('Page description', 'quform')),
            'pageElements' => array('name' => __('Page elements wrapper', 'quform')),
            'group' => array('name' => __('Group wrapper', 'quform')),
            'groupTitle' => array('name' => __('Group title', 'quform')),
            'groupDescription' => array('name' => __('Group description', 'quform')),
            'groupElements' => array('name' => __('Group elements wrapper', 'quform')),
            'submit' => array('name' => __('Submit button outer wrapper', 'quform')),
            'submitInner' => array('name' => __('Submit button inner wrapper', 'quform')),
            'submitButton' => array('name' => __('Submit button', 'quform')),
            'submitButtonHover' => array('name' => __('Submit button (hover)', 'quform')),
            'submitButtonActive' => array('name' => __('Submit button (active)', 'quform')),
            'submitButtonText' => array('name' => __('Submit button text', 'quform')),
            'submitButtonTextHover' => array('name' => __('Submit button text (hover)', 'quform')),
            'submitButtonTextActive' => array('name' => __('Submit button text (active)', 'quform')),
            'submitButtonIcon' => array('name' => __('Submit button icon', 'quform')),
            'submitButtonIconHover' => array('name' => __('Submit button icon (hover)', 'quform')),
            'submitButtonIconActive' => array('name' => __('Submit button icon (active)', 'quform')),
            'backInner' => array('name' => __('Back button inner wrapper', 'quform')),
            'backButton' => array('name' => __('Back button', 'quform')),
            'backButtonHover' => array('name' => __('Back button (hover)', 'quform')),
            'backButtonActive' => array('name' => __('Back button (active)', 'quform')),
            'backButtonText' => array('name' => __('Back button text', 'quform')),
            'backButtonTextHover' => array('name' => __('Back button text (hover)', 'quform')),
            'backButtonTextActive' => array('name' => __('Back button text (active)', 'quform')),
            'backButtonIcon' => array('name' => __('Back button icon', 'quform')),
            'backButtonIconHover' => array('name' => __('Back button icon (hover)', 'quform')),
            'backButtonIconActive' => array('name' => __('Back button icon (active)', 'quform')),
            'uploadButton' => array('name' => __('Upload button', 'quform')),
            'uploadButtonHover' => array('name' => __('Upload button (hover)', 'quform')),
            'uploadButtonActive' => array('name' => __('Upload button (active)', 'quform')),
            'uploadButtonText' => array('name' => __('Upload button text', 'quform')),
            'uploadButtonTextHover' => array('name' => __('Upload button text (hover)', 'quform')),
            'uploadButtonTextActive' => array('name' => __('Upload button text (active)', 'quform')),
            'uploadButtonIcon' => array('name' => __('Upload button icon', 'quform')),
            'uploadButtonIconHover' => array('name' => __('Upload button icon (hover)', 'quform')),
            'uploadButtonIconActive' => array('name' => __('Upload button icon (active)', 'quform'))
        );

        foreach ($styles as $k => $style) {
            $styles[$k]['config'] = array('type' => $k, 'css' => '');
        }

        $styles = apply_filters('quform_admin_global_styles', $styles);

        if (is_string($key)) {
            if (isset($styles[$key])) {
                return $styles[$key];
            } else {
                return null;
            }
        }

        return $styles;
    }

    /**
     * Get the HTML for a style
     *
     * @return string
     */
    protected function getStyleHtml()
    {
        ob_start(); ?>
        <div class="qfb-style qfb-box">
            <div class="qfb-style-inner qfb-cf">
                <div class="qfb-style-actions">
                    <span class="qfb-style-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                    <span class="qfb-style-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                </div>
                <div class="qfb-style-title"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a global style
     *
     * @param   array   $style
     * @return  string
     */
    public function getGlobalStyleHtml(array $style = array())
    {
        $styles = $this->getGlobalStyles();
        $name = ! empty($style) && isset($styles[$style['type']]) ? $styles[$style['type']]['name'] : '';

        ob_start(); ?>
        <div class="qfb-global-style qfb-box"<?php echo !empty($style) ? sprintf(' data-style="%s"', Quform::escape(wp_json_encode($style))) : ''; ?>>
            <div class="qfb-global-style-inner qfb-cf">
                <div class="qfb-global-style-actions">
                    <span class="qfb-global-style-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                    <span class="qfb-global-style-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                </div>
                <div class="qfb-global-style-title"><?php echo esc_html($name); ?></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Gets the list of styles that are visible for each element
     *
     * @return array
     */
    protected function getVisibleStyles()
    {
        $visible = array(
            'text' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'email' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'password' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'captcha' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'textarea' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementTextarea', 'elementTextareaHover', 'elementTextareaFocus', 'elementSubLabel', 'elementDescription'),
            'select' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementSelect', 'elementSelectHover', 'elementSelectFocus', 'elementSubLabel', 'elementDescription'),
            'file' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'uploadButton', 'uploadButtonHover', 'uploadButtonActive', 'uploadButtonText', 'uploadButtonTextHover', 'uploadButtonTextActive', 'uploadButtonIcon', 'uploadButtonIconHover', 'uploadButtonIconActive', 'elementSubLabel', 'elementDescription'),
            'recaptcha' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementSubLabel', 'elementDescription'),
            'date' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementText', 'elementIcon', 'elementIconHover', 'elementSubLabel', 'elementDescription'),
            'time' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementText', 'elementIcon', 'elementIconHover', 'elementSubLabel', 'elementDescription'),
            'radio' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'options', 'option', 'optionLabel', 'optionLabelSelected', 'optionIcon', 'optionIconSelected', 'optionText', 'optionTextSelected', 'elementSubLabel', 'elementDescription'),
            'checkbox' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'options', 'option', 'optionLabel', 'optionLabelSelected', 'optionIcon', 'optionIconSelected', 'optionText', 'optionTextSelected', 'elementSubLabel', 'elementDescription'),
            'multiselect' => array('element', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInput', 'elementSubLabel', 'elementDescription'),
            'page' => array('page', 'pageTitle', 'pageDescription', 'pageElements'),
            'group' => array('group', 'groupTitle', 'groupDescription', 'groupElements'),
            'submit' => array('submit', 'submitInner', 'submitButton', 'submitButtonHover', 'submitButtonActive', 'submitButtonText', 'submitButtonTextHover', 'submitButtonTextActive', 'submitButtonIcon', 'submitButtonIconHover', 'submitButtonIconActive', 'backInner', 'backButton', 'backButtonHover', 'backButtonActive', 'backButtonText', 'backButtonTextHover', 'backButtonTextActive', 'backButtonIcon', 'backButtonIconHover', 'backButtonIconActive', 'nextInner', 'nextButton', 'nextButtonHover', 'nextButtonActive', 'nextButtonText', 'nextButtonTextHover', 'nextButtonTextActive', 'nextButtonIcon', 'nextButtonIconHover', 'nextButtonIconActive')
        );

        $visible = apply_filters('quform_visible_styles', $visible);

        return $visible;
    }

    /**
     * Get the list of filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            'alpha' => array(
                'name' => _x('Alpha', 'the alphabet filter', 'quform'),
                'tooltip' => __('Removes any non-alphabet characters', 'quform'),
                'config' => Quform_Filter_Alpha::getDefaultConfig()
            ),
            'alphaNumeric' => array(
                'name' => _x('Alphanumeric', 'the alphanumeric filter', 'quform'),
                'tooltip' => __('Removes any non-alphabet characters and non-digits', 'quform'),
                'config' => Quform_Filter_AlphaNumeric::getDefaultConfig()
            ),
            'digits' => array(
                'name' => _x('Digits', 'the digits filter', 'quform'),
                'tooltip' => __('Removes any non-digits', 'quform'),
                'config' => Quform_Filter_Digits::getDefaultConfig()
            ),
            'stripTags' => array(
                'name' => _x('Strip Tags', 'the strip tags filter', 'quform'),
                'tooltip' => __('Removes any HTML tags', 'quform'),
                'config' => Quform_Filter_StripTags::getDefaultConfig()
            ),
            'trim' => array(
                'name' => _x('Trim', 'the trim filter', 'quform'),
                'tooltip' => __('Removes white space from the start and end', 'quform'),
                'config' => Quform_Filter_Trim::getDefaultConfig()
            ),
            'regex' => array(
                'name' => _x('Regex', 'the regex filter', 'quform'),
                'tooltip' => __('Removes characters matching the given regular expression', 'quform'),
                'config' => Quform_Filter_Regex::getDefaultConfig()
            )
        );

        $filters = apply_filters('quform_admin_filters', $filters);

        return $filters;
    }

    /**
     * Get the HTML for a filter
     *
     * @return string
     */
    protected function getFilterHtml()
    {
        ob_start();
        ?>
        <div class="qfb-filter qfb-box">
            <div class="qfb-filter-inner qfb-cf">
                <div class="qfb-filter-actions">
                    <span class="qfb-filter-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                    <span class="qfb-filter-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                </div>
                <div class="qfb-filter-title"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the list of visible filters for the elements
     *
     * @return array
     */
    protected function getVisibleFilters()
    {
        $visible = array(
            'text' => array('alpha', 'alphaNumeric', 'digits', 'stripTags', 'trim', 'regex'),
            'email' => array('trim'),
            'textarea' => array('alpha', 'alphaNumeric', 'digits', 'stripTags', 'trim', 'regex')
        );

        $visible = apply_filters('quform_visible_filters', $visible);

        return $visible;
    }

    /**
     * Get the validator configurations
     *
     * @return array
     */
    public function getValidators()
    {
        $validators = array(
            'alpha' => array(
                'name' => _x('Alpha', 'the alphabet validator', 'quform'),
                'tooltip' => __('Checks that the value contains only alphabet characters', 'quform'),
                'config' => Quform_Validator_Alpha::getDefaultConfig()
            ),
            'alphaNumeric' => array(
                'name' => _x('Alphanumeric', 'the alphanumeric validator', 'quform'),
                'tooltip' => __('Checks that the value contains only alphabet or digit characters', 'quform'),
                'config' => Quform_Validator_AlphaNumeric::getDefaultConfig()
            ),
            'digits' => array(
                'name' => _x('Digits', 'the digits validator', 'quform'),
                'tooltip' => __('Checks that the value contains only digits', 'quform'),
                'config' => Quform_Validator_Digits::getDefaultConfig()
            ),
            'duplicate' => array(
                'name' => _x('Prevent Duplicates', 'the duplicate validator', 'quform'),
                'tooltip' => __('Checks that the same value has not already been submitted', 'quform'),
                'config' => Quform_Validator_Duplicate::getDefaultConfig()
            ),
            'email' => array(
                'name' => _x('Email', 'the strip tags validator', 'quform'),
                'tooltip' => __('Checks that the value is a valid email address', 'quform'),
                'config' => Quform_Validator_Email::getDefaultConfig()
            ),
            'greaterThan' => array(
                'name' => _x('Greater Than', 'the greater than validator', 'quform'),
                'tooltip' => __('Checks that the value is numerically greater than the given minimum', 'quform'),
                'config' => Quform_Validator_GreaterThan::getDefaultConfig()
            ),
            'identical' => array(
                'name' => _x('Identical', 'the identical validator', 'quform'),
                'tooltip' => __('Checks that the value is identical to the given token', 'quform'),
                'config' => Quform_Validator_Identical::getDefaultConfig()
            ),
            'inArray' => array(
                'name' => _x('In Array', 'the in array validator', 'quform'),
                'tooltip' => __('Checks that the value is in a list of allowed values', 'quform'),
                'config' => Quform_Validator_InArray::getDefaultConfig()
            ),
            'lessThan' => array(
                'name' => _x('Less Than', 'the less than validator', 'quform'),
                'tooltip' => __('Checks that the value is numerically less than the given maximum', 'quform'),
                'config' => Quform_Validator_LessThan::getDefaultConfig()
            ),
            'length' => array(
                'name' => _x('Length', 'the length validator', 'quform'),
                'tooltip' => __('Checks that the length of the value is between the given maximum and minimum', 'quform'),
                'config' => Quform_Validator_Length::getDefaultConfig()
            ),
            'regex' => array(
                'name' => _x('Regex', 'the regex validator', 'quform'),
                'tooltip' => __('Checks that the value matches the given regular expression', 'quform'),
                'config' => Quform_Validator_Regex::getDefaultConfig()
            )
        );

        $validators = apply_filters('quform_admin_validators', $validators);

        return $validators;
    }

    /**
     * Get the HTML for a validator
     *
     * @return string
     */
    protected function getValidatorHtml()
    {
        ob_start();
        ?>
        <div class="qfb-validator qfb-box">
            <div class="qfb-validator-inner qfb-cf">
                <div class="qfb-validator-actions">
                    <span class="qfb-validator-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                    <span class="qfb-validator-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                </div>
                <div class="qfb-validator-title"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the list of visible validators for the elements
     *
     * @return array
     */
    protected function getVisibleValidators()
    {
        $visible = array(
            'text' => array('alpha', 'alphaNumeric', 'digits', 'duplicate', 'email', 'greaterThan', 'identical', 'inArray', 'length', 'lessThan', 'regex'),
            'textarea' => array('alpha', 'alphaNumeric', 'digits', 'duplicate', 'email', 'greaterThan', 'identical', 'inArray', 'length', 'lessThan', 'regex'),
            'email' => array('duplicate', 'regex'),
            'password' => array('alpha', 'alphaNumeric', 'digits', 'identical', 'inArray', 'length', 'regex'),
            'select' => array('duplicate', 'greaterThan', 'identical', 'inArray', 'lessThan', 'regex'),
            'checkbox' => array('duplicate'),
            'radio' => array('duplicate', 'greaterThan', 'identical', 'inArray', 'lessThan', 'regex'),
            'multiselect' => array('duplicate'),
            'date' => array('duplicate', 'inArray'),
            'time' => array('duplicate', 'inArray'),
            'name' => array('duplicate', 'inArray')
        );

        $visible = apply_filters('quform_visible_validators', $visible);

        return $visible;
    }

    /**
     * Get the HTML for a notification
     *
     * @param   array   $notification
     * @return  string
     */
    public function getNotificationHtml($notification = null)
    {
        if ( ! is_array($notification)) {
            $notification = Quform_Notification::getDefaultConfig();
            $notification['id'] = 0;
        }

        ob_start();
        ?>
        <div class="qfb-notification qfb-box qfb-cf" data-id="<?php echo esc_attr($notification['id']); ?>">
            <div class="qfb-notification-name"><?php echo esc_html($notification['name']); ?></div>
            <div class="qfb-notification-actions">
                <span class="qfb-notification-action-toggle" title="<?php esc_attr_e('Toggle enabled/disabled', 'quform'); ?>"><input type="checkbox" id="qfb-notification-toggle-<?php echo esc_attr($notification['id']); ?>" class="qfb-notification-toggle qfb-mini-toggle" <?php checked($notification['enabled']); ?>><label for="qfb-notification-toggle-<?php echo esc_attr($notification['id']); ?>"></label></span>
                <span class="qfb-notification-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                <span class="qfb-notification-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="mdi mdi-content_copy"></i></span>
                <span class="qfb-notification-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
            </div>
            <div class="qfb-notification-subject"><span class="qfb-notification-subject-text"><?php echo esc_html($notification['subject']); ?></span></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for an email recipient
     *
     * @return  string
     */
    public function getRecipientHtml()
    {
        ob_start();
        ?>
        <div class="qfb-recipient">
            <div class="qfb-recipient-inner qfb-cf">
                <div class="qfb-recipient-left">
                    <select class="qfb-recipient-type">
                        <option value="to"><?php esc_html_e('To', 'quform'); ?></option>
                        <option value="cc"><?php esc_html_e('Cc', 'quform'); ?></option>
                        <option value="bcc"><?php esc_html_e('Bcc', 'quform'); ?></option>
                        <option value="reply"><?php esc_html_e('Reply-To', 'quform'); ?></option>
                    </select>
                </div>
                <div class="qfb-recipient-right">
                    <div class="qfb-recipient-right-inner">
                        <div class="qfb-settings-row qfb-settings-row-2">
                            <div class="qfb-settings-column">
                                <div class="qfb-input-variable">
                                    <input class="qfb-recipient-address" type="text" placeholder="<?php esc_attr_e('Email address (required)', 'quform'); ?>">
                                    <?php echo $this->getInsertVariableHtml(); ?>
                                </div>
                            </div>
                            <div class="qfb-settings-column">
                                <div class="qfb-input-variable">
                                    <input class="qfb-recipient-name" type="text" placeholder="<?php esc_attr_e('Name (optional)', 'quform'); ?>">
                                    <?php echo $this->getInsertVariableHtml(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="qfb-small-remove-button fa fa-trash" title="<?php esc_attr_e('Remove', 'quform'); ?>"></span>
                <span class="qfb-small-add-button mdi mdi-add_circle" title="<?php esc_attr_e('Add', 'quform'); ?>"></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for the insert variable button
     *
     * @param   string  $targetId    The unique ID of the target field
     * @param   bool    $preProcess  Whether it is the pre process variables
     * @return  string
     */
    public function getInsertVariableHtml($targetId = '', $preProcess = false)
    {
        return sprintf(
            '<span class="qfb-insert-variable%s" title="%s"%s><i class="fa fa-code"></i></span>',
            $preProcess ? ' qfb-insert-variable-pre-process' : '',
            esc_attr__('Insert variable...', 'quform'),
            $targetId ? ' data-target-id="' . esc_attr($targetId) . '"' : ''
        );
    }

    /**
     * Get the HTML for a confirmation
     *
     * @param   array   $confirmation
     * @return  string
     */
    public function getConfirmationHtml($confirmation = null)
    {
        if ( ! is_array($confirmation)) {
            $confirmation = Quform_Confirmation::getDefaultConfig();
            $confirmation['id'] = 0;
        }

        ob_start();
        ?>
        <div class="qfb-confirmation qfb-box qfb-cf" data-id="<?php echo esc_attr($confirmation['id']); ?>">
            <div class="qfb-confirmation-name"><?php echo esc_html($confirmation['name']); ?></div>
            <div class="qfb-confirmation-actions">
                <?php if ($confirmation['id'] != 1) : ?>
                    <span class="qfb-confirmation-action-toggle" title="<?php esc_attr_e('Toggle enabled/disabled', 'quform'); ?>"><input type="checkbox" id="qfb-confirmation-toggle-<?php echo esc_attr($confirmation['id']); ?>" class="qfb-confirmation-toggle qfb-mini-toggle" <?php checked($confirmation['enabled']); ?>><label for="qfb-confirmation-toggle-<?php echo esc_attr($confirmation['id']); ?>"></label></span>
                    <span class="qfb-confirmation-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                <?php endif; ?>
                <span class="qfb-confirmation-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="mdi mdi-content_copy"></i></span>
                <span class="qfb-confirmation-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
            </div>
            <div class="qfb-confirmation-description"><?php echo $this->getConfirmationDescription($confirmation); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the confirmation description
     *
     * Changes should be mirrored in builder.confirmations.js:getConfirmationDescription
     *
     * @param   array   $confirmation
     * @return  string
     */
    protected function getConfirmationDescription(array $confirmation)
    {
        $type = $confirmation['type'];

        $output = sprintf(
            '<div class="qfb-settings-row%s">',
            $type == 'message-redirect-page' || $type == 'message-redirect-url' ? ' qfb-settings-row-2' : ''
        );

        $output .= '<div class="qfb-settings-column">';

        switch ($type) {
            case 'message':
            case 'message-redirect-page':
            case 'message-redirect-url':
                $output .= sprintf('<i class="mdi mdi-message" title="%s"></i>', esc_attr__('Display a message', 'quform'));
                $output .= sprintf(
                    '<span class="qfb-confirmation-description-message">%s</span>',
                    Quform::escape(mb_substr(strip_tags($confirmation['message']), 0, 64))
                );
                break;
            case 'redirect-page';
                $output .= sprintf('<i class="mdi mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                $output .= sprintf(
                    '<span class="qfb-confirmation-description-redirect-page">%s</span>',
                    Quform::escape(Quform::getPostTitle(get_post($confirmation['redirectPage'])))
                );
                break;
            case 'redirect-url';
                $output .= sprintf('<i class="mdi mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                $output .= sprintf(
                    '<span class="qfb-confirmation-description-redirect-url">%s</span>',
                    Quform::escape($confirmation['redirectUrl'])
                );
                break;
            case 'reload';
                $output .= sprintf('<i class="mdi mdi-refresh" title="%s"></i>', esc_attr__('Reload the page', 'quform'));
                break;
        }

        $output .= '</div>';

        if ($type == 'message-redirect-page' || $type == 'message-redirect-url') {
            $output .= '<div class="qfb-settings-column">';

            switch ($type) {
                case 'message-redirect-page';
                    $output .= sprintf('<i class="mdi mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                    $output .= sprintf(
                        '<span class="qfb-confirmation-description-redirect-page">%s</span>',
                        Quform::escape(Quform::getPostTitle(get_post($confirmation['redirectPage'])))
                    );
                    break;
                case 'message-redirect-url';
                    $output .= sprintf('<i class="mdi mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                    $output .= sprintf(
                        '<span class="qfb-confirmation-description-redirect-url">%s</span>',
                        Quform::escape($confirmation['redirectUrl'])
                    );
                    break;
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for a select menu of available title tag options
     *
     * @param   string  $id        The ID of the field
     * @param   string  $selected  The selected value
     * @return  string
     */
    public function getTitleTagSelectHtml($id, $selected = '')
    {
        $tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span');
        $output = sprintf('<select id="%s">', $id);

        foreach ($tags as $tag) {
            $output .= sprintf('<option value="%1$s"%2$s>%1$s</option>', Quform::escape($tag), $selected == $tag ? ' selected="selected"' : '');
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * Get the HTML for the custom database column settings
     *
     * @param   array|null  $column  The existing column config
     * @return  string
     */
    public function getDbColumnHtml($column = null)
    {
        if ( ! is_array($column)) {
            $column = array(
                'name' => '',
                'value' => ''
            );
        }

        $variableId = uniqid('q');
        ob_start();
        ?>
        <div class="qfb-form-db-column qfb-cf">
            <input type="text" class="qfb-form-db-column-name" placeholder="<?php esc_attr_e('Column', 'quform'); ?>" value="<?php echo esc_attr($column['name']); ?>">
            <input id="<?php echo esc_attr($variableId); ?>" type="text" class="qfb-form-db-column-value" placeholder="<?php esc_attr_e('Value', 'quform'); ?>" value="<?php echo esc_attr($column['value']); ?>">
            <?php echo $this->getInsertVariableHtml($variableId); ?>
            <span class="qfb-small-remove-button fa fa-trash" title="<?php esc_attr_e('Remove', 'quform'); ?>"></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the default form configuration array with populated default settings
     *
     * @return array
     */
    public function getDefaultForm()
    {
        $page = $this->getDefaultElementConfig('page');
        $page['id'] = 1;
        $page['parentId'] = 0;
        $page['position'] = 0;

        $submit = $this->getDefaultElementConfig('submit');
        $submit['id'] = 2;
        $submit['parentId'] = 1;
        $submit['position'] = 0;

        $page['elements'] = array($submit);

        $notification = Quform_Notification::getDefaultConfig();
        $notification['id'] = 1;
        $notification['name'] = __('Admin notification', 'quform');
        $notification['html'] = '{all_form_data}';

        $confirmation = Quform_Confirmation::getDefaultConfig();
        $confirmation['id'] = 1;
        $confirmation['name'] = __('Default confirmation', 'quform');
        $confirmation['message'] = __('Your message has been sent, thanks.', 'quform');
        $confirmation['messageIcon'] = 'qicon-check';

        $form = Quform_Form::getDefaultConfig();
        $form['nextElementId'] = 3;
        $form['elements'] = array($page);
        $form['nextNotificationId'] = 2;
        $form['notifications'] = array($notification);
        $form['nextConfirmationId'] = 2;
        $form['confirmations'] = array($confirmation);

        $form = apply_filters('quform_default_form', $form);

        return $form;
    }

    /**
     * @param   array   $form
     * @param   string  $key
     * @return  mixed
     */
    public function getFormConfigValue($form, $key)
    {
        $value = Quform::get($form, $key);

        if ($value === null) {
            $value = Quform::get(Quform_Form::getDefaultConfig(), $key);
        }

        return $value;
    }

    /**
     * Get the HTML for all pages and elements for the form builder
     *
     * @param   array   $elements  The array of element configs
     * @return  string
     */
    public function renderFormElements($elements)
    {
        $output = '';

        foreach ($elements as $element) {
            $output .= $this->getElementHtml($element);
        }

        return $output;
    }

    /**
     * Get the HTML for an element in the form builder
     *
     * @param   array   $element  The element config
     * @return  string
     */
    protected function getElementHtml(array $element)
    {
        switch ($element['type']) {
            case 'page':
                $output = $this->getPageHtml($element);
                break;
            case 'group':
                $output = $this->getGroupHtml($element);
                break;
            case 'row':
                $output = $this->getRowHtml($element);
                break;
            case 'column':
                $output = $this->getColumnHtml($element);
                break;
            default:
                $output = $this->getFieldHtml($element);
                break;
        }

        return $output;
    }

    /**
     * Get the HTML for a page for the form builder
     *
     * @param   array   $element  The page config
     * @return  string
     */
    protected function getPageHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-page" data-id="<?php echo esc_attr($element['id']); ?>" data-type="page">
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf">
                <?php
                    foreach ($element['elements'] as $child) {
                        echo $this->getElementHtml($child);
                    }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a group for the form builder
     *
     * @param   array  $element  The group config
     * @return  string           The HTML
     */
    protected function getGroupHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-group" data-id="<?php echo esc_attr($element['id']); ?>" data-type="group">
            <div class="qfb-element-inner qfb-cf">
                <span class="qfb-element-type-icon"><i class="fa fa-object-group"></i></span>
                <label class="qfb-preview-label<?php echo ( ! Quform::isNonEmptyString($element['label']) ? ' qfb-hidden' : ''); ?>"><span id="qfb-plc-<?php echo esc_attr($element['id']); ?>" class="qfb-preview-label-content"><?php echo esc_html($element['label']); ?></span></label>
                <div class="qfb-element-actions">
                    <span class="qfb-element-action-collapse" title="<?php esc_attr_e('Collapse', 'quform'); ?>"><i class="mdi mdi-remove_circle_outline"></i></span>
                    <span class="qfb-element-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                    <span class="qfb-element-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="mdi mdi-content_copy"></i></span>
                    <span class="qfb-element-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                </div>
            </div>
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf">
                <?php
                    foreach ($element['elements'] as $child) {
                        echo $this->getElementHtml($child);
                    }
                ?>
            </div>
            <div class="qfb-element-group-empty-indicator"><span class="qfb-element-group-empty-indicator-arrow"><i class="fa fa-arrow-down"></i></span><span class="qfb-element-group-empty-add-row" title="<?php esc_attr_e('Add column layout'); ?>"><i class="fa fa-columns"></i><i class="mdi mdi-add_circle"></i></span></div>
        </div>
        <?php
        return ob_get_clean();

    }

    /**
     * Get the HTML for a row for the form builder
     *
     * @param   array   $element  The row config
     * @return  string
     */
    protected function getRowHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-row" data-id="<?php echo esc_attr($element['id']); ?>" data-type="row">
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf qfb-<?php echo esc_attr(count($element['elements'])); ?>-columns">
            <?php
                foreach ($element['elements'] as $child) {
                    echo $this->getElementHtml($child);
                }
            ?>
            </div>
            <div class="qfb-row-actions">
                <span class="qfb-row-action-add-column" title="<?php esc_attr_e('Add column', 'quform'); ?>"><i class="mdi mdi-add_circle"></i></span>
                <span class="qfb-row-action-remove-column" title="<?php esc_attr_e('Remove column', 'quform'); ?>"><i class="mdi mdi-remove_circle"></i></span>
                <span class="qfb-row-action-remove" title="<?php esc_attr_e('Remove row', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                <span class="qfb-row-action-duplicate" title="<?php esc_attr_e('Duplicate row', 'quform'); ?>"><i class="mdi mdi-content_copy"></i></span>
                <span class="qfb-row-action-settings" title="<?php esc_attr_e('Row settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                <span class="qfb-row-action-move" title="<?php esc_attr_e('Move row', 'quform'); ?>"><i class="fa fa-arrows"></i></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a column for the form builder
     *
     * @param   array   $element  The column config
     * @return  string
     */
    protected function getColumnHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-column" data-id="<?php echo esc_attr($element['id']); ?>" data-type="column">
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf">
                <?php
                foreach ($element['elements'] as $child) {
                    echo $this->getElementHtml($child);
                }
                ?>
            </div>
            <div class="qfb-column-actions">
                <span class="qfb-column-action-remove" title="<?php esc_attr_e('Remove column', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                <span class="qfb-column-action-duplicate" title="<?php esc_attr_e('Duplicate column', 'quform'); ?>"><i class="mdi mdi-content_copy"></i></span>
                <span class="qfb-column-action-move" title="<?php esc_attr_e('Move column', 'quform'); ?>"><i class="fa fa-arrows"></i></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a field for the form builder
     *
     * @param   array  $element  The element config
     * @return  string           The HTML
     */
    protected function getFieldHtml(array $element)
    {
        $data = $this->getElements($element['type']);

        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-<?php echo esc_attr($element['type']) . (isset($element['required']) && $element['required'] ? ' qfb-element-required' : ''); ?>" data-id="<?php echo esc_attr($element['id']); ?>" data-type="<?php echo esc_attr($element['type']); ?>">
            <div class="qfb-element-inner qfb-cf">
                <span class="qfb-element-type-icon"><?php echo $data['icon']; ?></span>
                <label class="qfb-preview-label<?php echo ( ! Quform::isNonEmptyString($element['label']) ? ' qfb-hidden' : ''); ?>"><span id="qfb-plc-<?php echo esc_attr($element['id']); ?>" class="qfb-preview-label-content"><?php echo esc_html($element['label']); ?></span></label>
                <div class="qfb-element-actions">
                    <span class="qfb-element-action-required" title="<?php esc_attr_e('Toggle required', 'quform'); ?>"><i class="mdi mdi-done"></i></span>
                    <span class="qfb-element-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="fa fa-trash"></i></span>
                    <span class="qfb-element-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="mdi mdi-content_copy"></i></span>
                    <span class="qfb-element-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="mdi mdi-settings"></i></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a default element with the given type
     *
     * @param   string  $type  The element type
     * @return  string
     */
    protected function getDefaultElementHtml($type)
    {
        $element = $this->getDefaultElementConfig($type);
        $element['id'] = 0;

        return $this->getElementHtml($element);
    }

    /**
     * Get the HTML for a single page tab nav
     *
     * @param   int     $key
     * @param   array   $elementId
     * @param   string  $label
     * @return  string
     */
    public function getPageTabNavHtml($key = null, $elementId = null, $label = null)
    {
        $output = '<li class="qfb-page-tab-nav k-item' . ($key === 0 ? ' qfb-current-page k-state-active' : '') . '"' . (is_numeric($elementId) ? sprintf(' data-id="%d"', esc_attr($elementId)) : '') . '>';

        $output .= sprintf('<span class="qfb-page-tab-nav-label">%s</span>', Quform::isNonEmptyString($label) ? esc_html($label) : esc_html__('Untitled page', 'quform'));

        $output .= '<span class="qfb-page-actions">';

        $output .= '<span class="qfb-page-action-settings" title="' . esc_attr__('Settings', 'quform') . '"><i class="mdi mdi-settings"></i></span>';
        $output .= '<span class="qfb-page-action-duplicate" title="' . esc_attr__('Duplicate', 'quform') . '"><i class="mdi mdi-content_copy"></i></span>';
        $output .= '<span class="qfb-page-action-remove" title="' . esc_attr__('Remove', 'quform') . '"><i class="fa fa-trash"></i></span>';

        $output .= '</span></li>';

        return $output;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        $variables = $this->getPreProcessVariables();

        $variables['general']['variables']['{entry_id}'] = __('Entry ID', 'quform');
        $variables['general']['variables']['{form_name}'] = __('Form Name', 'quform');
        $variables['general']['variables']['{all_form_data}'] = __('All Form Data', 'quform');
        $variables['general']['variables']['{default_email_address}'] = __('Default Email Address', 'quform');
        $variables['general']['variables']['{default_email_name}'] = __('Default Email Name', 'quform');
        $variables['general']['variables']['{default_from_email_address}'] = __('Default "From" Email Address', 'quform');
        $variables['general']['variables']['{default_from_email_name}'] = __('Default "From" Email Name', 'quform');
        $variables['general']['variables']['{admin_email}'] = __('Admin Email', 'quform');

        return apply_filters('quform_variables', $variables);
    }

    /**
     * @return array
     */
    public function getPreProcessVariables()
    {
        return apply_filters('quform_pre_process_variables', array(
            'general' => array(
                'heading' => __('General', 'quform'),
                'variables' => array(
                    '{url}' => __('Form URL', 'quform'),
                    '{referring_url}' => __('Referring URL', 'quform'),
                    '{post|ID}' => __('Post ID', 'quform'),
                    '{post|post_title}' => __('Post Title', 'quform'),
                    '{date}' => __('Date', 'quform'),
                    '{time}' => __('Time', 'quform'),
                    '{site_title}' => __('Site Title', 'quform'),
                    '{site_tagline}' => __('Site Description', 'quform')
                )
            ),
            'user' => array(
                'heading' => __('User', 'quform'),
                'variables' => array(
                    '{ip}' => __('IP Address', 'quform'),
                    '{user_agent}' => __('User Agent', 'quform'),
                    '{user|display_name}' => __('Display Name', 'quform'),
                    '{user|user_email}' => __('Email', 'quform'),
                    '{user|user_login}' => __('Login', 'quform')
                )
            )
        ));
    }

    /**
     * The supported reCAPTCHA languages from https://developers.google.com/recaptcha/docs/language
     *
     * @return array
     */
    public function getRecaptchaLanguages()
    {
        return array(
            '' => __('Autodetect', 'quform'),
            'ar' => 'Arabic',
            'bn' => 'Bengali',
            'bg' => 'Bulgarian',
            'ca' => 'Catalan',
            'zh-CN' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en-GB' => 'English (UK)',
            'en' => 'English',
            'et' => 'Estonian',
            'fil' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'fr-CA' => 'French (Canadian)',
            'de' => 'German',
            'gu' => 'Gujarati',
            'de-AT' => 'German (Austria)',
            'de-CH' => 'German (Switzerland)',
            'el' => 'Greek',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mr' => 'Marathi',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt-BR' => 'Portuguese (Brazil)',
            'pt-PT' => 'Portuguese (Portugal)',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'es-419' => 'Spanish (Latin America)',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'vi' => 'Vietnamese'
        );
    }

    /**
     * Get the HTML for a blank logic rule
     *
     * @return string
     */
    protected function getLogicRuleHtml()
    {
        $output = '<div class="qfb-logic-rule qfb-box">';
        $output .= '<div class="qfb-logic-rule-columns qfb-cf">';
        $output .= '<div class="qfb-logic-rule-column qfb-logic-rule-column-element"></div>';
        $output .= '<div class="qfb-logic-rule-column qfb-logic-rule-column-operator"></div>';
        $output .= '<div class="qfb-logic-rule-column qfb-logic-rule-column-value"></div>';
        $output .= '</div>';
        $output .= sprintf('<span class="qfb-small-add-button mdi mdi-add_circle" title="%s"></span>', esc_attr__('Add new logic rule', 'quform'));
        $output .= sprintf('<span class="qfb-small-remove-button fa fa-trash" title="%s"></span>', esc_attr__('Remove logic rule', 'quform'));
        $output .= '</div>';

        return $output;
    }

    /**
     * Get the element types that can be used as a source for conditional logic
     *
     * @return array
     */
    protected function getLogicSourceTypes()
    {
        return apply_filters('quform_logic_source_types', array(
            'text', 'textarea', 'email', 'select', 'radio', 'checkbox', 'multiselect', 'file', 'date', 'time', 'hidden', 'password'
        ));
    }

    /**
     * Get the element types than can be used as a source for attachments
     *
     * @return array
     */
    protected function getAttachmentSourceTypes()
    {
        return apply_filters('quform_attachment_source_types', array(
            'file'
        ));
    }

    /**
     * Handle the request to save the form via Ajax
     */
    public function save()
    {
        $this->validateSaveRequest();

        $config = json_decode(stripslashes($_POST['form']), true);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Malformed form configuration', 'quform')
            ));
        }

        $config = $this->sanitiseForm($config);

        $this->validateForm($config);

        $config = $this->repository->save($config);

        $this->scriptLoader->handleSaveForm($config);

        wp_send_json(array(
            'type' => 'success'
        ));
    }

    /**
     * Validate the request to save the form
     */
    protected function validateSaveRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['form'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! Quform::currentUserCan('quform_edit_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_save_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Sanitise the given form config and return it
     *
     * @param   array  $config
     * @return  array
     */
    public function sanitiseForm(array $config)
    {
        $config['name'] = sanitize_text_field($config['name']);

        foreach($config['elements'] as $key => $page) {
            $config['elements'][$key] = $this->sanitisePage($page);
        }

        return $config;
    }

    /**
     * Sanitise the given page config and return it
     *
     * @param   array  $page
     * @return  array
     */
    protected function sanitisePage(array $page)
    {
        $page = $this->sanitiseContainer($page);

        return $page;
    }

    /**
     * Sanitise the given container config and return it
     *
     * @param   array  $container
     * @return  array
     */
    protected function sanitiseContainer(array $container)
    {
        foreach($container['elements'] as $key => $element) {
            $container['elements'][$key] = $this->sanitiseElement($element);

            if ($element['type'] == 'group' || $element['type'] == 'row' || $element['type'] == 'column') {
                $container['elements'][$key] = $this->sanitiseContainer($element);
            }
        }

        return $container;
    }

    /**
     * Sanitise the given element config and return it
     *
     * @param   array  $element
     * @return  array
     */
    protected function sanitiseElement(array $element)
    {
        switch ($element['type']) {
            case 'captcha':
                $element['captchaLength'] = isset($element['captchaLength']) && is_numeric($element['captchaLength']) ? (string) Quform::clamp((int) $element['captchaLength'], 2, 32) : Quform_Element_Captcha::getDefaultConfig('captchaLength');
                $element['captchaWidth'] = isset($element['captchaWidth']) && is_numeric($element['captchaWidth']) ? (string) Quform::clamp((int) $element['captchaWidth'], 20, 300) : Quform_Element_Captcha::getDefaultConfig('captchaWidth');
                $element['captchaHeight'] = isset($element['captchaHeight']) && is_numeric($element['captchaHeight']) ? (string) Quform::clamp((int) $element['captchaHeight'], 10, 300) : Quform_Element_Captcha::getDefaultConfig('captchaHeight');
                $element['captchaBgColor'] = isset($element['captchaBgColor']) && Quform::isNonEmptyString($element['captchaBgColor']) ? sanitize_text_field($element['captchaBgColor']) : Quform_Element_Captcha::getDefaultConfig('captchaBgColor');
                $element['captchaBgColorRgba'] = is_array($element['captchaBgColorRgba']) ? $this->sanitiseRgbColorArray($element['captchaBgColorRgba']) : Quform_Element_Captcha::getDefaultConfig('captchaBgColorRgba');
                $element['captchaTextColor'] = isset($element['captchaTextColor']) && Quform::isNonEmptyString($element['captchaTextColor']) ? sanitize_text_field($element['captchaTextColor']) : Quform_Element_Captcha::getDefaultConfig('captchaTextColor');
                $element['captchaTextColorRgba'] = is_array($element['captchaTextColorRgba']) ? $this->sanitiseRgbColorArray($element['captchaTextColorRgba']) : Quform_Element_Captcha::getDefaultConfig('captchaTextColorRgba');
                $element['captchaFont'] = isset($element['captchaFont']) && Quform::isNonEmptyString($element['captchaFont']) ? sanitize_text_field($element['captchaFont']) : Quform_Element_Captcha::getDefaultConfig('captchaFont');
                $element['captchaMinFontSize'] = isset($element['captchaMinFontSize']) && is_numeric($element['captchaMinFontSize']) ? (string) Quform::clamp((int) $element['captchaMinFontSize'], 5, 72) : Quform_Element_Captcha::getDefaultConfig('captchaMinFontSize');
                $element['captchaMaxFontSize'] = isset($element['captchaMaxFontSize']) && is_numeric($element['captchaMaxFontSize']) ? (string) Quform::clamp((int) $element['captchaMaxFontSize'], 5, 72) : Quform_Element_Captcha::getDefaultConfig('captchaMaxFontSize');
                $element['captchaMinAngle'] = isset($element['captchaMinAngle']) && is_numeric($element['captchaMinAngle']) ? (string) Quform::clamp((int) $element['captchaMinAngle'], 0, 360) : Quform_Element_Captcha::getDefaultConfig('captchaMinAngle');
                $element['captchaMaxAngle'] = isset($element['captchaMaxAngle']) && is_numeric($element['captchaMaxAngle']) ? (string) Quform::clamp((int) $element['captchaMaxAngle'], 0, 360) : Quform_Element_Captcha::getDefaultConfig('captchaMaxAngle');
                $element['captchaRetina'] = isset($element['captchaRetina']) ? (bool) $element['captchaRetina'] : Quform_Element_Captcha::getDefaultConfig('captchaRetina');

                // If any minimums are greater than maximums, swap them around
                if ($element['captchaMinFontSize'] > $element['captchaMaxFontSize']) {
                    $tmp = $element['captchaMaxFontSize'];
                    $element['captchaMaxFontSize'] = $element['captchaMinFontSize'];
                    $element['captchaMinFontSize'] = $tmp;
                }

                if ($element['captchaMinAngle'] > $element['captchaMaxAngle']) {
                    $tmp = $element['captchaMaxAngle'];
                    $element['captchaMaxAngle'] = $element['captchaMinAngle'];
                    $element['captchaMinAngle'] = $tmp;
                }
                break;
        }

        return $element;
    }

    /**
     * Make sure the colour values are acceptable
     *
     * @param   array  $color
     * @return  array
     */
    protected function sanitiseRgbColorArray(array $color)
    {
        $color = array(
            'r' => Quform::clamp((int) $color['r'], 0, 255),
            'g' => Quform::clamp((int) $color['g'], 0, 255),
            'b' => Quform::clamp((int) $color['b'], 0, 255)
        );

        return $color;
    }

    /**
     * Handle the Ajax request to add a new form
     */
    public function add()
    {
        $this->validateAddRequest();

        $name = wp_unslash($_POST['name']);

        $nameLength = Quform::strlen($name);

        if ($nameLength == 0) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-forms-add-name' => __('This field is required', 'quform')
                )
            ));
        } elseif ($nameLength > 64) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-forms-add-name' => __('The form name must be no longer than 64 characters', 'quform')
                )
            ));
        }

        $config = $this->getDefaultForm();
        $config['name'] = $name;

        $config = $this->repository->add($config);

        wp_send_json(array(
            'type' => 'success',
            'url'  => admin_url('admin.php?page=quform.forms&sp=edit&id=' . $config['id'])
        ));
    }

    /**
     * Validate the request to add a new form
     */
    protected function validateAddRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['name']) || ! is_string($_POST['name'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! Quform::currentUserCan('quform_add_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_add_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request to preview the form via Ajax
     */
    public function preview()
    {
        $this->validatePreviewRequest();

        $config = json_decode(stripslashes(Quform::get($_POST, 'form')), true);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        // Ajax must be enable to submit the form in the preview
        $config = $this->sanitiseForm($config);
        $config['ajax'] = true;
        $config['environment'] = 'preview';

        $form = $this->factory->create($config);
        $form->setCurrentPageById(Quform::get($_POST, 'page'));

        wp_send_json(array(
            'type' => 'success',
            'form' => $form->render(),
            'css' => $form->getCss()
        ));
    }

    /**
     * Validate the request to preview the form
     */
    protected function validatePreviewRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['form'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! Quform::currentUserCan('quform_edit_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }
    }

    /**
     * @param array $config
     */
    protected function validateForm(array $config)
    {
        if ( ! Quform::isNonEmptyString($config['name'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('A form name is required.', 'quform')
            ));
        }
    }

    /**
     * @return array
     */
    public function getThemes()
    {
        return $this->themes->getThemes();
    }

    /**
     * return array
     */
    public function getLocales()
    {
        return array('' => array('name' => 'Default')) + Quform::getLocales();
    }

    /**
     * @return array
     */
    protected function getLoadedPreviewLocales()
    {
        $activeLocales = array();

        foreach ($this->options->get('activeLocales') as $locales) {
            $activeLocales = array_merge($activeLocales, $locales);
        }

        return $activeLocales;
    }

    /**
     * @return string
     */
    protected function getAttachmentHtml()
    {
        ob_start();
        ?>
        <div class="qfb-attachment qfb-box qfb-cf">
            <div class="qfb-attachment-inner">
                <span class="qfb-attachment-remove qfb-small-remove-button fa fa-trash" title="<?php esc_attr_e('Remove', 'quform'); ?>"></span>
                <div class="qfb-sub-setting">
                    <div class="qfb-sub-setting-label">
                        <label><?php esc_html_e('Source', 'quform'); ?></label>
                    </div>
                    <div class="qfb-sub-setting-inner">
                        <div class="qfb-sub-setting-input">
                            <select class="qfb-attachment-source">
                                <option value="media"><?php esc_html_e('Media library', 'quform'); ?></option>
                                <option value="element"><?php esc_html_e('Form element', 'quform'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="qfb-sub-setting">
                    <div class="qfb-sub-setting-label">
                        <label><?php esc_html_e('Element', 'quform'); ?></label>
                    </div>
                    <div class="qfb-sub-setting-inner">
                        <div class="qfb-sub-setting-input">
                            <select class="qfb-attachment-element"></select>
                        </div>
                    </div>
                </div>
                <div class="qfb-sub-setting">
                    <div class="qfb-sub-setting-label">
                        <label><?php esc_html_e('File(s)', 'quform'); ?></label>
                    </div>
                    <div class="qfb-sub-setting-inner">
                        <div class="qfb-sub-setting-input">
                            <div class="qfb-cf">
                                <span class="qfb-button-blue qfb-attachment-browse"><i class="mdi mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                            </div>
                            <div class="qfb-attachment-media"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for the database password field
     *
     * @return string
     */
    public function getDbPasswordHtml()
    {
        ob_start();
        ?>
        <input type="text" id="qfb_form_db_password" value="">
        <p class="qfb-description"><?php esc_html_e('The password for the user above.', 'quform'); ?></p>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a select menu
     *
     * @param   string  $id             The ID of the field
     * @param   array   $options        The select options
     * @param   string  $selectedValue  The selected value
     * @return  string
     */
    protected function getSelectHtml($id, array $options, $selectedValue = '')
    {
        $output = sprintf('<select id="%s">', Quform::escape($id));

        foreach ($options as $value => $label) {
            $output .= sprintf(
                '<option value="%s"%s>%s</option>',
                Quform::escape($value),
                $selectedValue == $value ? ' selected="selected"' : '',
                Quform::escape($label)
            );
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * Get the HTML for the responsive setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue           The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getResponsiveSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Off', 'quform'),
            'phone-portrait' => __('Phone portrait (479px)', 'quform'),
            'phone-landscape' => __('Phone landscape (767px)', 'quform'),
            'tablet-landscape' => __('Tablet landscape (1024px)', 'quform'),
            'custom' => __('Custom...', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the element size setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getSizeSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Default', 'quform'),
            'slim' => __('Slim', 'quform'),
            'medium' => __('Medium', 'quform'),
            'fat' => __('Fat', 'quform'),
            'huge' => __('Huge', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the field width setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getFieldWidthSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            'tiny' => __('Tiny', 'quform'),
            'small' => __('Small', 'quform'),
            'medium' => __('Medium', 'quform'),
            'large' => __('Large', 'quform'),
            '' => __('100% (default)', 'quform'),
            'custom' => __('Custom...', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the button style setting select menu
     *
     * @param   string       $id                 The ID of the field
     * @param   string       $selectedValue      The selected value
     * @param   bool         $showInheritOption  Shows the "Inherit" option if true
     * @param   string|null  $emptyOptionText    The text for the empty option
     * @return  string
     */
    public function getButtonStyleSelectHtml($id, $selectedValue = '', $showInheritOption = true, $emptyOptionText = null)
    {
        $options = array(
            '' => is_string($emptyOptionText) ? $emptyOptionText : __('Default', 'quform'),
            'theme' => __('Use form theme button style', 'quform'),
            'sexy-silver' => __('Sexy Silver', 'quform'),
            'classic' => __('Classic', 'quform'),
            'background-blending-gradient' => __('Blending Gradient', 'quform'),
            'shine-gradient' => __('Shine Gradient', 'quform'),
            'blue-3d' => __('3D', 'quform'),
            'hollow' => __('Hollow', 'quform'),
            'hollow-rounded' => __('Hollow Rounded', 'quform'),
            'chilled' => __('Chilled', 'quform'),
            'pills' => __('Pill', 'quform'),
            'bootstrap' => __('Bootstrap', 'quform'),
            'bootstrap-primary' => __('Bootstrap Primary', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the button width setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getButtonWidthSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Auto (default)', 'quform'),
            'tiny' => __('Tiny', 'quform'),
            'small' => __('Small', 'quform'),
            'medium' => __('Medium', 'quform'),
            'large' => __('Large', 'quform'),
            'full' => __('100%', 'quform'),
            'custom' => __('Custom...', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the select icon field
     *
     * @param   string  $id        The ID of the field
     * @param   string  $selected  The selected icon
     * @return  string
     */
    public function getSelectIconHtml($id, $selected = '')
    {
        $output = '<div class="qfb-select-icon qfb-cf">';
        $output .= sprintf('<div class="qfb-select-icon-button qfb-button">%s</div>', esc_html__('Choose', 'quform'));
        $output .= '<div class="qfb-select-icon-preview">';

        if (Quform::isNonEmptyString($selected)) {
            $output .= sprintf('<i class="fa %s"></i>', esc_attr($selected));
        } else {
            $output .= esc_attr__('No icon', 'quform');
        }

        $output .= '</div>';

        $output .= sprintf(
            '<div class="qfb-select-icon-clear%s">%s</div>',
            ! Quform::isNonEmptyString($selected) ? ' qfb-hidden' : '',
            esc_html__('Clear', 'quform')
        );

        $output .= sprintf(
            '<input type="hidden" id="%s"%s class="qfb-select-icon-value">',
            esc_attr($id),
            Quform::isNonEmptyString($selected) ? sprintf(' value="%s"', esc_attr($selected)) : ''
        );

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for the icon position select
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getIconPositionSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            'left' => __('Left', 'quform'),
            'right' => __('Right', 'quform'),
            'above' => __('Above', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        $output = sprintf('<select id="%s">', Quform::escape($id));

        foreach ($options as $value => $label) {
            $output .= sprintf(
                '<option value="%s"%s>%s</option>',
                Quform::escape($value),
                $selectedValue == $value ? ' selected="selected"' : '',
                Quform::escape($label)
            );
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * Get the HTML for the CSS helper widget
     *
     * @return string
     */
    public function getCssHelperHtml()
    {
        $output = '';

        $helpers = array(
            array('css' => 'background-color: ;', 'icon' => 'mdi mdi-format_color_fill', 'title' => __('Background color', 'quform')),
            array('css' => 'background-image: url() top left no-repeat;', 'icon' => 'mdi mdi-wallpaper', 'title' => __('Background image', 'quform')),
            array('css' => 'border-color: ;', 'icon' => 'mdi mdi-border_color', 'title' => __('Border color', 'quform')),
            array('css' => 'color: ;', 'icon' => 'mdi mdi-format_color_text', 'title' => __('Text color', 'quform')),

            array('css' => 'padding: ;', 'icon' => 'fa fa-external-link-square', 'title' => __('Padding', 'quform')),
            array('css' => 'margin: ;', 'icon' => 'fa fa-external-link', 'title' => __('Margin', 'quform')),

            array('css' => 'font-size: ;', 'icon' => 'mdi mdi-format_size', 'title' => __('Font size', 'quform')),
            array('css' => 'font-weight: bold;', 'icon' => 'mdi mdi-format_bold', 'title' => __('Bold', 'quform')),
            array('css' => 'text-decoration: underline;', 'icon' => 'mdi mdi-format_underlined', 'title' => __('Underline', 'quform')),

            array('css' => 'text-align: left;', 'icon' => 'mdi mdi-format_align_left', 'title' => __('Text align left', 'quform')),
            array('css' => 'text-align: center;', 'icon' => 'mdi mdi-format_align_center', 'title' => __('Text align center', 'quform')),
            array('css' => 'text-align: right;', 'icon' => 'mdi mdi-format_align_right', 'title' => __('Text align right', 'quform')),

            array('css' => 'width: ;', 'icon' => 'mdi mdi-keyboard_tab', 'title' => __('Width', 'quform')),
            array('css' => 'height: ;', 'icon' => 'mdi mdi-vertical_align_top', 'title' => __('Height', 'quform')),

            array('css' => 'display: none;', 'icon' => 'mdi mdi-visibility_off', 'title' => __('Hide', 'quform')),
        );

        foreach ($helpers as $helper) {
            $output .= sprintf(
                '<span class="qfb-css-helper" data-css="%s" title="%s"><i class="%s"></i></span>',
                esc_attr($helper['css']),
                esc_attr($helper['title']),
                esc_attr($helper['icon'])
            );
        }

        return $output;
    }

    /**
     * Format the given variables array to display in a <pre> tag
     *
     * @param   array   $variables
     * @return  string
     */
    public function formatVariables(array $variables)
    {
        $lines = array();

        foreach ($variables as $tag => $description) {
            $lines[] = sprintf('%s = %s', $tag, $description);
        }

        return join("\n", $lines);
    }

    /**
     * Get the array of available Quform icons
     *
     * @return array
     */
    public function getQuformIcons()
    {
        return array(
            'qicon-add_circle', 'qicon-arrow_back', 'qicon-arrow_forward', 'qicon-check', 'qicon-close',
            'qicon-remove_circle', 'qicon-schedule', 'qicon-mode_edit', 'qicon-favorite_border', 'qicon-file_upload', 'qicon-star',
            'qicon-keyboard_arrow_down', 'qicon-keyboard_arrow_up', 'qicon-send', 'qicon-thumb_down', 'qicon-thumb_up',
            'qicon-refresh', 'qicon-question-circle', 'qicon-calendar', 'qicon-qicon-star-half', 'qicon-paper-plane',
            'qicon-search'
        );
    }

    /**
     * Get the array of available FontAwesome icons
     *
     * Updated for v4.7.0
     *
     * @return array
     */
    public function getFontAwesomeIcons()
    {
        return array('fa-glass', 'fa-music', 'fa-search', 'fa-envelope-o', 'fa-heart', 'fa-star', 'fa-star-o',
            'fa-user', 'fa-film', 'fa-th-large', 'fa-th', 'fa-th-list', 'fa-check', 'fa-remove', 'fa-close',
            'fa-times', 'fa-search-plus', 'fa-search-minus', 'fa-power-off', 'fa-signal', 'fa-gear', 'fa-cog',
            'fa-trash-o', 'fa-home', 'fa-file-o', 'fa-clock-o', 'fa-road', 'fa-download', 'fa-arrow-circle-o-down',
            'fa-arrow-circle-o-up', 'fa-inbox', 'fa-play-circle-o', 'fa-rotate-right', 'fa-repeat', 'fa-refresh',
            'fa-list-alt', 'fa-lock', 'fa-flag', 'fa-headphones', 'fa-volume-off', 'fa-volume-down', 'fa-volume-up',
            'fa-qrcode', 'fa-barcode', 'fa-tag', 'fa-tags', 'fa-book', 'fa-bookmark', 'fa-print', 'fa-camera',
            'fa-font', 'fa-bold', 'fa-italic', 'fa-text-height', 'fa-text-width', 'fa-align-left', 'fa-align-center',
            'fa-align-right', 'fa-align-justify', 'fa-list', 'fa-dedent', 'fa-outdent', 'fa-indent', 'fa-video-camera',
            'fa-photo', 'fa-image', 'fa-picture-o', 'fa-pencil', 'fa-map-marker', 'fa-adjust', 'fa-tint', 'fa-edit',
            'fa-pencil-square-o', 'fa-share-square-o', 'fa-check-square-o', 'fa-arrows', 'fa-step-backward',
            'fa-fast-backward', 'fa-backward', 'fa-play', 'fa-pause', 'fa-stop', 'fa-forward', 'fa-fast-forward',
            'fa-step-forward', 'fa-eject', 'fa-chevron-left', 'fa-chevron-right', 'fa-plus-circle', 'fa-minus-circle',
            'fa-times-circle', 'fa-check-circle', 'fa-question-circle', 'fa-info-circle', 'fa-crosshairs',
            'fa-times-circle-o', 'fa-check-circle-o', 'fa-ban', 'fa-arrow-left', 'fa-arrow-right', 'fa-arrow-up',
            'fa-arrow-down', 'fa-mail-forward', 'fa-share', 'fa-expand', 'fa-compress', 'fa-plus', 'fa-minus',
            'fa-asterisk', 'fa-exclamation-circle', 'fa-gift', 'fa-leaf', 'fa-fire', 'fa-eye', 'fa-eye-slash',
            'fa-warning', 'fa-exclamation-triangle', 'fa-plane', 'fa-calendar', 'fa-random', 'fa-comment', 'fa-magnet',
            'fa-chevron-up', 'fa-chevron-down', 'fa-retweet', 'fa-shopping-cart', 'fa-folder', 'fa-folder-open',
            'fa-arrows-v', 'fa-arrows-h', 'fa-bar-chart-o', 'fa-bar-chart', 'fa-twitter-square', 'fa-facebook-square',
            'fa-camera-retro', 'fa-key', 'fa-gears', 'fa-cogs', 'fa-comments', 'fa-thumbs-o-up', 'fa-thumbs-o-down',
            'fa-star-half', 'fa-heart-o', 'fa-sign-out', 'fa-linkedin-square', 'fa-thumb-tack', 'fa-external-link',
            'fa-sign-in', 'fa-trophy', 'fa-github-square', 'fa-upload', 'fa-lemon-o', 'fa-phone', 'fa-square-o',
            'fa-bookmark-o', 'fa-phone-square', 'fa-twitter', 'fa-facebook-f', 'fa-facebook', 'fa-github', 'fa-unlock',
            'fa-credit-card', 'fa-feed', 'fa-rss', 'fa-hdd-o', 'fa-bullhorn', 'fa-bell', 'fa-certificate',
            'fa-hand-o-right', 'fa-hand-o-left', 'fa-hand-o-up', 'fa-hand-o-down', 'fa-arrow-circle-left',
            'fa-arrow-circle-right', 'fa-arrow-circle-up', 'fa-arrow-circle-down', 'fa-globe', 'fa-wrench', 'fa-tasks',
            'fa-filter', 'fa-briefcase', 'fa-arrows-alt', 'fa-group', 'fa-users', 'fa-chain', 'fa-link', 'fa-cloud',
            'fa-flask', 'fa-cut', 'fa-scissors', 'fa-copy', 'fa-files-o', 'fa-paperclip', 'fa-save', 'fa-floppy-o',
            'fa-square', 'fa-navicon', 'fa-reorder', 'fa-bars', 'fa-list-ul', 'fa-list-ol', 'fa-strikethrough',
            'fa-underline', 'fa-table', 'fa-magic', 'fa-truck', 'fa-pinterest', 'fa-pinterest-square',
            'fa-google-plus-square', 'fa-google-plus', 'fa-money', 'fa-caret-down', 'fa-caret-up', 'fa-caret-left',
            'fa-caret-right', 'fa-columns', 'fa-unsorted', 'fa-sort', 'fa-sort-down', 'fa-sort-desc', 'fa-sort-up',
            'fa-sort-asc', 'fa-envelope', 'fa-linkedin', 'fa-rotate-left', 'fa-undo', 'fa-legal', 'fa-gavel',
            'fa-dashboard', 'fa-tachometer', 'fa-comment-o', 'fa-comments-o', 'fa-flash', 'fa-bolt', 'fa-sitemap',
            'fa-umbrella', 'fa-paste', 'fa-clipboard', 'fa-lightbulb-o', 'fa-exchange', 'fa-cloud-download',
            'fa-cloud-upload', 'fa-user-md', 'fa-stethoscope', 'fa-suitcase', 'fa-bell-o', 'fa-coffee', 'fa-cutlery',
            'fa-file-text-o', 'fa-building-o', 'fa-hospital-o', 'fa-ambulance', 'fa-medkit', 'fa-fighter-jet',
            'fa-beer', 'fa-h-square', 'fa-plus-square', 'fa-angle-double-left', 'fa-angle-double-right',
            'fa-angle-double-up', 'fa-angle-double-down', 'fa-angle-left', 'fa-angle-right', 'fa-angle-up',
            'fa-angle-down', 'fa-desktop', 'fa-laptop', 'fa-tablet', 'fa-mobile-phone', 'fa-mobile', 'fa-circle-o',
            'fa-quote-left', 'fa-quote-right', 'fa-spinner', 'fa-circle', 'fa-mail-reply', 'fa-reply', 'fa-github-alt',
            'fa-folder-o', 'fa-folder-open-o', 'fa-smile-o', 'fa-frown-o', 'fa-meh-o', 'fa-gamepad', 'fa-keyboard-o',
            'fa-flag-o', 'fa-flag-checkered', 'fa-terminal', 'fa-code', 'fa-mail-reply-all', 'fa-reply-all',
            'fa-star-half-empty', 'fa-star-half-full', 'fa-star-half-o', 'fa-location-arrow', 'fa-crop', 'fa-code-fork',
            'fa-unlink', 'fa-chain-broken', 'fa-question', 'fa-info', 'fa-exclamation', 'fa-superscript',
            'fa-subscript', 'fa-eraser', 'fa-puzzle-piece', 'fa-microphone', 'fa-microphone-slash', 'fa-shield',
            'fa-calendar-o', 'fa-fire-extinguisher', 'fa-rocket', 'fa-maxcdn', 'fa-chevron-circle-left',
            'fa-chevron-circle-right', 'fa-chevron-circle-up', 'fa-chevron-circle-down', 'fa-html5', 'fa-css3',
            'fa-anchor', 'fa-unlock-alt', 'fa-bullseye', 'fa-ellipsis-h', 'fa-ellipsis-v', 'fa-rss-square',
            'fa-play-circle', 'fa-ticket', 'fa-minus-square', 'fa-minus-square-o', 'fa-level-up', 'fa-level-down',
            'fa-check-square', 'fa-pencil-square', 'fa-external-link-square', 'fa-share-square', 'fa-compass',
            'fa-toggle-down', 'fa-caret-square-o-down', 'fa-toggle-up', 'fa-caret-square-o-up', 'fa-toggle-right',
            'fa-caret-square-o-right', 'fa-euro', 'fa-eur', 'fa-gbp', 'fa-dollar', 'fa-usd', 'fa-rupee', 'fa-inr',
            'fa-cny', 'fa-rmb', 'fa-yen', 'fa-jpy', 'fa-ruble', 'fa-rouble', 'fa-rub', 'fa-won', 'fa-krw', 'fa-bitcoin',
            'fa-btc', 'fa-file', 'fa-file-text', 'fa-sort-alpha-asc', 'fa-sort-alpha-desc', 'fa-sort-amount-asc',
            'fa-sort-amount-desc', 'fa-sort-numeric-asc', 'fa-sort-numeric-desc', 'fa-thumbs-up', 'fa-thumbs-down',
            'fa-youtube-square', 'fa-youtube', 'fa-xing', 'fa-xing-square', 'fa-youtube-play', 'fa-dropbox',
            'fa-stack-overflow', 'fa-instagram', 'fa-flickr', 'fa-adn', 'fa-bitbucket', 'fa-bitbucket-square',
            'fa-tumblr', 'fa-tumblr-square', 'fa-long-arrow-down', 'fa-long-arrow-up', 'fa-long-arrow-left',
            'fa-long-arrow-right', 'fa-apple', 'fa-windows', 'fa-android', 'fa-linux', 'fa-dribbble', 'fa-skype',
            'fa-foursquare', 'fa-trello', 'fa-female', 'fa-male', 'fa-gittip', 'fa-gratipay', 'fa-sun-o', 'fa-moon-o',
            'fa-archive', 'fa-bug', 'fa-vk', 'fa-weibo', 'fa-renren', 'fa-pagelines', 'fa-stack-exchange',
            'fa-arrow-circle-o-right', 'fa-arrow-circle-o-left', 'fa-toggle-left', 'fa-caret-square-o-left',
            'fa-dot-circle-o', 'fa-wheelchair', 'fa-vimeo-square', 'fa-turkish-lira', 'fa-try', 'fa-plus-square-o',
            'fa-space-shuttle', 'fa-slack', 'fa-envelope-square', 'fa-wordpress', 'fa-openid', 'fa-institution',
            'fa-bank', 'fa-university', 'fa-mortar-board', 'fa-graduation-cap', 'fa-yahoo', 'fa-google', 'fa-reddit',
            'fa-reddit-square', 'fa-stumbleupon-circle', 'fa-stumbleupon', 'fa-delicious', 'fa-digg',
            'fa-pied-piper-pp', 'fa-pied-piper-alt', 'fa-drupal', 'fa-joomla', 'fa-language', 'fa-fax', 'fa-building',
            'fa-child', 'fa-paw', 'fa-spoon', 'fa-cube', 'fa-cubes', 'fa-behance', 'fa-behance-square', 'fa-steam',
            'fa-steam-square', 'fa-recycle', 'fa-automobile', 'fa-car', 'fa-cab', 'fa-taxi', 'fa-tree', 'fa-spotify',
            'fa-deviantart', 'fa-soundcloud', 'fa-database', 'fa-file-pdf-o', 'fa-file-word-o', 'fa-file-excel-o',
            'fa-file-powerpoint-o', 'fa-file-photo-o', 'fa-file-picture-o', 'fa-file-image-o', 'fa-file-zip-o',
            'fa-file-archive-o', 'fa-file-sound-o', 'fa-file-audio-o', 'fa-file-movie-o', 'fa-file-video-o',
            'fa-file-code-o', 'fa-vine', 'fa-codepen', 'fa-jsfiddle', 'fa-life-bouy', 'fa-life-buoy', 'fa-life-saver',
            'fa-support', 'fa-life-ring', 'fa-circle-o-notch', 'fa-ra', 'fa-resistance', 'fa-rebel', 'fa-ge',
            'fa-empire', 'fa-git-square', 'fa-git', 'fa-y-combinator-square', 'fa-yc-square', 'fa-hacker-news',
            'fa-tencent-weibo', 'fa-qq', 'fa-wechat', 'fa-weixin', 'fa-send', 'fa-paper-plane', 'fa-send-o',
            'fa-paper-plane-o', 'fa-history', 'fa-circle-thin', 'fa-header', 'fa-paragraph', 'fa-sliders',
            'fa-share-alt', 'fa-share-alt-square', 'fa-bomb', 'fa-soccer-ball-o', 'fa-futbol-o', 'fa-tty',
            'fa-binoculars', 'fa-plug', 'fa-slideshare', 'fa-twitch', 'fa-yelp', 'fa-newspaper-o', 'fa-wifi',
            'fa-calculator', 'fa-paypal', 'fa-google-wallet', 'fa-cc-visa', 'fa-cc-mastercard', 'fa-cc-discover',
            'fa-cc-amex', 'fa-cc-paypal', 'fa-cc-stripe', 'fa-bell-slash', 'fa-bell-slash-o', 'fa-trash', 'fa-copyright',
            'fa-at', 'fa-eyedropper', 'fa-paint-brush', 'fa-birthday-cake', 'fa-area-chart', 'fa-pie-chart',
            'fa-line-chart', 'fa-lastfm', 'fa-lastfm-square', 'fa-toggle-off', 'fa-toggle-on', 'fa-bicycle', 'fa-bus',
            'fa-ioxhost', 'fa-angellist', 'fa-cc', 'fa-shekel', 'fa-sheqel', 'fa-ils', 'fa-meanpath', 'fa-buysellads',
            'fa-connectdevelop', 'fa-dashcube', 'fa-forumbee', 'fa-leanpub', 'fa-sellsy', 'fa-shirtsinbulk',
            'fa-simplybuilt', 'fa-skyatlas', 'fa-cart-plus', 'fa-cart-arrow-down', 'fa-diamond', 'fa-ship',
            'fa-user-secret', 'fa-motorcycle', 'fa-street-view', 'fa-heartbeat', 'fa-venus', 'fa-mars', 'fa-mercury',
            'fa-intersex', 'fa-transgender', 'fa-transgender-alt', 'fa-venus-double', 'fa-mars-double', 'fa-venus-mars',
            'fa-mars-stroke', 'fa-mars-stroke-v', 'fa-mars-stroke-h', 'fa-neuter', 'fa-genderless',
            'fa-facebook-official', 'fa-pinterest-p', 'fa-whatsapp', 'fa-server', 'fa-user-plus', 'fa-user-times',
            'fa-hotel', 'fa-bed', 'fa-viacoin', 'fa-train', 'fa-subway', 'fa-medium', 'fa-yc', 'fa-y-combinator',
            'fa-optin-monster', 'fa-opencart', 'fa-expeditedssl', 'fa-battery-4', 'fa-battery', 'fa-battery-full',
            'fa-battery-3', 'fa-battery-three-quarters', 'fa-battery-2', 'fa-battery-half', 'fa-battery-1',
            'fa-battery-quarter', 'fa-battery-0', 'fa-battery-empty', 'fa-mouse-pointer', 'fa-i-cursor',
            'fa-object-group', 'fa-object-ungroup', 'fa-sticky-note', 'fa-sticky-note-o', 'fa-cc-jcb',
            'fa-cc-diners-club', 'fa-clone', 'fa-balance-scale', 'fa-hourglass-o', 'fa-hourglass-1',
            'fa-hourglass-start', 'fa-hourglass-2', 'fa-hourglass-half', 'fa-hourglass-3', 'fa-hourglass-end',
            'fa-hourglass', 'fa-hand-grab-o', 'fa-hand-rock-o', 'fa-hand-stop-o', 'fa-hand-paper-o',
            'fa-hand-scissors-o', 'fa-hand-lizard-o', 'fa-hand-spock-o', 'fa-hand-pointer-o', 'fa-hand-peace-o',
            'fa-trademark', 'fa-registered', 'fa-creative-commons', 'fa-gg', 'fa-gg-circle', 'fa-tripadvisor',
            'fa-odnoklassniki', 'fa-odnoklassniki-square', 'fa-get-pocket', 'fa-wikipedia-w', 'fa-safari', 'fa-chrome',
            'fa-firefox', 'fa-opera', 'fa-internet-explorer', 'fa-tv', 'fa-television', 'fa-contao', 'fa-500px',
            'fa-amazon', 'fa-calendar-plus-o', 'fa-calendar-minus-o', 'fa-calendar-times-o', 'fa-calendar-check-o',
            'fa-industry', 'fa-map-pin', 'fa-map-signs', 'fa-map-o', 'fa-map', 'fa-commenting', 'fa-commenting-o',
            'fa-houzz', 'fa-vimeo', 'fa-black-tie', 'fa-fonticons', 'fa-reddit-alien', 'fa-edge', 'fa-credit-card-alt',
            'fa-codiepie', 'fa-modx', 'fa-fort-awesome', 'fa-usb', 'fa-product-hunt', 'fa-mixcloud', 'fa-scribd',
            'fa-pause-circle', 'fa-pause-circle-o', 'fa-stop-circle', 'fa-stop-circle-o', 'fa-shopping-bag',
            'fa-shopping-basket', 'fa-hashtag', 'fa-bluetooth', 'fa-bluetooth-b', 'fa-percent', 'fa-gitlab',
            'fa-wpbeginner', 'fa-wpforms', 'fa-envira', 'fa-universal-access', 'fa-wheelchair-alt',
            'fa-question-circle-o', 'fa-blind', 'fa-audio-description', 'fa-volume-control-phone', 'fa-braille',
            'fa-assistive-listening-systems', 'fa-asl-interpreting', 'fa-american-sign-language-interpreting',
            'fa-deafness', 'fa-hard-of-hearing', 'fa-deaf', 'fa-glide', 'fa-glide-g', 'fa-signing', 'fa-sign-language',
            'fa-low-vision', 'fa-viadeo', 'fa-viadeo-square', 'fa-snapchat', 'fa-snapchat-ghost', 'fa-snapchat-square',
            'fa-pied-piper', 'fa-first-order', 'fa-yoast', 'fa-themeisle', 'fa-google-plus-circle',
            'fa-google-plus-official', 'fa-fa', 'fa-font-awesome', 'fa-handshake-o', 'fa-envelope-open',
            'fa-envelope-open-o', 'fa-linode', 'fa-address-book', 'fa-address-book-o', 'fa-vcard', 'fa-address-card',
            'fa-vcard-o', 'fa-address-card-o', 'fa-user-circle', 'fa-user-circle-o', 'fa-user-o', 'fa-id-badge',
            'fa-drivers-license', 'fa-id-card', 'fa-drivers-license-o', 'fa-id-card-o', 'fa-quora', 'fa-free-code-camp',
            'fa-telegram', 'fa-thermometer-4', 'fa-thermometer', 'fa-thermometer-full', 'fa-thermometer-3',
            'fa-thermometer-three-quarters', 'fa-thermometer-2', 'fa-thermometer-half', 'fa-thermometer-1',
            'fa-thermometer-quarter', 'fa-thermometer-0', 'fa-thermometer-empty', 'fa-shower', 'fa-bathtub',
            'fa-s15', 'fa-bath', 'fa-podcast', 'fa-window-maximize', 'fa-window-minimize', 'fa-window-restore',
            'fa-times-rectangle', 'fa-window-close', 'fa-times-rectangle-o', 'fa-window-close-o', 'fa-bandcamp',
            'fa-grav', 'fa-etsy', 'fa-imdb', 'fa-ravelry', 'fa-eercast', 'fa-microchip', 'fa-snowflake-o',
            'fa-superpowers', 'fa-wpexplorer', 'fa-meetup'
        );
    }
}
