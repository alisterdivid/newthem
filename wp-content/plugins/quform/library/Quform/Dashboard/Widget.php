<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Dashboard_Widget
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_ViewFactory
     */
    protected $viewFactory;

    /**
     * @var string
     */
    protected $template;

    public function __construct(Quform_Repository $repository, Quform_ViewFactory $viewFactory)
    {
        $this->repository = $repository;
        $this->viewFactory = $viewFactory;
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/dashboard-widget.php';
    }

    public function setup()
    {
        if ( ! Quform::currentUserCan('quform_view_entries') || ! $this->repository->getAllUnreadEntriesCount() > 0) {
            // The user doesn't have permission to view entries or there are no unread entries
            return;
        }

        wp_enqueue_style('qfb-dashboard', Quform::adminUrl('css/dashboard.min.css'), array(), QUFORM_VERSION);
        wp_add_dashboard_widget('qfb-dashboard-widget', Quform::getPluginName(), array($this, 'display'));
    }

    public function display()
    {
        $data = array(
            'forms' => $this->repository->getAllFormsWithUnreadEntries()
        );

        echo $this->viewFactory->create($this->template, $data)->render();
    }
}
