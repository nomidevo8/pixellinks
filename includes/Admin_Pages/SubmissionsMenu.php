<?php 
namespace DSF\Admin_Pages;
use DSF\Service;
use DSF\Database;
trait SubmissionsMenu {


    /**
     * Submissions page
     */
    public function page_submissions() {
        // Handle delete action
        if (!empty($_POST['dsf_delete_submission'])) {
            $this->delete_submission();
            return;
        }

        // Get pagination parameters
        $params = $this->get_pagination_params();
        
        // Get all submissions
        global $wpdb;
        $all_submissions = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}dsf_submissions ORDER BY created_at DESC",
            ARRAY_A
        );

        // Convert to filterable format
        $submissions_with_details = array_map(function($submission) {
            $service = new Service($submission['service_id']);
            return array_merge($submission, [
                'service_name' => $service->get('name'),
                'search_text' => strtolower(
                    $submission['business_name'] . ' ' . 
                    $submission['email'] . ' ' . 
                    $submission['first_name'] . ' ' . 
                    $submission['last_name'] . ' ' .
                    $service->get('name')
                )
            ]);
        }, $all_submissions);

        // Filter by search
        $search = $params['search'];
        if (!empty($search)) {
            $search_lower = strtolower($search);
            $submissions_with_details = array_filter($submissions_with_details, function($sub) use ($search_lower) {
                return strpos($sub['search_text'], $search_lower) !== false;
            });
        }

        // Paginate
        $pagination = $this->paginate_array($submissions_with_details, $params['page'], $params['per_page']);
        $submissions = $pagination['items'];
        $total_pages = $pagination['pages'];
        $total_submissions = $pagination['total'];
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Form Submissions', 'dynamic-services-form'); ?></h1>
            
            <!-- Search Bar -->
            <?php $this->render_search_bar($search); ?>
            
            <!-- Results Info -->
            <div style="margin-bottom: 20px; padding: 10px; background: #f0f8ff; border-radius: 5px; border-left: 4px solid #3498db;">
                <?php 
                if (!empty($search)) {
                    printf(
                        esc_html__('Found %d submission(s) matching "%s"', 'dynamic-services-form'),
                        $total_submissions,
                        esc_html($search)
                    );
                } else {
                    printf(
                        esc_html__('Total Submissions: %d', 'dynamic-services-form'),
                        $total_submissions
                    );
                }
                ?>
            </div>

            <!-- Submissions Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 15%;"><?php esc_html_e('Date', 'dynamic-services-form'); ?></th>
                        <th style="width: 18%;"><?php esc_html_e('Business Name', 'dynamic-services-form'); ?></th>
                        <th style="width: 18%;"><?php esc_html_e('Email', 'dynamic-services-form'); ?></th>
                        <th style="width: 20%;"><?php esc_html_e('Service', 'dynamic-services-form'); ?></th>
                        <th style="width: 12%;"><?php esc_html_e('Total Price', 'dynamic-services-form'); ?></th>
                        <th style="width: 15%;"><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)) : ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                <p style="color: #999;">
                                    <?php esc_html_e('No submissions found.', 'dynamic-services-form'); ?>
                                </p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($submissions as $submission) : 
                            $service = new Service($submission['service_id']);
                            $form_data = json_decode($submission['form_data'], true);
                        ?>
                            <tr>
                                <td>
                                    <?php echo esc_html(date_format(date_create($submission['created_at']), 'M d, Y H:i')); ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($submission['business_name']); ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr($submission['email']); ?>" style="color: #0073aa;">
                                        <?php echo esc_html($submission['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo esc_html($service->get('name')); ?>
                                </td>
                                <td>
                                    <strong style="color: #27ae60;">
                                        <?php echo !empty($submission['total_price']) ? '$' . number_format((float) $submission['total_price'], 2) : '--'; ?>
                                    </strong>
                                </td>
                                <td>
                                    <button type="button" class="button button-small" onclick="showSubmissionModal(<?php echo intval($submission['id']); ?>)" style="margin-right: 5px;">
                                        👁️ View
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this submission?');">
                                        <?php wp_nonce_field('dsf_admin_nonce', 'dsf_nonce'); ?>
                                        <input type="hidden" name="dsf_action" value="delete_submission">
                                        <input type="hidden" name="id" value="<?php echo intval($submission['id']); ?>">
                                        <button type="submit" name="dsf_delete_submission" class="button button-small button-link-delete">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php $this->render_pagination_controls($params['page'], $total_pages, $search); ?>
        </div>

        <!-- Modal for Submission Details -->
        <div id="dsf-submission-modal" style="display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div style="background-color: white; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">
                    <h2 id="dsf-modal-title" style="margin: 0;">Submission Details</h2>
                    <button type="button" onclick="closeSubmissionModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">✕</button>
                </div>
                <div id="dsf-modal-content">
                    <div style="text-align: center; padding: 40px;">
                        <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                        <p style="margin-top: 15px; color: #666;">Loading submission details...</p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .dsf-submission-section {
                margin-bottom: 25px;
            }
            .dsf-submission-section h3 {
                font-size: 16px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 2px solid #3498db;
            }
            .dsf-info-row {
                display: flex;
                padding: 10px;
                background: #f9f9f9;
                margin-bottom: 10px;
                border-radius: 4px;
            }
            .dsf-info-label {
                font-weight: 600;
                color: #555;
                width: 30%;
                min-width: 150px;
            }
            .dsf-info-value {
                color: #333;
                flex: 1;
            }
            .dsf-price-box {
                background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                margin: 20px 0;
            }
            .dsf-price-label {
                font-size: 12px;
                opacity: 0.9;
                margin-bottom: 5px;
            }
            .dsf-price-amount {
                font-size: 32px;
                font-weight: 700;
            }
        </style>

        <script>
            // Global controller to cancel pending requests
            let dsf_submission_request = null;
            let dsf_current_submission_id = null;

            function showSubmissionModal(submissionId) {
                const modal = document.getElementById('dsf-submission-modal');
                const content = document.getElementById('dsf-modal-content');
                modal.style.display = 'block';

                // Cancel any previous request
                if (dsf_submission_request) {
                    dsf_submission_request.abort();
                }

                // Store the submission ID we're loading
                dsf_current_submission_id = submissionId;

                // Create a new AbortController for this request
                dsf_submission_request = new AbortController();

                // Show loading state
                content.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div><p style="margin-top: 15px; color: #666;">Loading submission details...</p></div>';

                // Fetch submission details via AJAX
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=dsf_get_submission_details&submission_id=' + submissionId + '&nonce=<?php echo wp_create_nonce('dsf_form_nonce'); ?>',
                    signal: dsf_submission_request.signal
                })
                .then(response => response.json())
                .then(data => {
                    // Only update if this is still the submission we're trying to load
                    if (submissionId === dsf_current_submission_id) {
                        if (data.success) {
                            content.innerHTML = data.data.html;
                        } else {
                            content.innerHTML = '<p style="color: red; padding: 20px;">Error loading submission details.</p>';
                        }
                    }
                })
                .catch(error => {
                    // Only show error if this is still the submission we're trying to load
                    if (submissionId === dsf_current_submission_id && error.name !== 'AbortError') {
                        console.error('Error:', error);
                        content.innerHTML = '<p style="color: red; padding: 20px;">Error loading submission details.</p>';
                    }
                });
            }

            function closeSubmissionModal() {
                // Cancel any pending request when closing modal
                if (dsf_submission_request) {
                    dsf_submission_request.abort();
                }
                dsf_current_submission_id = null;
                document.getElementById('dsf-submission-modal').style.display = 'none';
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('dsf-submission-modal');
                if (event.target === modal) {
                    closeSubmissionModal();
                }
            }
        </script>
        <?php
    }

    /**
     * Delete submission
     */
    private function delete_submission() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            global $wpdb;
            $wpdb->delete(
                Database::get_table('submissions'),
                ['id' => $id],
                ['%d']
            );
        }
        wp_redirect(admin_url('admin.php?page=dsf-submissions'));
        exit;
    }
}