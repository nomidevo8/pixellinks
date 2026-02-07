# Dynamic Services Form Plugin - Complete Architecture Documentation

## Overview

The **Dynamic Services Form Plugin** is a production-ready WordPress plugin with **fully normalized database** architecture for creating sophisticated multi-step forms with dynamic pricing, location management, package handling, and comprehensive submission tracking.

Built with:
- **OOP PHP** - Clean, maintainable object-oriented architecture (11 classes)
- **Normalized Database Schema** - 7 tables with junction patterns (Locations & Packages stored once, linked to services)
- **Vanilla JavaScript** - No jQuery dependency (though jQuery compatible)
- **Responsive Design** - Mobile-first form and admin interface
- **WordPress Best Practices** - Security, performance, extensibility, and nonce verification

---

## Plugin Features

### ✅ Complete Feature Set

1. **4-Step Form Workflow**
   - Service Selection
   - Location & Pricing Selection
   - Contact Information (9 required fields)
   - Review & Submit

2. **Universal Pricing Toggle**
   - Single Price: `is_universal=1` - One price per location
   - Tiered Pricing: `is_universal=0` - Standard/Premium pricing
   - Toggle per location in admin

3. **Multiple Pricing Models**
   - **State-based**: Users select location + optional package
   - **Portal-based**: Users select multiple portals (additive)
   - **Fixed price**: Single set price
   - **Calculator**: User inputs amount

4. **9-Field Contact Form**
   - First Name, Last Name (required)
   - Business Name, Address, City, State, Zipcode (required)
   - Email, Phone (required)
   - Entity Type, Additional Notes (optional)

5. **Normalized Database Design**
   - Locations stored once, reused across services
   - Package types stored once, reused across services
   - Each service-location-pricing independently configured
   - No data duplication

6. **Admin Management Panel** (7 menus)
   - Services
   - Locations
   - Service Location Pricing (with universal price toggle)
   - Package Types
   - Service Package Pricing
   - Submissions
   - Settings

7. **Query Parameter Support**
   - Pre-select services via URL
   - Automatically skips service selection step
   - Direct deep-linking to specific services

8. **Real-Time Price Calculation**
   - Updates as users make selections
   - AJAX-powered without page reloads
   - Price breakdown on review step

9. **Form Submission Tracking**
   - All submissions with 11 fields stored
   - Track service, location, package, contact info
   - Submission status management

---

## Database Schema - 7 Normalized Tables

### wp_dsf_services
```sql
CREATE TABLE wp_dsf_services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(100),              -- "USA", "UK", "Federal"
    category VARCHAR(100),          -- "Core Company", "Banking"
    name VARCHAR(255),              -- "DBA Fictitious Name"
    pricing_model VARCHAR(50),      -- "state_based", "portal_based", "fixed_price", "calculator"
    description LONGTEXT,
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_locations (NEW - Stored Once, Reused)
```sql
CREATE TABLE wp_dsf_locations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),              -- "California", "Texas", "UK"
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_service_location_pricing (NEW - Junction Table)
```sql
CREATE TABLE wp_dsf_service_location_pricing (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,              -- Foreign key to services
    location_id BIGINT,             -- Foreign key to locations
    standard_price DECIMAL(10, 2),  -- Used for both universal and tiered
    premium_price DECIMAL(10, 2),   -- NULL if universal pricing
    is_universal TINYINT(1),        -- 1=single price, 0=tiered (Standard/Premium)
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE(service_id, location_id)
)
```

**Key Feature**: `is_universal` flag
- When 1: Show only one price (standard_price)
- When 0: Show Standard/Premium pricing

### wp_dsf_package_types (NEW - Stored Once, Reused)
```sql
CREATE TABLE wp_dsf_package_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),              -- "Standard", "Premium", "Enterprise"
    description LONGTEXT,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE(name)
)
```

### wp_dsf_service_package_pricing (NEW - Junction Table)
```sql
CREATE TABLE wp_dsf_service_package_pricing (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,              -- Foreign key to services
    package_type_id BIGINT,         -- Foreign key to package_types
    price DECIMAL(10, 2),
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE(service_id, package_type_id)
)
```

