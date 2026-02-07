# Dynamic Services Form Plugin

A production-ready WordPress plugin with **fully normalized database architecture** for building dynamic multi-step service forms with flexible pricing, automatic price calculations, location management, and complete submission tracking.

## 🎯 Key Features

- **💾 Normalized Database** - Locations & packages stored once, linked to services with independent pricing
- **🔘 Universal Price Toggle** - Single price for non-tiered services, tiered pricing for premium offerings  
- **📝 9-Field Contact Form** - Comprehensive information collection (first/last name, address, email, phone, etc.)
- **4️⃣ 4-Step Workflow** - Service Selection → Pricing → Contact Info → Review & Submit
- **💹 4 Pricing Models** - State-based, Portal-based, Fixed, Calculator (easily extensible)
- **⚡ Real-Time Pricing** - AJAX-powered dynamic price updates as users make selections
- **🔗 Query Parameters** - Pre-select services via URL to skip service selection step
- **🛠️ Admin Panel** - 7 management menus for complete setup and monitoring
- **📊 Submissions Tracking** - All submissions with 9+ contact fields stored for CRM integration
- **🔐 Production Ready** - Security, performance, and extensibility built-in

## 📥 Installation & Setup

### 1. **Manual Installation**
- Unzip plugin folder to `/wp-content/plugins/`
- Go to Plugins menu in WordPress admin
- Click "Activate" on "Dynamic Services Form"
- Database tables created automatically

### 2. **Database Tables Created**
Plugin automatically creates 7 normalized tables:
- `wp_dsf_services` - Service definitions
- `wp_dsf_locations` - Geographic regions (states, countries, etc.)  
- `wp_dsf_service_location_pricing` - Service-to-location links with independent pricing
- `wp_dsf_package_types` - Package definitions
- `wp_dsf_service_package_pricing` - Service-to-package links with independent pricing
- `wp_dsf_submissions` - Form submissions with 11 fields

### 3. **Add Form to Page/Post**
```
[dynamic_services_form]
```

## 🛠️ Admin Configuration

Once activated, access 7 management menus under **Dynamic Services Form** in WordPress admin:

### 1. **Services** - Define Service Offerings
1. Go to **Dynamic Services Form** → **Services**
2. Click "Add New Service"
3. Fill in:
   - **Service Type**: USA, UK, Federal, etc.
   - **Category**: Core Company, Banking Finance, etc.
   - **Name**: DBA Fictitious Name, EIN with IRS, etc.
   - **Pricing Model**: state_based, portal_based, fixed_price, calculator
4. Click "Save Service"

### 2. **Locations** - Set Geographic Regions
1. Go to **Dynamic Services Form** → **Locations**
2. Click "Add New Location"
3. Add states, countries, or regions (e.g., "California", "Texas", "UK Region")
4. Click "Save Location"

### 3. **Service Location Pricing** - Link Services to Locations with Pricing
1. Go to **Dynamic Services Form** → **Service Location Pricing**
2. Click "Add New Price Link"
3. Select Service and Location
4. **Check "Use Universal Price?"** if service has no packages (single price)
   - Shows single "Universal Price" field
5. **Uncheck if tiered pricing** (Standard/Premium packages)
   - Shows "Standard Price" and "Premium Price" fields
6. Click "Save Pricing"

### 4. **Package Types** - Define Pricing Tiers
1. Go to **Dynamic Services Form** → **Package Types**
2. Create package types: Standard, Premium, Enterprise, etc.
3. Each service can link to multiple package types with different pricing

### 5. **Service Package Pricing** - Link Services to Packages  
1. Go to **Dynamic Services Form** → **Service Package Pricing**
2. Link services to package types with independent pricing
3. Same service can have different prices for different packages

### 6. **Submissions** - View Form Submissions
1. Go to **Dynamic Services Form** → **Submissions**
2. View all form submissions with:
   - Submission date/time
   - All 9 contact fields (first/last name, address, city, state, zipcode)
   - Selected service and location
   - Total price
   - Submission status

### Viewing Submissions

1. Go to **Dynamic Services** → **Submissions**
2. View all form submissions with:
   - Submission date and time
   - Business name and contact info
   - Selected service
   - Total price
   - Submission status

## Query Parameters

Pre-select a service by using URL query parameters:

```
example.com/service-form/?service_type=usa&service_category=core-company&service_name=dba-fictitious-name
```

This will skip Step 1 (Service Selection) and jump directly to pricing options.

