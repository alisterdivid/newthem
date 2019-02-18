<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Settings
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Permissions
     */
    protected $permissions;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @param Quform_Options      $options
     * @param Quform_Permissions  $permissions
     * @param Quform_ScriptLoader $scriptLoader
     */
    public function __construct(Quform_Options $options, Quform_Permissions $permissions, Quform_ScriptLoader $scriptLoader)
    {
        $this->options = $options;
        $this->permissions = $permissions;
        $this->scriptLoader = $scriptLoader;
    }

    /**
     * Handle saving the settings page via Ajax
     */
    public function save()
    {
        $this->validateSaveRequest();

        $options = json_decode(stripslashes($_POST['options']), true);

        if (isset($options['permissions'])) {
            $this->permissions->update($options['permissions']);
            unset($options['permissions']);
        }

        $this->options->set($options);

        $this->scriptLoader->generateFiles();

        wp_send_json(array(
            'type' => 'success'
        ));
    }

    /**
     * Validate the request to save the settings
     */
    protected function validateSaveRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['options']) || ! is_string($_POST['options'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! Quform::currentUserCan('quform_settings')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_save_settings', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the Ajax request to rebuild the feature cache and custom CSS
     */
    public function rebuildScriptCache()
    {
        if ( ! Quform::currentUserCan('quform_settings')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_rebuild_script_cache', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }

        $this->scriptLoader->rebuildScriptCache();

        wp_send_json(array(
            'type'    => 'success'
        ));
    }
}
