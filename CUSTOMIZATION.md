# Dynamic Services Form Plugin - Customization Examples

This file contains code snippets and examples for customizing behavior, integrations, and hooks. Examples assume the plugin is active and the `dsf_form_submitted` action and `dsf_calculate_total_price` filter are available.

## Code Examples for Common Tasks

### 1. Send Email on Form Submission

Add to your theme's `functions.php` or create a custom plugin:

```php
<?php
// Send email when form is submitted
add_action('dsf_form_submitted', function($submission_id, $service, $form_data, $total_price) {
    // Get form data (9 required + 2 optional fields)
    $first_name = $form_data['first_name'] ?? '';
    $last_name = $form_data['last_name'] ?? '';
    $business_name = $form_data['business_name'] ?? '';
    $business_address = $form_data['business_address'] ?? '';
    $city = $form_data['city'] ?? '';
    $state = $form_data['state'] ?? '';
    $zipcode = $form_data['zipcode'] ?? '';
    $email = $form_data['email'] ?? '';
    $phone = $form_data['phone'] ?? '';
    
    // Build email content
    $subject = 'Service Request: ' . $service->get('name');
    $message = sprintf(
        "New service request submitted:\n\n" .
        "Name: %s %s\n" .
        "Business: %s\n" .
        "Address: %s, %s %s %s\n" .
        "Email: %s\n" .
        "Phone: %s\n" .
        "Service: %s\n" .
        "Total Price: \$%.2f\n",
        $first_name,
        $last_name,
        $business_name,
        $business_address,
        $city,
        $state,
        $zipcode,
        $email,
        $phone,
        $service->get('name'),
        $total_price
    );
    
    // Send to admin
    wp_mail(get_option('admin_email'), $subject, $message);
    
    // Send confirmation to user
    wp_mail($email, 'We received your request', 'Thank you for your submission. We will contact you soon.');
}, 10, 4);
?>
```

### 2. Send Webhook on Submission

```php
<?php
// Send webhook to external service
add_action('dsf_form_submitted', function($submission_id, $service, $form_data, $total_price) {
    $payload = [
        'submission_id' => $submission_id,
        'service_name' => $service->get('name'),
        'business_name' => $form_data['business_name'] ?? '',
        'email' => $form_data['email'] ?? '',
        'phone' => $form_data['phone'] ?? '',
        'total_price' => $total_price,
        'timestamp' => current_time('mysql'),
    ];
    
    // Send to external webhook
    wp_remote_post('https://example.com/webhook/submissions', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode($payload),
    ]);
}, 10, 4);
?>
```

### 3. Create Post from Submission

```php
<?php
// Create WordPress post when form is submitted
add_action('dsf_form_submitted', function($submission_id, $service, $form_data, $total_price) {
    $post_id = wp_insert_post([
        'post_title' => $form_data['business_name'] ?? 'Submission #' . $submission_id,
        'post_type' => 'service_request', // Create custom post type
        'post_status' => 'pending',
        'post_content' => sprintf(
            "Service: %s\nLocation: %s\nPrice: \$%.2f\n\nContact:\nName: %s %s\nEmail: %s\nPhone: %s\nAddress: %s, %s %s %s",
            $service->get('name'),
            $form_data['location'] ?? '',
            $total_price,
            $form_data['first_name'] ?? '',
            $form_data['last_name'] ?? '',
            $form_data['email'] ?? '',
            $form_data['phone'] ?? '',
            $form_data['business_address'] ?? '',
            $form_data['city'] ?? '',
            $form_data['state'] ?? '',
            $form_data['zipcode'] ?? ''
        ),
    ]);
    
    // Add post meta
    update_post_meta($post_id, '_submission_id', $submission_id);
    update_post_meta($post_id, '_service_id', $service->get_id());
    update_post_meta($post_id, '_total_price', $total_price);
    update_post_meta($post_id, '_form_data', $form_data);
}, 10, 4);
?>
```

### 4. Integrate with ActiveCampaign CRM

```php
<?php
// Sync submission to ActiveCampaign with new 9-field form
add_action('dsf_form_submitted', function($submission_id, $service, $form_data, $total_price) {
    $ac_api = 'https://YOUR_ACCOUNT.activehosted.com/api/3';
    $ac_key = 'YOUR_API_KEY'; // Store in wp-config.php
    
    // Create contact with all new fields
    $response = wp_remote_post($ac_api . '/contacts', [
        'headers' => [
            'Api-Token' => $ac_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'contact' => [
                'firstName' => $form_data['first_name'] ?? '',
                'lastName' => $form_data['last_name'] ?? '',
                'email' => $form_data['email'] ?? '',
                'phone' => $form_data['phone'] ?? '',
                'fieldValues' => [
                    ['field' => 1, 'value' => $form_data['business_name'] ?? ''],
                    ['field' => 2, 'value' => $form_data['business_address'] ?? ''],
                    ['field' => 3, 'value' => $form_data['city'] ?? ''],
                    ['field' => 4, 'value' => $form_data['state'] ?? ''],
                    ['field' => 5, 'value' => $form_data['zipcode'] ?? ''],
                    ['field' => 6, 'value' => $service->get('name')],
                    ['field' => 7, 'value' => (string) $total_price],
                ],
            ],
        ]),
    ]);
}, 10, 4);
?>
```

