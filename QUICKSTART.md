# Quick Start Guide - Dynamic Services Form Plugin

## Installation Steps

### 1. Copy Plugin Files
The entire plugin folder is located at:
```
/wp-content/plugins/dynamic-services-form/
```

### 2. Activate Plugin
1. Go to WordPress Admin Dashboard
2. Navigate to **Plugins** → **Installed Plugins**
3. Find **Dynamic Services Form**
4. Click **Activate**
5. Database tables will be created automatically once activated

### 3. Verify Installation
After activation, you should see **Dynamic Services** in the left admin menu.

---

## First-Time Setup

### Step 1: Create a Service

1. Go to **Dynamic Services** → **Services**
2. Click **Add New**
3. Fill in the form:
   - **Service Type**: `USA` (business registration location)
   - **Category**: `Core Company` (business type)
   - **Name**: `DBA Fictitious Name` (specific service)
   - **Pricing Model**: Select `state_based` (users select a state + optional package)
   - **Has Packages**: ✓ Check this to enable Standard/Premium options
   - **Description**: Optional description
   - **Enabled**: ✓ Check to activate
4. Click **Save Service**

### Step 2: Create Packages (optional, only if "Has Packages" is enabled)

1. Go to **Dynamic Services** → **Packages**
2. Click **Add New**
3. Fill in:
   - **Service**: Select the service you just created
   - **Package Type**: `Standard` or `Premium`
   - **Price**: `99.99` (or leave empty for free)
   - **Description**: `Includes basic filing` (optional)
   - **Enabled**: ✓ Check
4. Click **Save Package**
5. Repeat to create another package with different price

### Step 3: Create States (for state-based pricing)

1. Go to **Dynamic Services** → **States**
2. Click **Add New**
3. Fill in:
   - **Service**: Select your service
   - **State Name**: `California`
   - **Standard Price**: `149.99` (applies if no premium package selected)
   - **Premium Price**: `199.99` (applies if premium package selected)
   - **Enabled**: ✓ Check
4. Click **Save State**
5. Repeat for other states

### Step 4: Add Form to Page

1. Create or edit a WordPress page
2. Add the shortcode:
   ```
   [dynamic_service_form]
   ```
3. Publish the page

---

## Example Configurations

### Example 1: State-Based Pricing with Packages

**Service**: DBA Fictitious Name (state_based + has_packages)

**Packages**:
- Standard: $99.99
- Premium: $149.99

**States**:
- California:  Standard: $149.99 | Premium: $199.99
- Texas:       Standard: $99.99  | Premium: $149.99
- New York:    Standard: $199.99 | Premium: $249.99

**Form Flow**:
1. Service already selected (via URL or Step 1)
2. User selects state → selects package → price calculates
3. Enter contact info
4. Review and submit

---

### Example 2: Portal-Based Pricing (No Packages)

**Service**: Multi-Portal Filing (portal_based, no packages)

**Portals**:
- Secretary of State: $50.00
- IRS Portal: $75.00
- Local County: $25.00

**Form Flow**:
1. Service selected
2. User checks multiple portals → price updates (sum of selected)
3. Enter contact info
4. Review and submit

---

### Example 3: Fixed Price

**Service**: Business License (fixed_price)

**Configuration**: Set pricing model to "fixed_price"

**Form Flow**:
1. Service selected
2. Price is fixed (no selections needed)
3. Enter contact info
4. Review and submit

---

### Example 4: Calculator-Based

**Service**: Premium Package (calculator)

**Form Flow**:
1. Service selected
2. User enters amount
3. Price calculates (e.g., amount × rate)
4. Enter contact info
5. Review and submit

---

## Using Query Parameters

Pre-select a service and skip the service selection step:

```
example.com/service-form/?service_type=USA&service_category=Core%20Company&service_name=DBA%20Fictitious%20Name
```

**Parameters**:
- `service_type`: Type of service (must match exactly)
- `service_category`: Service category (case-sensitive)
- `service_name`: Service name (case-sensitive)

If query parameters are present, Step 1 is skipped automatically.

---

## Admin Features

### View Submissions

1. Go to **Dynamic Services** → **Submissions**
2. See all form submissions with:
   - Submission date
   - Business name
   - Email
   - Service selected
   - Total price
   - Status (pending, etc.)

### Edit/Delete Items

**Services**:
- Click **Edit** to modify
- Click **Delete** to remove (also removes related packages/states/portals)

**Packages/States/Portals**:
- Click **Edit** to modify pricing
- Click **Delete** to remove
- Changes update immediately on frontend

---

## Form Workflow (4 Steps)

### Step 1: Service Selection
Shows all services organized by Type → Category → Name
(Skipped if URL parameters provided)

### Step 2: Pricing & Options
Varies by pricing model:
- **State-based**: Dropdown for states + optional package radio buttons
- **Portal-based**: Checkboxes for multiple portals
- **Fixed Price**: Display only (no selections)
- **Calculator**: Input field for amount

Real-time price calculation shown at bottom.

### Step 3: Contact Information
- Business Name (required)
- Email (required)
- Phone (required)
- Entity Type (optional)
- Additional Notes (optional)

### Step 4: Review & Submit
- Summary of service and pricing
- Contact info review
- Total price display
- Submit button

---

## Troubleshooting

### Form Not Showing
- Verify shortcode is added: `[dynamic_service_form]`
- Check plugin is activated
- Ensure you've created at least one service

### Pricing Not Calculating
- Verify states/portals have prices set
- Check package prices are entered
- Ensure items are marked as "Enabled"

### Services Not Appearing
- Go to **Dynamic Services** → **Services**
- Verify service is marked as "Enabled"
- Check "Pricing Model" is set correctly

### Database Error
- Plugin automatically creates tables on activation
- If error persists, deactivate and reactivate plugin

---

## Next Steps

1. ✓ Install and activate plugin
2. ✓ Create services, packages, states, portals
3. ✓ Add form shortcode to page
4. ✓ Test form with sample data
5. View submissions in admin panel
6. Optional: Customize CSS in `/assets/css/form.css`

---

## Support & Customization

For extending functionality:
- Add custom pricing models
- Hook into form submission: `do_action('dsf_form_submitted', ...)`
- Modify form layout in Form.php class
- Customize styles in CSS files

---

## Files Overview

```
dynamic-services-form/
├── dynamic-services-form.php   ← Main plugin file (don't edit)
├── includes/
│   ├── Plugin.php             ← Core initialization
│   ├── Admin.php              ← Admin pages
│   ├── Form.php               ← Frontend form
│   ├── Ajax.php               ← Form handling
│   ├── Database.php           ← Database setup
│   ├── Service.php            ← Service model
│   ├── Package.php            ← Package model
│   ├── State.php              ← State model
│   ├── Portal.php             ← Portal model
│   └── Submission.php         ← Submission model
├── assets/
│   ├── js/
│   │   ├── form.js            ← Frontend form logic
│   │   └── admin.js           ← Admin logic
│   └── css/
│       ├── form.css           ← Frontend styles (customize here)
│       └── admin.css          ← Admin styles
├── README.md
└── QUICKSTART.md (this file)
```

---

Enjoy your Dynamic Services Form plugin!
