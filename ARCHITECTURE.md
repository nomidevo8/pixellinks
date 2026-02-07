# Dynamic Services Form Plugin - Complete Documentation

## Overview

The **Dynamic Services Form Plugin** is a production-ready WordPress plugin that enables you to create sophisticated, fully-featured multi-step forms with dynamic pricing, service management, and form submission tracking.

Built with:
- **OOP PHP** - Clean, maintainable class-based architecture
- **Custom Database Tables** - Dedicated database for services, packages, states, portals, and submissions
- **Vanilla JavaScript** - No jQuery dependency required (though jQuery is used for AJAX compatibility)
- **Responsive Design** - Mobile-friendly form and admin interface
- **WordPress Best Practices** - Security, performance, and extensibility

---

## Plugin Features

### ✅ Complete Feature Set

1. **4-Step Form Workflow**
   - Service Selection
   - Dynamic Pricing Options
   - Contact Information
   - Review & Submit

2. **Multiple Pricing Models**
   - **State-based**: Users select state + optional package
   - **Portal-based**: Users select multiple portals (additive pricing)
   - **Fixed price**: Single set price
   - **Calculator**: User inputs amount (custom formula)

3. **Admin Management Panel**
   - Services: Create, edit, delete services
   - Packages: Manage Standard/Premium options
   - States: Add states with per-state pricing
   - Portals: Add portals with individual prices
   - Submissions: View all form submissions

4. **Query Parameter Support**
   - Pre-select services via URL: `?service_type=usa&service_category=core&service_name=dba`
   - Automatically skips service selection step
   - Direct deep-linking to specific services

5. **Real-Time Price Calculation**
   - Updates as users make selections
   - AJAX-powered without page reloads
   - Visual price breakdown on review step

6. **Form Submission Tracking**
   - All submissions stored in database
   - Track business name, email, phone, entity type
   - Record total price and submission date
   - Submission status management

---

## Database Schema

### wp_dsf_services
```sql
CREATE TABLE wp_dsf_services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(100),              -- "USA", "UK", "Federal", etc.
    category VARCHAR(100),           -- "Core Company", "Banking", etc.
    name VARCHAR(255),               -- "DBA Fictitious Name", etc.
    pricing_model VARCHAR(50),       -- "state_based", "portal_based", "fixed_price", "calculator"
    has_packages TINYINT(1),         -- 1 if service has Standard/Premium packages
    description LONGTEXT,
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_packages
```sql
CREATE TABLE wp_dsf_packages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,               -- Foreign key to services
    package_type VARCHAR(100),       -- "Standard", "Premium", etc.
    price DECIMAL(10, 2),            -- NULL = Free
    description LONGTEXT,
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_states
```sql
CREATE TABLE wp_dsf_states (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,               -- Foreign key to services
    state_name VARCHAR(100),         -- "California", "Texas", etc.
    standard_price DECIMAL(10, 2),   -- Standard tier pricing
    premium_price DECIMAL(10, 2),    -- Premium tier pricing (if packages enabled)
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_portals
```sql
CREATE TABLE wp_dsf_portals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,               -- Foreign key to services
    portal_name VARCHAR(255),        -- "Secretary of State", "IRS", etc.
    price DECIMAL(10, 2),            -- NULL = Free
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_submissions
```sql
CREATE TABLE wp_dsf_submissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,               -- Foreign key to services
    form_data LONGTEXT,              -- JSON of all form selections
    total_price DECIMAL(10, 2),
    business_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    entity_type VARCHAR(100),
    notes LONGTEXT,
    status VARCHAR(50),              -- "pending", "processed", etc.
    created_at DATETIME,
    updated_at DATETIME
)
```

---

## Class Structure

### Plugin.php
Main plugin initialization class
- `activate()` - Creates database tables on activation
- `deactivate()` - Cleans up on deactivation
- `init()` - Initializes plugin components
- `enqueue_frontend_assets()` - Loads CSS/JS
- `render_form_shortcode()` - Renders form shortcode

### Database.php
Database management
- `create_tables()` - Creates all plugin tables
- `drop_tables()` - Removes plugin tables on uninstall
- `get_table()` - Returns prefixed table name

### Service.php
Service model
- `load($id)` - Load single service
- `get_by_identifier()` - Load by type/category/name
- `get_all()` - Get all services
- `save()` - Create or update service
- `delete()` - Remove service
- `get_full_data()` - Service with all related items

### Package.php
Package model (Standard/Premium)
- `load($id)`, `save()`, `delete()`
- `get_by_service()` - Get packages for a service

### State.php
State model (for state-based pricing)
- `load($id)`, `save()`, `delete()`
- `get_by_service()` - Get states for a service

### Portal.php
Portal model (for portal-based pricing)
- `load($id)`, `save()`, `delete()`
- `get_by_service()` - Get portals for a service

### Submission.php
Submission model
- `load($id)`, `save()`, `delete()`
- `get_by_service()`, `get_by_email()`, `get_by_status()`
- `update_status()` - Change submission status
- `get_total_revenue()` - Total sales
- `get_service_revenue()` - Revenue per service

