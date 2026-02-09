<?php
/**
 * Admin class for admin panel management
 *
 * @package DSF
 */

namespace DSF;

class Admin {
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
        // Register admin menu
        add_action('admin_menu', [$this, 'register_menu']);
        
        // Handle form submissions
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

    /**
     * Services page
     */
    public function page_services() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_service_form($id);
        } elseif ($action === 'add') {
            $this->show_service_form();
        } else {
            $this->show_services_list();
        }
    }

    /**
     * Show services list
     */
    private function show_services_list() {
        $services = Service::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Services', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-services&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Type', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Category', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Name', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Pricing Model', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Packages', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No services found.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($services as $service) : ?>
                            <tr>
                                <td><?php echo esc_html($service['type']); ?></td>
                                <td><?php echo esc_html($service['category']); ?></td>
                                <td><?php echo esc_html($service['name']); ?></td>
                                    <td>
                                        <?php if ($service['pricing_model'] === 'fixed_price' && !empty($service['fixed_price'])) : ?>
                                            <?php echo '$' . number_format((float) $service['fixed_price'], 2); ?>
                                        <?php else : ?>
                                            --
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($service['pricing_model']); ?></td>
                                <td><?php echo $service['has_packages'] ? esc_html__('Yes', 'dynamic-services-form') : esc_html__('No', 'dynamic-services-form'); ?></td>
                                <td><?php echo $service['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-services&action=edit&id=' . $service['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_service">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($service['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show service form
     *
     * @param int $id Service ID (optional)
     */
    private function show_service_form($id = 0) {
        $service_data = [
            'id' => 0,
            'type' => '',
            'category' => '',
            'name' => '',
            'pricing_model' => 'state_based',
            'fixed_price' => '',
            'has_packages' => 0,
            'description' => '',
            'enabled' => 1,
        ];
        
        if ($id) {
            $service = new Service($id);
            if ($service->get_id()) {
                $service_data = $service->get_data();
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Service', 'dynamic-services-form') : esc_html__('Add New Service', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_service">
                <input type="hidden" name="id" value="<?php echo esc_attr($service_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="type"><?php esc_html_e('Service Type', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="type" name="type" value="<?php echo esc_attr($service_data['type']); ?>" required>
                            <p class="description"><?php esc_html_e('e.g., USA, UK, Federal', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category"><?php esc_html_e('Category', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="category" name="category" value="<?php echo esc_attr($service_data['category']); ?>" required>
                            <p class="description"><?php esc_html_e('e.g., Core Company, Banking Finance', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="name"><?php esc_html_e('Service Name', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="name" name="name" value="<?php echo esc_attr($service_data['name']); ?>" required>
                            <p class="description"><?php esc_html_e('e.g., DBA Fictitious Name, EIN with IRS', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pricing_model"><?php esc_html_e('Pricing Model', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="pricing_model" name="pricing_model" required>
                                <option value="state_based" <?php selected($service_data['pricing_model'], 'state_based'); ?>>
                                    <?php esc_html_e('State Based', 'dynamic-services-form'); ?>
                                </option>
                                <option value="portal_based" <?php selected($service_data['pricing_model'], 'portal_based'); ?>>
                                    <?php esc_html_e('Portal Based', 'dynamic-services-form'); ?>
                                </option>
                                <option value="fixed_price" <?php selected($service_data['pricing_model'], 'fixed_price'); ?>>
                                    <?php esc_html_e('Fixed Price', 'dynamic-services-form'); ?>
                                </option>
                                <option value="calculator" <?php selected($service_data['pricing_model'], 'calculator'); ?>>
                                    <?php esc_html_e('Calculator', 'dynamic-services-form'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr id="fixed-price-row" style="display: none;">
                        <th scope="row"><label for="fixed_price"><?php esc_html_e('Fixed Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="fixed_price" name="fixed_price" step="0.01" value="<?php echo esc_attr($service_data['fixed_price']); ?>">
                            <p class="description"><?php esc_html_e('Price for fixed price model. Required when Pricing Model is "Fixed Price".', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="has_packages"><?php esc_html_e('Has Packages', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="has_packages" name="has_packages" value="1" <?php checked($service_data['has_packages']); ?>>
                            <p class="description"><?php esc_html_e('Enable standard/premium package selection', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php esc_html_e('Description', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <textarea id="description" name="description" rows="4"><?php echo esc_textarea($service_data['description']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($service_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Service', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-services')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Save service
     */
    private function save_service() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $pricing_model = isset($_POST['pricing_model']) ? sanitize_text_field($_POST['pricing_model']) : 'state_based';
        $has_packages = isset($_POST['has_packages']) ? 1 : 0;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        $fixed_price = isset($_POST['fixed_price']) && $_POST['fixed_price'] !== '' ? floatval($_POST['fixed_price']) : null;
        
        $args = [
            'type' => $type,
            'category' => $category,
            'name' => $name,
            'pricing_model' => $pricing_model,
            'fixed_price' => $fixed_price,
            'has_packages' => $has_packages,
            'description' => $description,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        Service::save($args);
        wp_safe_remote_post(admin_url('admin.php?page=dsf-services'));
        wp_redirect(admin_url('admin.php?page=dsf-services'));
        exit;
    }

    /**
     * Delete service
     */
    private function delete_service() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            Service::delete($id);
        }
        wp_redirect(admin_url('admin.php?page=dsf-services'));
        exit;
    }

    /**
     * Packages page
     */
    public function page_packages() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_package_form($id);
        } elseif ($action === 'add') {
            $this->show_package_form();
        } else {
            $this->show_packages_list();
        }
    }

    /**
     * Show packages list
     */
    private function show_packages_list() {
        $packages = Package::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Packages', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-packages&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Service', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Type', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($packages)) : ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No packages found.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($packages as $package) : ?>
                            <?php $service = new Service($package['service_id']); ?>
                            <tr>
                                <td><?php echo esc_html($service->get('name')); ?></td>
                                <td><?php echo esc_html($package['package_type']); ?></td>
                                <td><?php echo !empty($package['price']) ? '$' . number_format((float) $package['price'], 2) : '--'; ?></td>
                                <td><?php echo $package['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-packages&action=edit&id=' . $package['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_package">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($package['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show package form
     *
     * @param int $id Package ID (optional)
     */
    private function show_package_form($id = 0) {
        $package_data = [
            'id' => 0,
            'service_id' => 0,
            'package_type' => '',
            'price' => '',
            'description' => '',
            'enabled' => 1,
        ];
        
        if ($id) {
            $package = new Package($id);
            if ($package->get_id()) {
                $package_data = $package->get_data();
            }
        }
        
        $services = Service::get_all();
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Package', 'dynamic-services-form') : esc_html__('Add New Package', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_package">
                <input type="hidden" name="id" value="<?php echo esc_attr($package_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="service_id"><?php esc_html_e('Service', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="service_id" name="service_id" required>
                                <option value="">-- <?php esc_html_e('Select Service', 'dynamic-services-form'); ?> --</option>
                                <?php foreach ($services as $service) : ?>
                                    <option value="<?php echo esc_attr($service['id']); ?>" <?php selected($package_data['service_id'], $service['id']); ?>>
                                        <?php echo esc_html($service['type'] . ' - ' . $service['category'] . ' - ' . $service['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="package_type"><?php esc_html_e('Package Type', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="package_type" name="package_type" value="<?php echo esc_attr($package_data['package_type']); ?>" required>
                            <p class="description"><?php esc_html_e('e.g., Standard, Premium', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price"><?php esc_html_e('Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo esc_attr($package_data['price']); ?>">
                            <p class="description"><?php esc_html_e('Leave empty for free or enter price', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php esc_html_e('Description', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <textarea id="description" name="description" rows="4"><?php echo esc_textarea($package_data['description']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($package_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Package', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-packages')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Save package
     */
    private function save_package() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $package_type = isset($_POST['package_type']) ? sanitize_text_field($_POST['package_type']) : '';
        $price = isset($_POST['price']) && $_POST['price'] !== '' ? sanitize_text_field($_POST['price']) : null;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $args = [
            'service_id' => $service_id,
            'package_type' => $package_type,
            'price' => $price,
            'description' => $description,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        Package::save($args);
        wp_redirect(admin_url('admin.php?page=dsf-packages'));
        exit;
    }

    /**
     * Delete package
     */
    private function delete_package() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            Package::delete($id);
        }
        wp_redirect(admin_url('admin.php?page=dsf-packages'));
        exit;
    }

    /**
     * Package Types page
     */
    public function page_package_types() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_package_type_form($id);
        } elseif ($action === 'add') {
            $this->show_package_type_form();
        } else {
            $this->show_package_types_list();
        }
    }

    /**
     * Show package types list
     */
    private function show_package_types_list() {
        $package_types = PackageType::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Package Types', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-package-types&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Manage unique package types (Standard, Premium, Enterprise, etc.) that are shared across services. Services can have different prices for the same package type.', 'dynamic-services-form'); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Package Type', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Used By Services', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($package_types)) : ?>
                        <tr>
                            <td colspan="4"><?php esc_html_e('No package types found.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($package_types as $package_type) : ?>
                            <?php
                            // Count how many services use this package type
                            $service_count = count(ServicePackagePricing::get_by_package_type($package_type['id']));
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($package_type['package_type_name']); ?></strong></td>
                                <td><?php echo intval($service_count) . ' ' . _n('service', 'services', $service_count, 'dynamic-services-form'); ?></td>
                                <td><?php echo $package_type['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-package-types&action=edit&id=' . $package_type['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_package_type">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($package_type['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure? This package type will be removed from all services.', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show package type form
     *
     * @param int $id PackageType ID (optional)
     */
    private function show_package_type_form($id = 0) {
        $package_type_data = [
            'id' => 0,
            'package_type_name' => '',
            'description' => '',
            'enabled' => 1,
        ];
        
        if ($id) {
            $package_type = new PackageType($id);
            if ($package_type->get_id()) {
                $package_type_data = $package_type->get_data();
            }
        }
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Package Type', 'dynamic-services-form') : esc_html__('Add New Package Type', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_package_type">
                <input type="hidden" name="id" value="<?php echo esc_attr($package_type_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="package_type_name"><?php esc_html_e('Package Type Name', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="package_type_name" name="package_type_name" value="<?php echo esc_attr($package_type_data['package_type_name']); ?>" required>
                            <p class="description"><?php esc_html_e('e.g., Standard, Premium, Enterprise, Basic, Advanced', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php esc_html_e('Description', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <textarea id="description" name="description" rows="4"><?php echo esc_textarea($package_type_data['description']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($package_type_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Package Type', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-package-types')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Save package type
     */
    private function save_package_type() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $package_type_name = isset($_POST['package_type_name']) ? sanitize_text_field($_POST['package_type_name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $args = [
            'package_type_name' => $package_type_name,
            'description' => $description,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        PackageType::save($args);
        wp_redirect(admin_url('admin.php?page=dsf-package-types'));
        exit;
    }

    /**
     * Delete package type
     */
    private function delete_package_type() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            PackageType::delete($id);
            // Also delete all service-package pricing for this package type
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'dsf_service_package_pricing', ['package_type_id' => $id], ['%d']);
        }
        wp_redirect(admin_url('admin.php?page=dsf-package-types'));
        exit;
    }

    /**
     * Service-Package Pricing page
     */
    public function page_package_pricing() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_package_pricing_form($id);
        } elseif ($action === 'add') {
            $this->show_package_pricing_form();
        } else {
            $this->show_package_pricing_list();
        }
    }

    /**
     * Show service-package pricing list
     */
    private function show_package_pricing_list() {
        $pricings = ServicePackagePricing::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Package Pricing', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-package-pricing&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Set pricing for each service-package combination. Multiple services can have different prices for the same package type.', 'dynamic-services-form'); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Service', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Package Type', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pricings)) : ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No pricing found. Create a service, package type, and then add pricing.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($pricings as $pricing) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($pricing['service_name']); ?></strong></td>
                                <td><?php echo esc_html($pricing['package_type_name']); ?></td>
                                <td><?php echo !empty($pricing['price']) ? '$' . number_format((float) $pricing['price'], 2) : '--'; ?></td>
                                <td><?php echo $pricing['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-package-pricing&action=edit&id=' . $pricing['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_package_pricing">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($pricing['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show service-package pricing form
     *
     * @param int $id Pricing ID (optional)
     */
    private function show_package_pricing_form($id = 0) {
        $pricing_data = [
            'id' => 0,
            'service_id' => 0,
            'package_type_id' => 0,
            'price' => '',
            'description' => '',
            'enabled' => 1,
        ];
        
        if ($id) {
            $pricing = new ServicePackagePricing($id);
            if ($pricing->get_id()) {
                $pricing_data = $pricing->get_data();
            }
        }
        
        $services = Service::get_all();
        $package_types = PackageType::get_all();
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Package Pricing', 'dynamic-services-form') : esc_html__('Add New Package Pricing', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_package_pricing">
                <input type="hidden" name="id" value="<?php echo esc_attr($pricing_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="service_id"><?php esc_html_e('Service', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="service_id" name="service_id" required>
                                <option value="">-- <?php esc_html_e('Select Service', 'dynamic-services-form'); ?> --</option>
                                <?php foreach ($services as $service) : ?>
                                    <option value="<?php echo esc_attr($service['id']); ?>" <?php selected($pricing_data['service_id'], $service['id']); ?>>
                                        <?php echo esc_html($service['type'] . ' > ' . $service['category'] . ' > ' . $service['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="package_type_id"><?php esc_html_e('Package Type', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="package_type_id" name="package_type_id" required>
                                <option value="">-- <?php esc_html_e('Select Package Type', 'dynamic-services-form'); ?> --</option>
                                <?php foreach ($package_types as $package_type) : ?>
                                    <option value="<?php echo esc_attr($package_type['id']); ?>" <?php selected($pricing_data['package_type_id'], $package_type['id']); ?>>
                                        <?php echo esc_html($package_type['package_type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price"><?php esc_html_e('Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo esc_attr($pricing_data['price']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php esc_html_e('Description', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <textarea id="description" name="description" rows="4"><?php echo esc_textarea($pricing_data['description']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($pricing_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Pricing', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-package-pricing')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Save service-package pricing
     */
    private function save_package_pricing() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $package_type_id = isset($_POST['package_type_id']) ? intval($_POST['package_type_id']) : 0;
        $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $args = [
            'service_id' => $service_id,
            'package_type_id' => $package_type_id,
            'price' => $price,
            'description' => $description,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        ServicePackagePricing::save($args);
        wp_redirect(admin_url('admin.php?page=dsf-package-pricing'));
        exit;
    }

    /**
     * Delete service-package pricing
     */
    private function delete_package_pricing() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            ServicePackagePricing::delete($id);
        }
        wp_redirect(admin_url('admin.php?page=dsf-package-pricing'));
        exit;
    }

    /**
     * Locations page
     */
    public function page_locations() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_location_form($id);
        } elseif ($action === 'add') {
            $this->show_location_form();
        } else {
            $this->show_locations_list();
        }
    }

    /**
     * Show locations list
     */
    private function show_locations_list() {
        $locations = Location::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Locations (States/Regions)', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-locations&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Manage unique locations that are shared across services. Services link to these locations with their specific pricing.', 'dynamic-services-form'); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Code', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Type', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Used By Services', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($locations)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No locations found.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($locations as $location) : ?>
                            <?php
                            // Count how many services use this location
                            $service_count = count(ServiceLocationPricing::get_by_location($location['id']));
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($location['location_name']); ?></strong></td>
                                <td><?php echo !empty($location['location_code']) ? esc_html($location['location_code']) : '--'; ?></td>
                                <td><?php echo esc_html(ucfirst($location['location_type'])); ?></td>
                                <td><?php echo intval($service_count) . ' ' . _n('service', 'services', $service_count, 'dynamic-services-form'); ?></td>
                                <td><?php echo $location['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-locations&action=edit&id=' . $location['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_location">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($location['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure? This location will be removed from all services.', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show location form
     *
     * @param int $id Location ID (optional)
     */
    private function show_location_form($id = 0) {
        $location_data = [
            'id' => 0,
            'location_name' => '',
            'location_code' => '',
            'location_type' => 'state',
            'enabled' => 1,
        ];
        
        if ($id) {
            $location = new Location($id);
            if ($location->get_id()) {
                $location_data = $location->get_data();
            }
        }
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Location', 'dynamic-services-form') : esc_html__('Add New Location', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_location">
                <input type="hidden" name="id" value="<?php echo esc_attr($location_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="location_name"><?php esc_html_e('Location Name', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="location_name" name="location_name" value="<?php echo esc_attr($location_data['location_name']); ?>" required>
                            <p class="description"><?php esc_html_e('e.g., California, Texas, New York', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location_code"><?php esc_html_e('Location Code', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="location_code" name="location_code" maxlength="10" value="<?php echo esc_attr($location_data['location_code']); ?>">
                            <p class="description"><?php esc_html_e('e.g., CA, TX, NY (optional)', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location_type"><?php esc_html_e('Location Type', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="location_type" name="location_type" required>
                                <option value="state" <?php selected($location_data['location_type'], 'state'); ?>>
                                    <?php esc_html_e('State', 'dynamic-services-form'); ?>
                                </option>
                                <option value="region" <?php selected($location_data['location_type'], 'region'); ?>>
                                    <?php esc_html_e('Region', 'dynamic-services-form'); ?>
                                </option>
                                <option value="country" <?php selected($location_data['location_type'], 'country'); ?>>
                                    <?php esc_html_e('Country', 'dynamic-services-form'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($location_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Location', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-locations')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Save location
     */
    private function save_location() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $location_name = isset($_POST['location_name']) ? sanitize_text_field($_POST['location_name']) : '';
        $location_code = isset($_POST['location_code']) ? sanitize_text_field($_POST['location_code']) : '';
        $location_type = isset($_POST['location_type']) ? sanitize_text_field($_POST['location_type']) : 'state';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $args = [
            'location_name' => $location_name,
            'location_code' => $location_code,
            'location_type' => $location_type,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        Location::save($args);
        wp_redirect(admin_url('admin.php?page=dsf-locations'));
        exit;
    }

    /**
     * Delete location
     */
    private function delete_location() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            Location::delete($id);
            // Also delete all service-location pricing for this location
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'dsf_service_location_pricing', ['location_id' => $id], ['%d']);
        }
        wp_redirect(admin_url('admin.php?page=dsf-locations'));
        exit;
    }

    /**
     * Service-Location Pricing page
     */
    public function page_location_pricing() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_location_pricing_form($id);
        } elseif ($action === 'add') {
            $this->show_location_pricing_form();
        } else {
            $this->show_location_pricing_list();
        }
    }

    /**
     * Show service-location pricing list
     */
    private function show_location_pricing_list() {
        $pricings = ServiceLocationPricing::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Location Pricing', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-pricing&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Set pricing for each service in each location. Multiple services can have different prices for the same location. Use Universal Price for services without package tiers.', 'dynamic-services-form'); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Service', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Location', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Price Type', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Standard Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Premium Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pricings)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No pricing found. Create a service, location, and then add pricing.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($pricings as $pricing) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($pricing['service_name']); ?></strong></td>
                                <td><?php echo esc_html($pricing['location_name']); ?></td>
                                <td>
                                    <?php if ($pricing['is_universal']) : ?>
                                        <span style="background-color: #e7f3ff; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                            <?php esc_html_e('Universal', 'dynamic-services-form'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span style="background-color: #fff8e5; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                            <?php esc_html_e('Tiered', 'dynamic-services-form'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($pricing['standard_price']) ? '$' . number_format((float) $pricing['standard_price'], 2) : '--'; ?></td>
                                <td><?php echo !empty($pricing['premium_price']) ? '$' . number_format((float) $pricing['premium_price'], 2) : '--'; ?></td>
                                <td><?php echo $pricing['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-pricing&action=edit&id=' . $pricing['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_pricing">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($pricing['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show service-location pricing form
     *
     * @param int $id Pricing ID (optional)
     */
    private function show_location_pricing_form($id = 0) {
        $pricing_data = [
            'id' => 0,
            'service_id' => 0,
            'location_id' => 0,
            'standard_price' => '',
            'premium_price' => '',
            'is_universal' => 0,
            'enabled' => 1,
        ];
        
        if ($id) {
            $pricing = new ServiceLocationPricing($id);
            if ($pricing->get_id()) {
                $pricing_data = $pricing->get_data();
            }
        }
        
        $services = Service::get_all();
        $locations = Location::get_all();
        $is_universal = intval($pricing_data['is_universal']);
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Location Pricing', 'dynamic-services-form') : esc_html__('Add New Location Pricing', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_pricing">
                <input type="hidden" name="id" value="<?php echo esc_attr($pricing_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="service_id"><?php esc_html_e('Service', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="service_id" name="service_id" required>
                                <option value="">-- <?php esc_html_e('Select Service', 'dynamic-services-form'); ?> --</option>
                                <?php foreach ($services as $service) : ?>
                                    <option value="<?php echo esc_attr($service['id']); ?>" <?php selected($pricing_data['service_id'], $service['id']); ?>>
                                        <?php echo esc_html($service['type'] . ' > ' . $service['category'] . ' > ' . $service['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location_id"><?php esc_html_e('Location', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="location_id" name="location_id" required>
                                <option value="">-- <?php esc_html_e('Select Location', 'dynamic-services-form'); ?> --</option>
                                <?php foreach ($locations as $location) : ?>
                                    <option value="<?php echo esc_attr($location['id']); ?>" <?php selected($pricing_data['location_id'], $location['id']); ?>>
                                        <?php echo esc_html($location['location_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="is_universal"><?php esc_html_e('Use Universal Price?', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="is_universal" name="is_universal" value="1" <?php checked($is_universal); ?>>
                            <p class="description"><?php esc_html_e('Check this for services without packages. Unchecked for services with standard and premium pricing tiers.', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr id="universal-price-row" style="display: <?php echo $is_universal ? 'table-row' : 'none'; ?>;">
                        <th scope="row"><label for="universal_price"><?php esc_html_e('Universal Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="universal_price" name="standard_price" step="0.01" value="<?php echo esc_attr($pricing_data['standard_price']); ?>">
                            <p class="description"><?php esc_html_e('Single price for this location across all service levels', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr id="tiered-prices-row" style="display: <?php echo !$is_universal ? 'table-row' : 'none'; ?>;">
                        <th scope="row"><label for="standard_price"><?php esc_html_e('Standard Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="standard_price" name="standard_price_tiered" step="0.01" value="<?php echo !$is_universal ? esc_attr($pricing_data['standard_price']) : ''; ?>">
                        </td>
                    </tr>
                    <tr id="premium-price-row" style="display: <?php echo !$is_universal ? 'table-row' : 'none'; ?>;">
                        <th scope="row"><label for="premium_price"><?php esc_html_e('Premium Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="premium_price" name="premium_price" step="0.01" value="<?php echo !$is_universal ? esc_attr($pricing_data['premium_price']) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($pricing_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Pricing', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-pricing')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const universalCheckbox = document.getElementById('is_universal');
                const universalPriceRow = document.getElementById('universal-price-row');
                const tieredPricesRow = document.getElementById('tiered-prices-row');
                const premiumPriceRow = document.getElementById('premium-price-row');
                const standardPriceUniversal = document.getElementById('universal_price');
                const standardPriceTiered = document.getElementById('standard_price_tiered');
                const premiumPrice = document.getElementById('premium_price');
                
                function togglePriceFields() {
                    if (universalCheckbox.checked) {
                        universalPriceRow.style.display = 'table-row';
                        tieredPricesRow.style.display = 'none';
                        premiumPriceRow.style.display = 'none';
                        standardPriceUniversal.required = true;
                        standardPriceTiered.required = false;
                        premiumPrice.required = false;
                    } else {
                        universalPriceRow.style.display = 'none';
                        tieredPricesRow.style.display = 'table-row';
                        premiumPriceRow.style.display = 'table-row';
                        standardPriceUniversal.required = false;
                        standardPriceTiered.required = true;
                        premiumPrice.required = true;
                    }
                }
                
                universalCheckbox.addEventListener('change', togglePriceFields);
            });
        </script>
        <?php
    }

    /**
     * Save service-location pricing
     */
    private function save_location_pricing() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
        $is_universal = isset($_POST['is_universal']) ? 1 : 0;
        
        // Handle price fields based on universal price flag
        if ($is_universal) {
            // Universal price: only standard_price is used
            $standard_price = isset($_POST['standard_price']) && $_POST['standard_price'] !== '' ? floatval($_POST['standard_price']) : null;
            $premium_price = null;
        } else {
            // Tiered pricing: both standard and premium prices
            $standard_price = isset($_POST['standard_price_tiered']) && $_POST['standard_price_tiered'] !== '' ? floatval($_POST['standard_price_tiered']) : null;
            $premium_price = isset($_POST['premium_price']) && $_POST['premium_price'] !== '' ? floatval($_POST['premium_price']) : null;
        }
        
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $args = [
            'service_id' => $service_id,
            'location_id' => $location_id,
            'standard_price' => $standard_price,
            'premium_price' => $premium_price,
            'is_universal' => $is_universal,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        ServiceLocationPricing::save($args);
        wp_redirect(admin_url('admin.php?page=dsf-pricing'));
        exit;
    }

    /**
     * Delete service-location pricing
     */
    private function delete_location_pricing() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            ServiceLocationPricing::delete($id);
        }
        wp_redirect(admin_url('admin.php?page=dsf-pricing'));
        exit;
    }

    /**
     * Portals page
     */
    public function page_portals() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $id) {
            $this->show_portal_form($id);
        } elseif ($action === 'add') {
            $this->show_portal_form();
        } else {
            $this->show_portals_list();
        }
    }

    /**
     * Show portals list
     */
    private function show_portals_list() {
        $portals = Portal::get_all();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Portals', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-portals&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Service', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Portal Name', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($portals)) : ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No portals found.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($portals as $portal) : ?>
                            <?php $service = new Service($portal['service_id']); ?>
                            <tr>
                                <td><?php echo esc_html($service->get('name')); ?></td>
                                <td><?php echo esc_html($portal['portal_name']); ?></td>
                                <td><?php echo !empty($portal['price']) ? '$' . number_format((float) $portal['price'], 2) : '--'; ?></td>
                                <td><?php echo $portal['enabled'] ? esc_html__('Enabled', 'dynamic-services-form') : esc_html__('Disabled', 'dynamic-services-form'); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-portals&action=edit&id=' . $portal['id'])); ?>">
                                        <?php esc_html_e('Edit', 'dynamic-services-form'); ?>
                                    </a> |
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_portal">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($portal['id']); ?>">
                                        <button type="submit" class="delete-link" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'dynamic-services-form'); ?>');">
                                            <?php esc_html_e('Delete', 'dynamic-services-form'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Show portal form
     *
     * @param int $id Portal ID (optional)
     */
    private function show_portal_form($id = 0) {
        $portal_data = [
            'id' => 0,
            'service_id' => 0,
            'portal_name' => '',
            'price' => '',
            'enabled' => 1,
        ];
        
        if ($id) {
            $portal = new Portal($id);
            if ($portal->get_id()) {
                $portal_data = $portal->get_data();
            }
        }
        
        $services = Service::get_all();
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Edit Portal', 'dynamic-services-form') : esc_html__('Add New Portal', 'dynamic-services-form'); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                <input type="hidden" name="dsf_action" value="save_portal">
                <input type="hidden" name="id" value="<?php echo esc_attr($portal_data['id']); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="service_id"><?php esc_html_e('Service', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <select id="service_id" name="service_id" required>
                                <option value="">-- <?php esc_html_e('Select Service', 'dynamic-services-form'); ?> --</option>
                                <?php foreach ($services as $service) : ?>
                                    <option value="<?php echo esc_attr($service['id']); ?>" <?php selected($portal_data['service_id'], $service['id']); ?>>
                                        <?php echo esc_html($service['type'] . ' - ' . $service['category'] . ' - ' . $service['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="portal_name"><?php esc_html_e('Portal Name', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="text" id="portal_name" name="portal_name" value="<?php echo esc_attr($portal_data['portal_name']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price"><?php esc_html_e('Price', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo esc_attr($portal_data['price']); ?>">
                            <p class="description"><?php esc_html_e('Leave empty for free or enter price', 'dynamic-services-form'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enabled"><?php esc_html_e('Enabled', 'dynamic-services-form'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($portal_data['enabled']); ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Portal', 'dynamic-services-form'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-portals')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'dynamic-services-form'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Save portal
     */
    private function save_portal() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $portal_name = isset($_POST['portal_name']) ? sanitize_text_field($_POST['portal_name']) : '';
        $price = isset($_POST['price']) && $_POST['price'] !== '' ? sanitize_text_field($_POST['price']) : null;
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $args = [
            'service_id' => $service_id,
            'portal_name' => $portal_name,
            'price' => $price,
            'enabled' => $enabled,
        ];
        
        if ($id) {
            $args['id'] = $id;
        }
        
        Portal::save($args);
        wp_redirect(admin_url('admin.php?page=dsf-portals'));
        exit;
    }

    /**
     * Delete portal
     */
    private function delete_portal() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            Portal::delete($id);
        }
        wp_redirect(admin_url('admin.php?page=dsf-portals'));
        exit;
    }

    /**
     * Submissions page
     */
    public function page_submissions() {
        global $wpdb;
        
        $submissions = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}dsf_submissions ORDER BY created_at DESC",
            ARRAY_A
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Form Submissions', 'dynamic-services-form'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Business Name', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Email', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Service', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Total Price', 'dynamic-services-form'); ?></th>
                        <th><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No submissions found.', 'dynamic-services-form'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($submissions as $submission) : ?>
                            <?php $service = new Service($submission['service_id']); ?>
                            <tr>
                                <td><?php echo esc_html(date_format(date_create($submission['created_at']), 'M d, Y H:i')); ?></td>
                                <td><?php echo esc_html($submission['business_name']); ?></td>
                                <td><?php echo esc_html($submission['email']); ?></td>
                                <td><?php echo esc_html($service->get('name')); ?></td>
                                <td><?php echo !empty($submission['total_price']) ? '$' . number_format((float) $submission['total_price'], 2) : '--'; ?></td>
                                <td><?php echo esc_html($submission['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
