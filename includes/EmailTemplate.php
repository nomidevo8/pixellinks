<?php
/**
 * EmailTemplate class for managing email templates
 *
 * @package DSF
 */

namespace DSF;

class EmailTemplate {
    /**
     * Template ID
     *
     * @var int
     */
    private $id;
    
    /**
     * Template data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Template ID (optional)
     */
    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load template from database
     *
     * @param int $id Template ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        
        $table = Database::get_table('email_templates');
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$template) {
            return false;
        }
        
        $this->id = $id;
        $this->data = $template;
        
        return true;
    }

    /**
     * Get template by slug
     *
     * @param string $slug Template slug
     * @return EmailTemplate|null
     */
    public static function get_by_slug($slug) {
        global $wpdb;
        
        $table = Database::get_table('email_templates');
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE template_slug = %s AND enabled = 1", $slug),
            ARRAY_A
        );
        
        if (!$template) {
            return null;
        }
        
        $instance = new self();
        $instance->id = $template['id'];
        $instance->data = $template;
        
        return $instance;
    }

    /**
     * Get all templates
     *
     * @return array
     */
    public static function get_all() {
        global $wpdb;
        
        $table = Database::get_table('email_templates');
        return $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY template_name ASC",
            ARRAY_A
        );
    }

    /**
     * Save template to database
     *
     * @param array $data Template data
     * @return int|false Template ID on success, false on failure
     */
    public function save($data) {
        global $wpdb;
        
        $table = Database::get_table('email_templates');
        
        // Sanitize data
        $template_data = [
            'template_name'    => sanitize_text_field($data['template_name'] ?? ''),
            'template_slug'    => sanitize_text_field($data['template_slug'] ?? ''),
            'template_subject' => sanitize_text_field($data['template_subject'] ?? ''),
            'template_html'    => wp_kses_post($data['template_html'] ?? ''),
            'template_css'     => wp_kses_post($data['template_css'] ?? ''),
            'description'      => sanitize_textarea_field($data['description'] ?? ''),
            'is_default'       => isset($data['is_default']) ? 1 : 0,
            'enabled'          => isset($data['enabled']) ? 1 : 1,
            'updated_at'       => current_time('mysql'),
        ];
        
        // If editing existing template
        if ($this->id) {
            $result = $wpdb->update(
                $table,
                $template_data,
                ['id' => $this->id],
                [
                    '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s'
                ],
                ['%d']
            );
            
            if ($result !== false) {
                $this->data = array_merge($this->data, $template_data);
                return $this->id;
            }
            return false;
        }
        
        // If creating new template
        $template_data['created_at'] = current_time('mysql');
        
        $result = $wpdb->insert(
            $table,
            $template_data,
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'
            ]
        );
        
        if ($result !== false) {
            $this->id = $wpdb->insert_id;
            $this->data = $template_data;
            return $this->id;
        }
        
        return false;
    }

    /**
     * Delete template
     *
     * @param int $id Template ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        
        $table = Database::get_table('email_templates');
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get template property
     *
     * @param string $key Property name
     * @return mixed
     */
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Get template ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get template data as array
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Render template with dynamic values
     *
     * @param array $values Dynamic values to replace in template
     * @return string Rendered HTML
     */
    public function render($values = []) {
        $html = $this->get('template_html');
        $css = $this->get('template_css');
        
        // Replace dynamic tags with values
        foreach ($values as $key => $value) {
            $tag = '[' . strtoupper($key) . ']';
            $html = str_replace($tag, esc_html($value), $html);
        }
        
        // Also try lowercase tags
        foreach ($values as $key => $value) {
            $tag = '[' . $key . ']';
            $html = str_replace($tag, esc_html($value), $html);
        }
        
        // Add CSS to template if provided
        if ($css) {
            $html = preg_replace(
                '/<\/head>/i',
                '<style>' . $css . '</style></head>',
                $html
            );
        }
        
        return $html;
    }

    /**
     * Get available dynamic tags
     *
     * @return array
     */
    public static function get_available_tags() {
        return [
            'CLIENT_NAME'      => 'Client/Customer name',
            'CLIENT_EMAIL'     => 'Client/Customer email',
            'CLIENT_PHONE'     => 'Client/Customer phone',
            'BUSINESS_NAME'    => 'Business/Company name',
            'SERVICE_NAME'     => 'Service name',
            'SERVICE_TYPE'     => 'Service type',
            'SERVICE_CATEGORY' => 'Service category',
            'TOTAL_PRICE'      => 'Total price',
            'YOUR_NAME'        => 'Sender name',
            'YOUR_TITLE'       => 'Sender job title',
            'COMPANY_NAME'     => 'Company name',
            'COMPANY_PHONE'    => 'Company phone',
            'COMPANY_WEBSITE'  => 'Company website',
            'SUBMISSION_DATE'  => 'Submission date',
            'SUBMISSION_ID'    => 'Submission ID',
        ];
    }

    /**
     * Create default templates
     */
    public static function create_default_templates() {
        global $wpdb;
        
        $table = Database::get_table('email_templates');
        
        // Check if templates already exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        if ($count > 0) {
            return;
        }
        
        // Default quotation template
        $quotation_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Quotation Details</title>
