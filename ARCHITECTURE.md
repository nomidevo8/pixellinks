# Architecture — Dynamic Services Form

This document describes the plugin's high level architecture, dataflow, and main components.

## Overview

The plugin follows a single source of truth approach: the backend prepares structured service data which is embedded into the page as JSON and consumed by a small JS state engine that renders the multi‑step form and calculates pricing.

## Main components

- PHP (includes/): models and controllers that prepare data and expose admin screens
- Database: normalized tables for services, locations, portals, packages, pricing, and submissions
- Frontend JS: `assets/js/form.js` — renders UI from `DSF_SERVICES_DATA`, manages steps, and calculates totals
- Admin UI: CRUD screens for services, locations, package types, pricing, portals, and submissions

## Dataflow

1. Admin creates services, locations, package types, portals and pricing via admin screens
2. On page render the backend loads active services and related pricing
3. Backend outputs a JSON object `DSF_SERVICES_DATA` to the frontend
4. Frontend JS initializes state from `DSF_SERVICES_DATA`, renders form steps, and performs pricing calculations client‑side
5. Submissions are sent over AJAX to the plugin; server validates nonce and stores normalized submission records

```
Admin (CRUD)
	↓
Database (normalized tables)
	↓
PHP (Service objects → JSON)
	↓
Frontend (DSF_SERVICES_DATA → JS state engine)
	↓
UI rendering and live pricing (client)
```

## Key design decisions

- Data driven UI: avoids coupling business logic to DOM attributes
- Normalized schema: locations, packages and portals are stored once and reused
- Pricing computation is performed client‑side for immediate feedback; server re‑calculates/validates on submission

## Includes overview

- `Plugin.php` — bootstrap and hooks
- `Database.php` — table creation and schema
- `Service.php` — service model and loaders
- `Location.php` — location model
- `PackageType.php` — package model
- `ServiceLocationPricing.php` — service/location pricing junction
- `ServicePackagePricing.php` — service/package pricing junction
- `Portal.php` — portals and portal pricing
- `Form.php` — view rendering helpers for the frontend
- `Ajax.php` — AJAX endpoints (submission, price calc)
- `Admin.php` — admin screens

## Extensibility

- Use `do_action('dsf_form_submitted', $submission_id, $service, $form_data, $total_price)` to react to submissions
- Use `add_filter('dsf_calculate_total_price', $price, $service_id, $form_data)` to customize pricing calculation

For implementation details see the other docs and the `includes/` source files.
