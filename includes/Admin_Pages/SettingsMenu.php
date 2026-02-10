<?php 
namespace DSF\Admin_Pages;
trait SettingsMenu {
    /**
     * Settings page
     */
    public function page_settings() {
        // Check if user has permission
        if ( ! current_user_can('manage_options') ) {
            return;
        }

        // Handle form submission
        if ( isset($_POST['dsf_settings_nonce']) && wp_verify_nonce($_POST['dsf_settings_nonce'], 'dsf_save_settings') ) {
            if ( isset($_POST['dsf_email']) && is_email($_POST['dsf_email']) ) {
                update_option('dsf_admin_email', sanitize_email($_POST['dsf_email']));
                echo '<div class="updated notice"><p>Email updated successfully!</p></div>';
            } else {
                echo '<div class="error notice"><p>Please enter a valid email.</p></div>';
            }
        }

        // Get current email
        $current_email = get_option('dsf_admin_email', get_option('admin_email'));
        ?>
        <div class="wrap">
            <h1><?php _e('Dynamic Services Settings', 'dynamic-services-form'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('dsf_save_settings', 'dsf_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="dsf_email"><?php _e('Admin Email', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="email" name="dsf_email" id="dsf_email" value="<?php echo esc_attr($current_email); ?>" class="regular-text" required>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Changes', 'dynamic-services-form')); ?>
            </form>
        </div>
        <?php
    }
}