</head>
<body style="margin:0;padding:0;background:#eef2f6;font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;color:#444444;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f6;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;margin:30px auto;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td align="center" style="background:linear-gradient(135deg, #007bff00, #218764);padding:35px 20px;">
                            <p style="margin:0;color:#394783;font-size:18px;font-weight:bold;">Your Quotation Details</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 40px 10px 40px;">
                            <p style="font-size:16px;line-height:1.7;margin:0 0 18px 0;">Hello <strong>[CLIENT_NAME]</strong>,</p>
                            <p style="font-size:16px;line-height:1.7;margin:0;">It was a pleasure connecting with you! We truly appreciate you reaching out to us.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:25px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fbff;border:1px solid #d9e8ff;border-radius:10px;">
                                <tr>
                                    <td style="padding:25px;">
                                        <p style="margin:0;font-size:12px;color:#007bff;letter-spacing:1px;text-transform:uppercase;font-weight:bold;">Service Overview</p>
                                        <p style="margin:14px 0 10px 0;font-size:20px;color:#1f2d3d;font-weight:bold;">[SERVICE_NAME]</p>
                                        <p style="margin:0;font-size:14px;line-height:1.6;color:#5f6b76;">Price: <strong>[TOTAL_PRICE]</strong></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 25px 40px;">
                            <p style="margin:30px 0 0 0;font-size:16px;line-height:1.7;">Warm regards,<br><strong>[YOUR_NAME]</strong><br><span style="color:#888888;font-size:14px;">[YOUR_TITLE]</span></p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background:#f3f5f7;padding:20px;font-size:12px;color:#8a8f98;line-height:1.6;">
                            <p style="margin:6px 0;">You are receiving this email because you requested a quote.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        
        // Default submission confirmation template
        $confirmation_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Confirmation</title>
</head>
<body style="margin:0;padding:0;background:#eef2f6;font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;color:#444444;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f6;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;margin:30px auto;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td align="center" style="background:linear-gradient(135deg, #007bff, #0056b3);padding:35px 20px;">
                            <p style="margin:0;color:#ffffff;font-size:18px;font-weight:bold;">Submission Confirmed!</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 40px 10px 40px;">
                            <p style="font-size:16px;line-height:1.7;margin:0 0 18px 0;">Hello <strong>[CLIENT_NAME]</strong>,</p>
                            <p style="font-size:16px;line-height:1.7;margin:0;">Thank you for submitting your application. We have received your submission and will review it shortly.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:25px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fbff;border:1px solid #d9e8ff;border-radius:10px;">
                                <tr>
                                    <td style="padding:25px;">
                                        <p style="margin:0 0 15px 0;font-size:14px;color:#555;"><strong>Submission Details:</strong></p>
                                        <p style="margin:5px 0;font-size:13px;"><strong>Submission ID:</strong> [SUBMISSION_ID]</p>
                                        <p style="margin:5px 0;font-size:13px;"><strong>Service:</strong> [SERVICE_NAME]</p>
                                        <p style="margin:5px 0;font-size:13px;"><strong>Amount:</strong> [TOTAL_PRICE]</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 25px 40px;">
                            <p style="margin:30px 0 0 0;font-size:16px;line-height:1.7;">Best regards,<br><strong>[COMPANY_NAME]</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background:#f3f5f7;padding:20px;font-size:12px;color:#8a8f98;line-height:1.6;">
                            <p style="margin:6px 0;">This is an automated confirmation email. Please do not reply to this message.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        
        // Insert quotation template
        $wpdb->insert(
            $table,
            [
                'template_name'    => 'Quotation Email',
                'template_slug'    => 'quotation_email',
                'template_subject' => 'Your Quotation Details - [SERVICE_NAME]',
                'template_html'    => $quotation_html,
                'template_css'     => '',
                'description'      => 'Email template for sending quotations to clients',
                'is_default'       => 1,
                'enabled'          => 1,
                'created_at'       => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
        );
        
        // Insert confirmation template
        $wpdb->insert(
            $table,
            [
                'template_name'    => 'Submission Confirmation',
                'template_slug'    => 'submission_confirmation',
                'template_subject' => 'Submission Confirmed - [SUBMISSION_ID]',
                'template_html'    => $confirmation_html,
                'template_css'     => '',
                'description'      => 'Email template for confirming submissions to clients',
                'is_default'       => 1,
                'enabled'          => 1,
                'created_at'       => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
        );
    }
}
