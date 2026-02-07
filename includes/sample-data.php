<?php
/**
 * Sample Data Initialization
 * 
 * Optional file to populate database with sample services, locations, and pricing for testing
 * This creates shared locations (states) that multiple services can link to with different prices
 *
 * @package DSF
 */

namespace DSF;

// Only run if WordPress is loaded
if ( ! function_exists( 'add_action' ) ) {
    return;
}

/**
 * Create sample data using the normalized Location and ServiceLocationPricing structure
 */
function create_sample_data() {
    global $wpdb;
    
    // Check if sample data already exists
    $existing = $wpdb->get_var( "SELECT COUNT(*) FROM " . Database::get_table( 'services' ) );
    if ( $existing > 0 ) {
        return; // Sample data already exists
    }

    // STEP 1: Create reusable package types (stored once)
    $package_types_data = [ 'Standard', 'Premium', 'Enterprise' ];
    $package_type_ids = [];
    foreach ( $package_types_data as $pt_name ) {
        $pt_id = PackageType::save( [
            'package_type_name' => $pt_name,
            'description'       => ucfirst( $pt_name ) . ' package option',
            'enabled'           => 1,
        ] );
        $package_type_ids[ $pt_name ] = $pt_id;
    }

    // STEP 2: Create reusable locations (normalized - stored once)
    $sample_locations_data = [
        [ 'name' => 'Alabama', 'code' => 'AL' ],
        [ 'name' => 'Alaska', 'code' => 'AK' ],
        [ 'name' => 'Arizona', 'code' => 'AZ' ],
        [ 'name' => 'Arkansas', 'code' => 'AR' ],
        [ 'name' => 'California', 'code' => 'CA' ],
        [ 'name' => 'Colorado', 'code' => 'CO' ],
        [ 'name' => 'Connecticut', 'code' => 'CT' ],
        [ 'name' => 'Delaware', 'code' => 'DE' ],
        [ 'name' => 'Florida', 'code' => 'FL' ],
        [ 'name' => 'Georgia', 'code' => 'GA' ],
        [ 'name' => 'Hawaii', 'code' => 'HI' ],
        [ 'name' => 'Idaho', 'code' => 'ID' ],
        [ 'name' => 'Illinois', 'code' => 'IL' ],
        [ 'name' => 'Indiana', 'code' => 'IN' ],
        [ 'name' => 'Iowa', 'code' => 'IA' ],
    ];

    $location_ids = [];
    foreach ( $sample_locations_data as $loc_data ) {
        $location_id = Location::save( [
            'location_name' => $loc_data['name'],
            'location_code' => $loc_data['code'],
            'location_type' => 'state',
            'enabled'       => 1,
        ] );
        $location_ids[ $loc_data['name'] ] = $location_id;
    }

    // UK locations
    $uk_locations = [ 'England', 'Scotland', 'Wales' ];
    foreach ( $uk_locations as $country ) {
        $location_id = Location::save( [
            'location_name' => $country,
            'location_code' => substr( $country, 0, 2 ),
            'location_type' => 'region',
            'enabled'       => 1,
        ] );
        $location_ids[ $country ] = $location_id;
    }

    // STEP 3: Service 1: DBA Fictitious Name (State-based with packages)
    // SAME PACKAGE TYPES, DIFFERENT PRICES per service!
    $service_1_id = Service::save( [
        'type'          => 'USA',
        'category'      => 'Core Company',
        'name'          => 'DBA Fictitious Name',
        'pricing_model' => 'state_based',
        'has_packages'  => 1,
        'description'   => 'File for a DBA (Doing Business As) or Fictitious Business Name in any US state',
        'enabled'       => 1,
    ] );

    // Link Service 1 to package types with DIFFERENT PRICING
    ServicePackagePricing::save( [
        'service_id'     => $service_1_id,
        'package_type_id' => $package_type_ids['Standard'],
        'price'          => 99.99,
        'description'    => 'Basic filing with government agency',
        'enabled'        => 1,
    ] );

    ServicePackagePricing::save( [
        'service_id'     => $service_1_id,
        'package_type_id' => $package_type_ids['Premium'],
        'price'          => 149.99,
        'description'    => 'Includes filing + registered agent + consultation',
        'enabled'        => 1,
    ] );

    ServicePackagePricing::save( [
        'service_id'     => $service_1_id,
        'package_type_id' => $package_type_ids['Enterprise'],
        'price'          => 199.99,
        'description'    => 'Full service with ongoing support and renewals',
        'enabled'        => 1,
    ] );

    // STEP 4: Service 2: EIN with IRS (State-based with packages)
    // SAME PACKAGE TYPES, DIFFERENT PRICING than Service 1!
    $service_2_id = Service::save( [
        'type'          => 'USA',
        'category'      => 'Core Company',
        'name'          => 'EIN with IRS',
        'pricing_model' => 'state_based',
        'has_packages'  => 1,
        'description'   => 'Obtain an Employer Identification Number (EIN) from the IRS',
        'enabled'       => 1,
    ] );

    // Link Service 2 to SAME package types but with DIFFERENT pricing
    ServicePackagePricing::save( [
        'service_id'     => $service_2_id,
        'package_type_id' => $package_type_ids['Standard'],
        'price'          => 49.99,
        'description'    => 'EIN application processing',
        'enabled'        => 1,
    ] );

    ServicePackagePricing::save( [
        'service_id'     => $service_2_id,
        'package_type_id' => $package_type_ids['Premium'],
        'price'          => 99.99,
        'description'    => 'EIN application + IRS consultation + expedited processing',
        'enabled'        => 1,
    ] );

    ServicePackagePricing::save( [
        'service_id'     => $service_2_id,
        'package_type_id' => $package_type_ids['Enterprise'],
        'price'          => 149.99,
        'description'    => 'Full EIN service with ongoing tax support',
        'enabled'        => 1,
    ] );

    // STEP 5: Service 3: Multi-State Filing (Portal-based, no packages)
    $service_3_id = Service::save( [
        'type'          => 'USA',
        'category'      => 'Compliance',
        'name'          => 'Multi-State Filing Portal',
        'pricing_model' => 'portal_based',
        'has_packages'  => 0,
        'description'   => 'File compliance documents across multiple state portals',
        'enabled'       => 1,
    ] );

    // Portals for Service 3
    Portal::save( [
        'service_id'  => $service_3_id,
        'portal_name' => 'Secretary of State',
        'price'       => 50.00,
        'enabled'     => 1,
    ] );

    Portal::save( [
        'service_id'  => $service_3_id,
        'portal_name' => 'IRS Portal',
        'price'       => 25.00,
        'enabled'     => 1,
    ] );

    Portal::save( [
        'service_id'  => $service_3_id,
        'portal_name' => 'County Clerk',
        'price'       => 75.00,
        'enabled'     => 1,
    ] );

    Portal::save( [
        'service_id'  => $service_3_id,
        'portal_name' => 'State Tax Board',
        'price'       => 35.00,
        'enabled'     => 1,
    ] );

    // STEP 6: Service 4: Business License (Fixed Price)
    $service_4_id = Service::save( [
        'type'          => 'USA',
        'category'      => 'Permits & Licenses',
        'name'          => 'Business License',
        'pricing_model' => 'fixed_price',
        'has_packages'  => 0,
        'description'   => 'General business operating license in any state',
        'enabled'       => 1,
    ] );

    // STEP 7: Service 5: Calculator-Based Service
    $service_5_id = Service::save( [
        'type'          => 'USA',
        'category'      => 'Custom Services',
        'name'          => 'Document Review Package',
        'pricing_model' => 'calculator',
        'has_packages'  => 0,
        'description'   => 'Custom document review hourly or per-package basis',
        'enabled'       => 1,
    ] );

    // STEP 8: Service 6: UK Company Formation (State-based, no packages)
    $service_6_id = Service::save( [
        'type'          => 'UK',
        'category'      => 'Core Company',
        'name'          => 'Company Formation',
        'pricing_model' => 'state_based',
        'has_packages'  => 0,
        'description'   => 'Register a limited company with Companies House UK',
        'enabled'       => 1,
    ] );

    // Link UK service to regions (location-based without packages)
    foreach ( [ 'England', 'Scotland', 'Wales' ] as $country ) {
        ServiceLocationPricing::save( [
            'service_id'     => $service_6_id,
            'location_id'    => $location_ids[ $country ],
            'standard_price' => 199.99,
            'premium_price'  => null,
            'enabled'        => 1,
        ] );
    }

    // STEP 9: Service 7: Federal Tax ID
    $service_7_id = Service::save( [
        'type'          => 'Federal',
        'category'      => 'Tax Services',
        'name'          => 'Federal EIN Request',
        'pricing_model' => 'fixed_price',
        'has_packages'  => 0,
        'description'   => 'Federal Employer Identification Number application',
        'enabled'       => 1,
    ] );
}

// Add admin notice to offer sample data initialization
add_action( 'admin_notices', function() {
    // Only show on DSF pages
    if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'dsf' ) === false ) {
    global $wpdb;
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM " . Database::get_table( 'services' ) );
    
    if ( $count === 0 ) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong><?php esc_html_e( 'Dynamic Services Form', 'dynamic-services-form' ); ?>:</strong>
                <?php esc_html_e( 'No services found. Would you like to load sample data?', 'dynamic-services-form' ); ?>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'dsf_action', 'load_sample_data' ), 'dsf_load_sample' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Load Sample Data', 'dynamic-services-form' ); ?>
                </a>
            </p>
        </div>
        <?php
    }
} });

// Handle sample data loading
add_action( 'init', function() {
    if ( ! isset( $_GET['dsf_action'] ) || $_GET['dsf_action'] !== 'load_sample_data' ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'dsf_load_sample' ) ) {
        return;
    }

    create_sample_data();
    wp_safe_redirect( remove_query_arg( [ 'dsf_action', '_wpnonce' ] ) );
    exit;
} );
