<?php if (!defined('ABSPATH')) exit;
/** @var Quform_Admin_Page_Dashboard $page */
/** @var Quform_Options $options */
?><div class="qfb qfb-cf">
    <?php
        echo $page->getNavHtml();
        echo $page->getMessagesHtml();
    ?>

    <div class="qfb-dashboard qfb-cf">

        <div class="qfb-db-row qfb-cf">
            <div class="qfb-db-col">

                <div class="qfb-box">
                    <div class="qfb-cf">
                        <h3 class="qfb-box-heading qfb-db-heading">
                            <i class="mdi mdi-view_stream"></i>
                            <?php esc_html_e('Forms', 'quform'); ?>
                        </h3>
                    </div>
                    <div class="qfb-content qfb-form-switcher qfb-db-form-list">
                        <?php if (count($forms)) : ?>
                            <ul class="qfb-nav-menu qfb-cf">
                                <?php foreach ($forms as $form) : ?>
                                    <li class="qfb-cf">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $form['id'])); ?>"><?php echo esc_html($form['name']); ?><span class="qfb-fade-overflow"></span></a>
                                        <span class="qfb-form-switcher-icons">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=quform.entries&id=' . $form['id'])); ?>"><i title="<?php esc_attr_e('View entries', 'quform'); ?>" class="mdi mdi-chat"></i></a>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $form['id'])); ?>"><i title="<?php esc_attr_e('Edit this form', 'quform'); ?>" class="fa fa-pencil"></i></a>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                                <li class="qfb-cf qfb-form-switcher-add-form-button qfb-form-switcher-two-buttons">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=quform.forms')); ?>"><?php esc_html_e('View all', 'quform'); ?></a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=quform.forms&sp=add')); ?>"><?php esc_html_e('Add New', 'quform'); ?></a>
                                </li>
                            </ul>
                        <?php else : ?>
                            <div class="qfb-message-box qfb-message-box-info">
                                <div class="qfb-message-box-inner">
                                    <?php
                                        printf(
                                            esc_html__('No forms yet, %sclick here to create one%s.', 'quform'),
                                            sprintf('<a href="%s">', esc_url(admin_url('admin.php?page=quform.forms&sp=add'))),
                                            '</a>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <ul class="qfb-nav-menu qfb-cf">
                                <li class="qfb-cf qfb-form-switcher-add-form-button">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=quform.forms&sp=add')); ?>"><?php esc_html_e('Add New', 'quform'); ?></a>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="qfb-db-col">

                <div class="qfb-box">
                    <div class="qfb-cf">
                        <h3 class="qfb-box-heading qfb-db-heading">
                            <i class="mdi mdi-chat"></i>
                            <?php esc_html_e('Recent entries', 'quform'); ?>
                            <?php if ($unreadCount > 0) : ?>
                                <span class="qfb-db-new-message-count"><?php echo esc_html(Quform::formatCount($unreadCount)); ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="qfb-content qfb-form-switcher qfb-db-entry-list">
                        <?php if (count($recentEntries)) : ?>
                            <ul class="qfb-nav-menu qfb-cf">
                                <?php foreach ($recentEntries as $recentEntry) : ?>
                                    <li class="qfb-cf<?php echo $recentEntry['unread'] == '1' ? ' qfb-unread' : ''; ?>">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=quform.entries&sp=view&eid=' . $recentEntry['id'])); ?>">
                                            <?php if ($recentEntry['unread'] == '1') : ?>
                                                <i class="fa fa-envelope"></i>
                                            <?php else : ?>
                                                <i class="fa fa-envelope-open-o"></i>
                                            <?php endif; ?>
                                            <span class="qfb-db-entry-list-date"><?php echo esc_html($options->formatDate($recentEntry['created_at'], true)); ?></span>
                                            <span class="qfb-db-entry-list-form-name"><?php echo esc_html($recentEntry['name']); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('No recent entries yet.', 'quform'); ?></div></div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="qfb-db-col">
                <?php if (count($tools)) : ?>
                    <div class="qfb-box">
                        <h3 class="qfb-box-heading qfb-db-heading"><i class="mdi mdi-build"></i> <?php esc_html_e('Tools', 'quform'); ?></h3>
                        <div class="qfb-content">
                            <div class="qfb-tools qfb-db-tools qfb-cf">
                                <?php foreach ($tools as $tool) : ?>
                                    <div class="qfb-tool">
                                        <div class="qfb-tool-box qfb-box">
                                            <div class="qfb-tool-icon"><a href="<?php echo esc_url($tool['url']); ?>"><?php echo $tool['icon']; ?></a></div>
                                            <div class="qfb-tool-title"><a href="<?php echo esc_url($tool['url']); ?>"><?php echo esc_html($tool['title']); ?></a></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="qfb-box">
                    <h3 class="qfb-box-heading qfb-db-heading"><i class="mdi mdi-help_outline"></i> <?php esc_html_e('Support', 'quform'); ?></h3>
                    <div class="qfb-content">
                        <div class="qfb-cf qfb-db-about-text">
                            <a class="qfb-db-forum-button" href="http://support.themecatcher.net" target="_blank"><i class="fa fa-life-ring"></i><?php esc_html_e('Visit help site', 'quform'); ?></a>
                            <h4><?php esc_html_e('If you need assistance, see our help resources.', 'quform'); ?></h4>
                            <p><?php esc_html_e('Please make a search to find help with your problem, or head over to our support forums to ask a question.', 'quform'); ?></p>
                        </div>
                        <div class="qfb-cf qfb-db-form">
                            <form action="http://support.themecatcher.net" method="get">
                                <input type="hidden" name="c" value="5">
                                <div class="qfb-db-form-button">
                                    <button class="qfb-button"><?php esc_html_e('Search', 'quform'); ?></button>
                                </div>
                                <div class="qfb-db-form-input">
                                    <input type="text" name="s" placeholder="<?php esc_attr_e('Enter search query', 'quform'); ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
