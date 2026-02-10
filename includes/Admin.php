<?php
namespace DSF;
use DSF\Admin_Pages\Utils\Pagination;
use DSF\Admin_Pages\ServicesMenu;
use DSF\Admin_Pages\PackageTypesMenu;
use DSF\Admin_Pages\PackagePricingMenu;
use DSF\Admin_Pages\LocationsMenu;
use DSF\Admin_Pages\LocationPricingMenu;
use DSF\Admin_Pages\PortalsMenu;
use DSF\Admin_Pages\SubmissionsMenu;
use DSF\Admin_Pages\SettingsMenu;
/**
 * Admin class for admin panel management
 *
 * @package DSF
 */

class Admin {

    // All the Traits 
    use Pagination;
    use ServicesMenu;
    use PackageTypesMenu;
    use PackagePricingMenu;
    use LocationsMenu;
    use LocationPricingMenu;
    use PortalsMenu;
    use SubmissionsMenu;
    use SettingsMenu;
    /**
     * Admin instance
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get admin instance
     *
     * @return self
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('init', [$this, 'handle_form_submissions']);
    }

    /**
     * Register admin menu
     */
    public function register_menu() {
        // Main menu
        add_menu_page(
            __('Dynamic Services', 'dynamic-services-form'),
            __('Dynamic Services', 'dynamic-services-form'),
            'manage_options',
            'dsf-services',
            [$this, 'page_services'],
            'dashicons-hammer',
            30
        );
        
        // Services submenu
        add_submenu_page(
            'dsf-services',
            __('Services', 'dynamic-services-form'),
            __('Services', 'dynamic-services-form'),
            'manage_options',
            'dsf-services',
            [$this, 'page_services']
        );
        
        // Packages submenu
        add_submenu_page(
            'dsf-services',
            __('Package Types', 'dynamic-services-form'),
            __('Package Types', 'dynamic-services-form'),
            'manage_options',
            'dsf-package-types',
            [$this, 'page_package_types']
        );
        
        // Service-Package Pricing submenu
        add_submenu_page(
            'dsf-services',
            __('Package Pricing', 'dynamic-services-form'),
            __('Package Pricing', 'dynamic-services-form'),
            'manage_options',
            'dsf-package-pricing',
            [$this, 'page_package_pricing']
        );
        
        // Locations submenu
        add_submenu_page(
            'dsf-services',
            __('Locations', 'dynamic-services-form'),
            __('Locations', 'dynamic-services-form'),
            'manage_options',
            'dsf-locations',
            [$this, 'page_locations']
        );
        
        // Service-Location Pricing submenu
        add_submenu_page(
            'dsf-services',
            __('Location Pricing', 'dynamic-services-form'),
            __('Location Pricing', 'dynamic-services-form'),
            'manage_options',
            'dsf-pricing',
            [$this, 'page_location_pricing']
        );
        
        // Portals submenu
        add_submenu_page(
            'dsf-services',
            __('Portals', 'dynamic-services-form'),
            __('Portals', 'dynamic-services-form'),
            'manage_options',
            'dsf-portals',
            [$this, 'page_portals']
        );
        
        // Submissions submenu
        add_submenu_page(
            'dsf-services',
            __('Submissions', 'dynamic-services-form'),
            __('Submissions', 'dynamic-services-form'),
            'manage_options',
            'dsf-submissions',
            [$this, 'page_submissions']
        );
        
        // Settings submenu
        add_submenu_page(
            'dsf-services',
            __('Settings', 'dynamic-services-form'),
            __('Settings', 'dynamic-services-form'),
            'manage_options',
            'dsf-settings',
            [$this, 'page_settings']
        );
    }

    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        if (empty($_POST['dsf_action']) || !wp_verify_nonce(sanitize_text_field($_POST['dsf_nonce'] ?? ''), 'dsf_admin_nonce')) {
            return;
        }
        
        $action = sanitize_text_field($_POST['dsf_action']);
        
        switch ($action) {
            case 'save_service':
                $this->save_service();
                break;
            case 'delete_service':
                $this->delete_service();
                break;
            case 'save_package_type':
                $this->save_package_type();
                break;
            case 'delete_package_type':
                $this->delete_package_type();
                break;
            case 'save_package_pricing':
                $this->save_package_pricing();
                break;
            case 'delete_package_pricing':
                $this->delete_package_pricing();
                break;
            case 'save_location':
                $this->save_location();
                break;
            case 'delete_location':
                $this->delete_location();
                break;
            case 'save_pricing':
                $this->save_location_pricing();
                break;
            case 'delete_pricing':
                $this->delete_location_pricing();
                break;
            case 'save_portal':
                $this->save_portal();
                break;
            case 'delete_portal':
                $this->delete_portal();
                break;
        }
    }

}
