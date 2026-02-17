<?php
namespace DSF\Admin_Pages;
use DSF\Database;
use DSF\EmailTemplate;

/**
 * Email Templates menu trait
 *
 * @package DSF
 */
trait EmailTemplatesMenu {

    /**
     * Email templates page
     */
    public function page_email_templates() {
        // Handle save/delete actions
        if (!empty($_POST['dsf_template_action'])) {
            if (!wp_verify_nonce(sanitize_text_field($_POST['dsf_nonce'] ?? ''), 'dsf_admin_nonce')) {
                wp_die('Security check failed');
            }
            
            $action = sanitize_text_field($_POST['dsf_template_action']);
            
            if ($action === 'save_template') {
                $this->save_email_template();
            } elseif ($action === 'delete_template') {
                $this->delete_email_template();
            }
        }

        // Get action from URL
        $current_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
        
        if ($current_action === 'edit' && $template_id) {
            $this->render_edit_template($template_id);
        } elseif ($current_action === 'new') {
            $this->render_edit_template(null);
        } else {
            $this->render_templates_list();
        }
    }

    /**
     * Render templates list
     */
    private function render_templates_list() {
        $templates = EmailTemplate::get_all();
        
        ?>
        <div class="wrap">
            <div style=" justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1><?php esc_html_e('Email Templates', 'dynamic-services-form'); ?></h1>
                <a href="<?php echo admin_url('admin.php?page=dsf-email-templates&action=new'); ?>" class="button button-primary">
                    ➕ Add New Template
                </a>
            </div>

            <?php if (empty($templates)) : ?>
                <div style="padding: 40px 20px; background: #f0f0f0; border-radius: 5px; text-align: center;">
                    <p><?php esc_html_e('No email templates found. Create your first template!', 'dynamic-services-form'); ?></p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;"><?php esc_html_e('Template Name', 'dynamic-services-form'); ?></th>
                            <th style="width: 15%;"><?php esc_html_e('Slug', 'dynamic-services-form'); ?></th>
                            <th style="width: 30%;"><?php esc_html_e('Subject', 'dynamic-services-form'); ?></th>
                            <th style="width: 10%;"><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                            <th style="width: 10%;"><?php esc_html_e('Default', 'dynamic-services-form'); ?></th>
                            <th style="width: 10%;"><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($template['template_name']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo esc_html($template['template_slug']); ?></code>
                                </td>
                                <td>
                                    <?php echo esc_html($template['template_subject']); ?>
                                </td>
                                <td>
                                    <?php 
                                    $status = $template['enabled'] ? '✅ Active' : '❌ Inactive';
                                    echo esc_html($status);
                                    ?>
                                </td>
                                <td>
                                    <?php echo $template['is_default'] ? '⭐ Default' : '-'; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=dsf-email-templates&action=edit&template_id=' . $template['id']); ?>" class="button button-small" style="margin-right: 5px;">
                                        ✏️ Edit
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_template_action" value="delete_template">
                                        <input type="hidden" name="template_id" value="<?php echo intval($template['id']); ?>">
                                        <button type="submit" class="button button-small button-link-delete">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render edit template form
     */
    private function render_edit_template($template_id = null) {
        $template = null;
        if ($template_id) {
            $template = new EmailTemplate($template_id);
            if (!$template->get_id()) {
                wp_die('Template not found');
            }
        }

        $available_tags = EmailTemplate::get_available_tags();
        
        ?>
        <div class="wrap" style="max-width: 1400px;">
            <h1><?php echo $template ? esc_html_e('Edit Email Template', 'dynamic-services-form') : esc_html_e('Create New Email Template', 'dynamic-services-form'); ?></h1>

            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <!-- Main Editor -->
                <div style="flex: 1;">
                    <form method="POST" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                        <input type="hidden" name="dsf_template_action" value="save_template">
                        <?php if ($template) : ?>
                            <input type="hidden" name="template_id" value="<?php echo intval($template->get_id()); ?>">
                        <?php endif; ?>

                        <!-- Template Name -->
                        <div style="margin-bottom: 20px;">
                            <label for="template_name" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <?php esc_html_e('Template Name', 'dynamic-services-form'); ?>
                            </label>
                            <input type="text" id="template_name" name="template_name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="e.g., Quotation Email" value="<?php echo $template ? esc_attr($template->get('template_name')) : ''; ?>">
                            <small style="display: block; margin-top: 5px; color: #666;"><?php esc_html_e('A friendly name for this template', 'dynamic-services-form'); ?></small>
                        </div>

                        <!-- Template Slug -->
                        <div style="margin-bottom: 20px;">
                            <label for="template_slug" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <?php esc_html_e('Template Slug', 'dynamic-services-form'); ?>
                            </label>
                            <input type="text" id="template_slug" name="template_slug" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="e.g., quotation_email" value="<?php echo $template ? esc_attr($template->get('template_slug')) : ''; ?>" <?php echo $template ? 'readonly' : ''; ?>>
                            <small style="display: block; margin-top: 5px; color: #666;"><?php esc_html_e('Unique identifier (lowercase, underscores)', 'dynamic-services-form'); ?></small>
                        </div>

                        <!-- Template Subject -->
                        <div style="margin-bottom: 20px;">
                            <label for="template_subject" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <?php esc_html_e('Email Subject', 'dynamic-services-form'); ?>
                            </label>
                            <input type="text" id="template_subject" name="template_subject" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Your Quotation Details - [SERVICE_NAME]" value="<?php echo $template ? esc_attr($template->get('template_subject')) : ''; ?>">
                            <small style="display: block; margin-top: 5px; color: #666;"><?php esc_html_e('You can use dynamic tags here', 'dynamic-services-form'); ?></small>
                        </div>

                        <!-- Template Description -->
                        <div style="margin-bottom: 20px;">
                            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <?php esc_html_e('Description (Optional)', 'dynamic-services-form'); ?>
                            </label>
                            <textarea id="description" name="description" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 60px;" placeholder="Describe what this template is for..."><?php echo $template ? esc_textarea($template->get('description')) : ''; ?></textarea>
                        </div>

                        <!-- Template HTML -->
                        <div style="margin-bottom: 20px;">
                            <label for="template_html" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <?php esc_html_e('Template HTML', 'dynamic-services-form'); ?>
                            </label>
                            <textarea id="template_html" name="template_html" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 400px; font-family: 'Courier New', monospace; font-size: 12px;"><?php echo $template ? esc_textarea($template->get('template_html')) : '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n    <title>Email Template</title>\n</head>\n<body>\n    <p>Hello [CLIENT_NAME],</p>\n    <p>Your service: [SERVICE_NAME]</p>\n    <p>Total: [TOTAL_PRICE]</p>\n</body>\n</html>'; ?></textarea>
                            <small style="display: block; margin-top: 5px; color: #666;"><?php esc_html_e('Enter your complete HTML template. Use dynamic tags like [CLIENT_NAME], [SERVICE_NAME], etc.', 'dynamic-services-form'); ?></small>
                        </div>

                        <!-- Template CSS -->
                        <div style="margin-bottom: 20px;">
                            <label for="template_css" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <?php esc_html_e('Custom CSS (Optional)', 'dynamic-services-form'); ?>
                            </label>
                            <textarea id="template_css" name="template_css" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 150px; font-family: 'Courier New', monospace; font-size: 12px;"><?php echo $template ? esc_textarea($template->get('template_css')) : '/* Add your custom CSS here */'; ?></textarea>
                            <small style="display: block; margin-top: 5px; color: #666;"><?php esc_html_e('Custom CSS will be added to the email template', 'dynamic-services-form'); ?></small>
                        </div>

                        <!-- Checkboxes -->
                        <div style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
                            <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                                <input type="checkbox" name="enabled" value="1" <?php echo !$template || $template->get('enabled') ? 'checked' : ''; ?>>
                                <span style="margin-left: 8px;"><?php esc_html_e('Enable this template', 'dynamic-services-form'); ?></span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="is_default" value="1" <?php echo $template && $template->get('is_default') ? 'checked' : ''; ?>>
                                <span style="margin-left: 8px;"><?php esc_html_e('Mark as default template', 'dynamic-services-form'); ?></span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="button button-primary" style="padding: 10px 30px;">
                                💾 <?php echo $template ? esc_html_e('Update Template', 'dynamic-services-form') : esc_html_e('Create Template', 'dynamic-services-form'); ?>
                            </button>
                            <a href="<?php echo admin_url('admin.php?page=dsf-email-templates'); ?>" class="button">
                                ← <?php esc_html_e('Back to Templates', 'dynamic-services-form'); ?>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Sidebar - Dynamic Tags Reference -->
                <div style="width: 350px;">
                    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 20px;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px;">📋 Available Dynamic Tags</h3>
                        <p style="font-size: 12px; color: #666; margin-bottom: 15px;"><?php esc_html_e('Copy and paste these tags into your template to insert dynamic content:', 'dynamic-services-form'); ?></p>
                        
                        <div style="background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px; max-height: 400px; overflow-y: auto;">
                            <?php foreach ($available_tags as $tag => $description) : ?>
                                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e0e0e0;">
                                    <code style="background: #e8f4f8; padding: 4px 8px; border-radius: 3px; display: block; margin-bottom: 4px; cursor: pointer; word-break: break-all;" onclick="copyToClipboard('[<?php echo esc_attr($tag); ?>]')" title="Click to copy">
                                        [<?php echo esc_html($tag); ?>]
                                    </code>
                                    <small style="color: #666; display: block;"><?php echo esc_html($description); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px; margin-top: 15px; font-size: 12px;">
                            <strong>💡 Tip:</strong> Tags are case-insensitive. Both [CLIENT_NAME] and [client_name] will work.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                        if (typeof showToast === 'function') {
                            showToast('Tag copied: ' + text, 'success');
                        } else {
                            alert('Tag copied: ' + text);
                        }
                    });
            }

