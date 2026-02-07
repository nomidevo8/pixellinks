-- =============================================================================
-- Dynamic Services Form - Dummy Data for phpMyAdmin
-- =============================================================================
-- 1. Ensure the plugin has created the tables first (activate plugin or run
--    the CREATE TABLE statements from includes/Database.php).
-- 2. If your WordPress table prefix is not "wp_", replace "wp_" in this file
--    with your prefix (e.g. "wp_2_" for multisite).
-- 3. Run this entire script in phpMyAdmin SQL tab.
-- =============================================================================

-- Optional: clear existing plugin data (uncomment if you want a clean insert)
-- DELETE FROM wp_dsf_submissions;
-- DELETE FROM wp_dsf_portals;
-- DELETE FROM wp_dsf_service_location_pricing;
-- DELETE FROM wp_dsf_service_package_pricing;
-- DELETE FROM wp_dsf_locations;
-- DELETE FROM wp_dsf_package_types;
-- DELETE FROM wp_dsf_services;

-- =============================================================================
-- 1. SERVICES (all pricing models and scenarios)
-- =============================================================================
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(1, 'USA', 'Core Company', 'DBA Fictitious Name', 'state_based', 1, 'File for a DBA (Doing Business As) or Fictitious Business Name in any US state', 1, NOW(), NOW()),
(2, 'USA', 'Core Company', 'EIN with IRS', 'state_based', 1, 'Obtain an Employer Identification Number (EIN) from the IRS', 1, NOW(), NOW()),
(3, 'USA', 'Compliance', 'Multi-State Filing Portal', 'portal_based', 0, 'File compliance documents across multiple state portals', 1, NOW(), NOW()),
(4, 'USA', 'Permits & Licenses', 'Business License', 'fixed_price', 0, 'General business operating license in any state', 1, NOW(), NOW()),
(5, 'USA', 'Custom Services', 'Document Review Package', 'calculator', 0, 'Custom document review – tiered by amount ($350k–$500k, $500k–$2M, $2M+)', 1, NOW(), NOW()),
(6, 'UK', 'Core Company', 'Company Formation', 'state_based', 0, 'Register a limited company with Companies House UK', 1, NOW(), NOW()),
(7, 'Federal', 'Tax Services', 'Federal EIN Request', 'fixed_price', 0, 'Federal Employer Identification Number application', 1, NOW(), NOW());

-- =============================================================================
-- 2. PACKAGE TYPES (shared across services)
-- =============================================================================
INSERT INTO wp_dsf_package_types (id, package_type_name, description, enabled, created_at, updated_at) VALUES
(1, 'Standard', 'Basic filing with government agency', 1, NOW(), NOW()),
(2, 'Premium', 'Includes filing + registered agent + consultation', 1, NOW(), NOW()),
(3, 'Enterprise', 'Full service with ongoing support and renewals', 1, NOW(), NOW());

-- =============================================================================
-- 3. LOCATIONS (US states + UK regions)
-- =============================================================================
INSERT INTO wp_dsf_locations (id, location_name, location_code, location_type, enabled, created_at, updated_at) VALUES
(1, 'Alabama', 'AL', 'state', 1, NOW(), NOW()),
(2, 'California', 'CA', 'state', 1, NOW(), NOW()),
(3, 'Florida', 'FL', 'state', 1, NOW(), NOW()),
(4, 'New York', 'NY', 'state', 1, NOW(), NOW()),
(5, 'Texas', 'TX', 'state', 1, NOW(), NOW()),
(6, 'England', 'EN', 'region', 1, NOW(), NOW()),
(7, 'Scotland', 'SC', 'region', 1, NOW(), NOW()),
(8, 'Wales', 'WA', 'region', 1, NOW(), NOW());

