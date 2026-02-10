# Quick Start Guide - Dynamic Services Form Plugin

## Installation Steps

1. Copy the plugin into your site:

```
wp-content/plugins/dynamic-services-form/
```

2. Activate the plugin in WordPress admin. Database tables are created automatically on activation.

3. After activation you will see the **Dynamic Services** menu in the admin.

---

## First-Time Setup (normalized structure)

1. Create one or more **Services** under **Dynamic Services** → **Services**. Configure `type`, `category`, `name`, and `pricing_model`.
2. Create **Locations** under **Dynamic Services** → **Locations** (reusable across services).
3. Add **Service Location Pricing** links to associate a service with a location and set `standard_price` / `premium_price` or `is_universal`.
4. Create **Package Types** and then **Service Package Pricing** if your service uses packages.
5. (Optional) Create **Portals** when using `portal_based` pricing.

---

## Add the form to a page

Place the shortcode on any page:

```
[dynamic_service_form]
```

The form includes the standard contact fields (first/last name, business name, address, city, state, zipcode, email, phone) plus optional fields (entity type, notes).

---

## Using Query Parameters (preselection)

You can deep‑link and preselect a service. Matching is case‑insensitive and tolerant to spaces/kebab/snake casing.

Example:

```
/your-page/?service_type=usa&service_category=core-company&service_name=llc-formation&pkg=premium
```

If parameters match an active service the selection step will be skipped.

---

## Troubleshooting

- Form not showing: verify shortcode `[dynamic_service_form]`, plugin activated, and at least one service exists.
- Pricing not calculating: ensure pricing entries exist for locations/packages/portals and are enabled.
- Services not appearing: check the service `enabled` flag and `pricing_model` setting.

---

## Admin & Next steps

- View submissions under **Dynamic Services** → **Submissions**.
- Test the form with sample/demo data (see `includes/sample-data.php`).
- Customize styles in `assets/css/form.css`.

---

For developer references see [ARCHITECTURE.md](ARCHITECTURE.md) and [DB-SCHEMA.md](DB-SCHEMA.md).
