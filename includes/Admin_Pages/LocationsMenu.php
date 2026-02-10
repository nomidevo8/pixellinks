<?php
namespace DSF\Admin_Pages;
use DSF\Service;
use DSF\Location;
use DSF\ServiceLocationPricing;
trait LocationsMenu {
      
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
        $all_locations = Location::get_all();
        $params = $this->get_pagination_params();
        
        // Filter by search
        $locations = $this->filter_by_search(
            $all_locations,
            $params['search'],
            ['location_name', 'location_code', 'location_type']
        );
        
        // Paginate
        $pagination = $this->paginate_array($locations, $params['page'], $params['per_page']);
        $locations = $pagination['items'];
        
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
            
            <?php $this->render_search_bar($params['search']); ?>
            
            <p style="color: #666; margin-bottom: 15px;">
                <?php printf(esc_html__('Total: %d location(s)', 'dynamic-services-form'), $pagination['total']); ?>
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
            
            <?php $this->render_pagination_controls($pagination['current_page'], $pagination['pages'], $params['search']); ?>
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

}