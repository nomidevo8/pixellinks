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

## First-Time Setup (New Normalized Structure)

### Step 1: Create a Service

1. Go to **Dynamic Services Form** → **Services**
2. Click **Add New Service**
3. Fill in:
   - **Service Type**: `USA`
   - **Category**: `Core Company`
   - **Name**: `DBA Fictitious Name`
   - **Pricing Model**: `state_based`
   - **Description**: Optional
   - **Enabled**: ✓ Check
4. Click **Save Service**

### Step 2: Create Locations (NEW - Replaces Old States)

1. Go to **Dynamic Services Form** → **Locations**
2. Click **Add New Location**
3. Fill in:
   - **Location Name**: `California` (or any geographic region)
   - Click **Save Location**
4. Repeat for other locations (Texas, New York, etc.)

**Why separate Locations table?**
- Locations stored once, reused across multiple services
- Reduces data duplication
- Easy to update location info in one place

### Step 3: Create Service Location Pricing (NEW)

1. Go to **Dynamic Services Form** → **Service Location Pricing**
2. Click **Add New Price Link**
3. Fill in:
   - **Service**: Select "DBA Fictitious Name"
   - **Location**: Select "California"
   - **Use Universal Price?**: ✓ Check for single price
     - Shows single "Universal Price" field
   - OR uncheck for tiered pricing:
     - Shows "Standard Price" and "Premium Price" fields
4. Click **Save Pricing**
5. Repeat for other location + service combinations

**Why this structure?**
- Same location can have different prices for different services
- Single price (universal) or tiered pricing (Standard/Premium) toggle
- Cleaner separation of concerns

### Step 4: Create Package Types (NEW)

1. Go to **Dynamic Services Form** → **Package Types**
2. Click **Add New Package Type**
3. Fill in:
   - **Package Name**: `Standard`
   - **Description**: Optional
   - Click **Save**
4. Create more: `Premium`, `Enterprise`, etc.

**Why separate Package Types?**
- Package types stored once
- Same package type can be used by multiple services
- Each service links with independent pricing

### Step 5: Create Service Package Pricing (NEW)

1. Go to **Dynamic Services Form** → **Service Package Pricing**
2. Click **Add New Package Pricing**
3. Fill in:
   - **Service**: Select "DBA Fictitious Name"
   - **Package Type**: Select "Standard"
   - **Price**: `99.99`
   - Click **Save**
4. Repeat for Premium package: `149.99`
5. Create more service-package combinations as needed

### Step 6: Add Form to Page

1. Create or edit a WordPress page
2. Add shortcode:
   ```
   [dynamic_services_form]
   ```
3. Publish

Form now has 9 required contact fields:
- First Name
- Last Name
- Business Name
- Business Address
- City
- State/Province
- Zipcode
- Email
- Phone

(Plus 2 optional: Entity Type, Additional Notes)

---

## Example Configurations

### Example 1: Service with Universal Price (Single Price)

**Service**: Simple Business Filing (state_based, no packages)

**Locations**:
- California
- Texas
- New York

**Service Location Pricing** (toggle ON: "Use Universal Price?"):
- California: $149.99 (universal)
- Texas: $99.99 (universal)
- New York: $199.99 (universal)

**Form Flow**:
1. Service name shown
2. User selects location → price updates to single price
3. Enter 9 contact fields
4. Review and submit

---

### Example 2: Service with Tiered Pricing (Standard/Premium)

**Service**: Premium Business Filing (state_based, has packages)

**Locations**:
- California
- Texas

**Package Types**:
- Standard
- Premium

**Service Location Pricing** (toggle OFF: "Use Universal Price?"):
- California: Standard $149.99 | Premium $199.99
- Texas: Standard $99.99 | Premium $149.99

**Service Package Pricing**:
- Standard: $99.99 (base)
- Premium: $149.99 (base)

**Form Flow**:
1. Service name shown
2. User selects location
3. User selects package (Standard/Premium) → price updates
4. Enter 9 contact fields
5. Review and submit

---

### Example 3: Multiple Services Using Same Locations

**Locations** (created once, reused):
- California
- Texas

**Service 1**: DBA Filing
- California: $149.99 (universal)
- Texas: $99.99 (universal)

**Service 2**: EIN Application
- California: $199.99 (standard) / $249.99 (premium)
- Texas: $149.99 (standard) / $199.99 (premium)

**Benefit**:
- Location data maintained in one place
- Each service has independent pricing
- Updates to location name/info apply everywhere

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

## Admin Features

### View Submissions

1. Go to **Dynamic Services Form** → **Submissions**
2. See all form submissions with:
   - Submission date/time
   - All 9 contact fields (first/last name, address, email, phone, etc.)
   - Service and location selected
   - Package selected (if applicable)
   - Total price
   - Status

### Edit/Delete Items

**Services**: Click Edit to modify, Click Delete to remove

**Locations**: Click Edit to modify, Click Delete to remove

**Service Location Pricing**: Click Edit pricing or Delete link

**Package Types & Service Package Pricing**: Update pricing independently

Changes update immediately on frontend form.

---

## Form Workflow (4 Steps)

### Step 1: Service Selection
Shows all services organized by Type → Category → Name
(Skipped if URL parameters provided)

### Step 2: Location & Pricing Selection
Based on pricing model:
- **State-based**: Dropdown for location + optional package radio buttons
- **Portal-based**: Checkboxes for multiple portals
- **Fixed Price**: Display only (no selections)
- **Calculator**: Input field for amount

Real-time price calculation shown at bottom.

**Universal Price Toggle**:
- Single location selection shows single price (if universal=1)
- OR shows Standard/Premium pricing (if universal=0)

### Step 3: Contact Information (9 fields)
**Required**:
- First Name
- Last Name
- Business Name
- Business Address
- City
- State/Province
- Zipcode
- Email
- Phone

**Optional**:
- Entity Type (dropdown)
- Additional Notes (textarea)

### Step 4: Review & Submit
- Summary of service and pricing
- Review of all 9+ contact fields
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
├── dynamic-services-form.php      ← Main plugin file
├── includes/
│   ├── Plugin.php                 ← Core initialization
│   ├── Database.php               ← 7-table schema
│   ├── Service.php                ← Service model
│   ├── Location.php               ← Location model (NEW)
│   ├── ServiceLocationPricing.php ← Service-Location junction (NEW)
│   ├── PackageType.php            ← Package type model (NEW)
│   ├── ServicePackagePricing.php  ← Service-Package junction (NEW)
│   ├── Form.php                   ← Frontend form (9 fields)
│   ├── Admin.php                  ← Admin (7 menus)
│   ├── Ajax.php                   ← AJAX handlers
│   └── sample-data.php            ← Optional test data
├── assets/
│   ├── js/
│   │   ├── form.js            ← Form logic
│   │   └── admin.js           ← Admin enhancements
│   └── css/
│       ├── form.css           ← Frontend styles
│       └── admin.css          ← Admin styles
├── README.md
├── QUICKSTART.md
├── ARCHITECTURE.md
├── CUSTOMIZATION.md
└── aboutplugin.md
```

---

Enjoy your Dynamic Services Form plugin!