## Form Workflow

### Step 1: Service Selection
- Shows hierarchically grouped services (Type → Category → Name)
- Hidden if URL parameters are provided
- Required to proceed

### Step 2: Pricing & Options
- Content varies based on pricing model:
  - **State-based**: State dropdown + optional package selection
  - **Portal-based**: Multi-select checkboxes for portals
  - **Fixed Price**: Display only
  - **Calculator**: User input field
- Real-time price calculation as selections change

### Step 3: Contact Information
Collects 9 required + 2 optional fields:
- **First Name** (required)
- **Last Name** (required)
- **Business Name** (required)
- **Business Address** (required)
- **City** (required)
- **State/Province** (required)
- **Zipcode** (required)
- **Email** (required)
- **Phone** (required)
- **Entity Type** (optional dropdown)
- **Additional Notes** (optional textarea)

### Step 4: Review & Submit
- Summary of selected service and pricing
- Display of contact information
- Total price display
- Submit button

## 📊 Database Architecture

### Normalized Design (7 Tables)

**wp_dsf_services**
- Core service definitions
- Fields: type, category, name, pricing_model, description, enabled

**wp_dsf_locations**
- Geographic regions (stored once, reused across services)
- Fields: name (e.g., "California", "Texas", "UK")

**wp_dsf_service_location_pricing** (Junction Table)
- Links services to locations with **independent pricing**
- Fields: service_id, location_id, standard_price, premium_price, `is_universal` (0=tiered, 1=single price)
- **Key Feature**: Same location can have different prices for different services
- **Key Feature**: `is_universal` flag toggles single vs. tiered pricing

**wp_dsf_package_types**
- Package definitions (stored once, reused across services)
- Fields: name (Standard, Premium, Enterprise, etc.), description

**wp_dsf_service_package_pricing** (Junction Table)
- Links services to package types with **independent pricing**
- Fields: service_id, package_type_id, price
- **Key Feature**: Same package type can cost differently per service

**wp_dsf_submissions**
- Form submissions with comprehensive contact data
- Fields: service_id, location_id, package_id, first_name, last_name, business_name, business_address, city, state, zipcode, email, phone, entity_type, notes, total_price, status, created_at

### Why Normalized?

**Before Normalization (Data Duplication Problem):**
- Location data repeated for each service
- Package data repeated for each service
- Storage inefficient, updates prone to errors

**After Normalization (Current):**
- Locations stored once → linked to services
- Package types stored once → linked to services  
- Each link has independent pricing
- Efficient, scalable, no duplication

## Shortcode

```
[dynamic_service_form]
```

Attributes (future enhancement):
```
[dynamic_service_form service_type="usa" service_category="core-company"]
```

## 📁 File Structure

```
dynamic-services-form/
├── dynamic-services-form.php      # Main plugin file
├── includes/
│   ├── Plugin.php                 # Core plugin class
│   ├── Database.php               # Database schema (7 tables)
│   ├── Service.php                # Service model
│   ├── Location.php               # Location model (NEW)
│   ├── ServiceLocationPricing.php # Service-Location junction (NEW)
│   ├── PackageType.php            # Package type model (NEW)
│   ├── ServicePackagePricing.php  # Service-Package junction (NEW)
│   ├── Form.php                   # Frontend form (4-step, 9 fields)
│   ├── Admin.php                  # Admin (7 menus, 1700+ lines)
│   ├── Ajax.php                   # AJAX endpoints
│   └── sample-data.php            # Optional test data
├── assets/
│   ├── js/
│   │   ├── form.js                # Multi-step form logic
│   │   └── admin.js               # Admin UI enhancements
│   └── css/
│       ├── form.css               # Frontend styles
│       └── admin.css              # Admin styles
└── documentation/
    ├── README.md
    ├── QUICKSTART.md
    ├── ARCHITECTURE.md
    ├── CUSTOMIZATION.md
    └── aboutplugin.md
```

## AJAX Endpoints

All AJAX calls use WordPress nonce for security:

### dsf_get_pricing_options
- Gets pricing options for a service
- Parameters: service_type, service_category, service_name or service_id
- Returns: HTML for pricing step

### dsf_calculate_price
- Calculates total price based on selections
- Parameters: service_id, state_id, package_id, portal_ids[], calculator_amount
- Returns: total_price, breakdown

### dsf_get_review_summary
- Gets review summary HTML
- Parameters: form_data
- Returns: HTML for review step

