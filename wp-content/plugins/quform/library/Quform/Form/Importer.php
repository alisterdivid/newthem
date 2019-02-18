<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Form_Importer
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param Quform_Repository $repository
     */
    public function __construct(Quform_Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the Ajax request to import a form
     *
     * Sends a JSON response, ending execution
     */
    public function import()
    {
        $this->validateImportRequest();

        $config = base64_decode(trim(stripslashes($_POST['config'])));
        $config = maybe_unserialize($config);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-import-form-data' => __('The import data is invalid', 'quform')
                )
            ));
        }

        $config = $this->repository->add($config);

        wp_send_json(array(
            'type' => 'success',
            'message' => sprintf(__('Form imported successfully, %sedit the form%s', 'quform'), '<a href="' . esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $config['id'])) . '">', '</a>')
        ));
    }

    /**
     * Validate the Ajax request to import a form
     *
     * Sends a JSON response if the request is invalid, ending execution
     */
    protected function validateImportRequest()
    {
        if ( ! isset($_POST['config']) || ! Quform::isNonEmptyString($_POST['config'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! Quform::currentUserCan('quform_import_forms')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_import_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }
}