### wp_dsf_submissions
```sql
CREATE TABLE wp_dsf_submissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,
    location_id BIGINT,
    package_id BIGINT,
    first_name VARCHAR(255),        -- NEW
    last_name VARCHAR(255),         -- NEW
    business_name VARCHAR(255),
    business_address VARCHAR(255),  -- NEW
    city VARCHAR(255),              -- NEW
    state VARCHAR(255),             -- Address state, state/province -- NEW
    zipcode VARCHAR(20),            -- NEW
    email VARCHAR(255),
    phone VARCHAR(20),
    entity_type VARCHAR(100),       -- Optional
    notes LONGTEXT,
    total_price DECIMAL(10, 2),
    status VARCHAR(50),
    created_at DATETIME,
    updated_at DATETIME
)
```

### wp_dsf_portals (LEGACY - For portal-based pricing)
```sql
CREATE TABLE wp_dsf_portals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT,
    portal_name VARCHAR(255),
    price DECIMAL(10, 2),
    enabled TINYINT(1),
    created_at DATETIME,
    updated_at DATETIME
)
```

---

## Normalization Benefits

**Before (Old Schema - Data Duplication):**
```
Service "DBA Filing" had 15 state records:
- California: $149.99 / $199.99
- Texas: $99.99 / $149.99
- New York: $199.99 / $249.99
... (creating 15 rows for same service)

Service "EIN Filing" also had 15 state records:
- California: $199.99 / $249.99
- Texas: $149.99 / $199.99
... (duplication!)
```

**After (New Schema - Locations Stored Once):**
```
Locations table (created once):
- California
- Texas
- New York

Service "DBA Filing":
- California: $149.99 / $199.99
- Texas: $99.99 / $149.99
- New York: $199.99 / $249.99

Service "EIN Filing" (reuses same locations):
- California: $199.99 / $249.99
- Texas: $149.99 / $199.99

✅ No duplication, clean separation
```

---

## Class Structure (11 Classes)

### Plugin.php
Main plugin initialization
- `activate()` - Creates 7 database tables
- `deactivate()` - Cleanup on deactivation
- `init()` - Initializes components
- `enqueue_frontend_assets()` - Loads CSS/JS
- `render_form_shortcode()` - Renders form

### Database.php
Database schema definition (7 tables)
- `create_tables()` - Creates all tables with foreign keys
- `drop_tables()` - Removes all tables on uninstall
- `get_table()` - Returns prefixed table name

### Service.php
Service model
- `load($id)` - Load single service
- `get_by_identifier()` - Load by type/category/name
- `get_all()` - Get all services
- `save($args)` - Create/update service
- `delete($id)` - Remove service

### Location.php (NEW)
Location model (countries, states, regions)
- `load($id)` - Load single location
- `get_all()` - Get all locations
- `save($args)` - Create/update location
- `delete($id)` - Remove location
- `get_by_name($name)` - Get location by name

### ServiceLocationPricing.php (NEW - Junction)
Links services to locations with independent pricing
- `load($id)` - Load single pricing record
- `get_by_service_and_location($service_id, $location_id)` - Get specific link
- `get_by_service($service_id)` - Get all locations for service
- `get_all()` - Get all links
- `save($args)` - Create/update link with `is_universal` toggle
  - `is_universal` flag: 1=single price, 0=tiered pricing
  - `standard_price` used for both universal and base
  - `premium_price` NULL if universal
- `delete($id)` - Remove link

### PackageType.php (NEW)
Package type model (Standard, Premium, Enterprise, etc.)
- `load($id)`
- `get_all()`
- `save($args)`
- `delete($id)`
- `get_by_name($name)`

### ServicePackagePricing.php (NEW - Junction)
Links services to package types with independent pricing
- `load($id)`
- `get_by_service_and_package($service_id, $package_type_id)`
- `get_by_service($service_id)` - Get all package options for service
- `save($args)` - Create/update link with price
- `delete($id)`

### Form.php
Frontend form rendering (4-step form with 9 fields)
- `render()` - Main form boilerplate
- `render_step_1_service_selection()` - Service selection
- `render_step_2_pricing()` - Location selection + pricing
- `render_step_3_contact()` - 9 contact information fields
- `render_step_4_review()` - Review and submit
- `render_state_based_pricing()` - Location dropdown + package selection
- `render_portal_based_pricing()` - Portal checkboxes
- Helper methods for pricing rendering

### Admin.php (Largest - 7 Menus)
Admin pages and management (1700+ lines)
- `register_menus()` - Sets up 7 admin menus
- **Services**: `page_services()`, `save_service()`, `delete_service()`
- **Locations**: `page_locations()`, `save_location()`, `delete_location()`
- **Service Location Pricing**: `page_service_location_pricing()`, `save_service_location_pricing()`, `delete_service_location_pricing()`
  - Form includes universal price checkbox with JavaScript toggle
  - Lists show "Universal" or "Tiered" badge
