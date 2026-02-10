# Dynamic Services Form – Database Schema (from code)

This schema is derived from `includes/Database.php`. Table names use the WordPress prefix (e.g. `wp_`); replace with your actual `$wpdb->prefix` if different.

---

## Table order (creation / dependencies)

1. **dsf_services** – no dependencies  
2. **dsf_package_types** – no dependencies  
3. **dsf_service_package_pricing** – depends on `dsf_services`, `dsf_package_types`  
4. **dsf_locations** – no dependencies  
5. **dsf_service_location_pricing** – depends on `dsf_services`, `dsf_locations`  
6. **dsf_portals** – depends on `dsf_services`  
7. **dsf_submissions** – depends on `dsf_services`  

---

## 1. `{prefix}dsf_services`

| Column           | Type             | Null | Default               | Description                          |
|-----------------|------------------|------|------------------------|--------------------------------------|
| id              | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                          |
| type            | VARCHAR(100)     | NO   | -                     | Service type (e.g. USA, UK)          |
| category        | VARCHAR(100)     | NO   | -                     | Service category                     |
| name            | VARCHAR(255)     | NO   | -                     | Service name                         |
| pricing_model   | VARCHAR(50)      | NO   | 'state_based'         | state_based, portal_based, fixed_price, calculator |
| has_packages    | TINYINT(1)       | NO   | 0                     | 1 = has package tiers                |
| description     | LONGTEXT         | YES  | NULL                  | Service description                  |
| enabled         | TINYINT(1)       | NO   | 1                     | 1 = enabled                          |
| created_at      | DATETIME         | YES  | CURRENT_TIMESTAMP     |                                      |
| updated_at      | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                          |

- **UNIQUE KEY** `unique_service` (type, category, name)  
- **KEY** type_category (type, category)  
- **KEY** pricing_model (pricing_model)  
- **KEY** enabled (enabled)  

---

## 2. `{prefix}dsf_package_types`

| Column             | Type             | Null | Default               | Description                |
|--------------------|------------------|------|------------------------|----------------------------|
| id                 | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| package_type_name  | VARCHAR(100)     | NO   | -                     | Unique name (e.g. Standard, Premium) |
| description        | LONGTEXT         | YES  | NULL                  | Optional description       |
| enabled            | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at         | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at         | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **UNIQUE KEY** on package_type_name  
- **KEY** enabled (enabled)  

---

## 3. `{prefix}dsf_service_package_pricing`

Junction table: service ↔ package type with price.

| Column           | Type             | Null | Default               | Description                |
|------------------|------------------|------|------------------------|----------------------------|
| id               | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| service_id        | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_services.id       |
| package_type_id  | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_package_types.id  |
| price            | DECIMAL(10,2)    | YES  | NULL                  | Price for this combo       |
| description       | LONGTEXT         | YES  | NULL                  | Optional                   |
| enabled          | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at       | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at       | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **UNIQUE KEY** `unique_service_package` (service_id, package_type_id)  
- **KEY** service_id, package_type_id, enabled  
- **FOREIGN KEY** service_id → dsf_services(id) ON DELETE CASCADE  
- **FOREIGN KEY** package_type_id → dsf_package_types(id) ON DELETE CASCADE  

---

## 4. `{prefix}dsf_locations`

Stores states/regions (reusable across services).

| Column         | Type             | Null | Default               | Description                |
|----------------|------------------|------|------------------------|----------------------------|
| id             | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| location_name  | VARCHAR(100)     | NO   | -                     | e.g. California, England   |
| location_code  | VARCHAR(10)      | YES  | NULL                  | e.g. CA                    |
| location_type  | VARCHAR(50)      | NO   | 'state'               | e.g. state, region         |
| enabled        | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at     | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at     | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **UNIQUE KEY** `unique_location` (location_name, location_type)  
- **KEY** location_type (location_type)  
- **KEY** enabled (enabled)  

---

## 5. `{prefix}dsf_service_location_pricing`

Junction table: service ↔ location with standard/premium pricing.

