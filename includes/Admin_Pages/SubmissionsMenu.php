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
                        <th style="width: 10%;"><?php esc_html_e('Total Price', 'dynamic-services-form'); ?></th>
                        <th style="width: 10%;"><?php esc_html_e('Total Paid', 'dynamic-services-form'); ?></th>
                        <th style="width: 10%;"><?php esc_html_e('Discount', 'dynamic-services-form'); ?></th>
                        <th style="width: 12%;"><?php esc_html_e('Status', 'dynamic-services-form'); ?></th>
                        <th style="width: 15%;"><?php esc_html_e('Actions', 'dynamic-services-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)) : ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">
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
                                        <?php echo isset($submission['total_price']) && $submission['total_price'] !== null ? '$' . number_format((float) $submission['total_price'], 2) : '--'; ?>
                                    </strong>
                                </td>
                                <td>
                                    <strong style="color: #27ae60;">
                                        <?php echo isset($submission['total_paid']) && $submission['total_paid'] !== null ? '$' . number_format((float) $submission['total_paid'], 2) : '--'; ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php echo (isset($submission['discount_percentage']) && $submission['discount_percentage'] !== null && $submission['discount_percentage'] !== '') ? number_format((float)$submission['discount_percentage'], 2) . '%' : '--'; ?>
                                </td>
                                <td>
                                    <select onchange="updateSubmissionStatus(<?php echo intval($submission['id']); ?>, 'submissions', this)" style="width: 100%;">
                                        <?php $statuses = ['pending','success','failed','canceled']; foreach($statuses as $st): ?>
                                            <option value="<?php echo esc_attr($st); ?>" <?php selected($submission['status'], $st); ?>><?php echo esc_html(ucfirst($st)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
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

        <!-- Toast Notification -->
        <div id="dsf-toast" style="display:none; position: fixed; right: 20px; bottom: 20px; z-index: 2000; padding: 10px 14px; border-radius: 6px; color: #fff; font-weight: 600;"></div>

        <!-- Modal for Sending Email -->
        <div id="dsf-send-email-modal" style="display: none; position: fixed; z-index: 101; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div style="background-color: white; margin: 15% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">
                    <h2 style="margin: 0;">Send Email</h2>
                    <button type="button" onclick="closeSendEmailModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">✕</button>
                </div>
                <form id="dsf-send-email-form" method="POST">
                    <div style="margin-bottom: 20px;">
                        <label for="dsf-email-template" style="display: block; margin-bottom: 8px; font-weight: 600;">Select Email Template:</label>
                        <select id="dsf-email-template" name="template_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">-- Choose a template --</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label for="dsf-email-files" style="display: block; margin-bottom: 8px; font-weight: 600;">Attach Files (Optional):</label>
                        <input type="file" id="dsf-email-files" name="attachments[]" multiple style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <small style="display: block; margin-top: 5px; color: #666;">You can attach PDF, images, or other files</small>
                    </div>
                    <div style="background: #f0f8ff; padding: 12px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #3498db;">
                        <strong>To:</strong> <span id="dsf-email-recipient"></span>
                    </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="dsf-send-email-submit" class="button button-primary" style="flex: 1; padding: 10px;" disabled>Send Email</button>
                        <button type="button" onclick="closeSendEmailModal()" class="button" style="flex: 1; padding: 10px;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal for Submission Details -->
        <div id="dsf-submission-modal" style="display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div style="background-color: white; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">
                    <h2 id="dsf-modal-title" style="margin: 0;">Submission Details</h2>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" id="dsf-send-email-btn" class="button button-primary" style="padding: 5px 15px; font-size: 14px;" onclick="showSendEmailModal(dsf_current_submission_id)">📧 Send Email</button>
                        <button type="button" onclick="closeSubmissionModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">✕</button>
                    </div>
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
                // disable send email button while details load
                const sendBtn = document.getElementById('dsf-send-email-btn');
                if (sendBtn) sendBtn.disabled = true;
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
                            // enable send button after details are loaded
                            if (sendBtn) sendBtn.removeAttribute('disabled');
                        } else {
                            content.innerHTML = '<p style="color: red; padding: 20px;">Error loading submission details.</p>';
                            if (sendBtn) sendBtn.disabled = true;
                        }
                    }
                })
                .catch(error => {
                    // Only show error if this is still the submission we're trying to load
                    if (submissionId === dsf_current_submission_id && error.name !== 'AbortError') {
                        content.innerHTML = '<p style="color: red; padding: 20px;">Error loading submission details.</p>';
                        if (sendBtn) sendBtn.disabled = true;
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
                const emailModal = document.getElementById('dsf-send-email-modal');
                if (event.target === emailModal) {
                    closeSendEmailModal();
                }
            }

            // Send Email Modal Functions
            let dsf_current_submission_email = null;
            let dsf_email_templates = [];
            let dsf_templates_loaded = false;
            const dsf_form_nonce = '<?php echo wp_create_nonce('dsf_form_nonce'); ?>';
            const dsf_admin_nonce = '<?php echo wp_create_nonce('dsf_admin_nonce'); ?>';

            // Preload templates on page load so modal is instant
            (function(){
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=dsf_get_email_templates&nonce=' + dsf_form_nonce
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        dsf_email_templates = data.data.templates || [];
                        dsf_templates_loaded = true;
                        // populate any template selects on page
                        ['dsf-email-template','dsf-wpforms-email-template'].forEach(id => {
                            const sel = document.getElementById(id);
                            if (sel) {
                                // clear existing options except placeholder
                                while (sel.options.length > 1) sel.remove(1);
                                dsf_email_templates.forEach(t => {
                                    const option = document.createElement('option');
                                    option.value = t.id;
                                    option.textContent = t.template_name + ' - ' + t.template_subject;
                                    sel.appendChild(option);
                                });
                            }
                        });
                        // enable send buttons if present
                        document.getElementById('dsf-send-email-submit')?.removeAttribute('disabled');
                        document.getElementById('dsf-wpforms-send-email-submit')?.removeAttribute('disabled');
                    }
                })
                .catch(()=>{});
            })();

            function showSendEmailModal(submissionId) {
                const modal = document.getElementById('dsf-send-email-modal');
                const emailSpan = document.getElementById('dsf-email-recipient');
                const templateSelect = document.getElementById('dsf-email-template');
                
                // Get email from current modal data if available
                const emailElements = document.querySelectorAll('.dsf-info-value a[href^="mailto:"]');
                if (emailElements.length > 0) {
                    const email = emailElements[0].textContent;
                    emailSpan.textContent = email;
                    dsf_current_submission_email = email;
                }
                
                // Load email templates
                if (templateSelect.options.length <= 1) {
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=dsf_get_email_templates&nonce=<?php echo wp_create_nonce('dsf_form_nonce'); ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            data.data.templates.forEach(template => {
                                const option = document.createElement('option');
                                option.value = template.id;
                                option.textContent = template.template_name + ' - ' + template.template_subject;
                                templateSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading templates:', error));
                }
                
                modal.style.display = 'block';
            }

            function closeSendEmailModal() {
                document.getElementById('dsf-send-email-modal').style.display = 'none';
                document.getElementById('dsf-send-email-form').reset();
            }

            // Handle form submission
            document.getElementById('dsf-send-email-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const templateId = document.getElementById('dsf-email-template').value;
                if (!templateId) {
                    showToast('Please select an email template', 'warning');
                    return;
                }
                
                const submitBtn = document.getElementById('dsf-send-email-submit');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                
                const formData = new FormData(this);
                formData.append('action', 'dsf_send_email_to_user');
                formData.append('nonce', '<?php echo wp_create_nonce('dsf_form_nonce'); ?>');
                formData.append('submission_id', dsf_current_submission_id);
                formData.append('email', dsf_current_submission_email);
                formData.append('template_id', templateId);
                formData.append('table', 'submissions');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '📧 Send Email';
                    
                    if (data.success) {
                        showToast('Email sent successfully', 'success');
                        closeSendEmailModal();
                    } else {
                        showToast('Error: ' + (data.data?.message || 'Failed to send email'), 'error');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '📧 Send Email';
                    showToast('Error sending email: ' + error.message, 'error');
                    console.error('Error:', error);
                });
            });

            // Toast helper
            function showToast(message, type) {
                const toast = document.getElementById('dsf-toast');
                if (!toast) return;
                toast.style.display = 'block';
                toast.textContent = message;
                toast.style.background = type === 'success' ? '#27ae60' : (type === 'warning' ? '#f39c12' : '#e74c3c');
                setTimeout(() => { toast.style.display = 'none'; }, 3500);
            }

            // Update submission status via AJAX
            function updateSubmissionStatus(submissionId, table, selectEl) {
                const newStatus = selectEl.value;
                selectEl.disabled = true;
                const body = 'action=dsf_update_submission_status&nonce=' + encodeURIComponent(dsf_admin_nonce) + '&submission_id=' + encodeURIComponent(submissionId) + '&table=' + encodeURIComponent(table) + '&status=' + encodeURIComponent(newStatus);
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: body
                })
                .then(r => r.json())
                .then(data => {
                    selectEl.disabled = false;
                    if (data.success) {
                        showToast('Status updated', 'success');
                    } else {
                        showToast('Failed to update status: ' + (data.data?.message || 'Unknown error'), 'error');
                    }
                })
                .catch(() => {
                    selectEl.disabled = false;
                    showToast('Failed to update status', 'error');
                });
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