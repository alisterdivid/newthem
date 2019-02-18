<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Admin_Page_Dashboard extends Quform_Admin_Page
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_ViewFactory  $viewFactory
     * @param  Quform_Repository   $repository
     * @param  Quform_Options      $options
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository, Quform_Options $options)
    {
        parent::__construct($viewFactory, $repository);

        $this->options = $options;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/dashboard.php';
    }

    /**
     * Get the HTML for the admin navigation menu
     *
     * @param   array|null  $form   The data for the current form (if any)
     * @param   array       $extra  Extra HTML to add to the nav, the array key is the hook position
     * @return  string
     */
    public function getNavHtml(array $form = null, array $extra = array())
    {
        ob_start();
        ?>
        <div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon mdi mdi-dashboard"></i><span class="qfb-nav-page-title"><?php esc_html_e('Dashboard', 'quform'); ?></span></div>
        <?php

        return parent::getNavHtml($form, array(40 => ob_get_clean()));
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        $recentEntries = $this->repository->getRecentEntries(10);

        $unreadCount = 0;

        foreach ($recentEntries as $recentEntry) {
            if ($recentEntry['unread'] == '1') {
                $unreadCount++;
            }
        }

        $this->view->with(array(
            'options' => $this->options,
            'forms' => $this->repository->getFormsForListTable(array('limit' => 9)),
            'unreadCount' => $unreadCount,
            'recentEntries' => $recentEntries,
            'tools' => $this->getTools(),
        ));
    }

    /**
     * Get the array of tools that the user has permission to use
     *
     * @return array
     */
    protected function getTools()
    {
        $tools = array(
            'export.entries' => array(
                'title' => __('Export Entries'),
                'cap' => 'quform_export_entries',
                'url' => admin_url('admin.php?page=quform.tools&sp=export.entries'),
                'icon' => '<i class="fa fa-file-excel-o"></i>'
            ),
            'export.form' => array(
                'title' => __('Export Form'),
                'cap' => 'quform_export_forms',
                'url' => admin_url('admin.php?page=quform.tools&sp=export.form'),
                'icon' => '<i class="fa fa-file-code-o"></i>'
            ),
            'import.form' => array(
                'title' => __('Import Form'),
                'cap' => 'quform_import_forms',
                'url' => admin_url('admin.php?page=quform.tools&sp=import.form'),
                'icon' => '<i class="mdi mdi-playlist_add"></i>'
            )
        );

        foreach ($tools as $key => $tool) {
            if ( ! Quform::currentUserCan($tool['cap'])) {
                unset($tools[$key]);
            }
        }

        return $tools;
    }
}
