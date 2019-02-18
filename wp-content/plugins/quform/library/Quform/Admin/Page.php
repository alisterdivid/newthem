<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
abstract class Quform_Admin_Page
{
    const BAD_REQUEST = 1;

    const NO_PERMISSION = 2;

    const NONCE_CHECK_FAILED = 3;

    /**
     * The path to the view template
     *
     * @var string
     */
    protected $template;

    /**
     * The view instance for this page
     *
     * @var Quform_View
     */
    protected $view;

    /**
     * @var Quform_ViewFactory
     */
    protected $viewFactory;

    /**
     * Notification messages to be displayed
     *
     * @var array
     */
    protected $messages = array();

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param Quform_ViewFactory $viewFactory
     * @param Quform_Repository  $repository
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository)
    {
        $this->viewFactory = $viewFactory;
        $this->repository = $repository;
    }

    /**
     * Sets up the page and view
     *
     * @return  $this
     */
    public function bootstrap()
    {
        $this->init();

        $this->view = $this->viewFactory->create($this->template, array('page' => $this));

        return $this;
    }

    /**
     * Initialize this page
     *
     * Subclasses can override this method to add their own bootstrap functionality.
     */
    public function init()
    {
        // Override in subclass to run on instantiation
    }

    /**
     * Enqueue the page assets
     */
    public function enqueueAssets()
    {
        $this->enqueueStyles();
        $this->enqueueScripts();
    }

    /**
     * Enqueue the page styles
     */
    protected function enqueueStyles()
    {
        wp_enqueue_style('font-awesome', Quform::url('css/font-awesome.min.css'), array(), '4.7.0');
        wp_enqueue_style('material-icons', Quform::adminUrl('fonts/material-icons.min.css'), array(), '3.0.1');
        wp_enqueue_style('quform-admin', Quform::adminUrl('css/admin.min.css'), array(), QUFORM_VERSION, 'all');
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        wp_enqueue_script('jquery-smooth-scroll', Quform::url('js/jquery.smooth-scroll.min.js'), array('jquery'), '2.2.0', true);
        wp_enqueue_script('quform-core', Quform::adminUrl('js/core.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-core', 'quformCoreL10n', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => Quform::url(),
            'adminImagesUrl' => Quform::adminUrl('images/'),
            'ajaxError' => __('Ajax error', 'quform'),
            'thisFieldIsRequired' => __('This field is required', 'quform'),
            'invalidEmailAddress' => __('Invalid email address', 'quform'),
            'selectImage' => __('Select Image', 'quform'),
            'select' => __('Select', 'quform'),
            'noResultsFound' => __('No results found.', 'quform')
        ));
    }

    /**
     * Process this page
     *
     * Subclasses can override this method to add their own process functionality.
     */
    public function process()
    {
        // Override in subclass to run on process
    }

    /**
     * Subclasses can override this method to set a custom page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return '';
    }

    /**
     * Override the page title if the current page has a custom title
     *
     * @param   string  $adminTitle  The current admin title
     * @return  string               The new admin title
     */
    public function setAdminTitle($adminTitle)
    {
        $title = $this->getAdminTitle();

        if (Quform::isNonEmptyString($title)) {
            $adminTitle = sprintf(__('%1$s &lsaquo; %2$s &#8212; WordPress', 'quform'), esc_html($title), esc_html(get_bloginfo('name')));
        }

        return $adminTitle;
    }

    /**
     * Renders this page's view
     *
     * @return string
     */
    public function display()
    {
        return $this->view->render();
    }

    /**
     * Add a notification message
     *
     * @param   string|array  $type     Type of message e.g. 'error' or 'success'
     * @param   string        $message  The message content
     * @return  $this
     */
    public function addMessage($type, $message = '')
    {
        if (is_array($type)) {
            return $this->addMessage($type['type'], $type['message']);
        } else {
            $message = array('type' => $type, 'message' => $message);
            $this->messages[] = $message;
        }

        return $this;
    }

    /**
     * Get the notification messages for this page
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the HTML for the page messages
     *
     * @return string
     */
    public function getMessagesHtml()
    {
        $output = '';

        if (count($this->getMessages())) {
            $output .= '<div id="qfb-page-messages" class="qfb-page-messages">';

            foreach ($this->getMessages() as $message) {
                $output .= '<div class="qfb-message-box qfb-message-box-' . $message['type'] . '"><div class="qfb-message-box-inner">' . $message['message'] . '</div></div>';
            }

            $output .= '</div>';
        }

        return $output;
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
        $links = array(
            array(
                'cap' => 'quform_view_dashboard',
                'href' => admin_url('admin.php?page=quform.dashboard'),
                'class' => 'dashboard',
                'icon' => '<i class="mdi mdi-dashboard"></i>',
                'text' => __('Dashboard', 'quform')
            ),
            array(
                'cap' => 'quform_list_forms',
                'href' => admin_url('admin.php?page=quform.forms'),
                'class' => 'forms',
                'icon' => '<i class="mdi mdi-view_stream"></i>',
                'text' => __('Forms', 'quform')
            ),
            array(
                'cap' => 'quform_view_entries',
                'href' => admin_url('admin.php?page=quform.entries'),
                'class' => 'entries',
                'icon' => '<i class="mdi mdi-message"></i>',
                'text' => __('Entries', 'quform')
            ),
            array(
                'cap' => 'quform_view_tools',
                'href' => admin_url('admin.php?page=quform.tools'),
                'class' => 'tools',
                'icon' => '<i class="mdi mdi-build"></i>',
                'text' => __('Tools', 'quform')
            ),
            array(
                'cap' => 'quform_settings',
                'href' => admin_url('admin.php?page=quform.settings'),
                'class' => 'settings',
                'icon' => '<i class="mdi mdi-settings"></i>',
                'text' => __('Settings', 'quform')
            ),
            array(
                'cap' => 'quform_help',
                'href' => admin_url('admin.php?page=quform.help'),
                'class' => 'help',
                'icon' => '<i class="mdi mdi-help_outline"></i>',
                'text' => __('Help', 'quform')
            )
        );

        $visible = array();
        foreach ($links as $link) {
            if (Quform::currentUserCan($link['cap'])) {
                $visible[] = $link;
            }
        }

        if ( ! count($visible)) {
            return '';
        }

        $forms = $this->repository->formsToSelectArray();

        ob_start();
        ?>
        <div id="qfb-nav" class="qfb-cf">
            <?php
                printf(
                    '<a class="qfb-logo"%s></a>',
                    Quform::currentUserCan('quform_view_dashboard') ? sprintf(' href="%s"', esc_url(admin_url('admin.php?page=quform.dashboard'))) : ''
                );
            ?>

            <?php echo $this->getExtraHtml(10, $extra); ?>

            <div class="qfb-nav-item qfb-nav-item-menu">
                <a class="qfb-nav-item-link qfb-nav-popup-trigger"><i class="fa fa-bars"></i></a>
                <div class="qfb-nav-popup-content">
                    <ul class="qfb-nav-menu">
                        <?php
                            foreach ($visible as $item) {
                                echo '<li class="qfb-page-' . esc_attr($item['class']) . '"><a href="' . esc_url($item['href']) . '">' . $item['icon'] . ' <span>' . esc_html($item['text']) . '</span></a></li>';
                            }
                        ?>
                    </ul>
                </div>
            </div>

            <?php echo $this->getExtraHtml(20, $extra); ?>

            <div class="qfb-nav-item qfb-nav-item-form-switcher">
                <a class="qfb-nav-item-link qfb-nav-popup-trigger"><i class="fa fa-folder-open-o"></i></a>
                <div class="qfb-form-switcher qfb-nav-popup-content">
                    <ul class="qfb-nav-menu qfb-cf">
                        <?php
                            if (is_array($form) && ! empty($form['id'])) {
                                echo $this->getFormSwitcherItemHtml($form['id'], $form['name'], true);
                            }

                            foreach ($forms as $id => $name) {
                                if (is_array($form) && ! empty($form['id']) && $id == $form['id']) {
                                    continue;
                                }

                                echo $this->getFormSwitcherItemHtml($id, $name);
                            }
                        ?>
                        <li class="qfb-cf qfb-form-switcher-add-form-button"><?php printf('<a href="%s">%s</a>', esc_url(admin_url('admin.php?page=quform.forms&sp=add')), esc_html__('Add New', 'quform')); ?></li>
                    </ul>
                </div>
            </div>

            <?php echo $this->getExtraHtml(30, $extra); ?>

            <?php if (Quform::currentUserCan('quform_add_forms')) : ?>
                <div class="qfb-nav-item qfb-nav-item-add">
                    <a class="qfb-nav-item-link" title="<?php esc_attr_e('Create a new form', 'quform'); ?>" href="<?php echo esc_url(admin_url('admin.php?page=quform.forms&sp=add')); ?>"><i class="mdi mdi-add_circle"></i></a>
                </div>
            <?php endif; ?>

            <?php echo $this->getExtraHtml(40, $extra); ?>

            <div class="qfb-nav-item qfb-nav-item-right qfb-nav-item-help">
                <a class="qfb-nav-item-link qfb-nav-popup-trigger"><i class="fa fa-question"></i></a>
                <div class="qfb-nav-popup-content">
                    <ul class="qfb-nav-menu qfb-cf">
                        <li class="qfb-cf"><a href="<?php echo esc_url(admin_url('admin.php?page=quform.help')); ?>"><i class="fa fa-book"></i> View documentation</a></li>
                        <li class="qfb-cf"><a href="<?php echo esc_url(admin_url('admin.php?page=quform.help')); ?>"><i class="fa fa-play"></i> Video tutorials</a></li>
                        <li class="qfb-cf"><a href="<?php echo esc_url(admin_url('admin.php?page=quform.help')); ?>"><i class="fa fa-plane"></i> Tour of Quform</a></li>
                    </ul>
                </div>

            </div>

            <?php echo $this->getExtraHtml(50, $extra); ?>

        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Get the HTML for a single form list item in the form switcher
     *
     * @param   int     $id         The form ID
     * @param   string  $name       The form name
     * @param   bool    $highlight  Whether to highlight the item
     * @return  string
     */
    protected function getFormSwitcherItemHtml($id, $name, $highlight = false)
    {
        if ( ! Quform::isNonEmptyString($name)) {
            $name = __('(no title)', 'quform');
        }

        if ($this instanceof Quform_Admin_Page_Entries_List ||
            $this instanceof Quform_Admin_Page_Entries_View ||
            $this instanceof Quform_Admin_Page_Entries_Edit
        ) {
            $linkUrl = sprintf(admin_url('admin.php?page=quform.entries&id=%d'), $id);
        } else {
            $linkUrl = sprintf(admin_url('admin.php?page=quform.forms&sp=edit&id=%d'), $id);
        }

        $output = sprintf('<li class="qfb-cf%s">', $highlight ? ' qfb-highlight' : '');
        $output .= sprintf('<a title="%s" href="%s">', esc_attr($name), esc_url($linkUrl));
        $output .= esc_html($name);
        $output .= '<span class="qfb-fade-overflow"></span></a>';
        $output .= sprintf('<span class="qfb-form-switcher-icons"><a href="%s"><i title="%s" class="mdi mdi-chat"></i></a>', esc_url(admin_url('admin.php?page=quform.entries&id=' . $id)), esc_attr__('View Entries', 'quform'));
        $output .= sprintf('<a href="%s"><i title="%s" class="fa fa-pencil"></i></a></span>', esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $id)), esc_attr__('Edit', 'quform'));
        $output .= '</li>';

        return $output;
    }

    /**
     * Get extra HTML for the nav and call hooks
     *
     * @param   int    $index
     * @param   array  $extra
     * @return  string
     */
    protected function getExtraHtml($index, array $extra)
    {
        ob_start();

        if (isset($extra[$index])) {
            echo $extra[$index];
        }

        do_action('quform_admin_nav', $index, $this);

        return ob_get_clean();
    }
}
