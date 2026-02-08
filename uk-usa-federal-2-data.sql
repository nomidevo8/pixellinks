-- =============================================================================
-- Dynamic Services Form - Complete Data for UK, US, and Federal Services
-- =============================================================================
-- Instructions:
-- 1. Replace "wp_" with your WordPress table prefix if different
-- 2. Run this entire script in phpMyAdmin SQL tab
-- 3. This includes all 50 US states + DC with correct pricing
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
-- 1. PACKAGE TYPES (Standard and Premium)
-- =============================================================================
INSERT INTO wp_dsf_package_types (id, package_type_name, description, enabled, created_at, updated_at) VALUES
(1, 'Standard', 'Standard processing with basic features', 1, NOW(), NOW()),
(2, 'Premium', 'Premium processing with priority support and additional features', 1, NOW(), NOW());
-- =============================================================================
-- LOCATIONS (USA States + DC + UK Regions)
-- =============================================================================
INSERT INTO wp_dsf_locations 
(id, location_name, location_code, location_type, enabled, created_at, updated_at) 
VALUES

-- ======================
-- USA (50 States + DC)
-- ======================
(1, 'Alabama', 'AL', 'state', 1, NOW(), NOW()),
(2, 'Alaska', 'AK', 'state', 1, NOW(), NOW()),
(3, 'Arizona', 'AZ', 'state', 1, NOW(), NOW()),
(4, 'Arkansas', 'AR', 'state', 1, NOW(), NOW()),
(5, 'California', 'CA', 'state', 1, NOW(), NOW()),
(6, 'Colorado', 'CO', 'state', 1, NOW(), NOW()),
(7, 'Connecticut', 'CT', 'state', 1, NOW(), NOW()),
(8, 'Delaware', 'DE', 'state', 1, NOW(), NOW()),
(9, 'Florida', 'FL', 'state', 1, NOW(), NOW()),
(10, 'Georgia', 'GA', 'state', 1, NOW(), NOW()),
(11, 'Hawaii', 'HI', 'state', 1, NOW(), NOW()),
(12, 'Idaho', 'ID', 'state', 1, NOW(), NOW()),
(13, 'Illinois', 'IL', 'state', 1, NOW(), NOW()),
(14, 'Indiana', 'IN', 'state', 1, NOW(), NOW()),
(15, 'Iowa', 'IA', 'state', 1, NOW(), NOW()),
(16, 'Kansas', 'KS', 'state', 1, NOW(), NOW()),
(17, 'Kentucky', 'KY', 'state', 1, NOW(), NOW()),
(18, 'Louisiana', 'LA', 'state', 1, NOW(), NOW()),
(19, 'Maine', 'ME', 'state', 1, NOW(), NOW()),
(20, 'Maryland', 'MD', 'state', 1, NOW(), NOW()),
(21, 'Massachusetts', 'MA', 'state', 1, NOW(), NOW()),
(22, 'Michigan', 'MI', 'state', 1, NOW(), NOW()),
(23, 'Minnesota', 'MN', 'state', 1, NOW(), NOW()),
(24, 'Mississippi', 'MS', 'state', 1, NOW(), NOW()),
(25, 'Missouri', 'MO', 'state', 1, NOW(), NOW()),
(26, 'Montana', 'MT', 'state', 1, NOW(), NOW()),
(27, 'Nebraska', 'NE', 'state', 1, NOW(), NOW()),
(28, 'Nevada', 'NV', 'state', 1, NOW(), NOW()),
(29, 'New Hampshire', 'NH', 'state', 1, NOW(), NOW()),
(30, 'New Jersey', 'NJ', 'state', 1, NOW(), NOW()),
(31, 'New Mexico', 'NM', 'state', 1, NOW(), NOW()),
(32, 'New York', 'NY', 'state', 1, NOW(), NOW()),
(33, 'North Carolina', 'NC', 'state', 1, NOW(), NOW()),
(34, 'North Dakota', 'ND', 'state', 1, NOW(), NOW()),
(35, 'Ohio', 'OH', 'state', 1, NOW(), NOW()),
(36, 'Oklahoma', 'OK', 'state', 1, NOW(), NOW()),
(37, 'Oregon', 'OR', 'state', 1, NOW(), NOW()),
(38, 'Pennsylvania', 'PA', 'state', 1, NOW(), NOW()),
(39, 'Rhode Island', 'RI', 'state', 1, NOW(), NOW()),
(40, 'South Carolina', 'SC', 'state', 1, NOW(), NOW()),
(41, 'South Dakota', 'SD', 'state', 1, NOW(), NOW()),
(42, 'Tennessee', 'TN', 'state', 1, NOW(), NOW()),
(43, 'Texas', 'TX', 'state', 1, NOW(), NOW()),
(44, 'Utah', 'UT', 'state', 1, NOW(), NOW()),
(45, 'Vermont', 'VT', 'state', 1, NOW(), NOW()),
(46, 'Virginia', 'VA', 'state', 1, NOW(), NOW()),
(47, 'Washington', 'WA', 'state', 1, NOW(), NOW()),
(48, 'West Virginia', 'WV', 'state', 1, NOW(), NOW()),
(49, 'Wisconsin', 'WI', 'state', 1, NOW(), NOW()),
(50, 'Wyoming', 'WY', 'state', 1, NOW(), NOW()),
(51, 'District of Columbia', 'DC', 'state', 1, NOW(), NOW()),