            // Auto-generate slug from name
            document.getElementById('template_name')?.addEventListener('change', function() {
                const slugInput = document.getElementById('template_slug');
                if (!slugInput.readOnly) {
                    slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                }
            });
        </script>

        <style>
            #template_html, #template_css {
                font-family: 'Courier New', Courier, monospace;
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 15px;
            }
        </style>
        <?php
    }

    /**
     * Save email template
     */
    private function save_email_template() {
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        
        $template_data = [
            'template_name'    => $_POST['template_name'] ?? '',
            'template_slug'    => $_POST['template_slug'] ?? '',
            'template_subject' => $_POST['template_subject'] ?? '',
            'template_html'    => $_POST['template_html'] ?? '',
            'template_css'     => $_POST['template_css'] ?? '',
            'description'      => $_POST['description'] ?? '',
            'enabled'          => isset($_POST['enabled']) ? 1 : 0,
            'is_default'       => isset($_POST['is_default']) ? 1 : 0,
        ];

        if ($template_id) {
            $template = new EmailTemplate($template_id);
            $result = $template->save($template_data);
            $message = $result ? 'Template updated successfully!' : 'Failed to update template.';
        } else {
            $template = new EmailTemplate();
            $result = $template->save($template_data);
            $message = $result ? 'Template created successfully!' : 'Failed to create template.';
        }

        $redirect_url = admin_url('admin.php?page=dsf-email-templates');
        if ($result) {
            $redirect_url = add_query_arg('template_action', 'saved', $redirect_url);
        }
        
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Delete email template
     */
    private function delete_email_template() {
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        
        if ($template_id) {
            EmailTemplate::delete($template_id);
        }

        wp_redirect(admin_url('admin.php?page=dsf-email-templates'));
        exit;
    }
}
