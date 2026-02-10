<?php
// includes/Admin_Pages/ServicesMenu.php
namespace DSF\Admin_Pages;
use DSF\Service;
trait ServicesMenu {
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
        $all_services = Service::get_all();
        $params = $this->get_pagination_params();
        
        // Filter by search
        $services = $this->filter_by_search(
            $all_services,
            $params['search'],
            ['type', 'category', 'name', 'pricing_model']
        );
        
        // Paginate
        $pagination = $this->paginate_array($services, $params['page'], $params['per_page']);
        $services = $pagination['items'];
        
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Services', 'dynamic-services-form'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dsf-services&action=add')); ?>" class="page-title-action">
                    <?php esc_html_e('Add New', 'dynamic-services-form'); ?>
                </a>
            </h1>
            
            <?php $this->render_search_bar($params['search']); ?>
            
            <p style="color: #666; margin-bottom: 15px;">
                <?php printf(esc_html__('Total: %d service(s)', 'dynamic-services-form'), $pagination['total']); ?>
            </p>
            
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
                            <td colspan="8"><?php esc_html_e('No services found.', 'dynamic-services-form'); ?></td>
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
            
            <?php $this->render_pagination_controls($pagination['current_page'], $pagination['pages'], $params['search']); ?>
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
}