### dsf_submit_form
- Submits the form
- Parameters: All form fields
- Returns: submission_id, success message

## ⚙️ Customization & Extension

### Adding a New Pricing Model

1. **Update Admin.php** - Add option to pricing model dropdown
2. **Update Form.php** - Add rendering method (e.g., `render_mynewmodel_pricing()`)
3. **Update Ajax.php** - Add calculation in `calculate_total_price()` method
4. Create form fields that collect needed data
5. Handle AJAX calculation and response

### Using WordPress Hooks

```php
// Fire custom action after successful submission
do_action('dsf_form_submitted', $submission_id, $service, $form_data, $total_price);

// Example: Send to email or CRM
add_action('dsf_form_submitted', function($submission_id, $service, $form_data, $total_price) {
    // Your custom logic here
    error_log('Form submitted: ' . $submission_id);
}, 10, 4);
```

### Modifying the Universal Price Feature

The `is_universal` flag in `ServiceLocationPricing` controls pricing mode:
```php
// When is_universal = 1 (checked):
// Single price shown (standard_price field only)

// When is_universal = 0 (unchecked):
// Tiered pricing shown (standard_price + premium_price fields)
```

Edit the toggle in **Service Location Pricing** admin page.

### Styling Customization

- Frontend form styles: `assets/css/form.css`
- Admin styles: `assets/css/admin.css`  
- Custom JavaScript: `assets/js/form.js` (multi-step logic)
- Override styles via child theme or custom CSS

### Adding Custom Form Fields

To add fields beyond the 9 contact fields:

1. **Add column to submissions table** in `Database.php`
2. **Add input field** in `Form.php` Step 3
3. **Update validation** in `Ajax.php` `submit_form()` method
4. **Update form data collection** in `form.js` `collectFormData()` function

## 🔐 Security

- **Nonce Verification** - All AJAX calls protected with WordPress nonces
- **Input Sanitization** - All user inputs sanitized and validated
- **Prepared Statements** - All database queries use wpdb prepared statements
- **Capability Checks** - Admin pages require `manage_options` capability
- **CSRF Protection** - Form submissions protected against cross-site requests
- **XSS Protection** - All output properly escaped

## ⚡ Performance

- **Optimized Queries** - Database queries use proper indexing
- **Efficient AJAX** - Minimal server calls with efficient data transfer
- **Selective Loading** - CSS/JS enqueued only on necessary pages
- **Scalability** - Handles unlimited services/locations/packages
- **Caching Ready** - Compatible with WordPress object caching

## Viewing Submissions

1. Go to **Dynamic Services Form** → **Submissions**
2. View comprehensive submission data:
   - First/Last Name
   - Business Name & Address
   - City, State, Zipcode
   - Email & Phone
   - Selected Service & Location
   - Package selected (if applicable)
   - Total Price
   - Submission Date/Time
   - Status

## 🔝 License

GPL v2 or later. See LICENSE file for details.

## 📄 Documentation

For detailed documentation, see:
- [QUICKSTART.md](QUICKSTART.md) - Quick start guide
- [ARCHITECTURE.md](ARCHITECTURE.md) - Detailed architecture & API
- [CUSTOMIZATION.md](CUSTOMIZATION.md) - Advanced customization
- [aboutplugin.md](aboutplugin.md) - Feature overview

## 📦 Changelog

### 2.0.0 - Database Normalization Release
- **NEW**: Location table - Geographic regions stored once
- **NEW**: ServiceLocationPricing junction - Service-location links with independent pricing
- **NEW**: PackageType table - Package definitions stored once
- **NEW**: ServicePackagePricing junction - Service-package links with independent pricing
- **NEW**: Universal Price Toggle - `is_universal` flag for single vs. tiered pricing
- **NEW**: 9-Field Contact Form - Comprehensive contact information collection (first/last name, address, city, state, zipcode)
- **ENHANCED**: Admin interface - 7 menus instead of 5
- **ENHANCED**: Database schema - 7 normalized tables instead of 5
- **IMPROVED**: Model classes - 11 classes instead of 9
- **FIXED**: Form field naming conflict (location dropdown renamed to location_id)
- **UPDATED**: AJAX handlers for normalized structure
- **UPDATED**: Sample data for new architecture

### 1.0.0 - Initial Release
- Core form functionality
- Service pricing models
- Multi-step form workflow
- Admin management interface
- Basic submission tracking