- **Package Types**: `page_package_types()`, `save_package_type()`, `delete_package_type()`
- **Service Package Pricing**: `page_service_package_pricing()`, `save_service_package_pricing()`, `delete_service_package_pricing()`
- **Submissions**: `page_submissions()` - View all 9-field submissions
- **Settings**: `page_settings()` - Plugin configuration

### Ajax.php
AJAX endpoint handlers
- `get_pricing_options()` - Returns pricing UI HTML
- `calculate_price()` - Calculates price (updated for location_id)
- `get_review_summary()` - Returns review HTML
- `submit_form()` - Processes submission
  - Validates all 9 required fields
  - Stores all 11 fields (9 required + 2 optional)
  - Calculates total price
  - Fires custom hook: `dsf_form_submitted`

---

## Frontend Workflow

### Step 1: Service Selection
```javascript
User selects service radio button
→ Service data stored in hidden fields
→ "Next" button validates selection
```

### Step 2: Location & Pricing Selection
```javascript
AJAX calls dsf_get_pricing_options
→ Renders location dropdown (location_id, not state)
→ Checks is_universal flag:
  - If universal: Shows single price field
  - If tiered: Shows Standard/Premium price fields
→ Optional package selection (if service has packages)
→ Real-time AJAX price calculation on change
→ Price displays at bottom
→ "Next" button validates selections
```

### Step 3: Contact Information (9 Required + 2 Optional)
```javascript
User fills form fields:

REQUIRED (9 fields):
- First Name
- Last Name
- Business Name
- Business Address
- City
- State/Province
- Zipcode
- Email
- Phone

OPTIONAL (2 fields):
- Entity Type (dropdown)
- Additional Notes (textarea)

Client-side and server-side validation:
- Email format check
- Required field check
- Phone format (basic)
```

### Step 4: Review & Submit
```javascript
AJAX calls dsf_get_review_summary
→ Displays service and location selected
→ Shows package selected (if applicable)
→ Shows contact info for review
→ Displays final total price
→ Submit button sends all 11 fields + pricing
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

Main Entry:
├── dynamic-services-form.php     # Plugin initialization, 200 lines
├── uninstall.php                 # Cleanup on uninstall

Core Classes (includes/) - 11 Classes:
├── Plugin.php                    # Main plugin class
├── Database.php                  # 7-table schema definition
├── Service.php                   # Service model
├── Location.php                  # Location model (NEW)
├── ServiceLocationPricing.php    # Service-Location junction (NEW)
├── PackageType.php               # Package type model (NEW)
├── ServicePackagePricing.php     # Service-Package junction (NEW)
├── Form.php                      # Frontend form (4-step, 9 fields)
├── Admin.php                     # Admin interface (7 menus, 1700+ lines)
├── Ajax.php                      # AJAX handlers
└── sample-data.php               # Optional test data

Frontend Assets (assets/):
├── js/
│   ├── form.js                   # Multi-step form logic (500 lines)
│   └── admin.js                  # Admin enhancements
├── css/
│   ├── form.css                  # Frontend styles (600 lines)
│   └── admin.css                 # Admin styles

Documentation:
├── README.md                     # Full documentation
├── QUICKSTART.md                 # Quick start guide
├── ARCHITECTURE.md (this file)   # Architecture details
├── CUSTOMIZATION.md              # Advanced customization
└── aboutplugin.md                # Feature overview
```

---

## Statistics

- **Total Classes**: 11 (Plugin, Database, Service, Location, ServiceLocationPricing, PackageType, ServicePackagePricing, Form, Admin, Ajax + sample-data)
- **Database Tables**: 7 (normalized schema)
- **Admin Pages**: 7 (Services, Locations, Service Location Pricing, Package Types, Service Package Pricing, Submissions, Settings)
- **AJAX Endpoints**: 4 (get_pricing_options, calculate_price, get_review_summary, submit_form)
- **Form Steps**: 4
- **Contact Form Fields**: 9 required + 2 optional
- **Pricing Models**: 4 (extensible)
- **Lines of Code**: ~4,500+ (production-ready)
- **CSS Lines**: ~600+
- **JavaScript Lines**: ~500+
- **Database Design**: Fully normalized with junction tables

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
