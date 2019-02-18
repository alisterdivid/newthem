<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Notification
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var Quform_Form
     */
    protected $form;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  array           $config
     * @param  Quform_Form     $form
     * @param  Quform_Options  $options
     */
    public function __construct(array $config, Quform_Form $form, Quform_Options $options)
    {
        $this->setConfig($config);
        $this->form = $form;
        $this->options = $options;
    }

    /**
     * Send the notification
     */
    public function send()
    {
        if ( ! $this->config('enabled')) {
            return;
        }

        $config = $this->getMailerConfig();

        add_action('phpmailer_init', array($this, 'processHooks'));

        wp_mail($config['to'], $config['subject'], $config['message'], $config['headers'], $config['attachments']);

        remove_action('phpmailer_init', array($this, 'processHooks'));
    }

    /**
     * Replace variable tokens within the recipient fields.
     *
     * @param  array  $recipient
     * @return array
     */
    protected function replaceVariables(array $recipient)
    {
        $recipient['address'] = $this->form->replaceVariables($recipient['address']);
        $recipient['name'] = $this->form->replaceVariables($recipient['name']);

        return $recipient;
    }

    /**
     * Returns the config value for the given $key
     *
     * @param   string  $key
     * @param   null    $default
     * @return  mixed   The config value or $default if not set
     */
    public function config($key, $default = null)
    {
        $value = Quform::get($this->config, $key, $default);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultConfig')), $key, $default);
        }

        return $value;
    }

    /**
     * Set the config value for the given $key
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  $this
     */
    public function setConfig($key, $value = null)
    {
        if (is_array($key)) {
            foreach($key as $k => $v) {
                $this->config[$k] = $v;
            }
        } else {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the parameters to pass to the mail function
     *
     * @return array
     */
    protected function getMailerConfig()
    {
        $config = array(
            'to' => array(),
            'headers' => array(),
            'subject' => $this->form->replaceVariables($this->config('subject')),
            'message' => '',
            'attachments' => array()
        );

        $recipients = array();
        $emailValidator = new Quform_Validator_Email();

        if ($this->config('conditional')) {
            foreach($this->config('conditionals') as $conditional) {
                if (count($conditional['logicRules']) && $this->form->checkLogicAction($conditional['logicAction'], $conditional['logicMatch'], $conditional['logicRules'])) {
                    foreach ($conditional['recipients'] as $recipient) {
                        $recipient = $this->replaceVariables($recipient);
                        if ($emailValidator->isValid($recipient['address'])) {
                            $recipients[] = $recipient;
                        }
                    }
                }
            }
        }

        // TODO should below be an option - if no logic match send to recipients or do nothing?
        if ( ! count($recipients)) {
            foreach ($this->config('recipients') as $recipient) {
                $recipient = $this->replaceVariables($recipient);
                if ($emailValidator->isValid($recipient['address'])) {
                    $recipients[] = $recipient;
                }
            }
        }

        foreach ($recipients as $recipient) {
            $formatted = $this->formatRecipient($recipient);

            switch ($recipient['type']) {
                case 'to':
                    $config['to'][] = $formatted;
                    break;
                case 'cc':
                    $config['headers'][] = 'Cc: ' . $formatted;
                    break;
                case 'bcc':
                    $config['headers'][] = 'Bcc: ' . $formatted;
                    break;
                case 'reply':
                    $config['headers'][] = 'Reply-to: ' . $formatted;
                    break;
            }
        }

        if (Quform::isNonEmptyString($this->config('from.address'))) {
            $from = $this->replaceVariables($this->config('from'));
            if ($emailValidator->isValid($from['address'])) {
                $config['headers'][] = 'From: ' . $this->formatRecipient($from);
            }
        }

        if ($this->config('format') == 'html') {
            $config['headers'][] = sprintf('Content-type: text/html; charset=%s', apply_filters('wp_mail_charset', get_bloginfo('charset')));
            $config['message'] = $this->config('html');

            if ($this->config('autoFormat')) {
                $config['message'] = nl2br($config['message']);
            }

            $config['message'] = $this->form->replaceVariables($config['message'], 'html');
            $config['message'] = $this->wrapHtmlMessage($config['message']);
        } else {
            $config['message'] = $this->form->replaceVariables($this->config('text'));
        }

        foreach ($this->config('attachments') as $attachment) {
            if ($attachment['source'] == 'element') {
                $element = $this->form->getElementById($attachment['element']);

                if ($element instanceof Quform_Attachable && $element->hasAttachments()) {
                    foreach ($element->getAttachments() as $file) {
                        if (is_file($file)) {
                            $config['attachments'][] = $file;
                        }
                    }
                }
            } elseif ($attachment['source'] == 'media') {
                if (is_array($attachment['media'])) {
                    foreach ($attachment['media'] as $medium) {
                        $post = get_post($medium['id']);

                        if ($post instanceof WP_Post && $post->post_type == 'attachment') {
                            $file = get_attached_file($post->ID);

                            if (is_file($file)) {
                                $config['attachments'][] = $file;
                            }
                        }
                    }
                }
            }
        }

        return $config;
    }

    /**
     * Run hooks before the email is sent
     *
     * @param PHPMailer $mailer
     */
    public function processHooks(PHPMailer $mailer)
    {
        do_action('quform_pre_send_notification', $mailer, $this, $this->form);
        do_action('quform_pre_send_notification_'. $this->form->getId() . '_' . $this->config('id'), $mailer, $this, $this->form);
    }

    /**
     * Format the given recipient for an email header
     *
     * @param   array   $recipient
     * @return  string
     */
    protected function formatRecipient(array $recipient)
    {
        if (Quform::isNonEmptyString($recipient['name'])) {
            $formatted = sprintf('%s <%s>', $recipient['name'], $recipient['address']);
        } else {
            $formatted = $recipient['address'];
        }

        return $formatted;
    }


    /**
     * Wraps the given message in outer HTML tags
     *
     * @param   string  $message
     * @return  string
     */
    protected function wrapHtmlMessage($message)
    {
        $start = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s" />
<title></title>
</head>
<body style="margin:0;padding:0;">';

        $start = sprintf($start, esc_attr(apply_filters('wp_mail_charset', get_bloginfo('charset'))));

        $start = apply_filters('quform_notification_html_start', $start, $this);
        $start = apply_filters('quform_notification_html_start_' . $this->form->getId() . '_' . $this->config('id'), $start, $this);

        $end = '</body></html>';

        $end = apply_filters('quform_notification_html_end', $end, $this);
        $end = apply_filters('quform_notification_html_end_' . $this->form->getId() . '_' . $this->config('id'), $end, $this);

        return $start . $message . $end;
    }

    /**
     * Get the default notification configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return apply_filters('quform_default_notification', array(
            'name' => '',
            'enabled' => true,
            'subject' => sprintf(__('New submission from %s', 'quform'), '{form_name}'),
            'format' => 'html',
            'html' => '',
            'autoFormat' => true,
            'text' => '',
            'recipients' => array(array('type' => 'to', 'address' => '{default_email_address}', 'name' => '{default_email_name}')),
            'conditional' => false,
            'conditionals' => array(),
            'from' => array('address' => '{default_from_email_address}', 'name' => '{default_from_email_name}'),
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),
            'attachments' => array()
        ));
    }
}