-- ======================
-- UK (Regions)
-- ======================
(52, 'England', 'ENG', 'region', 1, NOW(), NOW()),
(53, 'Wales', 'WLS', 'region', 1, NOW(), NOW()),
(54, 'Scotland', 'SCT', 'region', 1, NOW(), NOW()),
(55, 'Northern Ireland', 'NIR', 'region', 1, NOW(), NOW());

-- =============================================================================
-- 3. UK SERVICES
-- =============================================================================

-- UK Core Company Formation Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(1, 'uk', 'core-company-formation-services', 'director-service-address', 'state_based', 1, 'Director Service Address', 1, NOW(), NOW()),
(2, 'uk', 'core-company-formation-services', 'local-business-license-guidance', 'state_based', 1, 'Local business license guidance', 1, NOW(), NOW()),
(3, 'uk', 'core-company-formation-services', 'apostille-legalisation-documents', 'state_based', 1, 'Apostille & Legalisation of Documents', 1, NOW(), NOW());

-- UK Compliance & Legal Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(4, 'uk', 'compliance-legal-services', 'annual-confirmation-statement-filing', 'state_based', 1, 'Annual Confirmation Statement Filing', 1, NOW(), NOW()),
(5, 'uk', 'compliance-legal-services', 'annual-accounts-filing', 'state_based', 1, 'Annual Accounts Filing', 1, NOW(), NOW()),
(6, 'uk', 'compliance-legal-services', 'vat-registration-filing', 'state_based', 1, 'VAT Registration & Filing', 1, NOW(), NOW()),
(7, 'uk', 'compliance-legal-services', 'corporation-tax-registration-hmrc', 'state_based', 1, 'Corporation Tax Registration (HMRC)', 1, NOW(), NOW()),
(8, 'uk', 'compliance-legal-services', 'paye-payroll-registration', 'state_based', 1, 'PAYE (Payroll) Registration', 1, NOW(), NOW()),
(9, 'uk', 'compliance-legal-services', 'trademark-registration-uk-ipo', 'state_based', 1, 'Trademark Registration UK IPO', 1, NOW(), NOW()),
(10, 'uk', 'compliance-legal-services', 'certificate-of-good-standing', 'state_based', 1, 'Certificate of Good Standing', 1, NOW(), NOW()),
(11, 'uk', 'compliance-legal-services', 'dissolution-of-company-uk', 'state_based', 1, 'Dissolution of Company (wind-up service)', 1, NOW(), NOW());

-- UK Banking & Financial Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(12, 'uk', 'banking-financial-services', 'business-bank-account-modern-banks', 'state_based', 1, 'Business bank account setup assistance (Modern Banks: Monzo, Anna, Zempler Bank, WorldFirst, Tide)', 1, NOW(), NOW()),
(13, 'uk', 'banking-financial-services', 'business-bank-account-traditional-banks', 'state_based', 1, 'Business bank account setup assistance (Traditional Banks: Barclays, HSBC, Lloyds Bank, NatWest, Santander UK)', 1, NOW(), NOW()),
(14, 'uk', 'banking-financial-services', 'payment-gateway-integration', 'state_based', 1, 'Payment Gateway Integration (Stripe, PayPal, Revolut, Wise)', 1, NOW(), NOW()),
(15, 'uk', 'banking-financial-services', 'bookkeeping-accounting-setup-uk', 'state_based', 1, 'Bookkeeping & Accounting Setup', 1, NOW(), NOW()),
(16, 'uk', 'banking-financial-services', 'annual-tax-return-filing-uk', 'state_based', 1, 'Annual Tax Return Filing (Self Assessment, Corporation Tax)', 1, NOW(), NOW());

-- UK Business Address & Communication
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(17, 'uk', 'business-address-communication', 'registered-office-address', 'state_based', 1, 'Registered Office Address', 1, NOW(), NOW()),
(18, 'uk', 'business-address-communication', 'business-correspondence-address', 'state_based', 1, 'Business Correspondence Address', 1, NOW(), NOW()),
(19, 'uk', 'business-address-communication', 'physical-virtual-office', 'state_based', 1, 'Physical office setup / Virtual office upgrade', 1, NOW(), NOW()),
(20, 'uk', 'business-address-communication', 'virtual-office-mail-forwarding', 'state_based', 1, 'Virtual Office Address in UK, Mail Forwarding Service (international forwarding)', 1, NOW(), NOW());

