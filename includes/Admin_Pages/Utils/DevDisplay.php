<?php
namespace DSF\Admin_Pages\Utils;

trait DevDisplay
{
    /**
     * The transient key for dev display session
     */
    private $dev_display_transient = 'dsf_dev_display_session';
    
    /**
     * Session expiration time in seconds (20 minutes)
     */
    private $dev_display_expiration = 1200; 

    /**
     * Initialize dev display functionality
     * Handles query parameter and session management
     */
    public function init_dev_display()
    {
        // Check if user has dev display query parameter
        if (isset($_GET['dev_debug_display']) && $_GET['dev_debug_display'] === 'true') {
            $this->enable_dev_display_session();
        }

        // Handle clear dev display request
        if (isset($_GET['dsf_clear_dev_display']) && $_GET['dsf_clear_dev_display'] === '1') {
            $this->disable_dev_display_session();
        }

        // Add admin bar button if dev display is active
        if ($this->is_dev_display_active()) {
            add_action('admin_bar_menu', [$this, 'add_dev_display_toggle'], 100);
        }
    }

    /**
     * Enable dev display session for current user
     */
    private function enable_dev_display_session()
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            set_transient(
                $this->dev_display_transient . '_' . $user_id,
                1,
                $this->dev_display_expiration
            );
        }
    }

    /**
     * Disable dev display session for current user
     */
    private function disable_dev_display_session()
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            delete_transient($this->dev_display_transient . '_' . $user_id);
            // Redirect to remove query parameter from URL
            wp_safe_remote_post(admin_url('admin-ajax.php?action=dsf_redirect_clear'));
            wp_redirect(admin_url('admin.php?page=dsf-submit-services'));
            exit;
        }
    }

    /**
     * Check if dev display session is active
     */
    private function is_dev_display_active(): bool
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        return get_transient($this->dev_display_transient . '_' . $user_id) !== false;
    }

    /**
     * Hide or show submenus based on dev display session
     */
    public function dev_display()
    {
        if (!$this->is_dev_display_active()) {
            remove_submenu_page('dsf-submit-services', 'dsf-services');
            remove_submenu_page('dsf-submit-services', 'dsf-package-types');
            remove_submenu_page('dsf-submit-services', 'dsf-package-pricing');
            remove_submenu_page('dsf-submit-services', 'dsf-locations');
            remove_submenu_page('dsf-submit-services', 'dsf-location-pricing');
            remove_submenu_page('dsf-submit-services', 'dsf-portals');
        }
    }

    /**
     * Add toggle button to admin bar
     */
    public function add_dev_display_toggle($wp_admin_bar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $clear_url = add_query_arg('dsf_clear_dev_display', '1', admin_url('admin.php?page=dsf-submit-services'));

        $wp_admin_bar->add_menu([
            'id' => 'dsf-dev-display-toggle',
            'title' => __('🔧 Dev Display Active (' . $this->dev_display_expiration . ' sec)', 'dynamic-services-form'),
            'href' => $clear_url,
            'meta' => [
                'class' => 'dsf-dev-display-active',
                'title' => __('Click to disable dev display menus', 'dynamic-services-form'),
            ],
        ]);
    }
}