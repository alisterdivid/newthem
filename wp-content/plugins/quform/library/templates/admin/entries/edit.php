<?php if (!defined('ABSPATH')) exit;
/* @var Quform_Admin_Page_Entries_Edit $page
 * @var Quform_Form $form
 */
?><div id="top" class="qfb qfb-cf">
    <?php echo $page->getNavHtml(array('id' => $form->getId(), 'name' => $form->config('name'))); ?>

    <form id="qfb-entry-form" method="POST">
        <input type="password" class="qfb-hidden">
        <input type="hidden" name="quform_save_entry" value="1">
        <input type="hidden" name="quform_form_id" value="<?php echo Quform::escape($form->getId()); ?>">
        <input type="hidden" name="quform_form_uid" value="<?php echo Quform::escape($form->getUniqueId()); ?>">
        <input type="hidden" name="quform_entry_id" value="<?php echo Quform::escape($entry['id']); ?>">
        <?php wp_nonce_field('quform_edit_entry_' . $entry['id']); ?>

        <div class="qfb-fixed-buttons">
            <a href="<?php echo esc_url(add_query_arg(array('sp' => 'view'))); ?>" title="<?php esc_attr_e('View', 'quform'); ?>"><i class="fa fa-eye"></i></a>
            <div id="qfb-fixed-save-button" class="qfb-animated-save-button" title="<?php esc_attr_e('Save', 'quform'); ?>"><i class="fa fa-floppy-o"></i></div>
        </div>

        <div class="qfb-cf qfb-entry-wrap">
            <div class="qfb-entry-left">
                <div class="qfb-box">
                    <h3 class="qfb-entry-heading qfb-settings-heading"><i class="mdi mdi-mode_edit"></i><?php esc_html_e('Submitted form data', 'quform'); ?></h3>
                    <table class="qfb-entry-table qfb-settings">
                        <?php foreach ($form->getRecursiveIterator() as $element) : ?>
                            <?php if ($element instanceof Quform_Element_Editable && $element->config('saveToDatabase')) : ?>
                                <tr>
                                    <th><div class="qfb-entry-element-label"><?php echo $element->getEditLabelHtml(); ?></div></th>
                                </tr>
                                <tr>
                                    <td><div class="qfb-edit-element qfb-edit-element-<?php echo esc_attr($element->getIdentifier()); ?> qfb-cf"><div class="qfb-edit-input qfb-edit-input-<?php echo esc_attr($element->getIdentifier()); ?> qfb-cf"><?php echo $element->getEditFieldHtml(); ?></div></div></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <button type="button" id="qfb-save-entry" class="qfb-button-green"><i class="fa fa-floppy-o"></i><?php esc_html_e('Save', 'quform'); ?></button>
            </div>
            <div class="qfb-entry-right">
                <div class="qfb-box">
                    <h3 class="qfb-entry-heading qfb-settings-heading"><i class="mdi mdi-mode_edit"></i><?php esc_html_e('Additional information', 'quform'); ?></h3>
                    <table class="qfb-entry-table qfb-settings">
                        <tr>
                            <th><?php esc_html_e('Form', 'quform'); ?></th>
                            <td>
                                <?php
                                    printf(
                                        '<a href="%s" title="%s">%s</a>',
                                        esc_url(admin_url(sprintf('admin.php?page=quform.forms&sp=edit&id=%d', $form->getId()))),
                                        esc_attr__('Edit this form', 'quform'),
                                        Quform::isNonEmptyString($form->config('name')) ? esc_html($form->config('name')) : esc_html__('(no title)', 'quform')
                                    );
                                ?>
                            </td>
                        </tr>
                        <?php
                        $keys = array(
                            'created_at' => __('Date', 'quform'),
                            'id' => __('Entry ID', 'quform'),
                            'form_url' => __('Form URL', 'quform'),
                            'referring_url' => __('Referring URL', 'quform'),
                            'ip' => __('IP address', 'quform'),
                            'post_id' => __('Page', 'quform'),
                            'created_by' => __('User', 'quform')
                        );

                        foreach ($keys as $key => $label) {
                            $value = Quform::get($entry, $key);

                            if ( ! is_string($value)) {
                                $value = '';
                            }

                            switch ($key) {
                                case 'id':
                                    /* Disallow edit */
                                    $value = sprintf(
                                        '<a href="%s" title="%s">%s</a>',
                                        esc_url(admin_url(sprintf('admin.php?page=quform.entries&sp=view&eid=%d', $value))),
                                        esc_attr__('View this entry', 'quform'),
                                        esc_html($value)
                                    );
                                    break;
                                case 'created_by':
                                    $selected = $value;

                                    $value = '<select name="' . Quform::escape('entry_' . $key) . '">';
                                    $value .= '<option value="">' . esc_html__('None', 'quform') . '</option>';
                                    foreach (get_users() as $user) {
                                        $value .= '<option value="' . esc_attr($user->ID) . '" ' . selected($selected, $user->ID, false) . '>' . Quform::escape($user->user_login) . '</option>';
                                    }
                                    $value .= '</select>';
                                    break;
                                default:
                                    $value = '<input type="text" name="' . Quform::escape('entry_' . $key) . '" value="' . Quform::escape($value) . '">';
                                    break;
                            }

                            echo '<tr><th scope="row">' . esc_html($label) . '</th><td>' . $value . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('Labels', 'quform'); ?></th>
                            <td class="qfb-single-entry-labels" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
                                <?php echo $page->getEntryLabelsHtml($entry['labels']); ?>
                                <?php if (count($labels)) : ?>
                                    <div id="qfb-entry-label-set">
                                        <?php foreach ($labels as $label) : ?>
                                            <div class="qfb-entry-label" data-label="<?php echo Quform::escape(wp_json_encode($label)); ?>" style="background-color: <?php echo Quform::escape($label['color']); ?>;"><span class="qfb-entry-label-name"><?php echo Quform::escape($label['name']); ?></span><i class="fa fa-check"></i></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