| Column          | Type             | Null | Default               | Description                         |
|-----------------|------------------|------|------------------------|-------------------------------------|
| id              | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                         |
| service_id      | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_services.id                |
| location_id     | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_locations.id               |
| standard_price  | DECIMAL(10,2)    | YES  | NULL                  | Standard tier price                  |
| premium_price   | DECIMAL(10,2)    | YES  | NULL                  | Premium tier price (optional)        |
| is_universal    | TINYINT(1)       | NO   | 0                     | 1 = same price for all locations    |
| enabled         | TINYINT(1)       | NO   | 1                     | 1 = enabled                         |
| created_at      | DATETIME         | YES  | CURRENT_TIMESTAMP     |                                     |
| updated_at      | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                             |

# Dynamic Services Form – Database Schema (from code)

This schema is derived from `includes/Database.php`. Table names use the WordPress prefix (e.g. `wp_`); replace with your actual `$wpdb->prefix` if different.

---

## Table order (creation / dependencies)

1. **dsf_services** – no dependencies
2. **dsf_package_types** – no dependencies
3. **dsf_service_package_pricing** – depends on `dsf_services`, `dsf_package_types`
4. **dsf_locations** – no dependencies
5. **dsf_service_location_pricing** – depends on `dsf_services`, `dsf_locations`
6. **dsf_portals** – depends on `dsf_services`
7. **dsf_submissions** – depends on `dsf_services`

---

## 1. `{prefix}dsf_services`

| Column           | Type             | Null | Default               | Description                          |
|------------------|------------------|------|------------------------|--------------------------------------|
| id               | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                          |
| type             | VARCHAR(100)     | NO   | -                     | Service type (e.g. USA, UK)          |
| category         | VARCHAR(100)     | NO   | -                     | Service category                     |
| name             | VARCHAR(255)     | NO   | -                     | Service name                         |
| pricing_model    | VARCHAR(50)      | NO   | 'state_based'         | state_based, portal_based, fixed_price, calculator |
| has_packages     | TINYINT(1)       | NO   | 0                     | 1 = has package tiers                |
| description      | LONGTEXT         | YES  | NULL                  | Service description                  |
| enabled          | TINYINT(1)       | NO   | 1                     | 1 = enabled                          |
| created_at       | DATETIME         | YES  | CURRENT_TIMESTAMP     |                                      |
| updated_at       | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                          |

- **UNIQUE KEY** `unique_service` (type, category, name)
- **KEY** type_category (type, category)
- **KEY** pricing_model (pricing_model)
- **KEY** enabled (enabled)

---

## 2. `{prefix}dsf_package_types`

| Column             | Type             | Null | Default               | Description                |
|--------------------|------------------|------|------------------------|----------------------------|
| id                 | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| package_type_name  | VARCHAR(100)     | NO   | -                     | Unique name (e.g. Standard, Premium) |
| description        | LONGTEXT         | YES  | NULL                  | Optional description       |
| enabled            | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at         | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at         | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **UNIQUE KEY** on package_type_name
- **KEY** enabled (enabled)

---

## 3. `{prefix}dsf_service_package_pricing`

Junction table: service ↔ package type with price.

| Column           | Type             | Null | Default               | Description                |
|------------------|------------------|------|------------------------|----------------------------|
| id               | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| service_id       | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_services.id       |
| package_type_id  | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_package_types.id  |
| price            | DECIMAL(10,2)    | YES  | NULL                  | Price for this combo       |
| description      | LONGTEXT         | YES  | NULL                  | Optional                   |
| enabled          | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at       | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at       | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **UNIQUE KEY** `unique_service_package` (service_id, package_type_id)
- **KEY** service_id, package_type_id, enabled
- **FOREIGN KEY** service_id → dsf_services(id) ON DELETE CASCADE
- **FOREIGN KEY** package_type_id → dsf_package_types(id) ON DELETE CASCADE

---

## 4. `{prefix}dsf_locations`

Stores states/regions (reusable across services).

| Column         | Type             | Null | Default               | Description                |
|----------------|------------------|------|------------------------|----------------------------|
| id             | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| location_name  | VARCHAR(100)     | NO   | -                     | e.g. California, England   |
| location_code  | VARCHAR(10)      | YES  | NULL                  | e.g. CA                    |
| location_type  | VARCHAR(50)      | NO   | 'state'               | e.g. state, region         |
| enabled        | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at     | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at     | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **UNIQUE KEY** `unique_location` (location_name, location_type)
- **KEY** location_type (location_type)
- **KEY** enabled (enabled)

---

## 5. `{prefix}dsf_service_location_pricing`

