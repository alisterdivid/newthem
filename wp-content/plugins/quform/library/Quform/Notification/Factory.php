<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Notification_Factory
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param Quform_Options options
     */
    public function __construct(Quform_Options $options)
    {
        $this->options = $options;
    }

    public function create(array $config = array(), Quform_Form $form)
    {
        $notification = new Quform_Notification($form, $this->options);

        $notification->setConfig($config);

        return $notification;
    }
}
