<?php 
namespace DSF\Admin_Pages;
use DSF\Service;
use DSF\Portal;
trait PortalsMenu {
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
        $all_portals = Portal::get_all();
        $params = $this->get_pagination_params();
        
        // Filter by search (search across all fields when no specific fields provided)
        $portals = $this->filter_by_search(
            $all_portals,
            $params['search']
        );
        
        // Paginate
        $pagination = $this->paginate_array($portals, $params['page'], $params['per_page']);
        $portals = $pagination['items'];
        
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Portals', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-portals&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <?php $this->render_search_bar($params['search']); ?>
            
            <p style="color: #666; margin-bottom: 15px;">
                <?php printf(esc_html__('Total: %d portal(s)', 'dynamic-services-form'), $pagination['total']); ?>
            </p>
            
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
                            <tr>
                                <td><strong><?php echo esc_html($portal['service_name']); ?></strong></td>
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
            
            <?php $this->render_pagination_controls($pagination['current_page'], $pagination['pages'], $params['search']); ?>
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
}