Junction table: service ↔ location with standard/premium pricing.

| Column          | Type             | Null | Default               | Description                         |
|-----------------|------------------|------|------------------------|-------------------------------------|
| id              | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                         |
| service_id      | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_services.id                |
| location_id     | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_locations.id               |
| standard_price  | DECIMAL(10,2)    | YES  | NULL                  | Standard tier price                 |
| premium_price   | DECIMAL(10,2)    | YES  | NULL                  | Premium tier price (optional)       |
| is_universal    | TINYINT(1)       | NO   | 0                     | 1 = same price for all locations    |
| enabled         | TINYINT(1)       | NO   | 1                     | 1 = enabled                         |
| created_at      | DATETIME         | YES  | CURRENT_TIMESTAMP     |                                     |
| updated_at      | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                             |

- **UNIQUE KEY** `unique_service_location` (service_id, location_id)
- **KEY** service_id, location_id, is_universal, enabled
- **FOREIGN KEY** service_id → dsf_services(id) ON DELETE CASCADE
- **FOREIGN KEY** location_id → dsf_locations(id) ON DELETE CASCADE

---

## 6. `{prefix}dsf_portals`

Portals per service (used for `pricing_model = 'portal_based'`).

| Column       | Type             | Null | Default               | Description                |
|--------------|------------------|------|------------------------|----------------------------|
| id           | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| service_id   | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_services.id       |
| portal_name  | VARCHAR(255)     | NO   | -                     | Portal display name        |
| price        | DECIMAL(10,2)    | YES  | NULL                  | Portal price               |
| enabled      | TINYINT(1)       | NO   | 1                     | 1 = enabled                |
| created_at   | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at   | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **KEY** service_id, portal_name, enabled
- **FOREIGN KEY** service_id → dsf_services(id) ON DELETE CASCADE

---

## 7. `{prefix}dsf_submissions`

Form submissions (one per order/quote).

| Column          | Type             | Null | Default               | Description                |
|-----------------|------------------|------|------------------------|----------------------------|
| id              | BIGINT UNSIGNED  | NO   | AUTO_INCREMENT        | Primary key                |
| service_id      | BIGINT UNSIGNED  | NO   | -                     | FK → dsf_services.id       |
| form_data       | LONGTEXT         | NO   | -                     | JSON: location_id, package_id, portal_ids, calculator_amount, contact fields |
| total_price     | DECIMAL(10,2)    | YES  | NULL                  | Calculated total           |
| first_name      | VARCHAR(100)     | YES  | NULL                  |                            |
| last_name       | VARCHAR(100)     | YES  | NULL                  |                            |
| business_name   | VARCHAR(255)     | YES  | NULL                  |                            |
| business_address| VARCHAR(255)     | YES  | NULL                  |                            |
| phone           | VARCHAR(20)      | YES  | NULL                  |                            |
| email           | VARCHAR(255)     | YES  | NULL                  |                            |
| city            | VARCHAR(100)     | YES  | NULL                  |                            |
| state           | VARCHAR(50)      | YES  | NULL                  |                            |
| zipcode         | VARCHAR(10)      | YES  | NULL                  |                            |
| entity_type     | VARCHAR(100)     | YES  | NULL                  |                            |
| notes           | LONGTEXT         | YES  | NULL                  |                            |
| status          | VARCHAR(50)      | NO   | 'pending'             | e.g. pending, completed    |
| created_at      | DATETIME         | YES  | CURRENT_TIMESTAMP     |                            |
| updated_at      | DATETIME         | YES  | CURRENT_TIMESTAMP ON UPDATE |                    |

- **KEY** service_id, email, status, created_at

---

## Relationships (summary)

- **Services** are the main entity.
- **state_based**:
  - Locations come from **dsf_service_location_pricing** (and **dsf_locations**).
  - If **has_packages = 1**, prices come from **dsf_service_package_pricing** (user still picks a location).
  - If **has_packages = 0**, prices come from **dsf_service_location_pricing** (standard_price / premium_price).
- **portal_based**: Portals from **dsf_portals**; user can select multiple; total = sum of selected portal prices.
- **fixed_price** / **calculator**: No extra tables; price is fixed or computed in code.
- **Submissions** store `service_id`, JSON `form_data`, and denormalized contact/price/status.