### 5. Add Custom Static Fields

**1. Extend the form in Form.php:**

```php
private function render_static_fields() {
    // ... existing fields ...
    
    // Add custom field
    ?>
    <div class="dsf-field-group">
        <label for="dsf-company-website">
            <?php esc_html_e('Company Website', 'dynamic-services-form'); ?>
        </label>
        <input type="url" id="dsf-company-website" name="company_website">
    </div>
    <?php
}
```

**2. Update JavaScript in form.js to collect it:**

```javascript
collectFormData: function() {
    return {
        // ... existing fields ...
        company_website: $('#dsf-company-website').val(),
    };
}
```

**3. Handle in Ajax.php:**

```php
$form_data = [
    // ... existing ...
    'company_website' => isset($_POST['company_website']) ? sanitize_url($_POST['company_website']) : '',
];
```

### 6. Customize Pricing Calculation

**Override the calculate_total_price method:**

```php
// Add this to your theme's functions.php
add_filter('dsf_calculate_total_price', function($price, $service_id, $form_data) {
    // Custom pricing logic
    if ($service_id === 5) { // Specific service
        // Apply discount for certain packages
        if (in_array('premium', $form_data['packages'] ?? [])) {
            $price = $price * 0.9; // 10% discount
        }
    }
    return $price;
}, 10, 3);
```

### 7. Programmatically Create Service with Locations

```php
<?php
// Create service with locations (NEW normalized structure)
$service_id = \DSF\Service::save([
    'type' => 'USA',
    'category' => 'Core Company',
    'name' => 'LLC Formation',
    'pricing_model' => 'state_based',
    'description' => 'Form LLC in any US state',
    'enabled' => 1,
]);

// Create or get locations
$ca_location_id = \DSF\Location::save(['name' => 'California']);
$tx_location_id = \DSF\Location::save(['name' => 'Texas']);

// Link service to locations with pricing (is_universal = 1 for single price)
\DSF\ServiceLocationPricing::save([
    'service_id' => $service_id,
    'location_id' => $ca_location_id,
    'standard_price' => 149.99,
    'premium_price' => null,
    'is_universal' => 1, // Single price, not tiered
    'enabled' => 1,
]);

\DSF\ServiceLocationPricing::save([
    'service_id' => $service_id,
    'location_id' => $tx_location_id,
    'standard_price' => 99.99,
    'premium_price' => null,
    'is_universal' => 1,
    'enabled' => 1,
]);

// OR with tiered pricing (is_universal = 0)
\DSF\ServiceLocationPricing::save([
    'service_id' => $service_id,
    'location_id' => $ca_location_id,
    'standard_price' => 149.99,
    'premium_price' => 199.99,
    'is_universal' => 0, // Tiered: Standard/Premium
    'enabled' => 1,
]);

// Create package types (stored once, shared)
$standard_package_id = \DSF\PackageType::save(['name' => 'Standard']);
$premium_package_id = \DSF\PackageType::save(['name' => 'Premium']);

// Link service to package types
\DSF\ServicePackagePricing::save([
    'service_id' => $service_id,
    'package_type_id' => $standard_package_id,
    'price' => 99.99,
    'enabled' => 1,
]);

\DSF\ServicePackagePricing::save([
    'service_id' => $service_id,
    'package_type_id' => $premium_package_id,
    'price' => 149.99,
    'enabled' => 1,
]);
?>
```

**Key Features:**
- `is_universal` flag toggles single price (1) vs tiered pricing (0)
- Locations stored once in `wp_dsf_locations`, reused across services
- Same location can have different prices for different services
- Package types stored once, reused across services

### 8. Query Submissions Programmatically

```php
<?php
// Get all submissions
$submissions = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}dsf_submissions ORDER BY created_at DESC"
);

// Get submissions for specific service
$service_submissions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dsf_submissions WHERE service_id = %d",
        $service_id
    )
);

// Get by location
$location_submissions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dsf_submissions WHERE location_id = %d",
        $location_id
    )
);

// Get by email
$user_submissions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dsf_submissions WHERE email = %s",
        $email
    )
);

// Get recent submissions with all 9 contact fields
foreach ($submissions as $submission) {
    echo $submission->first_name . ' ' . $submission->last_name . '\n';
    echo $submission->business_address . ', ' . $submission->city . ', ' . $submission->state . ' ' . $submission->zipcode . '\n';
    echo $submission->email . ' | ' . $submission->phone . '\n';
    echo 'Total: $' . $submission->total_price . '\n\n';
}
?>
```

### 9. Modify Admin Table Columns

**Hook into Admin.php or add custom code:**

```php
<?php
// Add custom column to submissions table
add_filter('dsf_submission_columns', function($columns) {
    $columns['phone'] = 'Phone';
    $columns['entity_type'] = 'Entity Type';
    return $columns;
});
?>
```

### 10. Custom Frontend Styling

**Add to your theme's style.css:**

