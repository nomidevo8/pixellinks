<?php 
namespace DSF\Admin_Pages;

use DSF\Service;
use DSF\PackageType;
use DSF\ServicePackagePricing;

trait PackagePricingMenu {

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
        $all_pricings = ServicePackagePricing::get_all();
        $params = $this->get_pagination_params();
        
        // Filter by search (search across all fields when no specific fields provided)
        $pricings = $this->filter_by_search(
            $all_pricings,
            $params['search']
        );
        
        // Paginate
        $pagination = $this->paginate_array($pricings, $params['page'], $params['per_page']);
        $pricings = $pagination['items'];
        
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
            
            <?php $this->render_search_bar($params['search']); ?>
            
            <p style="color: #666; margin-bottom: 15px;">
                <?php printf(esc_html__('Total: %d pricing(s)', 'dynamic-services-form'), $pagination['total']); ?>
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
            
            <?php $this->render_pagination_controls($pagination['current_page'], $pagination['pages'], $params['search']); ?>
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

}