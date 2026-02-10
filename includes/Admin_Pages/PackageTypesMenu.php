<?php
namespace DSF\Admin_Pages;
use DSF\Service;
use DSF\PackageType;
use DSF\ServicePackagePricing;
trait PackageTypesMenu {

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
        $all_package_types = PackageType::get_all();
        $params = $this->get_pagination_params();
        
        // Filter by search
        $package_types = $this->filter_by_search(
            $all_package_types,
            $params['search'],
            ['package_type_name', 'description']
        );
        
        // Paginate
        $pagination = $this->paginate_array($package_types, $params['page'], $params['per_page']);
        $package_types = $pagination['items'];
        
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
            
            <?php $this->render_search_bar($params['search']); ?>
            
            <p style="color: #666; margin-bottom: 15px;">
                <?php printf(esc_html__('Total: %d package type(s)', 'dynamic-services-form'), $pagination['total']); ?>
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
            
            <?php $this->render_pagination_controls($pagination['current_page'], $pagination['pages'], $params['search']); ?>
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
}