```css
/* Override form colors */
.dsf-form {
    background-color: #f5f5f5;
    border: 2px solid #007bff;
}

.dsf-btn-next,
.dsf-btn-submit {
    background-color: #007bff !important;
}

.dsf-btn-next:hover,
.dsf-btn-submit:hover {
    background-color: #0056b3 !important;
}

/* Custom pricing box */
.dsf-price-total {
    background-color: #e7f3ff;
    border-left-color: #007bff;
}

/* Custom step styling */
.dsf-step h2 {
    color: #333;
    border-bottom-color: #007bff;
}
```

### 11. Add Price Tiers Based on Quantity

**Create custom calculator pricing:**

```php
<?php
// In Ajax.php calculate_total_price()
case 'calculator':
    $amount = (float) ($form_data['calculator_amount'] ?? 0);
    
    // Tiered pricing: first 100 = $1, 101-500 = $0.75, 500+ = $0.50
    if ($amount <= 100) {
        $total_price = $amount * 1.00;
    } elseif ($amount <= 500) {
        $total_price = (100 * 1.00) + (($amount - 100) * 0.75);
    } else {
        $total_price = (100 * 1.00) + (400 * 0.75) + (($amount - 500) * 0.50);
    }
    break;
?>
```

### 12. Add Coupon/Discount Code

**Extend the form:**

```php
// In Form.php Step 3
<div class="dsf-field-group">
    <label for="dsf-coupon">
        <?php esc_html_e('Coupon Code (Optional)', 'dynamic-services-form'); ?>
    </label>
    <input type="text" id="dsf-coupon" name="coupon_code" placeholder="e.g., SAVE10">
</div>
```

**In JavaScript form.js:**

```javascript
calculatePrice: function() {
    // ... existing code ...
    
    // Add coupon code to calculation
    const couponCode = $('#dsf-coupon').val();
    if (couponCode) {
        data.coupon_code = couponCode;
    }
}
```

**In Ajax.php:**

```php
// Calculate discount based on coupon
$coupon = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
if ($coupon === 'SAVE10') {
    $total_price = $total_price * 0.9; // 10% discount
}
```

### 13. Add Terms & Conditions

```php
// In Form.php Step 3
<div class="dsf-field-group">
    <label>
        <input type="checkbox" name="accept_terms" required>
        <?php esc_html_e('I agree to the Terms & Conditions', 'dynamic-services-form'); ?>
        <a href="/terms/" target="_blank"><?php esc_html_e('View', 'dynamic-services-form'); ?></a>
    </label>
</div>
```

**In JavaScript form.js validateContactInfo():**

```javascript
if (!$('[name="accept_terms"]:checked').length) {
    alert('Please accept the terms and conditions');
    return false;
}
```

### 14. Send Submissions to Slack

```php
<?php
add_action('dsf_form_submitted', function($submission_id, $service, $form_data, $total_price) {
    $slack_webhook = 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL';
    
    wp_remote_post($slack_webhook, [
        'body' => wp_json_encode([
            'text' => '📋 New Service Request',
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => sprintf(
                            "💼 *%s*\n📊 Service: %s\n💰 Price: $%.2f\n📧 Email: %s\n📞 Phone: %s",
                            $form_data['business_name'] ?? 'N/A',
                            $service->get('name'),
                            $total_price,
                            $form_data['email'] ?? 'N/A',
                            $form_data['phone'] ?? 'N/A'
                        ),
                    ],
                ],
            ],
        ]),
    ]);
}, 10, 4);
?>
```

### 15. Dynamically Load Services from External API

```php
<?php
// Load services from remote API on plugin init
add_action('dsf_before_form_render', function() {
    $api_response = wp_remote_get('https://api.example.com/services');
    if (!is_wp_error($api_response)) {
        $data = json_decode(wp_remote_retrieve_body($api_response), true);
        
        foreach ($data['services'] as $service_data) {
            \DSF\Service::save([
                'type' => $service_data['type'] ?? '',
                'category' => $service_data['category'] ?? '',
                'name' => $service_data['name'] ?? '',
                'pricing_model' => $service_data['pricing_model'] ?? 'fixed_price',
                'has_packages' => $service_data['has_packages'] ?? 0,
                'enabled' => 1,
            ]);
        }
    }
});
?>
```

---

## Testing the Plugin

### Manual Testing Checklist

- [ ] Form displays at shortcode location
- [ ] Service selection works
- [ ] State dropdown shows all states
- [ ] Package selection updates price
- [ ] Portal selection is multi-select
- [ ] Price updates in real-time
- [ ] Contact fields validate
- [ ] Review step shows all info
- [ ] Form submission succeeds
- [ ] Submission appears in admin
- [ ] AJAX endpoints work (check console)
- [ ] Mobile responsive design works
- [ ] Keyboard navigation works

### Database Testing

```php
// Check created tables
global $wpdb;
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}dsf_%'");
echo count($tables) . ' tables created';

// Check sample data
$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dsf_services");
echo count($services) . ' services found';
```

---

These examples should cover most customization needs. The plugin is designed to be extensible and maintainable!