-- UK Add-On / Premium Services (Contact Us - we'll use fixed_price with 0 for these)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(21, 'uk', 'add-on-premium-services', 'business-plan-writing-consulting', 'fixed_price', 0, 'Business Plan Writing & Consulting - Contact us for pricing', 1, NOW(), NOW()),
(22, 'uk', 'add-on-premium-services', 'website-domain-registration', 'fixed_price', 0, 'Website & Domain Registration - Contact us for pricing', 1, NOW(), NOW()),
(23, 'uk', 'add-on-premium-services', 'seo-digital-marketing', 'fixed_price', 0, 'SEO & Digital Marketing - Contact us for pricing', 1, NOW(), NOW());

-- =============================================================================
-- 4. US SERVICES
-- =============================================================================

-- US Core Company Formation Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(24, 'us', 'core-company-formation-services', 'dba-fictitious-name', 'state_based', 1, 'DBA / Fictitious Name Registration', 1, NOW(), NOW()),
(25, 'us', 'core-company-formation-services', 'registered-agent-service', 'state_based', 1, 'Registered Agent service (mandatory for all states)', 1, NOW(), NOW()),
(26, 'us', 'core-company-formation-services', 'ein-with-irs', 'state_based', 1, 'EIN (Employer Identification Number) application with IRS', 1, NOW(), NOW()),
(27, 'us', 'core-company-formation-services', 'itin-for-non-residents', 'state_based', 1, 'Tax ID (ITIN for non-residents)', 1, NOW(), NOW()),
(28, 'us', 'core-company-formation-services', 'annual-report-compliance-filings', 'state_based', 1, 'Annual Report & Compliance filings', 1, NOW(), NOW()),
(29, 'us', 'core-company-formation-services', 'certificate-of-good-standing', 'state_based', 1, 'Certificate of Good Standing', 1, NOW(), NOW()),
(30, 'us', 'core-company-formation-services', 'dissolve-your-company', 'state_based', 1, 'Dissolve Your Company', 1, NOW(), NOW());

-- US Banking & Finance Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(31, 'us', 'banking-finance-services', 'fintech-bank-accounts', 'state_based', 1, 'Business bank account setup assistance (Fintech: Mercury, Relay, Brex, Wise)', 1, NOW(), NOW()),
(32, 'us', 'banking-finance-services', 'traditional-bank-accounts', 'state_based', 1, 'Business bank account setup assistance (Traditional: JPMorgan Chase, Bank of America, Wells Fargo, Citibank, PNC Bank)', 1, NOW(), NOW()),
(33, 'us', 'banking-finance-services', 'payment-gateway-setup', 'state_based', 1, 'Stripe / PayPal / Wise integration', 1, NOW(), NOW()),
(34, 'us', 'banking-finance-services', 'bookkeeping-accounting-setup', 'state_based', 1, 'Bookkeeping & Accounting setup', 1, NOW(), NOW());

-- US Tax & Compliance Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(35, 'us', 'tax-compliance-services', 'sales-tax-registration', 'state_based', 1, 'Sales tax registration (e-Commerce or retail)', 1, NOW(), NOW()),
(36, 'us', 'tax-compliance-services', 'federal-tax-filing', 'state_based', 1, 'Federal tax filing (Form 1120 / 1065 etc.)', 1, NOW(), NOW()),
(37, 'us', 'tax-compliance-services', 'payroll-setup-for-employees', 'state_based', 1, 'Payroll setup for employees (ADP, Gusto)', 1, NOW(), NOW()),
(38, 'us', 'tax-compliance-services', 'state-board-of-election-registration', 'state_based', 1, 'State Board of Election Registration', 1, NOW(), NOW());

-- US Business Address & Communication
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(39, 'us', 'business-address-communication', 'virtual-us-business-address', 'state_based', 1, 'Virtual US business address', 1, NOW(), NOW()),
(40, 'us', 'business-address-communication', 'document-scanning-email-forwarding', 'state_based', 1, 'Document scanning & email forwarding', 1, NOW(), NOW()),
(41, 'us', 'business-address-communication', 'mail-forwarding-service', 'state_based', 1, 'Mail forwarding service (important for non-residents)', 1, NOW(), NOW());

-- US Extras (Value-Added Services)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(42, 'us', 'extras-value-added-services', 'trademark-registration', 'state_based', 1, 'Trademark registration', 1, NOW(), NOW()),
(43, 'us', 'extras-value-added-services', 'bid-bond-performance-bond', 'state_based', 1, 'Bid Bond & Performance Bond', 1, NOW(), NOW()),
(44, 'us', 'extras-value-added-services', 'liability-insurance', 'state_based', 1, 'Liability Insurance', 1, NOW(), NOW());

-- US Add-On / Premium Services
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(45, 'us', 'add-on-premium-services', 'business-plan-writing-consulting', 'fixed_price', 0, 'Business Plan Writing & Consulting - Contact us for pricing', 1, NOW(), NOW()),
(46, 'us', 'add-on-premium-services', 'website-domain-registration', 'fixed_price', 0, 'Website & Domain Registration - Contact us for pricing', 1, NOW(), NOW()),
(47, 'us', 'add-on-premium-services', 'seo-digital-marketing', 'fixed_price', 0, 'SEO & Digital Marketing - Contact us for pricing', 1, NOW(), NOW());

-- =============================================================================
-- 5. FEDERAL SERVICES
-- =============================================================================

INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(48, 'federal', 'other-services', 'reseller-certificate', 'state_based', 1, 'Reseller Certificate - State registration with standard or premium processing', 1, NOW(), NOW()),
(49, 'federal', 'other-services', 'government-portal-registration', 'state_based', 1, 'Government Portal Registration - State bidding portal access', 1, NOW(), NOW());

-- =============================================================================
-- 6. SERVICE PACKAGE PRICING
-- =============================================================================

-- UK Services Package Pricing
-- Service 1: director-service-address
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(1, 1, 50.00, 'Standard Director Service Address', 1, NOW(), NOW()),
(1, 2, 100.00, 'Premium Director Service Address', 1, NOW(), NOW());

-- Service 2: local-business-license-guidance
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(2, 1, 3000.00, 'Standard Local business license guidance', 1, NOW(), NOW()),
(2, 2, 6000.00, 'Premium Local business license guidance', 1, NOW(), NOW());

-- Service 3: apostille-legalisation-documents
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(3, 1, 100.00, 'Standard Apostille & Legalisation (Per Document)', 1, NOW(), NOW()),
(3, 2, 300.00, 'Premium Apostille & Legalisation (Per Document)', 1, NOW(), NOW());

-- Service 4: annual-confirmation-statement-filing
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(4, 1, 150.00, 'Standard Annual Confirmation Statement Filing', 1, NOW(), NOW()),
(4, 2, 250.00, 'Premium Annual Confirmation Statement Filing', 1, NOW(), NOW());

-- Service 5: annual-accounts-filing
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(5, 1, 1500.00, 'Standard Annual Accounts Filing', 1, NOW(), NOW()),
(5, 2, 2500.00, 'Premium Annual Accounts Filing', 1, NOW(), NOW());

-- Service 6: vat-registration-filing
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(6, 1, 1000.00, 'Standard VAT Registration & Filing', 1, NOW(), NOW()),
(6, 2, 2000.00, 'Premium VAT Registration & Filing', 1, NOW(), NOW());

-- Service 7: corporation-tax-registration-hmrc
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(7, 1, 800.00, 'Standard Corporation Tax Registration (HMRC)', 1, NOW(), NOW()),
(7, 2, 1500.00, 'Premium Corporation Tax Registration (HMRC)', 1, NOW(), NOW());

-- Service 8: paye-payroll-registration
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(8, 1, 800.00, 'Standard PAYE (Payroll) Registration', 1, NOW(), NOW()),
(8, 2, 1500.00, 'Premium PAYE (Payroll) Registration', 1, NOW(), NOW());

-- Service 9: trademark-registration-uk-ipo
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(9, 1, 800.00, 'Standard Trademark Registration UK IPO', 1, NOW(), NOW()),
(9, 2, 1500.00, 'Premium Trademark Registration UK IPO', 1, NOW(), NOW());

-- Service 10: certificate-of-good-standing
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(10, 1, 500.00, 'Standard Certificate of Good Standing', 1, NOW(), NOW()),
(10, 2, 1000.00, 'Premium Certificate of Good Standing', 1, NOW(), NOW());

-- Service 11: dissolution-of-company-uk
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(11, 1, 500.00, 'Standard Dissolution of Company', 1, NOW(), NOW()),
(11, 2, 1000.00, 'Premium Dissolution of Company', 1, NOW(), NOW());

-- Service 12: business-bank-account-modern-banks
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(12, 1, 200.00, 'Standard Modern Bank Account Setup', 1, NOW(), NOW()),
(12, 2, 400.00, 'Premium Modern Bank Account Setup', 1, NOW(), NOW());

-- Service 13: business-bank-account-traditional-banks
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(13, 1, 3000.00, 'Standard Traditional Bank Account Setup', 1, NOW(), NOW()),
(13, 2, 4000.00, 'Premium Traditional Bank Account Setup', 1, NOW(), NOW());

-- Service 14: payment-gateway-integration
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(14, 1, 200.00, 'Standard Payment Gateway Integration', 1, NOW(), NOW()),
(14, 2, 400.00, 'Premium Payment Gateway Integration', 1, NOW(), NOW());

-- Service 15: bookkeeping-accounting-setup-uk
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(15, 1, 700.00, 'Standard Bookkeeping & Accounting Setup', 1, NOW(), NOW()),
(15, 2, 1200.00, 'Premium Bookkeeping & Accounting Setup', 1, NOW(), NOW());

-- Service 16: annual-tax-return-filing-uk
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(16, 1, 7000.00, 'Standard Annual Tax Return Filing', 1, NOW(), NOW()),
(16, 2, 10000.00, 'Premium Annual Tax Return Filing', 1, NOW(), NOW());

-- Service 17: registered-office-address
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(17, 1, 200.00, 'Standard Registered Office Address', 1, NOW(), NOW()),
(17, 2, 300.00, 'Premium Registered Office Address', 1, NOW(), NOW());

-- Service 18: business-correspondence-address
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(18, 1, 350.00, 'Standard Business Correspondence Address', 1, NOW(), NOW()),
(18, 2, 400.00, 'Premium Business Correspondence Address', 1, NOW(), NOW());

-- Service 19: physical-virtual-office
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(19, 1, 400.00, 'Standard Physical/Virtual Office', 1, NOW(), NOW()),
(19, 2, 700.00, 'Premium Physical/Virtual Office', 1, NOW(), NOW());

-- Service 20: virtual-office-mail-forwarding
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(20, 1, 400.00, 'Standard Virtual Office & Mail Forwarding', 1, NOW(), NOW()),
(20, 2, 700.00, 'Premium Virtual Office & Mail Forwarding', 1, NOW(), NOW());

-- US Services Package Pricing
-- Service 24: dba-fictitious-name
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(24, 1, 199.00, 'Standard DBA/Fictitious Name (3 Weeks)', 1, NOW(), NOW()),
(24, 2, 249.00, 'Premium DBA/Fictitious Name (3 Days)', 1, NOW(), NOW());

-- Service 25: registered-agent-service
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(25, 1, 120.00, 'Standard Registered Agent Service', 1, NOW(), NOW()),
(25, 2, 150.00, 'Premium Registered Agent Service', 1, NOW(), NOW());

-- Service 26: ein-with-irs
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(26, 1, 99.00, 'Standard EIN Application', 1, NOW(), NOW()),
(26, 2, 150.00, 'Premium EIN Application', 1, NOW(), NOW());

-- Service 27: itin-for-non-residents
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(27, 1, 300.00, 'Standard ITIN Application', 1, NOW(), NOW()),
(27, 2, 600.00, 'Premium ITIN Application', 1, NOW(), NOW());

-- Service 28: annual-report-compliance-filings
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(28, 1, 149.00, 'Standard Annual Report Filing', 1, NOW(), NOW()),
(28, 2, 249.00, 'Premium Annual Report Filing', 1, NOW(), NOW());

-- Service 29: certificate-of-good-standing
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(29, 1, 149.00, 'Standard Certificate of Good Standing', 1, NOW(), NOW()),
(29, 2, 249.00, 'Premium Certificate of Good Standing', 1, NOW(), NOW());

-- Service 30: dissolve-your-company
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(30, 1, 249.00, 'Standard Company Dissolution', 1, NOW(), NOW()),
(30, 2, 299.00, 'Premium Company Dissolution', 1, NOW(), NOW());

-- Service 31: fintech-bank-accounts
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(31, 1, 199.00, 'Standard Fintech Bank Account Setup', 1, NOW(), NOW()),
(31, 2, 299.00, 'Premium Fintech Bank Account Setup', 1, NOW(), NOW());

-- Service 32: traditional-bank-accounts
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(32, 1, 2999.00, 'Standard Traditional Bank Account Setup', 1, NOW(), NOW()),
(32, 2, 3999.00, 'Premium Traditional Bank Account Setup', 1, NOW(), NOW());

-- Service 33: payment-gateway-setup
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(33, 1, 99.00, 'Standard Payment Gateway Setup', 1, NOW(), NOW()),
(33, 2, 149.00, 'Premium Payment Gateway Setup', 1, NOW(), NOW());

-- Service 34: bookkeeping-accounting-setup
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(34, 1, 399.00, 'Standard Bookkeeping Setup', 1, NOW(), NOW()),
(34, 2, 699.00, 'Premium Bookkeeping Setup', 1, NOW(), NOW());

-- Service 35: sales-tax-registration
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(35, 1, 149.00, 'Standard Sales Tax Registration', 1, NOW(), NOW()),
(35, 2, 199.00, 'Premium Sales Tax Registration', 1, NOW(), NOW());

-- Service 36: federal-tax-filing
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(36, 1, 1999.00, 'Standard Federal Tax Filing', 1, NOW(), NOW()),
(36, 2, 2999.00, 'Premium Federal Tax Filing', 1, NOW(), NOW());

-- Service 37: payroll-setup-for-employees
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(37, 1, 299.00, 'Standard Payroll Setup', 1, NOW(), NOW()),
(37, 2, 399.00, 'Premium Payroll Setup', 1, NOW(), NOW());

-- Service 38: state-board-of-election-registration
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(38, 1, 200.00, 'Standard Board of Election Registration', 1, NOW(), NOW()),
(38, 2, 300.00, 'Premium Board of Election Registration', 1, NOW(), NOW());

-- Service 39: virtual-us-business-address
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(39, 1, 149.00, 'Standard Virtual Business Address', 1, NOW(), NOW()),
(39, 2, 199.00, 'Premium Virtual Business Address', 1, NOW(), NOW());

-- Service 40: document-scanning-email-forwarding
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(40, 1, 119.00, 'Standard Document Scanning', 1, NOW(), NOW()),
(40, 2, 149.00, 'Premium Document Scanning', 1, NOW(), NOW());

-- Service 41: mail-forwarding-service
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(41, 1, 149.00, 'Standard Mail Forwarding', 1, NOW(), NOW()),
(41, 2, 199.00, 'Premium Mail Forwarding', 1, NOW(), NOW());

-- Service 42: trademark-registration
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(42, 1, 249.00, 'Standard Trademark Registration', 1, NOW(), NOW()),
(42, 2, 349.00, 'Premium Trademark Registration', 1, NOW(), NOW());

-- Service 43: bid-bond-performance-bond (Note: Data shows "bonds" in federal list, mapping to extras here)
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(43, 1, 300.00, 'Standard Bid Bond & Performance Bond', 1, NOW(), NOW()),
(43, 2, 500.00, 'Premium Bid Bond & Performance Bond', 1, NOW(), NOW());

-- Service 44: liability-insurance
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(44, 1, 250.00, 'Standard Liability Insurance', 1, NOW(), NOW()),
(44, 2, 400.00, 'Premium Liability Insurance', 1, NOW(), NOW());

-- Federal Services Package Pricing
-- Service 48: reseller-certificate
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(48, 1, 150.00, 'Standard Reseller Certificate - Standard Processing (10 Business Days)', 1, NOW(), NOW()),
(48, 2, 200.00, 'Premium Reseller Certificate - Priority Processing (3 Business Days)', 1, NOW(), NOW());

-- Service 49: government-portal-registration
INSERT INTO wp_dsf_service_package_pricing (service_id, package_type_id, price, description, enabled, created_at, updated_at) VALUES
(49, 1, 50.00, 'Standard Government Portal Registration - State Portal Access', 1, NOW(), NOW()),
(49, 2, 100.00, 'Premium Government Portal Registration - State Bidding Ready', 1, NOW(), NOW());

-- =============================================================================
-- 7. SERVICE LOCATION PRICING (All UK and US services get all 51 states/locations)
-- =============================================================================

-- For UK Services (Services 1-20): All get all 51 US states with 0 price (package determines price)
-- For US Services (Services 24-44): All get all 51 US states with 0 price (package determines price)
-- For Federal Services (Services 48-49): Get all 51 states with STATE FEES as standard_price

-- UK Services Location Pricing (Services 1-20) - All 51 states with 0 price
INSERT INTO wp_dsf_service_location_pricing (service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at)
SELECT s.id, l.id, 0.00, NULL, 0, 1, NOW(), NOW()
FROM wp_dsf_services s
CROSS JOIN wp_dsf_locations l
WHERE s.id BETWEEN 1 AND 20;

-- US Services Location Pricing (Services 24-44) - All 51 states with 0 price
INSERT INTO wp_dsf_service_location_pricing (service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at)
SELECT s.id, l.id, 0.00, NULL, 0, 1, NOW(), NOW()
FROM wp_dsf_services s
CROSS JOIN wp_dsf_locations l
WHERE s.id BETWEEN 24 AND 44;

-- Federal Service 48: Reseller Certificate - State Fees
INSERT INTO wp_dsf_service_location_pricing (service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at) VALUES
(48, 1, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Alabama
(48, 2, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Alaska
(48, 3, 12.00, 12.00, 0, 1, NOW(), NOW()),  -- Arizona
(48, 4, 50.00, 50.00, 0, 1, NOW(), NOW()),  -- Arkansas
(48, 5, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- California
(48, 6, 70.00, 70.00, 0, 1, NOW(), NOW()),  -- Colorado
(48, 7, 100.00, 100.00, 0, 1, NOW(), NOW()),  -- Connecticut
(48, 8, 0.00, 0.00, 1, 1, NOW(), NOW()),  -- Delaware (Not Applicable - marked as universal)
(48, 9, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Florida
(48, 10, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Georgia
(48, 11, 20.00, 20.00, 0, 1, NOW(), NOW()),  -- Hawaii
(48, 12, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Idaho
(48, 13, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Illinois
(48, 14, 25.00, 25.00, 0, 1, NOW(), NOW()),  -- Indiana
(48, 15, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Iowa
(48, 16, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Kansas
(48, 17, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Kentucky
(48, 18, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Louisiana
(48, 19, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Maine
(48, 20, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Maryland
(48, 21, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Massachusetts
(48, 22, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Michigan
(48, 23, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Minnesota
(48, 24, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Mississippi
(48, 25, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Missouri
(48, 26, 0.00, 0.00, 1, 1, NOW(), NOW()),  -- Montana (Not Applicable)
(48, 27, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Nebraska
(48, 28, 15.00, 15.00, 0, 1, NOW(), NOW()),  -- Nevada
(48, 29, 0.00, 0.00, 1, 1, NOW(), NOW()),  -- New Hampshire (Not Applicable)
(48, 30, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- New Jersey
(48, 31, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- New Mexico
(48, 32, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- New York
(48, 33, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- North Carolina
(48, 34, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- North Dakota
(48, 35, 25.00, 25.00, 0, 1, NOW(), NOW()),  -- Ohio
(48, 36, 30.00, 30.00, 0, 1, NOW(), NOW()),  -- Oklahoma
(48, 37, 0.00, 0.00, 1, 1, NOW(), NOW()),  -- Oregon (Not Applicable)
(48, 38, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Pennsylvania
(48, 39, 10.00, 10.00, 0, 1, NOW(), NOW()),  -- Rhode Island
(48, 40, 50.00, 50.00, 0, 1, NOW(), NOW()),  -- South Carolina
(48, 41, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- South Dakota
(48, 42, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Tennessee
(48, 43, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Texas
(48, 44, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Utah
(48, 45, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Vermont
(48, 46, 0.00, 0.00, 0, 1, NOW(), NOW()),  -- Virginia
(48, 47, 110.00, 110.00, 0, 1, NOW(), NOW()),  -- Washington
(48, 48, 30.00, 30.00, 0, 1, NOW(), NOW()),  -- West Virginia
(48, 49, 20.00, 20.00, 0, 1, NOW(), NOW()),  -- Wisconsin
(48, 50, 60.00, 60.00, 0, 1, NOW(), NOW()),  -- Wyoming
(48, 51, 0.00, 0.00, 0, 1, NOW(), NOW());  -- District of Columbia

-- Federal Service 49: Government Portal Registration - All states $0 (package determines price)
INSERT INTO wp_dsf_service_location_pricing (service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at)
SELECT 49, l.id, 0.00, 0.00, 
    CASE 
        WHEN l.location_code IN ('DE', 'MT', 'NH', 'OR') THEN 1 
        ELSE 0 
    END as is_universal, 
    1, NOW(), NOW()
FROM wp_dsf_locations l;

-- =============================================================================
-- COMPLETED
-- =============================================================================
-- Summary:
-- - 49 Services total (23 UK + 22 US + 2 Federal + 2 Fixed Price UK/US)
-- - 51 Locations (All US states + DC)
-- - 2 Package Types (Standard, Premium)
-- - All UK services (1-20): All 51 states, package pricing
-- - All US services (24-44): All 51 states, package pricing
-- - Federal Reseller Certificate (48): All 51 states with specific state fees
-- - Federal Portal Registration (49): All 51 states, package pricing
-- =============================================================================



-- =============================================================================
-- Dynamic Services Form - Remaining Federal Services
-- =============================================================================
-- Instructions:
-- 1. Run this AFTER the main SQL file
-- 2. This adds the remaining 5 Federal services
-- =============================================================================

-- =============================================================================
-- 1. INSERT REMAINING FEDERAL SERVICES
-- =============================================================================

-- Service 50: federal-third-party-bidding-setup (portal_based)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(50, 'federal', 'other-services', 'federal-third-party-bidding-setup', 'portal_based', 0, 'Federal & Third-Party Bidding Setup - Select portals for registration', 1, NOW(), NOW());

-- Service 51: duns-number-registration (fixed_price)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(51, 'federal', 'other-services', 'duns-number-registration', 'fixed_price', 0, 'D-U-N-S Number Registration - Fixed pricing', 1, NOW(), NOW());

-- Service 52: liability-insurance-for-business (fixed_price)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(52, 'federal', 'other-services', 'liability-insurance-for-business', 'fixed_price', 0, 'Liability Insurance for Business - Fixed pricing', 1, NOW(), NOW());

-- Service 53: notary-services (fixed_price)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(53, 'federal', 'other-services', 'notary-services', 'fixed_price', 0, 'Notary Services - Fixed pricing', 1, NOW(), NOW());

-- Service 54: bonds (calculator)
INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(54, 'federal', 'other-services', 'bonds', 'calculator', 0, 'Bonds - Tiered pricing based on bond amount ($350k-$500k: $900, $500k-$2M: $1,500, $2M+: 1% of amount)', 1, NOW(), NOW());

-- =============================================================================
-- 2. INSERT PORTALS FOR SERVICE 50 (federal-third-party-bidding-setup)
-- =============================================================================

INSERT INTO wp_dsf_portals (id, service_id, portal_name, price, enabled, created_at, updated_at) VALUES
(5, 50, 'SAM.gov', 1700.00, 1, NOW(), NOW()),
(6, 50, 'GSA Advantage', 2000.00, 1, NOW(), NOW()),
(7, 50, 'GSA eBuy', 2500.00, 1, NOW(), NOW()),
(8, 50, 'DLA DIBBS', 1500.00, 1, NOW(), NOW()),
(9, 50, 'NASA SEWP', 1000.00, 1, NOW(), NOW()),
(10, 50, 'SUB-Net', 300.00, 1, NOW(), NOW()),
(11, 50, 'FPDS', 800.00, 1, NOW(), NOW()),
(12, 50, 'NIH eRA Commons', 800.00, 1, NOW(), NOW()),
(13, 50, 'FedBid', 500.00, 1, NOW(), NOW()),
(14, 50, 'Unison Marketplace', 500.00, 1, NOW(), NOW()),
(15, 50, 'Grants.gov', 500.00, 1, NOW(), NOW()),
(16, 50, 'SBA DSBS', 1000.00, 1, NOW(), NOW()),
(17, 50, 'BidNet Direct', 300.00, 1, NOW(), NOW()),
(18, 50, 'Bonfire', 1200.00, 1, NOW(), NOW()),
(19, 50, 'PlanetBids', 900.00, 1, NOW(), NOW()),
(20, 50, 'GovWin IQ (Deltek)', 12000.00, 1, NOW(), NOW()),
(21, 50, 'BidSync (Periscope)', 13000.00, 1, NOW(), NOW()),
(22, 50, 'DemandStar', 400.00, 1, NOW(), NOW()),
(23, 50, 'ConstructConnect', 1200.00, 1, NOW(), NOW());

-- =============================================================================
-- NOTES FOR IMPLEMENTATION
-- =============================================================================

-- Service 50 (federal-third-party-bidding-setup):
-- - Portal-based pricing model
-- - User can select multiple portals
-- - Total price = sum of selected portal prices
-- - No state/location selection required

-- Service 51 (duns-number-registration):
-- - Fixed price model
-- - Price needs to be set in your Form.php or as a constant
-- - Suggested price based on market: $300-$500
-- - No state/location selection required

-- Service 52 (liability-insurance-for-business):
-- - Fixed price model
-- - Price needs to be set in your Form.php or as a constant
-- - Suggested price based on market: $350-$1,700
-- - No state/location selection required

-- Service 53 (notary-services):
-- - Fixed price model
-- - Price needs to be set in your Form.php or as a constant
-- - Suggested price based on market: $150-$300
-- - No state/location selection required

-- Service 54 (bonds):
-- - Calculator pricing model
-- - Tiered pricing based on bond amount:
--   * $350,000 - $500,000: $900.00
--   * $500,000 - $2,000,000: $1,500.00
--   * $2,000,000+: 1% of total amount
-- - User inputs amount, price calculated client-side
-- - No state/location selection required
-- - Already implemented in your existing JavaScript

-- =============================================================================
-- COMPLETED
-- =============================================================================
-- Summary of ALL Federal Services:
-- - Service 48: reseller-certificate (state_based with packages)
-- - Service 49: government-portal-registration (state_based with packages)
-- - Service 50: federal-third-party-bidding-setup (portal_based - 19 portals)
-- - Service 51: duns-number-registration (fixed_price)
-- - Service 52: liability-insurance-for-business (fixed_price)
-- - Service 53: notary-services (fixed_price)
-- - Service 54: bonds (calculator)
-- =============================================================================





-- =============================================================================
-- Dynamic Services Form - Certificate of Good Standing (Federal Service)
-- =============================================================================
-- Instructions:
-- 1. Run this AFTER the previous Federal services SQL
-- 2. This adds Certificate of Good Standing with state-specific pricing
-- =============================================================================

-- =============================================================================
-- 1. INSERT CERTIFICATE OF GOOD STANDING SERVICE
-- =============================================================================

INSERT INTO wp_dsf_services (id, type, category, name, pricing_model, has_packages, description, enabled, created_at, updated_at) VALUES
(55, 'federal', 'other-services', 'certificate-of-good-standing', 'state_based', 0, 'Certificate of Good Standing - State-specific pricing without packages', 1, NOW(), NOW());

-- =============================================================================
-- 2. INSERT STATE PRICING FOR CERTIFICATE OF GOOD STANDING
-- =============================================================================
-- This service is state_based but has_packages = 0
-- Price is set in standard_price column
-- No packages needed, just select state and pay the state price

INSERT INTO wp_dsf_service_location_pricing (service_id, location_id, standard_price, premium_price, is_universal, enabled, created_at, updated_at) VALUES
(55, 1, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Alabama - $100.00
(55, 2, 50.00, NULL, 0, 1, NOW(), NOW()),   -- Alaska - $50.00
(55, 3, 50.00, NULL, 0, 1, NOW(), NOW()),   -- Arizona - $50.00
(55, 4, 75.00, NULL, 0, 1, NOW(), NOW()),   -- Arkansas - $75.00
(55, 5, 50.00, NULL, 0, 1, NOW(), NOW()),   -- California - $50.00
(55, 6, 50.00, NULL, 0, 1, NOW(), NOW()),   -- Colorado - $50.00
(55, 7, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Connecticut - $100.00
(55, 8, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Delaware - $100.00
(55, 9, 60.00, NULL, 0, 1, NOW(), NOW()),   -- Florida - $60.00
(55, 10, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Georgia - $50.00
(55, 11, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Hawaii - $50.00
(55, 12, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Idaho - $50.00
(55, 13, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Illinois - $100.00
(55, 14, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Indiana - $50.00
(55, 15, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Iowa - $50.00
(55, 16, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Kansas - $50.00
(55, 17, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Kentucky - $50.00
(55, 18, 60.00, NULL, 0, 1, NOW(), NOW()),  -- Louisiana - $60.00
(55, 19, 80.00, NULL, 0, 1, NOW(), NOW()),  -- Maine - $80.00
(55, 20, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Maryland - $100.00
(55, 21, 75.00, NULL, 0, 1, NOW(), NOW()),  -- Massachusetts - $75.00
(55, 22, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Michigan - $50.00
(55, 23, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Minnesota - $50.00
(55, 24, 75.00, NULL, 0, 1, NOW(), NOW()),  -- Mississippi - $75.00
(55, 25, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Missouri - $50.00
(55, 26, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Montana - $50.00
(55, 27, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Nebraska - $50.00
(55, 28, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Nevada - $100.00
(55, 29, 50.00, NULL, 0, 1, NOW(), NOW()),  -- New Hampshire - $50.00
(55, 30, 75.00, NULL, 0, 1, NOW(), NOW()),  -- New Jersey - $75.00
(55, 31, 75.00, NULL, 0, 1, NOW(), NOW()),  -- New Mexico - $75.00
(55, 32, 75.00, NULL, 0, 1, NOW(), NOW()),  -- New York - $75.00
(55, 33, 75.00, NULL, 0, 1, NOW(), NOW()),  -- North Carolina - $75.00
(55, 34, 75.00, NULL, 0, 1, NOW(), NOW()),  -- North Dakota - $75.00
(55, 35, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Ohio - $50.00
(55, 36, 70.00, NULL, 0, 1, NOW(), NOW()),  -- Oklahoma - $70.00
(55, 37, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Oregon - $50.00
(55, 38, 100.00, NULL, 0, 1, NOW(), NOW()),  -- Pennsylvania - $100.00
(55, 39, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Rhode Island - $50.00
(55, 40, 50.00, NULL, 0, 1, NOW(), NOW()),  -- South Carolina - $50.00
(55, 41, 70.00, NULL, 0, 1, NOW(), NOW()),  -- South Dakota - $70.00
(55, 42, 70.00, NULL, 0, 1, NOW(), NOW()),  -- Tennessee - $70.00
(55, 43, 70.00, NULL, 0, 1, NOW(), NOW()),  -- Texas - $70.00
(55, 44, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Utah - $50.00
(55, 45, 75.00, NULL, 0, 1, NOW(), NOW()),  -- Vermont - $75.00
(55, 46, 75.00, NULL, 0, 1, NOW(), NOW()),  -- Virginia - $75.00
(55, 47, 75.00, NULL, 0, 1, NOW(), NOW()),  -- Washington - $75.00
(55, 48, 50.00, NULL, 0, 1, NOW(), NOW()),  -- West Virginia - $50.00
(55, 49, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Wisconsin - $50.00
(55, 50, 50.00, NULL, 0, 1, NOW(), NOW()),  -- Wyoming - $50.00
(55, 51, 100.00, NULL, 0, 1, NOW(), NOW()); -- District of Columbia - $100.00

-- =============================================================================
-- NOTES FOR IMPLEMENTATION
-- =============================================================================

-- Service 55 (certificate-of-good-standing):
-- - State-based pricing model
-- - has_packages = 0 (no package selection)
-- - User only selects state
-- - Price comes from standard_price in service_location_pricing table
-- - No package selection needed
-- - Price varies by state from $50 to $100

-- Price Range:
-- - Most common: $50.00 (26 states)
-- - $60.00: 2 states (Florida, Louisiana)
-- - $70.00: 5 states (Oklahoma, South Dakota, Tennessee, Texas)
-- - $75.00: 11 states (Arkansas, Massachusetts, Mississippi, New Jersey, etc.)
-- - $80.00: 1 state (Maine)
-- - $100.00: 6 states (Alabama, Connecticut, Delaware, Illinois, Maryland, Nevada, Pennsylvania, DC)

-- =============================================================================
-- COMPLETED - ALL FEDERAL SERVICES
-- =============================================================================
-- Summary of ALL Federal Services (Complete):
-- 
-- 1. Service 48: reseller-certificate (state_based with packages + state fees)
-- 2. Service 49: government-portal-registration (state_based with packages)
-- 3. Service 50: federal-third-party-bidding-setup (portal_based - 19 portals)
-- 4. Service 51: duns-number-registration (fixed_price - $300)
-- 5. Service 52: liability-insurance-for-business (fixed_price - $1,700)
-- 6. Service 53: notary-services (fixed_price - $150)
-- 7. Service 54: bonds (calculator - tiered pricing)
-- 8. Service 55: certificate-of-good-standing (state_based, no packages, state-specific pricing)
-- 
-- Total Federal Services: 8
-- =============================================================================