-- =============================================================================
-- 4. SERVICE–PACKAGE PRICING (state_based + has_packages: services 1 & 2)
-- =============================================================================
-- DBA Fictitious Name (service_id=1)
INSERT INTO wp_dsf_service_package_pricing (id, service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(1, 1, 1, 99.99, 'Basic filing with government agency', 1, NOW(), NOW()),
(2, 1, 2, 149.99, 'Includes filing + registered agent + consultation', 1, NOW(), NOW()),
(3, 1, 3, 199.99, 'Full service with ongoing support and renewals', 1, NOW(), NOW());
-- EIN with IRS (service_id=2)
INSERT INTO wp_dsf_service_package_pricing (id, service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(4, 2, 1, 49.99, 'EIN application processing', 1, NOW(), NOW()),
(5, 2, 2, 99.99, 'EIN application + IRS consultation + expedited processing', 1, NOW(), NOW()),
(6, 2, 3, 149.99, 'Full EIN service with ongoing tax support', 1, NOW(), NOW());

-- =============================================================================
-- 5. SERVICE–LOCATION PRICING
-- =============================================================================
-- Services 1 & 2 (state_based + packages): need locations for dropdown; price comes from package
INSERT INTO wp_dsf_service_location_pricing (id, service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at) VALUES
(1, 1, 1, 0, NULL, 0, 1, NOW(), NOW()),
(2, 1, 2, 0, NULL, 0, 1, NOW(), NOW()),
(3, 1, 3, 0, NULL, 0, 1, NOW(), NOW()),
(4, 1, 4, 0, NULL, 0, 1, NOW(), NOW()),
(5, 1, 5, 0, NULL, 0, 1, NOW(), NOW()),
(6, 2, 1, 0, NULL, 0, 1, NOW(), NOW()),
(7, 2, 2, 0, NULL, 0, 1, NOW(), NOW()),
(8, 2, 3, 0, NULL, 0, 1, NOW(), NOW()),
(9, 2, 4, 0, NULL, 0, 1, NOW(), NOW()),
(10, 2, 5, 0, NULL, 0, 1, NOW(), NOW());
-- Service 6 (UK Company Formation): state_based, no packages – standard_price + one with premium (tiered) and one is_universal
INSERT INTO wp_dsf_service_location_pricing (id, service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at) VALUES
(11, 6, 6, 199.99, 249.99, 0, 1, NOW(), NOW()),
(12, 6, 7, 199.99, 249.99, 0, 1, NOW(), NOW()),
(13, 6, 8, 199.99, NULL, 1, 1, NOW(), NOW());

-- =============================================================================
-- 6. PORTALS (portal_based service_id=3)
-- =============================================================================
INSERT INTO wp_dsf_portals (id, service_id, portal_name, price, enabled, created_at, updated_at) VALUES
(1, 3, 'Secretary of State', 50.00, 1, NOW(), NOW()),
(2, 3, 'IRS Portal', 25.00, 1, NOW(), NOW()),
(3, 3, 'County Clerk', 75.00, 1, NOW(), NOW()),
(4, 3, 'State Tax Board', 35.00, 1, NOW(), NOW());

-- =============================================================================
-- 7. SUBMISSIONS (one per scenario: state+package, state only, portal, fixed, calculator)
-- =============================================================================
INSERT INTO wp_dsf_submissions (id, service_id, form_data, total_price, first_name, last_name, business_name, business_address, phone, email, city, state, zipcode, entity_type, notes, status, created_at, updated_at) VALUES
-- State-based + package (DBA, service_id=1, location_id=2, package_id=2 → 149.99)
(1, 1, '{"location_id":2,"package_id":2,"portal_ids":[],"calculator_amount":null,"first_name":"Jane","last_name":"Doe","business_name":"Doe LLC","business_address":"123 Main St","phone":"555-0101","email":"jane@example.com","city":"Los Angeles","state":"CA","zipcode":"90001","entity_type":"LLC","notes":""}', 149.99, 'Jane', 'Doe', 'Doe LLC', '123 Main St', '555-0101', 'jane@example.com', 'Los Angeles', 'CA', '90001', 'LLC', '', 'pending', NOW(), NOW()),
-- State-based, no package (UK Company Formation, service_id=6, location_id=6 → 199.99)
(2, 6, '{"location_id":6,"package_id":null,"portal_ids":[],"calculator_amount":null,"first_name":"John","last_name":"Smith","business_name":"Smith Ltd","business_address":"1 High Street","phone":"555-0202","email":"john@example.com","city":"London","state":"England","zipcode":"SW1A 1AA","entity_type":"Limited Company","notes":"UK formation"}', 199.99, 'John', 'Smith', 'Smith Ltd', '1 High Street', '555-0202', 'john@example.com', 'London', 'England', 'SW1A 1AA', 'Limited Company', 'UK formation', 'completed', NOW(), NOW()),
-- Portal-based (Multi-State Filing, service_id=3, two portals: 50+25=75)
(3, 3, '{"location_id":null,"package_id":null,"portal_ids":[1,2],"calculator_amount":null,"first_name":"Bob","last_name":"Wilson","business_name":"Wilson Corp","business_address":"456 Oak Ave","phone":"555-0303","email":"bob@example.com","city":"Miami","state":"FL","zipcode":"33101","entity_type":"Corporation","notes":""}', 75.00, 'Bob', 'Wilson', 'Wilson Corp', '456 Oak Ave', '555-0303', 'bob@example.com', 'Miami', 'FL', '33101', 'Corporation', '', 'pending', NOW(), NOW()),
-- Calculator (service_id=5: amount 400000 → 900)
(4, 5, '{"location_id":null,"package_id":null,"portal_ids":[],"calculator_amount":400000,"first_name":"Alice","last_name":"Brown","business_name":"Brown Inc","business_address":"789 Pine Rd","phone":"555-0404","email":"alice@example.com","city":"Austin","state":"TX","zipcode":"73301","entity_type":"Corporation","notes":"Document review"}', 900.00, 'Alice', 'Brown', 'Brown Inc', '789 Pine Rd', '555-0404', 'alice@example.com', 'Austin', 'TX', '73301', 'Corporation', 'Document review', 'pending', NOW(), NOW()),
-- Fixed price (service_id=4 or 7 – plugin uses fixed_price from service; no column in DB, so total can be 0 or you set manually)
(5, 4, '{"location_id":null,"package_id":null,"portal_ids":[],"calculator_amount":null,"first_name":"Chris","last_name":"Lee","business_name":"Lee Services","business_address":"321 Elm St","phone":"555-0505","email":"chris@example.com","city":"Seattle","state":"WA","zipcode":"98101","entity_type":"Sole Proprietor","notes":""}', 0.00, 'Chris', 'Lee', 'Lee Services', '321 Elm St', '555-0505', 'chris@example.com', 'Seattle', 'WA', '98101', 'Sole Proprietor', '', 'pending', NOW(), NOW());

-- =============================================================================
-- Done. Scenarios covered:
-- - state_based + has_packages (services 1, 2): location + package selection, price from service_package_pricing
-- - state_based, no packages (service 6): location only, price from service_location_pricing (standard_price / premium_price)
-- - portal_based (service 3): multiple portals, total = sum of portal prices
-- - fixed_price (services 4, 7): no location/package/portal; fixed price (stored in code if needed)
-- - calculator (service 5): calculator_amount in form_data; tiered price in code
-- - submissions: pending, completed; form_data as JSON for each scenario
-- =============================================================================
