<?php if (!defined('ABSPATH')) exit; ?><div id="top" class="qfb qfb-cf">
    <?php echo $page->getMessagesHtml(); ?>
    <?php echo $page->getNavHtml(); ?>

    <div class="qfb-help qfb-cf">

        <div class="qfb-db-row qfb-cf">
            <div class="qfb-db-col">

                <div class="qfb-box">
                    <h3 class="qfb-box-heading qfb-db-heading"><i class="mdi mdi-help_outline"></i> <?php esc_html_e('Support', 'quform'); ?></h3>
                    <div class="qfb-content">
                        <div class="qfb-cf qfb-db-about-text">
                            <h2><?php esc_html_e('If you need assistance, see our help resources.', 'quform'); ?></h2>
                            <p><?php esc_html_e('Please make a search to find help with your problem, or head over to our support forums to ask a question.', 'quform'); ?></p>
                            <div class="qfb-db-row qfb-cf">
                                <div class="qfb-db-col">
                                    <a class="qfb-db-forum-button" href="http://support.themecatcher.net" target="_blank"><i class="fa fa-life-ring"></i><?php esc_html_e('Visit help site', 'quform'); ?></a>
                                </div>
                                <div class="qfb-db-col">
                                    <a class="qfb-db-forum-button" href="http://support.themecatcher.net/quform-wordpress/" target="_blank"><i class="fa fa-book"></i><?php esc_html_e('Documentation', 'quform'); ?></a>
                                </div>
                                <div class="qfb-db-col">
                                    <a class="qfb-db-forum-button" href="http://support.themecatcher.net/forums/" target="_blank"><i class="fa fa-comments"></i><?php esc_html_e('Forum', 'quform'); ?></a>
                                </div>
                            </div>
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
            <div class="qfb-db-col">

                <div class="qfb-db-col qfb-last">
                    <div class="qfb-box">
                        <h3 class="qfb-box-heading qfb-db-heading"><i class="mdi mdi-favorite_border"></i> About us <a class="qfb-logo-icon" href="http://www.quform.com"></a></h3>
                        <div class="qfb-content">

                            <div class="qfb-cf qfb-db-about-text">
                                <h4 class="qfb-bold">Quform is a drag and drop form builder to make building
                                    forms easy and enjoyable.</h4>

                                <p>Quform was developed by ThemeCatcher and is <a href="https://www.quform.com/buy.php" target="_blank">available for
                                        purchase</a> on CodeCanyon.</p>

                                <p>We work hard to give you an exceptional premium products
                                    and 5 star support. To show your appreciation you can
                                    buy us a coffee or simply by sharing or follow us on social
                                    media.</p>
                            </div>

                            <div class="qfb-cf qfb-db-social-links">
                                <ul>
                                    <li class="qfb-buy-coffee"><a href="https://www.themecatcher.net/buy-us-a-coffee"><i class="fa fa-coffee"></i> Buy us a coffee</a></li>
                                    <li class="qfb-facebook"><a href="https://www.facebook.com/ThemeCatcher"><i class="fa fa-facebook"></i> Like us</a></li>
                                    <li class="qfb-twitter"><a href="https://twitter.com/ThemeCatcher"><i class="fa fa-twitter"></i> Tweet us</a></li>
                                    <li class="qfb-envato"><a href="https://codecanyon.net/user/themecatcher"><i class="fa fa-leaf"></i> Follow us</a></li>
                                </ul>
                            </div>

                            <p><a href="https://www.themecatcher.net" class="qfb-link qfb-external-link">www.themecatcher.net</a> |
                                <a href="https://www.quform.com" class="qfb-link qfb-external-link">www.quform.com</a></p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>