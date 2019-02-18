<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Entry_Processor extends Quform_Form_Processor
{
    /**
     * Process the given form
     *
     * @param   Quform_Form  $form  The form to process
     * @return  array               The result array
     */
    public function process(Quform_Form $form)
    {
        // Strip slashes from the submitted data (WP adds them automatically)
        $_POST = wp_unslash($_POST);

        $this->uploader->mergeSessionFiles($form);

        $form->setValues($_POST, true);

        list($valid) = $form->isValid();

        if ($valid) {
            // Process any uploads first
            $this->uploader->process($form);

            $entry = $this->repository->saveEntry($this->getEntryConfig($form), Quform::get($_POST, 'quform_entry_id', 0));

            return array(
                'type' => 'success',
                'data' => array('id' => $entry['id']),
                'message' => __('Entry saved', 'quform')
            );
        }

        return array(
            'type' => 'error',
            'errors' => $form->getErrors()
        );
    }

    /**
     * Generate an entry config array from the form data
     *
     * @param   Quform_Form  $form
     * @return  array
     */
    protected function getEntryConfig(Quform_Form $form)
    {
        $currentTime = current_time('mysql', true);
        $createdAt = Quform::get($_POST, 'entry_created_at') ? date('Y-m-d H:i:s', strtotime(Quform::get($_POST, 'entry_created_at'))) : $currentTime;

        $entry = array(
            'form_id'       => $form->getId(),
            'ip'            => Quform::substr(Quform::get($_POST, 'entry_ip'), 0, 45),
            'form_url'      => Quform::substr(Quform::get($_POST, 'entry_form_url'), 0, 512),
            'referring_url' => Quform::substr(Quform::get($_POST, 'entry_referring_url'), 0, 512),
            'post_id'       => is_numeric($postId = Quform::get($_POST, 'entry_post_id')) && $postId > 0 ? (int) $postId : null,
            'created_by'    => is_numeric($createdBy = Quform::get($_POST, 'entry_created_by')) && $createdBy > 0 ? (int) $createdBy : null,
            'created_at'    => $createdAt,
            'updated_at'    => $currentTime
        );

        $entry['data'] = array();

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element instanceof Quform_Element_Editable && $element->config('saveToDatabase')) {
                $entry['data'][$element->getId()] = $element->getValueForStorage();
            }
        }

        return $entry;
    }
}
