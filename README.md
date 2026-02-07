# Dynamic Services Form Plugin

A comprehensive, scalable WordPress plugin for building fully dynamic multi-step forms with service pricing, state/portal selection, and package management.

## Features

- **OOP Architecture** - Clean, maintainable PHP classes for extensibility
- **Custom Database Tables** - Dedicated tables for services, packages, states, portals, and submissions
- **Multi-Step Forms** - 4-step form workflow (Service Selection → Pricing → Contact Info → Review & Submit)
- **Dynamic Pricing Models**:
  - State-based pricing (with optional Standard/Premium packages)
  - Portal-based pricing (multi-select portals)
  - Fixed pricing
  - Calculator-based pricing
- **Query Parameter Support** - Pre-select services via URL: `?service_type=usa&service_category=core-company&service_name=dba-fictitious-name`
- **Real-Time Price Calculation** - Vanilla JS calculates prices dynamically
- **Admin Panel** - Complete management interface for services, packages, states, and portals
- **Responsive Design** - Mobile-friendly form and admin interface
- **Form Submissions** - All submissions stored in custom database table for tracking

## Installation

1. **Download Plugin Files**
   - Place the entire `dynamic-services-form` folder into `/wp-content/plugins/`

2. **Activate Plugin**
   - Go to WordPress admin → Plugins → Find "Dynamic Services Form" → Click "Activate"
   - Database tables will be created automatically

3. **Add Form to Page/Post**
   ```
   [dynamic_service_form]
   ```

## Usage

### Adding Services

1. Go to WordPress Admin → **Dynamic Services** → **Services**
2. Click "Add New"
3. Fill in:
   - **Service Type**: USA, UK, Federal, etc.
   - **Category**: Core Company, Banking Finance, etc.
   - **Name**: DBA Fictitious Name, EIN with IRS, etc.
   - **Pricing Model**: Choose from:
     - `state_based` - Uses states with separate standard/premium pricing
     - `portal_based` - Users select multiple portals
     - `fixed_price` - Single set price
     - `calculator` - User inputs amount, price calculated
   - **Has Packages**: Enable if service offers Standard/Premium options
4. Click "Save Service"

### Managing Packages

1. Go to **Dynamic Services** → **Packages**
2. Create packages for services that have `has_packages` enabled
3. Set package type (Standard, Premium) and price
4. Packages appear in Step 2 of the form

### Managing States

1. Go to **Dynamic Services** → **States**
2. Add states for state-based pricing services
3. Set **Standard Price** and **Premium Price** (leave blank if not applicable)
4. States appear as dropdown in Step 2 of the form

### Managing Portals

1. Go to **Dynamic Services** → **Portals**
2. Add portals for portal-based pricing services
3. Set price for each portal
4. Users can select multiple portals (checkboxes in Step 2)

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
- Business Name (required)
- Email (required)
- Phone (required)
- Entity Type (optional dropdown)
- Additional Notes (optional textarea)

### Step 4: Review & Submit
- Summary of selected service and pricing
- Display of contact information
- Total price display
- Submit button

## Database Tables

The plugin creates the following tables:

### wp_dsf_services
- Core service definitions
- Fields: type, category, name, pricing_model, has_packages, description, enabled

### wp_dsf_packages
- Standard/Premium packages for services
- Fields: service_id, package_type, price, description, enabled

### wp_dsf_states
- States for state-based pricing
- Fields: service_id, state_name, standard_price, premium_price, enabled

### wp_dsf_portals
- Portals for portal-based pricing
- Fields: service_id, portal_name, price, enabled

### wp_dsf_submissions
- Form submissions
- Fields: service_id, form_data (JSON), total_price, business_name, email, phone, entity_type, notes, status, created_at

## Shortcode

```
[dynamic_service_form]
```

Attributes (future enhancement):
```
[dynamic_service_form service_type="usa" service_category="core-company"]
```

## File Structure

```
dynamic-services-form/
├── dynamic-services-form.php      # Main plugin file
├── includes/
│   ├── Plugin.php                 # Core plugin class
│   ├── Database.php               # Database setup and management
│   ├── Service.php                # Service model
│   ├── Package.php                # Package model
│   ├── State.php                  # State model
│   ├── Portal.php                 # Portal model
│   ├── Form.php                   # Frontend form rendering
│   ├── Admin.php                  # Admin interface
│   └── Ajax.php                   # AJAX handlers
├── assets/
│   ├── js/
│   │   ├── form.js                # Frontend form logic
│   │   └── admin.js               # Admin interface logic
│   └── css/
│       ├── form.css               # Frontend styles
│       └── admin.css              # Admin styles
└── README.md
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

## Customization

### Extending Pricing Models

To add a new pricing model:

1. Update the pricing model select in Admin.php
2. Add rendering method in Form.php (e.g., `render_mymodel_pricing()`)
3. Add calculation logic in Ajax.php `calculate_total_price()` method

### Extending with Hooks

The plugin provides custom hooks:

```php
// Fired after successful form submission
do_action('dsf_form_submitted', $submission_id, $service, $form_data, $total_price);
```

### Modifying Styles

- Frontend form styles: `/assets/css/form.css`
- Admin styles: `/assets/css/admin.css`
- Override via child theme or custom CSS in admin

## Security

- All AJAX calls verified with WordPress nonces
- User inputs sanitized and validated
- Database queries use prepared statements
- Admin pages require `manage_options` capability

## Performance

- Optimized database queries with proper indexing
- Minimal AJAX calls with efficient data transfer
- CSS/JS enqueued only on necessary pages
- Supports 50+ states/portals without performance degradation

## Support for 100+ Services

- Scalable database design handles unlimited services
- Efficient hierarchical service selection UI
- Query parameters allow direct service access without browsing

## License

GPL v2 or later

## Author

[Your Name/Company]

## Changelog

### 1.0.0
- Initial release
- Core features: Services, Packages, States, Portals
- Multi-step form with dynamic pricing
- Admin management interface
- Form submissions tracking