### Form.php
Frontend form rendering
- `render()` - Main form HTML
- `get_pricing_options()` - Returns pricing step HTML
- Pricing renderers: `render_state_based_pricing()`, `render_portal_based_pricing()`, etc.

### Admin.php
Admin pages and management
- `register_menu()` - Sets up admin menu
- `page_services()`, `page_packages()`, `page_states()`, `page_portals()`, `page_submissions()`
- Form handling: `save_service()`, `delete_service()`, etc.

### Ajax.php
AJAX endpoint handlers
- `get_pricing_options()` - Returns pricing HTML
- `calculate_price()` - Calculates total price
- `get_review_summary()` - Returns review step HTML
- `submit_form()` - Processes form submission

---

## Frontend Workflow

### Step 1: Service Selection
```javascript
User selects service radio button
→ Service data stored in hidden fields
→ "Next" button validates selection
```

### Step 2: Pricing Options
```javascript
AJAX calls dsf_get_pricing_options
→ Renders appropriate pricing UI based on pricing_model
→ User makes selections
→ Real-time AJAX calculates price on change
→ Price displays at bottom
→ "Next" button validates selections
```

### Step 3: Contact Information
```javascript
User fills required fields:
- Business Name (required)
- Email (required)
- Phone (required)
- Entity Type (optional)
- Notes (optional)

Form has client-side validation
- Email format check
- Required field check
```

### Step 4: Review & Submit
```javascript
AJAX calls dsf_get_review_summary
→ Displays service and pricing summary
→ Shows contact info for review
→ Displays final total price
→ Submit button sends all form data
→ On success: Form hidden, success message shown
```

---

## Backend Workflow

### Form Submission (dsf_submit_form action)
```php
1. Nonce verification for security
2. Validate all required fields
3. Load service and verify it exists
4. Collect and sanitize form data
5. Calculate total price based on selections
6. Insert submission record into database
7. Fire custom hook: do_action('dsf_form_submitted', ...)
8. Return success/error to frontend
```

### Price Calculation Logic
```php
State-based:
  price = state.standard_price (or premium_price if package = "Premium")

Portal-based:
  price = sum(selected_portal.price for each selected portal)

Calculator:
  price = user_input_amount

Fixed-price:
  price = fixed_price (set in service config)
```

---

## Admin Workflow

### Adding a Service
1. Admin → Dynamic Services → Services → Add New
2. Fill form with service details
3. Select pricing model (determines available options)
4. Set "Has Packages" if Standard/Premium options needed
5. Click "Save Service"
6. Service created with enabled=1

### Setting Up Pricing
**For state_based services:**
1. Create packages (if has_packages=1)
2. Create states with standard_price and premium_price

**For portal_based services:**
1. Create portals with individual prices

**For fixed_price services:**
1. Set fixed price in service configuration

**For calculator services:**
1. Implement custom formula in Ajax.php

---

## AJAX Endpoints

All AJAX endpoints verify WordPress nonce for security.

### GET Pricing Options
```javascript
action: 'dsf_get_pricing_options'
POST data: {
  service_type: 'USA',
  service_category: 'Core Company',
  service_name: 'DBA Fictitious Name'
}
Returns: {
  success: true,
  data: {
    html: '<div>...</div>'  // Pricing UI
  }
}
```

### Calculate Price
```javascript
action: 'dsf_calculate_price'
POST data: {
  service_id: 1,
  state_id: 5,
  package_id: 2,
  portal_ids: [1, 3],
  calculator_amount: 100
}
Returns: {
  success: true,
  data: {
    total_price: 249.99,
    breakdown: { ... }
  }
}
```

### Get Review Summary
```javascript
action: 'dsf_get_review_summary'
POST data: {
  form_data: { ... }
}
Returns: {
  success: true,
  data: {
    html: '<div>...</div>'  // Review HTML
  }
}
```

### Submit Form
```javascript
action: 'dsf_submit_form'
POST data: {
  All form fields including:
  service_type, service_category, service_name,
  state, package, portals[], calculator_amount,
  business_name, email, phone, entity_type, notes
}
Returns: {
  success: true,
  data: {
    submission_id: 42,
    message: 'Form submitted successfully'
  }
}
```

---

## Query Parameters

Pre-select a service by passing URL parameters:

```
https://example.com/service-form/?service_type=USA&service_category=Core%20Company&service_name=DBA%20Fictitious%20Name
```

**Parameters:**
- `service_type` - Must match service type exactly
- `service_category` - Must match service category exactly
- `service_name` - Must match service name exactly

**Behavior:**
- If parameters are valid, form skips Step 1
- If parameters are invalid, Step 1 shows with error
- State parameters are auto-loaded from database via AJAX

---

## Hooks & Filters

### Actions

#### `dsf_form_submitted`
Fired after successful form submission.

```php
do_action('dsf_form_submitted', $submission_id, $service, $form_data, $total_price);

// Usage:
add_action('dsf_form_submitted', function($id, $service, $data, $price) {
    // Send email, webhook, etc.
    wp_mail('admin@example.com', 'New Service Request', 'Details: ...');
}, 10, 4);
```

