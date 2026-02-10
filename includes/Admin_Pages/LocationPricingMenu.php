<?php
namespace DSF\Admin_Pages;
use DSF\Service;
use DSF\Location;
use DSF\ServiceLocationPricing;
trait LocationPricingMenu {

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
        $all_pricings = ServiceLocationPricing::get_all();
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
                <?php esc_html_e('Location Pricing', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-pricing&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Set pricing for each service in each location. Multiple services can have different prices for the same location. Use Universal Price for services without package tiers.', 'dynamic-services-form'); ?>
            </p>
            
            <?php $this->render_search_bar($params['search']); ?>
            
            <p style="color: #666; margin-bottom: 15px;">
                <?php printf(esc_html__('Total: %d location pricing(s)', 'dynamic-services-form'), $pagination['total']); ?>
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
            
            <?php $this->render_pagination_controls($pagination['current_page'], $pagination['pages'], $params['search']); ?>
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
}