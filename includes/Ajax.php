<?php
/**
 * Ajax class for handling form submissions
 *
 * @package DSF
 */

namespace DSF;

class Ajax {
    /**
     * Ajax instance
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get ajax instance
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
        
        add_action('wp_ajax_dsf_submit_form', [$this, 'submit_form']);
        add_action('wp_ajax_nopriv_dsf_submit_form', [$this, 'submit_form']);
        
        add_action('wp_ajax_dsf_get_submission_details', [$this, 'get_submission_details']);
        add_action('wp_ajax_dsf_get_wpforms_submission_details', [$this, 'get_wpforms_submission_details']);
        
        add_action('wp_ajax_dsf_get_template_preview', [$this, 'get_template_preview']);
        add_action('wp_ajax_dsf_get_dynamic_tags', [$this, 'get_dynamic_tags']);
        add_action('wp_ajax_dsf_get_email_templates', [$this, 'get_email_templates']);
        add_action('wp_ajax_dsf_send_email_to_user', [$this, 'send_email_to_user']);
        add_action('wp_ajax_dsf_update_submission_status', [$this, 'update_submission_status']);
        
        add_action('dsf_form_submitted', [$this, 'send_submission_email'], 10, 5);
    }

    /**
     * Submit form
     */
    public function submit_form() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $service_type = isset($_POST['service_type']) ? sanitize_text_field($_POST['service_type']) : '';
        $service_category = isset($_POST['service_category']) ? sanitize_text_field($_POST['service_category']) : '';
        $service_name = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : '';
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        
        // Load service
        if ($service_id) {
            $service = new Service($service_id);
        } else {
            $service = Service::get_by_identifier($service_type, $service_category, $service_name);
        }
        