---

## Extensibility

### Adding Custom Pricing Models

1. **Update Admin.php** - Add option to pricing_model select
2. **Update Form.php** - Add rendering method
3. **Update Ajax.php** - Add calculation logic

Example:
```php
// In Form.php
private function render_tiered_pricing(Service $service) {
    // Render custom pricing UI
}

// In Ajax.php calculate_total_price()
case 'tiered':
    // Calculate based on tiers
    break;
```

### Adding Custom Fields

1. Extend the contact information section in Form.php
2. Update form_data collection in JavaScript
3. Store in wp_dsf_submissions.form_data JSON

### Custom Submission Processing

Hook into `dsf_form_submitted` to:
- Send confirmation emails
- Trigger webhooks
- Create posts/custom post types
- Integrate with CRM

---

## Security Considerations

### Implemented Security Measures
- ✅ WordPress nonce verification on all AJAX calls
- ✅ Prepared statements for all database queries
- ✅ Input sanitization: sanitize_text_field, sanitize_email, sanitize_textarea_field
- ✅ Capability checks: `current_user_can('manage_options')`
- ✅ Escaped output: esc_html, esc_attr, esc_url, esc_textarea

### Best Practices
- Verify user inputs before database operations
- Use prepared statements for all queries
- Check nonces on form submissions
- Escape all output to prevent XSS
- Validate email addresses on backend
- Sanitize URLs and file paths

---

## Performance Optimization

### Database Optimization
- Foreign key relationships optimize queries
- Indexed fields: service_id, state_name, portal_name, email, status
- Efficient pagination for large submission lists

### Caching Opportunities
- Cache service list (invalidate on service update)
- Cache pricing options (invalidate on price update)

### Frontend Optimization
- Vanilla JS (minimal dependencies)
- CSS loaded only when needed
- AJAX for real-time updates (no page reloads)
- Responsive design optimized for mobile

---

## Troubleshooting

### Form Not Displaying
- Verify shortcode: `[dynamic_service_form]`
- Check plugin is activated and database tables were created
- Ensure at least one service exists and is enabled

### Prices Not Calculating
- Verify states/portals/packages have prices set
- Check items are marked as "enabled"
- Test AJAX endpoint directly

### Services Not Appearing
- Check service is enabled (enabled=1)
- Verify service type/category/name are correct
- Clear browser cache

### AJAX Errors
- Check browser console for JavaScript errors
- Verify nonce is being sent
- Check WordPress error logs

---

## File Locations

```
/wp-content/plugins/dynamic-services-form/

Main Files:
├── dynamic-services-form.php     # Entry point, 200 lines
├── uninstall.php                 # Cleanup on uninstall

Core Classes (includes/):
├── Plugin.php                    # Plugin initialization
├── Database.php                  # DB schema and management
├── Service.php                   # Service model
├── Package.php                   # Package model
├── State.php                     # State model
├── Portal.php                    # Portal model
├── Submission.php                # Submission model
├── Form.php                      # Frontend form rendering
├── Admin.php                     # Admin pages (1000+ lines)
├── Ajax.php                      # AJAX handlers
└── sample-data.php               # Optional sample data

Frontend Assets (assets/):
├── js/
│   ├── form.js                   # Form logic (400 lines)
│   └── admin.js                  # Admin logic
├── css/
│   ├── form.css                  # Frontend styles
│   └── admin.css                 # Admin styles

Documentation:
├── README.md                     # Full documentation
├── QUICKSTART.md                 # Quick setup guide
└── ARCHITECTURE.md (this file)   # Architecture details
```

---

## Statistics

- **Total Classes**: 9 (Plugin, Database, Service, Package, State, Portal, Submission, Form, Admin, Ajax)
- **Database Tables**: 5
- **Admin Pages**: 5 (Services, Packages, States, Portals, Submissions)
- **AJAX Endpoints**: 4
- **Form Steps**: 4
- **Pricing Models**: 4 (extensible to more)
- **Lines of Code**: ~3,500+ (production-ready)
- **CSS Lines**: ~600+
- **JavaScript Lines**: ~400+

---

## Getting Started

1. **Install**: Copy plugin folder to `/wp-content/plugins/`
2. **Activate**: Go to Plugins → Activate "Dynamic Services Form"
3. **Setup**: Create services, packages, states, portals in admin
4. **Deploy**: Add `[dynamic_service_form]` to page
5. **Test**: Fill out form and verify submission tracking
6. **Extend**: Use hooks and classes to customize further

---

## Next Steps

- ✅ Review database schema
- ✅ Understand class relationships
- ✅ Review AJAX workflow
- ✅ Test with sample data
- ✅ Customize CSS branding
- ✅ Integrate with email system
- ✅ Add custom pricing models
- ✅ Setup submission webhooks

---

## Support

For customization or issues:
1. Review class structure and methods
2. Check AJAX endpoints workflow
3. Look at sample-data initialization
4. Use WordPress hooks for extensions
5. All code is well-commented for easy modification

---

Version: 1.0.0
Last Updated: 2024
License: GPL v2 or later
