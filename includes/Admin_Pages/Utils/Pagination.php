<?php
namespace DSF\Admin_Pages\Utils;

trait Pagination{
       /**
     * Get pagination parameters
     *
     * @return array Array with 'page', 'search', 'per_page'
     */
    private function get_pagination_params() {
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $per_page = 20; // Items per page
        
        return [
            'page' => $page,
            'search' => $search,
            'per_page' => $per_page,
        ];
    }

    /**
     * Filter array by search term
     *
     * @param array $items Items to filter
     * @param string $search Search term
     * @param array $search_fields Fields to search in
     * @return array Filtered items
     */
    private function filter_by_search($items, $search, $search_fields = []) {
        if (empty($search)) {
            return $items;
        }

        if (empty($items) || !is_array($items)) {
            return $items;
        }

        // If no explicit fields provided, search all top-level keys of the first item
        $first = reset($items);
        if (empty($search_fields)) {
            if (is_array($first)) {
                $search_fields = array_keys($first);
            } else {
                $search_fields = [];
            }
        }

        $search_lower = strtolower($search);
        return array_filter($items, function($item) use ($search_lower, $search_fields) {
            foreach ($search_fields as $field) {
                if (!isset($item[$field])) {
                    continue;
                }

                $value = $item[$field];
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }

                if (!is_scalar($value)) {
                    continue;
                }

                if (stripos(strtolower((string) $value), $search_lower) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Paginate array
     *
     * @param array $items Items to paginate
     * @param int $page Current page
     * @param int $per_page Items per page
     * @return array ['items' => paginated items, 'total' => total count, 'pages' => total pages]
     */
    private function paginate_array($items, $page = 1, $per_page = 20) {
        $total = count($items);
        $pages = ceil($total / $per_page);
        $page = max(1, min($page, $pages)); // Ensure page is within bounds
        
        $offset = ($page - 1) * $per_page;
        $paginated = array_slice($items, $offset, $per_page);
        
        return [
            'items' => $paginated,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'per_page' => $per_page,
        ];
    }

    /**
     * Render search bar
     *
     * @param string $current_search Current search term
     */
    private function render_search_bar($current_search = '') {
        ?>
        <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
            <form method="get" style="display: flex; gap: 10px; align-items: center; flex: 1;">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? ''); ?>">
                <input type="text" name="s" value="<?php echo esc_attr($current_search); ?>" 
                       placeholder="<?php esc_attr_e('Search...', 'dynamic-services-form'); ?>" 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 3px; flex: 1;">
                <button type="submit" class="button"><?php esc_html_e('Search', 'dynamic-services-form'); ?></button>
                <?php if (!empty($current_search)) : ?>
                    <a href="<?php echo esc_url( remove_query_arg('s', add_query_arg('page', $_GET['page'] ?? '')) ); ?>" class="button">
                        <?php esc_html_e('Clear', 'dynamic-services-form'); ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render pagination controls
     *
     * @param int $current_page Current page
     * @param int $total_pages Total pages
     * @param string $search Current search term
     */
    private function render_pagination_controls($current_page, $total_pages, $search = '') {
        if ($total_pages <= 1) {
            return;
        }

        $page_param = $_GET['page'] ?? '';
        $query_args = ['page' => $page_param];
        if (!empty($search)) {
            $query_args['s'] = $search;
        }

        ?>
        <div style="margin-top: 20px; display: flex; gap: 10px; align-items: center; justify-content: center;">
            <?php if ($current_page > 1) : ?>
                <a href="<?php echo esc_url(add_query_arg(array_merge($query_args, ['paged' => 1]))); ?>" class="button">
                    « <?php esc_html_e('First', 'dynamic-services-form'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(array_merge($query_args, ['paged' => $current_page - 1]))); ?>" class="button">
                    ‹ <?php esc_html_e('Previous', 'dynamic-services-form'); ?>
                </a>
            <?php endif; ?>

            <span style="padding: 8px 12px; background: #f1f1f1; border-radius: 3px;">
                <?php printf(esc_html__('Page %d of %d', 'dynamic-services-form'), $current_page, $total_pages); ?>
            </span>

            <?php if ($current_page < $total_pages) : ?>
                <a href="<?php echo esc_url(add_query_arg(array_merge($query_args, ['paged' => $current_page + 1]))); ?>" class="button">
                    <?php esc_html_e('Next', 'dynamic-services-form'); ?> ›
                </a>
                <a href="<?php echo esc_url(add_query_arg(array_merge($query_args, ['paged' => $total_pages]))); ?>" class="button">
                    <?php esc_html_e('Last', 'dynamic-services-form'); ?> »
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}