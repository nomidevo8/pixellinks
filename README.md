# Dynamic Services Form

A production‑ready **WordPress plugin** for building **dynamic, data‑driven, multi‑step service forms** with advanced pricing logic, URL‑driven preselection, and a fully normalized database architecture.

This README reflects the **current functionality and architecture** of the plugin after recent frontend and pricing‑engine changes.

---

## 🎯 Core Features

### 🧩 Dynamic Service Engine

* Services are loaded **server‑side** and embedded into the frontend as structured JSON (`DSF_SERVICES_DATA`)
* Frontend rendering is **data‑driven**, not DOM‑driven
* Supports automatic service pre‑selection via URL parameters

### 💰 Pricing Models (Fully Implemented)

The plugin currently supports **4 pricing models**, all handled dynamically on the frontend:

1. **State‑Based Pricing**

   * Price varies by location/state
   * Optional package add‑ons (Standard / Premium / etc.)
   * Supports universal pricing and per‑state overrides

2. **Portal‑Based Pricing**

   * Price varies by selected portal
   * Portals are linked to services with independent pricing

3. **Fixed Price**

   * Single fixed price
   * No additional inputs required

4. **Calculator / Tiered Pricing**

   * Price calculated from user input
   * Tier rules applied client‑side

All pricing updates are reflected **in real time** without page reloads.

---

## 🔗 URL‑Driven Auto Selection

The form supports **deep‑linking** and auto‑selection via query parameters:

```text
?service_type=us
&service_category=core-company-formation-services
&service_name=reseller-certificate
&pkg=premium
```

### Supported Parameters

| Parameter          | Purpose                                                 |
| ------------------ | ------------------------------------------------------- |
| `service_type`     | Filters services (e.g. USA, Canada)                     |
| `service_category` | Filters service category                                |
| `service_name`     | Auto‑selects service (slug, kebab/snake/space tolerant) |
| `pkg`              | Auto‑selects package (standard / premium etc.)          |

Matching is **case‑insensitive**, supports kebab‑case, snake_case, and spaced names.

---

## 🧠 Frontend Architecture

### Data Flow

1. Services fetched server‑side using:

   ```php
   Service::get_by_type_and_category()
   ```
2. Embedded as JSON:

   ```js
   var DSF_SERVICES_DATA = [...];
   ```
3. UI is rendered **entirely from this data object**
4. Pricing is calculated client‑side using pure JS functions

⚠️ **Important:** The plugin does NOT rely on `<option data‑*>` attributes for logic. All business logic uses the JS data model.

---

## 🎛️ Dropdown Handling (Custom)

* All metadata is stored in:

  ```js
  DSF_SERVICES_DATA
  ```

To access service data:

```js
const service = DSF_SERVICES_DATA.find(s => s.id === selectedId);
```

This avoids DOM coupling and ensures stability.

---

## 🧾 Form Workflow

### Step 1: Service & Pricing

* Service selection
* Dynamic pricing inputs (state / portal / calculator / packages)
* Live total price display

### Step 2: Contact Information

* First name / Last name
* Business name
* Address (city, state, zipcode)
* Email / Phone
* Optional notes

### Submission

* AJAX‑based submission
* Secure nonce validation
* Admin Gets Notified
* Data stored in normalized tables

---



## QUICKSTART.md

### Quick Start Guide

1. Install and activate the plugin
2. Import demo SQL data
3. Create a page and add shortcode:

```
[dynamic_service_form]
```

4. (Optional) Preselect a service via query params (case‑insensitive):

```
/your-page/?service_type=usa&service_category=core-company&service_name=llc-formation&pkg=premium
```

## File overview

- `dynamic-services-form.php` — plugin bootstrap
- `includes/` — models, admin, DB and form logic (see QUICKSTART.md)
- `assets/js/form.js` — frontend form behavior and pricing engine
- `assets/css/form.css` — frontend styles

If you need help extending the plugin, see [CUSTOMIZATION.md](CUSTOMIZATION.md).