        if (!$service || !$service->get_id()) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'business_name', 'business_address', 'phone', 'email', 'city', 'state', 'zipcode'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(['message' => sprintf('Field %s is required', $field)]);
            }
        }
        
        // Sanitize form data
        $form_data = [
            'pricing_model' => isset($_POST['pricing_model']) ? sanitize_text_field($_POST['pricing_model']) : 'fixed_price',
            'service_type' => isset($_POST['service_type']) ? sanitize_text_field($_POST['service_type']) : null,
            'service_category' => isset($_POST['service_category']) ? sanitize_text_field($_POST['service_category']) : null,
            'location_id' => isset($_POST['location_id']) ? intval($_POST['location_id']) : null,
            'location_name' => isset($_POST['location_name']) ? sanitize_text_field($_POST['location_name']) : null,
            'package_id' => isset($_POST['package_id']) ? intval($_POST['package_id']) : null,
            'package_name' => isset($_POST['package_name']) ? sanitize_text_field($_POST['package_name']) : null,
            'portal_id' => isset($_POST['portal_id']) ? intval($_POST['portal_id']) : null,
            'portal_name' => isset($_POST['portal_name']) ? sanitize_text_field($_POST['portal_name']) : null,
            'user_input_amount' => isset($_POST['user_input_amount']) ? floatval($_POST['user_input_amount']) : null,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'business_name' => sanitize_text_field($_POST['business_name']),
            'business_address' => sanitize_text_field($_POST['business_address']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'zipcode' => sanitize_text_field($_POST['zipcode']),
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '',
        ];
        
        // Calculate total price
        $total_price = $this->calculate_total_price($service->get_id(), $form_data);
        
        // Save submission
        global $wpdb;
        $result = $wpdb->insert(
            Database::get_table('submissions'),
            [
                'service_id' => $service->get_id(),
                'form_data' => wp_json_encode($form_data),
                'total_price' => $total_price,
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'business_name' => $form_data['business_name'],
                'business_address' => $form_data['business_address'],
                'phone' => $form_data['phone'],
                'email' => $form_data['email'],
                'city' => $form_data['city'],
                'state' => $form_data['state'],
                'zipcode' => $form_data['zipcode'],
                'notes' => $form_data['notes'],
                'status' => 'pending',
            ],
            ['%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if (!$result) {
            wp_send_json_error(['message' => 'Failed to submit form']);
        }
        
        // Do something with the submission (e.g., send email)
        do_action('dsf_form_submitted', $wpdb->insert_id, $service, $form_data, $total_price, $service_name);
        
        // Send confirmation email to customer
        // $this->send_customer_confirmation_email($service, $form_data, $total_price, $service_name);
        
        wp_send_json_success([
            'message' => 'Form submitted successfully',
            'submission_id' => $wpdb->insert_id,
        ]);
    }

    /**
     * Send email to admin when form is submitted
     */
    public function send_submission_email($submission_id, $service, $form_data, $total_price, $service_name) {
        // Build pricing details based on pricing model
        $pricing_details = '';
        if (isset($form_data['pricing_model'])) {
            switch ($form_data['pricing_model']) {
                case 'state_based':
                    if (isset($form_data['location_name'])) {
                        $pricing_details .= '<tr><td style="padding: 10px; border: 1px solid #eee; font-weight:bold;">Location</td><td style="padding: 10px; border: 1px solid #eee;">' . esc_html($form_data['location_name']) . '</td></tr>';
                    }
                    if (isset($form_data['package_name'])) {
                        $pricing_details .= '<tr><td style="padding: 10px; border: 1px solid #eee; font-weight:bold;">Package</td><td style="padding: 10px; border: 1px solid #eee;">' . esc_html($form_data['package_name']) . '</td></tr>';
                    }
                    break;
                case 'portal_based':
                    if (isset($form_data['portal_name'])) {
                        $pricing_details .= '<tr><td style="padding: 10px; border: 1px solid #eee; font-weight:bold;">Portal</td><td style="padding: 10px; border: 1px solid #eee;">' . esc_html($form_data['portal_name']) . '</td></tr>';
                    }
                    break;
                case 'calculator':
                    if (isset($form_data['user_input_amount'])) {
                        $pricing_details .= '<tr><td style="padding: 10px; border: 1px solid #eee; font-weight:bold;">Amount</td><td style="padding: 10px; border: 1px solid #eee;">$' . number_format(floatval($form_data['user_input_amount']), 2) . '</td></tr>';
                    }
                    break;
            }
        }
        
        // Get admin email from settings
        $admin_email = get_option('dsf_admin_email', get_option('admin_email'));
        $service_type = $form_data['service_type'];
        $service_category = $form_data['service_category'];
        
        if (!is_email($admin_email)) {
            return; // No valid email
        }

        // Subject
        $subject = sprintf('🔔 New Submission Received: %s (ID: #%d)', $service_name, $submission_id);

        // Build modern HTML email
        $message = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Submission</title>
    <style>
        body { font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 700px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.9; }
        .content { background: white; padding: 30px 20px; }
        .badge-new { display: inline-block; background: #e74c3c; color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; margin-bottom: 15px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 16px; font-weight: 600; color: #2c3e50; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #3498db; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ecf0f1; }
        .info-table td:first-child { color: #555; font-weight: 600; width: 35%; background: #f8f9fa; }
        .info-table td:last-child { color: #333; }
        .price-section { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); padding: 20px; border-radius: 8px; margin: 20px 0; color: white; }
        .total-price { font-size: 32px; font-weight: 700; text-align: center; }
        .price-label { font-size: 12px; text-align: center; margin-bottom: 5px; opacity: 0.9; }
        .pricing-model { background: #ecf0f1; padding: 10px 15px; border-radius: 4px; font-size: 12px; color: #2c3e50; margin: 15px 0; }
        .alert-box { background: #fff8e1; border-left: 4px solid #f39c12; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .alert-box strong { color: #d68910; }
        .footer { background: #34495e; padding: 20px; text-align: center; font-size: 12px; color: white; border-radius: 0 0 8px 8px; }
        .footer-link { color: #3498db; text-decoration: none; }
        .action-button { display: inline-block; background: #3498db; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin-top: 10px; font-size: 13px; }
        /* Mobile-friendly stacked tables + wrapping */
        @media only screen and (max-width: 600px) {

            .info-table,
            .info-table tbody,
            .info-table tr,
            .info-table td {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .info-table tr {
                margin-bottom: 14px;
                border-bottom: 1px solid #ecf0f1;
                padding-bottom: 10px;
            }

            .info-table td {
                box-sizing: border-box;
                word-wrap: break-word;
                word-break: break-word;
                overflow-wrap: anywhere;
                white-space: normal;
            }

            /* Label */
            .info-table td:first-child {
                background: none !important;
                font-weight: 700;
                color: #2c3e50;
                padding-bottom: 4px;
                border-bottom: none;
            }

            /* Value */
            .info-table td:last-child {
                padding-top: 0;
                color: #333;
            }

            /* Fix long links (email, phone, URLs) */
            .info-table a {
                word-break: break-all;
                white-space: normal;
                display: inline-block;
                max-width: 100%;
            }
        }

    </style>
</head>
<body style="background-color: #ecf0f1; margin: 0; padding: 20px;">
    <div class="container" style="background: white; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); overflow: hidden;">
        <div class="header">
            <h1>New Submission Received</h1>
            <p>Submission ID: #' . intval($submission_id) . '</p>
        </div>

        <div class="content">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="badge-new">ACTION REQUIRED</span>
            </div>

            <div class="section">
                <div class="section-title">Customer Information</div>
                <table class="info-table">
                    <tr>
                        <td>First Name:</td>
                        <td>' . esc_html($form_data['first_name']) . '</td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td>' . esc_html($form_data['last_name']) . '</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Business Information</div>
                <table class="info-table">
                    <tr>
                        <td>Business Name:</td>
                        <td>' . esc_html($form_data['business_name']) . '</td>
                    </tr>
                    <tr>
                        <td>Business Address:</td>
                        <td>' . esc_html($form_data['business_address']) . '</td>
                    </tr>
                    <tr>
                        <td>Business City:</td>
                        <td>' . esc_html($form_data['city']) . '</td>
                    </tr>
                    <tr>
                        <td>Business State:</td>
                        <td>' . esc_html($form_data['state']) . '</td>
                    </tr>
                    <tr>
                        <td>Business Zip Code:</td>
                        <td>' . esc_html($form_data['zipcode']) . '</td>
                    </tr>
                    <tr>
                        <td>Business Email:</td>
                        <td><a href="mailto:' . esc_attr($form_data['email']) . '" style="color: #3498db;">' . esc_html($form_data['email']) . '</a></td>
                    </tr>
                    <tr>
                        <td>Business Phone:</td>
                        <td><a href="tel:' . esc_attr($form_data['phone']) . '" style="color: #3498db;">' . esc_html($form_data['phone']) . '</a></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Service Information</div>
                <table class="info-table">
                    <tr>
                        <td>Service Type:</td>
                        <td>' . esc_html($service_type) . '</td>
                    </tr>
                    <tr>
                        <td>Service Category:</td>
                        <td>' . esc_html($service_category) . '</td>
                    </tr>
                    <tr>
                        <td>Service Name:</td>
                        <td>' . esc_html($service_name) . '</td>
                    </tr>
                    <tr>
                        <td>Pricing Model:</td>
                        <td><strong>' . ucfirst(str_replace('_', ' ', esc_html($form_data['pricing_model'] ?? 'fixed_price'))) . '</strong></td>
                    </tr>
                </table>
                ' . (!empty($pricing_details) ? '<div class="pricing-model"><strong>Pricing Details:</strong><table width="100%" style="margin-top: 10px;">' . $pricing_details . '</table></div>' : '') . '
            </div>

            <div class="price-section">
                <div class="price-label">Total Service Cost</div>
                <div class="total-price">$' . number_format($total_price, 2) . '</div>
            </div>';
            
        if (!empty($form_data['notes'])) {
            $message .= '
            <div class="section">
                <div class="section-title">Additional Notes</div>
                <div style="background: #f8f9fa; padding: 12px; border-radius: 4px; color: #555; line-height: 1.6;">' . nl2br(esc_html($form_data['notes'])) . '</div>
            </div>';
        }

        $message .= '
            <div class="footer">
                <p style="margin: 0 0 10px; font-size: 13px;"><strong>Submission received at:</strong> ' . current_time('Y-m-d H:i:s') . '</p>
                <p style="margin: 0; color: #bdc3c7;">&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>';
        
        // Headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . sanitize_text_field($_SERVER['SERVER_NAME'] ?? $admin_email) . '>'
        ];

        // Send email
        wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * Send confirmation email to customer after form submission
     */
    public function send_customer_confirmation_email($service, $form_data, $total_price, $service_name) {
        $customer_email = $form_data['email'] ?? '';
        
        if (!is_email($customer_email)) {
            return; // No valid email
        }

        // Subject
        $subject = sprintf('✅ Your Service Request Submission Confirmed - %s', $service_name);

        // Build pricing details based on pricing model
        $pricing_details = '';
        if (isset($form_data['location_name'])) {
            $pricing_details .= '<tr><td style="padding: 8px; color:#666;">Location</td><td style="padding: 8px; font-weight:bold;">' . esc_html($form_data['location_name']) . '</td></tr>';
        }
        if (isset($form_data['package_name'])) {
            $pricing_details .= '<tr><td style="padding: 8px; color:#666;">Package</td><td style="padding: 8px; font-weight:bold;">' . esc_html($form_data['package_name']) . '</td></tr>';
        }
        if (isset($form_data['portal_name'])) {
            $pricing_details .= '<tr><td style="padding: 8px; color:#666;">Portal</td><td style="padding: 8px; font-weight:bold;">' . esc_html($form_data['portal_name']) . '</td></tr>';
        }
        if (isset($form_data['user_input_amount'])) {
            $pricing_details .= '<tr><td style="padding: 8px; color:#666;">Amount</td><td style="padding: 8px; font-weight:bold;">$' . number_format(floatval($form_data['user_input_amount']), 2) . '</td></tr>';
        }

        // Build modern HTML email
        $message = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Confirmed</title>
    <style>
        body { font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.9; }
        .content { background: white; padding: 30px 20px; }
        .status-badge { display: inline-block; background: #4CAF50; color: white; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #667eea; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .info-table td:first-child { color: #666; font-weight: 500; width: 40%; }
        .info-table td:last-child { color: #333; font-weight: 600; text-align: right; }
        .price-section { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 20px; border-radius: 8px; margin: 20px 0; }
        .total-price { font-size: 24px; font-weight: 700; color: #667eea; text-align: center; }
        .price-label { font-size: 12px; color: #666; text-align: center; margin-bottom: 5px; }
        .next-steps { background: #f9f9f9; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; border-radius: 4px; }
        .next-steps h4 { margin: 0 0 10px; color: #667eea; font-size: 14px; }
        .next-steps ul { margin: 0; padding-left: 20px; }
        .next-steps li { margin: 5px 0; color: #555; font-size: 13px; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777; border-radius: 0 0 8px 8px; }
        .footer-link { color: #667eea; text-decoration: none; }
        .contact-link { display: inline-block; background: #667eea; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin-top: 10px; font-size: 13px; }
    </style>
</head>
<body style="background-color: #f5f5f5; margin: 0; padding: 20px;">
    <div class="container" style="background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
        <div class="header">
            <h1>✅ Submission Confirmed!</h1>
            <p>Your service request has been received and is being processed</p>
        </div>

        <div class="content">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="status-badge">Status: PENDING REVIEW</span>
            </div>

            <div class="section">
                <div class="section-title">👤 Your Information</div>
                <table class="info-table">
                    <tr>
                        <td>Name:</td>
                        <td>' . esc_html($form_data['first_name'] . ' ' . $form_data['last_name']) . '</td>
                    </tr>
                    <tr>
                        <td>Business:</td>
                        <td>' . esc_html($form_data['business_name']) . '</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>' . esc_html($form_data['email']) . '</td>
                    </tr>
                    <tr>
                        <td>Phone:</td>
                        <td>' . esc_html($form_data['phone']) . '</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">🎯 Service Details</div>
                <table class="info-table">
                    <tr>
                        <td>Service:</td>
                        <td>' . esc_html($service_name) . '</td>
                    </tr>
                    ' . $pricing_details . '
                </table>
            </div>

            <div class="price-section">
                <div class="price-label">Estimated Total Cost</div>
                <div class="total-price">$' . number_format($total_price, 2) . '</div>
            </div>

            <div class="section">
                <div class="section-title">📍 Service Address</div>
                <table class="info-table">
                    <tr>
                        <td>Address:</td>
                        <td>' . esc_html($form_data['business_address']) . '</td>
                    </tr>
                    <tr>
                        <td>City:</td>
                        <td>' . esc_html($form_data['city']) . '</td>
                    </tr>
                    <tr>
                        <td>State:</td>
                        <td>' . esc_html($form_data['state']) . '</td>
                    </tr>
                    <tr>
                        <td>Zip Code:</td>
                        <td>' . esc_html($form_data['zipcode']) . '</td>
                    </tr>
                </table>
            </div>';

        if (!empty($form_data['notes'])) {
            $message .= '
            <div class="section">
                <div class="section-title">📝 Additional Notes</div>
                <p style="color: #555; line-height: 1.6; margin: 0;">' . nl2br(esc_html($form_data['notes'])) . '</p>
            </div>';
        }

        $message .= '
            <div class="next-steps">
                <h4>⏭️ What\'s Next?</h4>
                <ul>
                    <li><strong>Review:</strong> Our team will review your request within 24-48 hours</li>
                    <li><strong>Confirmation:</strong> We\'ll send you a confirmation email with next steps</li>
                    <li><strong>Questions?:</strong> Feel free to reach out - we\'re here to help!</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 25px;">
                <p style="color: #666; font-size: 13px; margin: 0 0 10px;">Need help? Contact our support team</p>
                <a href="mailto:support@' . sanitize_text_field($_SERVER['SERVER_NAME'] ?? 'example.com') . '" class="contact-link">
                    📧 Contact Support
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px;">Thank you for choosing our services!</p>
            <p style="margin: 0; color: #999;">&copy; ' . date('Y') . ' All Rights Reserved. | <a href="#" class="footer-link">Privacy Policy</a> | <a href="#" class="footer-link">Terms of Service</a></p>
        </div>
    </div>
</body>
</html>';

        // Headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . sanitize_text_field($_SERVER['SERVER_NAME'] ?? 'example.com') . '>'
        ];

        // Send email
        wp_mail($customer_email, $subject, $message, $headers);
    }

    /**
     * Calculate total price based on service and form data
     *
     * @param int $service_id Service ID
     * @param array $form_data Form data
     * @return float Total price
     */
    private function calculate_total_price($service_id, $form_data) {
        $service = new Service($service_id);
        $pricing_model = $service->get('pricing_model');
        $total_price = 0;
        
        switch ($pricing_model) {
            case 'state_based':
                if (!empty($form_data['location_id'])) {
                    $total_price = 0; // default

                    // 1️⃣ Package pricing
                    if ($service->get('has_packages') && !empty($form_data['package_id'])) {
                        $package_pricing = ServicePackagePricing::get_by_service_and_package_type($service_id, $form_data['package_id']);
                        if ($package_pricing) {
                            $total_price += floatval($package_pricing['price'] ?? 0);
                        }
                    }

                    // 2️⃣ Location pricing (always add if exists)
                    $location_pricing = ServiceLocationPricing::get_by_service_and_location($service_id, $form_data['location_id']);
                    if ($location_pricing) {
                        $total_price += floatval($location_pricing['standard_price'] ?? 0);
                    }
                }
                break;
                
            case 'portal_based':
                if (!empty($form_data['portal_id'])) {
                    $portal = new Portal($form_data['portal_id']);
                    if ($portal->get_id()) {
                        $total_price = floatval($portal->get('price') ?? 0);
                    }
                }
                break;
                
            case 'fixed_price':
                // Get fixed price from service configuration
                $fixed_price = $service->get('fixed_price') ?? 0;
                $total_price = floatval($fixed_price);
                break;
                
            case 'calculator':
                $total_price = $this->calculate_tiered_price($form_data['user_input_amount'] ?? 0);
                break;
        }
        
        return round($total_price, 2);
    }

    /**
     * Calculate price based on tiered calculator pricing
     * Tiers:
     * - $350,000 - $500,000: $900.00
     * - $500,000 - $2,000,000: $1,500.00
     * - $2,000,000+: 1% of total amount
     *
     * @param float $amount The amount entered by user
     * @return float Calculated price
     */
    private function calculate_tiered_price($amount) {
        if ($amount < 350000) {
            return 0; // Below minimum threshold
        } elseif ($amount >= 350000 && $amount < 500000) {
            return 900.00;
        } elseif ($amount >= 500000 && $amount < 2000000) {
            return 1500.00;
        } else {
            // $2,000,000+: 1% of total amount
            return $amount * 0.01;
        }
    }

    /**
     * Get submission details via AJAX for modal view
     */
    public function get_submission_details() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        
        if (!$submission_id) {
            wp_send_json_error(['message' => 'Invalid submission ID']);
        }

        global $wpdb;
        $submission = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dsf_submissions WHERE id = %d",
                $submission_id
            ),
            ARRAY_A
        );

        if (!$submission) {
            wp_send_json_error(['message' => 'Submission not found']);
        }

        $form_data = json_decode($submission['form_data'], true);
        $service = new Service($submission['service_id']);

        ob_start();
        ?>
        <div class="dsf-submission-section">
            <h3>👤 Customer Information</h3>
            <div class="dsf-info-row">
                <div class="dsf-info-label">First Name:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['first_name']); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Last Name:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['last_name']); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Email:</div>
                <div class="dsf-info-value"><a href="mailto:<?php echo esc_attr($submission['email']); ?>" style="color: #0073aa;"><?php echo esc_html($submission['email']); ?></a></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Phone:</div>
                <div class="dsf-info-value"><a href="tel:<?php echo esc_attr($submission['phone']); ?>" style="color: #0073aa;"><?php echo esc_html($submission['phone']); ?></a></div>
            </div>
        </div>

        <div class="dsf-submission-section">
            <h3>🏢 Business Information</h3>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Business Name:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['business_name']); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Business Address:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['business_address']); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">City:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['city']); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">State:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['state']); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Zip Code:</div>
                <div class="dsf-info-value"><?php echo esc_html($submission['zipcode']); ?></div>
            </div>
        </div>

        <div class="dsf-submission-section">
            <h3>🎯 Service Information</h3>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Service Name:</div>
                <div class="dsf-info-value"><?php echo esc_html($service->get('name')); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Service Type:</div>
                <div class="dsf-info-value"><?php echo isset($form_data['service_type']) ? esc_html($form_data['service_type']) : '--'; ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Service Category:</div>
                <div class="dsf-info-value"><?php echo isset($form_data['service_category']) ? esc_html($form_data['service_category']) : '--'; ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Pricing Model:</div>
                <div class="dsf-info-value"><strong><?php echo isset($form_data['pricing_model']) ? esc_html(ucwords(str_replace('_', ' ', $form_data['pricing_model']))) : '--'; ?></strong></div>
            </div>
        </div>

        <?php if (isset($form_data['pricing_model'])) : 
            switch ($form_data['pricing_model']) {
                case 'state_based':
                    ?>
                    <div class="dsf-submission-section">
                        <h3>Location & Pricing Details</h3>
                        <?php if (isset($form_data['location_name'])) : ?>
                        <div class="dsf-info-row">
                            <div class="dsf-info-label">Location:</div>
                            <div class="dsf-info-value"><?php echo esc_html($form_data['location_name']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($form_data['package_name'])) : ?>
                        <div class="dsf-info-row">
                            <div class="dsf-info-label">Package:</div>
                            <div class="dsf-info-value"><?php echo esc_html($form_data['package_name']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;
                    
                case 'portal_based':
                    ?>
                    <div class="dsf-submission-section">
                        <h3>🔌 Portal Details</h3>
                        <?php if (isset($form_data['portal_name'])) : ?>
                        <div class="dsf-info-row">
                            <div class="dsf-info-label">Portal:</div>
                            <div class="dsf-info-value"><?php echo esc_html($form_data['portal_name']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;
                    
                case 'calculator':
                    ?>
                    <div class="dsf-submission-section">
                        <h3>💰 Calculator Details</h3>
                        <?php if (isset($form_data['user_input_amount'])) : ?>
                        <div class="dsf-info-row">
                            <div class="dsf-info-label">Input Amount:</div>
                            <div class="dsf-info-value">$<?php echo esc_html(number_format(floatval($form_data['user_input_amount']), 2)); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;
            }
        endif; ?>

        <div class="dsf-price-box">
            <div class="dsf-price-label">Total Service Cost</div>
            <div class="dsf-price-amount">$<?php echo esc_html(number_format(floatval($submission['total_price']), 2)); ?></div>
        </div>

        <div class="dsf-submission-section">
            <h3>📋 Submission Status</h3>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Submitted:</div>
                <div class="dsf-info-value"><?php echo esc_html(date_format(date_create($submission['created_at']), 'M d, Y \a\t H:i:s')); ?></div>
            </div>
            <div class="dsf-info-row">
                <div class="dsf-info-label">Submission ID:</div>
                <div class="dsf-info-value"><strong>#<?php echo intval($submission['id']); ?></strong></div>
            </div>
        </div>

        <?php if (!empty($submission['notes'])) : ?>
        <div class="dsf-submission-section">
            <h3>📝 Additional Notes</h3>
            <div style="background: #f8f9fa; padding: 12px; border-radius: 4px; color: #555; line-height: 1.6; border-left: 4px solid #3498db;">
                <?php echo nl2br(esc_html($submission['notes'])); ?>
            </div>
        </div>
        <?php endif; ?>
        <?php
        
        wp_send_json_success([
            'html' => ob_get_clean(),
        ]);
    }

    /**
     * Get WP Forms submission details via AJAX
     */
    public function get_wpforms_submission_details() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        
        if (!$submission_id) {
            wp_send_json_error(['message' => 'Invalid submission ID']);
        }

        global $wpdb;
        $submission = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dsf_wpforms_submissions WHERE id = %d",
                $submission_id
            ),
            ARRAY_A
        );

        if (!$submission) {
            wp_send_json_error(['message' => 'Submission not found']);
        }

        $form_data = json_decode($submission['form_data'], true);
        $fields = $form_data['fields'] ?? [];

        ob_start();
           // If we have WP Forms form/entry IDs, provide a quick link to the full WPForms admin entry
        $entry_form_id = intval($form_data['form_id'] ?? $submission['form_id'] ?? 0);
        $entry_entry_id = intval($submission['entry_id'] ?? $form_data['entry_id'] ?? 0);
        if ($entry_entry_id) {
            $entry_link = admin_url('admin.php?page=wpforms-entries&view=details&entry_id=' . $entry_entry_id);
            echo '<div style="text-align:right;margin-bottom:12px;"><a href="' . esc_url($entry_link) . '" target="_blank" class="button">View Full WPForms Entry</a></div>';
        }

        ?>
        <div class="dsf-wpforms-section">
            <h3>👤 Personal Information</h3>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">First Name:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['first_name']); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Last Name:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['last_name']); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Email:</div>
                <div class="dsf-wpforms-info-value"><a href="mailto:<?php echo esc_attr($submission['email']); ?>" style="color: #0073aa;"><?php echo esc_html($submission['email']); ?></a></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Phone:</div>
                <div class="dsf-wpforms-info-value"><a href="tel:<?php echo esc_attr($submission['phone']); ?>" style="color: #0073aa;"><?php echo esc_html($submission['phone']); ?></a></div>
            </div>
        </div>

        <div class="dsf-wpforms-section">
            <h3>🏢 Business Information</h3>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Business Name:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['business_name'] ?: '-'); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Entity Type:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['entity_type'] ?: '-'); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Business Address:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['business_address'] ?: '-'); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">City:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['city'] ?: '-'); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">State:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['state'] ?: '-'); ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Zip Code:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html($submission['zipcode'] ?: '-'); ?></div>
            </div>
        </div>

        <div class="dsf-wpforms-price-box">
            <div class="dsf-wpforms-price-label">Total Amount:</div>
            <div class="dsf-wpforms-price-amount"><?php echo ! empty($submission['total_price']) ? '$' . number_format((float) $submission['total_price'], 2) : '$0.00'; ?></div>
        </div>

        <div class="dsf-wpforms-section">
            <h3>📋 WP Forms Data</h3>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Form ID:</div>
                <div class="dsf-wpforms-info-value"><?php echo isset($form_data['form_id']) ? esc_html($form_data['form_id']) : '-'; ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Entry ID:</div>
                <div class="dsf-wpforms-info-value"><?php echo isset($form_data['entry_id']) ? esc_html($form_data['entry_id']) : '-'; ?></div>
            </div>
            <div class="dsf-wpforms-info-row">
                <div class="dsf-wpforms-info-label">Submitted:</div>
                <div class="dsf-wpforms-info-value"><?php echo esc_html(date_format(date_create($submission['created_at']), 'M d, Y H:i:s')); ?></div>
            </div>
        </div>

        <div class="dsf-wpforms-section">
            <h3>🔍 All Form Fields</h3>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; max-height: 400px; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f0f0f0; border-bottom: 2px solid #ddd;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #333;">Field Name</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #333;">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fields as $field) : ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px; color: #555;"><?php echo esc_html($field['name'] ?? ''); ?></td>
                                <td style="padding: 8px; color: #333;">
                                    <?php 
                                        if (isset($field['value_choice']) && $field['value_choice']) {
                                            echo esc_html($field['value_choice']);
                                            if (isset($field['amount']) && $field['amount']) {
                                                echo ' - $' . number_format((float)$field['amount'], 2);
                                            }
                                        } else {
                                            $value = $field['value'] ?? '';
                                            if (is_array($value)) {
                                                echo esc_html(implode(', ', $value));
                                            } else {
                                                echo esc_html($value ?: '-');
                                            }
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($submission['notes'])) : ?>
        <div class="dsf-wpforms-section">
            <h3>📝 Additional Notes</h3>
            <div style="background: #f8f9fa; padding: 12px; border-radius: 4px; color: #555; line-height: 1.6; border-left: 4px solid #3498db;">
                <?php echo nl2br(esc_html($submission['notes'])); ?>
            </div>
        </div>
        <?php endif; ?>
        <?php
        
        wp_send_json_success([
            'html' => ob_get_clean(),
        ]);
    }

    /**
     * Get template preview via AJAX
     */
    public function get_template_preview() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $template_html = isset($_POST['template_html']) ? wp_kses_post($_POST['template_html']) : '';
        $template_css = isset($_POST['template_css']) ? wp_kses_post($_POST['template_css']) : '';
        
        // Sample replacement values for preview
        $sample_values = [
            'CLIENT_NAME'      => 'John Doe',
            'CLIENT_EMAIL'     => 'john@example.com',
            'BUSINESS_NAME'    => 'ABC Corporation',
            'SERVICE_NAME'     => 'LLC Formation',
            'TOTAL_PRICE'      => '$500.00',
            'YOUR_NAME'        => 'Jane Smith',
            'YOUR_TITLE'       => 'Business Consultant',
        ];
        
        // Replace tags
        $html = $template_html;
        foreach ($sample_values as $key => $value) {
            $html = str_replace('[' . $key . ']', $value, $html);
            $html = str_replace('[' . strtolower($key) . ']', $value, $html);
        }
        
        // Add CSS
        if ($template_css) {
            $html = preg_replace(
                '/<\/head>/i',
                '<style>' . $template_css . '</style></head>',
                $html
            );
        }
        
        wp_send_json_success([
            'html' => $html,
        ]);
    }

    /**
     * Get available dynamic tags via AJAX
     */
    public function get_dynamic_tags() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $tags = EmailTemplate::get_available_tags();
        
        wp_send_json_success([
            'tags' => $tags,
        ]);
    }

    /**
     * Get email templates list via AJAX
     */
    public function get_email_templates() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $templates = EmailTemplate::get_all();
        
        // Only return basic info (id, name, subject)
        $template_list = array_map(function($template) {
            return [
                'id' => $template['id'],
                'template_name' => $template['template_name'],
                'template_subject' => $template['template_subject'],
            ];
        }, $templates);
        
        wp_send_json_success([
            'templates' => $template_list,
        ]);
    }

    /**
     * Update submission status (admin)
     */
    public function update_submission_status() {
        check_ajax_referer('dsf_admin_nonce', 'nonce');

        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        $table = isset($_POST['table']) ? sanitize_text_field($_POST['table']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!$submission_id || !$table || !$status) {
            wp_send_json_error(['message' => 'Invalid parameters']);
        }

        // Allow only expected table names
        $allowed = [ 'submissions', 'wpforms_submissions' ];
        if (!in_array($table, $allowed, true)) {
            wp_send_json_error(['message' => 'Invalid table']);
        }

        global $wpdb;
        $table_name = Database::get_table($table);

        $updated = $wpdb->update(
            $table_name,
            [ 'status' => $status, 'updated_at' => current_time('mysql') ],
            [ 'id' => $submission_id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );

        if ($updated === false) {
            wp_send_json_error(['message' => 'Failed to update status']);
        }

        // If status changed to success, attempt to send confirmation email to submitter
        if ($status === 'success') {
            $submission_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $submission_id),
                ARRAY_A
            );

            if ($submission_row && is_email($submission_row['email'])) {
                // Prefer named confirmation template, fallback to default or first enabled
                $template_inst = EmailTemplate::get_by_slug('payment_success_template');

                if (!$template_inst) {
                    $all = EmailTemplate::get_all();
                    $fallback = null;
                    foreach ($all as $t) {
                        if (!empty($t['is_default'])) { $fallback = $t; break; }
                    }
                    if (!$fallback && !empty($all)) { $fallback = $all[0]; }
                    if ($fallback) {
                        $template_inst = new EmailTemplate(intval($fallback['id']));
                    }
                }

                if ($template_inst && $template_inst->get_id()) {
                    $values = [
                        'client_name'   => trim(($submission_row['first_name'] ?? '') . ' ' . ($submission_row['last_name'] ?? '')),
                        'client_email'  => $submission_row['email'] ?? '',
                        'business_name' => $submission_row['business_name'] ?? '',
                        'service_name'  => $submission_row['service_id'] ?? ($submission_row['entity_type'] ?? ''),
                        'total_price'   => !empty($submission_row['total_price']) ? '$' . number_format(floatval($submission_row['total_price']), 2) : '$0.00',
                        'submission_id' => $submission_row['id'],
                        'submission_date' => isset($submission_row['created_at']) ? date_format(date_create($submission_row['created_at']), 'M d, Y') : '',
                    ];

                    $rendered_html = $template_inst->render($values);
                    $rendered_subject = $template_inst->get('template_subject') ?: 'Submission Update';

                    // Replace subject tags
                    foreach ($values as $k => $v) {
                        $rendered_subject = str_ireplace('[' . strtoupper($k) . ']', $v, $rendered_subject);
                        $rendered_subject = str_ireplace('[' . $k . ']', $v, $rendered_subject);
                    }

                    $headers = [
                        'Content-Type: text/html; charset=UTF-8',
                        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
                    ];

                    wp_mail($submission_row['email'], $rendered_subject, $rendered_html, $headers);
                }
            }
        }

        wp_send_json_success(['message' => 'Status updated']);
    }

    /**
     * Send email to user with template and attachments
     */
    public function send_email_to_user() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $table = isset($_POST['table']) ? sanitize_text_field($_POST['table']) : 'submissions';

        // Validate inputs
        if (!$submission_id || !$template_id || !is_email($email)) {
            wp_send_json_error(['message' => 'Invalid submission, template, or email']);
        }

        // Allow only expected table names
        $allowed_tables = ['submissions', 'wpforms_submissions'];
        if (!in_array($table, $allowed_tables, true)) {
            wp_send_json_error(['message' => 'Invalid table']);
        }

        // Get submission data from specified table
        global $wpdb;
        $table_name = Database::get_table($table);
        $submission = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $submission_id
            ),
            ARRAY_A
        );

        if (!$submission) {
            wp_send_json_error(['message' => 'Submission not found']);
        }

        // Get email template by ID
        $template = null;
        $templates = EmailTemplate::get_all();
        foreach ($templates as $t) {
            if ($t['id'] == $template_id) {
                $template = $t;
                break;
            }
        }
        if (!$template) {
            wp_send_json_error(['message' => 'Email template not found']);
        }

        // Prepare template data for rendering
        $form_data = json_decode($submission['form_data'], true);
        
        // For WP Forms submissions, entity_type is the "service"
        $service_name = $submission['entity_type'] ?? 'Service';

        // Build template context data
        $template_data = [
            'CLIENT_NAME' => $submission['first_name'] . ' ' . $submission['last_name'],
            'CLIENT_EMAIL' => $submission['email'],
            'BUSINESS_NAME' => $submission['business_name'],
            'SERVICE_NAME' => $service_name,
            'SERVICE_TYPE' => $form_data['service_type'] ?? 'Business Registration',
            'TOTAL_PRICE' => '$' . number_format(floatval($submission['total_price']), 2),
            'YOUR_NAME' => get_bloginfo('admin_email'), 
            'YOUR_TITLE' => 'Business Manager',
            'COMPANY_NAME' => get_bloginfo('name'),
            'SUBMISSION_DATE' => date_format(date_create($submission['created_at']), 'M d, Y'),
            'SUBMISSION_ID' => $submission['id'],
            'BUSINESS_ADDRESS' => $submission['business_address'],
            'BUSINESS_PHONE' => $submission['phone'],
        ];

        // Helper function to convert slug to human-readable format
        $slugToHuman = function($slug) {
            return ucwords(str_replace(['-', '_'], ' ', $slug));
        };

        // Apply slug conversion
        $template_data['SERVICE_NAME'] = $slugToHuman($service_name);
        $template_data['SERVICE_TYPE'] = $slugToHuman($template_data['SERVICE_TYPE']);

        // Render template with data
        $rendered_html = $template['template_html'];
        $rendered_subject = $template['template_subject'];

        // Replace dynamic tags in both HTML and subject
        foreach ($template_data as $tag => $value) {
            $rendered_html = str_ireplace('[' . $tag . ']', $value, $rendered_html);
            $rendered_subject = str_ireplace('[' . $tag . ']', $value, $rendered_subject);
        }

        // Inject CSS if present
        if (!empty($template['template_css'])) {
            $rendered_html = '<style>' . $template['template_css'] . '</style>' . $rendered_html;
        }

        // Handle file attachments
        $attachments = [];
        if (!empty($_FILES['attachments'])) {
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['path'] . '/';

            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                if (empty($tmp_name)) {
                    continue;
                }

                $filename = sanitize_file_name($_FILES['attachments']['name'][$key]);
                $file_path = $upload_path . $filename;

                // Check file size (max 10MB)
                if ($_FILES['attachments']['size'][$key] > 10 * 1024 * 1024) {
                    wp_send_json_error(['message' => 'File ' . $filename . ' is too large (max 10MB)']);
                }

                // Move uploaded file
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $attachments[] = $file_path;
                }
            }
        }

        // Send email
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];

        $result = wp_mail(
            $email,
            $rendered_subject,
            $rendered_html,
            $headers,
            $attachments
        );

        // Clean up uploaded files
        foreach ($attachments as $file_path) {
            @unlink($file_path);
        }

        if ($result) {
            wp_send_json_success(['message' => 'Email sent successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to send email']);
        }
    }
}