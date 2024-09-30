<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ACQUISITION_FLOW_VERSION', '1.0.0' );

// Activation and deactivation hooks (empty for now)
function activate_acquisition_flow() {}
function deactivate_acquisition_flow() {}

// Enqueue scripts and styles
function acquisition_flow_enqueue_scripts() {
    // Enqueue the custom JavaScript file (acquisition-flow.js)
    wp_enqueue_script( 'acquisition-flow', plugin_dir_url( __FILE__ ) . 'js/acquisition-flow.js', array(), '1.0.0', true );

    // Enqueue your main JavaScript file and set it as dependent on 'acquisition-flow'
    wp_enqueue_script( 'acquisition-flow-main', plugin_dir_url( __FILE__ ) . 'dist/assets/index-JbbHz58U.js', array('acquisition-flow'), '1.3.2', true );

    // Enqueue the CSS file
    wp_enqueue_style( 'acquisition-flow-style', plugin_dir_url( __FILE__ ) . 'dist/assets/index-DAexjyvx.css' );

    // Localize dynamic data to pass it from PHP to JS
    $options = get_option('aqfl_plugin_options');
    wp_localize_script( 'acquisition-flow', 'acquisitionFlowData', array(
        'COMPANIESHOUSE_API_URL' => '/app/plugins/acquisition-flow-plugin/api/companieshouse.php',
        'SALESFORCE_API_URL' => '/app/plugins/acquisition-flow-plugin/api/salesforce.php',
        'MAILCHIMP_API_URL' => '/app/plugins/acquisition-flow-plugin/api/mailchimp.php',
        'CALENDLY_URL' => isset($options['bookcallurl']) ? $options['bookcallurl'] : '',
        'WPAQFL_BASE_SOLE' => isset($options['quote_st_base']) ? $options['quote_st_base'] : '',
        'WPAQFL_BASE_PARTNERSHIP' => isset($options['quote_pt_base']) ? $options['quote_pt_base'] : '',
        'WPAQFL_BASE_LTD' => isset($options['quote_ltd_base']) ? $options['quote_ltd_base'] : '',
        'WPAQFL_BASE_LLP' => isset($options['quote_llp_base']) ? $options['quote_llp_base'] : '',
        'WPAQFL_PAYROLL_MATRIX' => isset($options['quote_payrollmatrix']) ? $options['quote_payrollmatrix'] : '',
        'WPAQFL_VAT' => isset($options['quote_fees_vat']) ? $options['quote_fees_vat'] : '',
        'WPAQFL_SETUP' => isset($options['quote_fees_setup']) ? $options['quote_fees_setup'] : '',
    ));
}
add_action('wp_enqueue_scripts', 'acquisition_flow_enqueue_scripts');

register_activation_hook( __FILE__, 'activate_acquisition_flow' );
register_deactivation_hook( __FILE__, 'deactivate_acquisition_flow' );

// Render the acquisition flow content (output to wp_footer)

add_action( 'wp_footer', 'render_acquisition_flow' );
function render_acquisition_flow() {
    $options = get_option('aqfl_plugin_options');
    $page_name = $options['pagename'];

    // Only show the flow on the specified page and hide it initially
    if ( isset($page_name) && $page_name !== '' && is_singular() && is_page( $page_name ) ) {
        echo '<style type="text/css">.breadcrumb, .flexibleblocks, .ctafooter{ display: none; }</style><div id="mazuma-flow-root" style="display:none;"></div>';
    }
}


function acquisition_flow_add_settings_page() {
    // add_options_page( 'Acquisition Flow', 'Acquisition Flow', 'manage_options', 'aqfl_plugin', 'acquisition_flow_render_plugin_settings_page' );
    add_menu_page('Acquisition Flow', 'Acquisition Flow', 'manage_options', 'aqfl_plugin', 'acquisition_flow_render_plugin_settings_page', 'dashicons-admin-generic');
}
add_action( 'admin_menu', 'acquisition_flow_add_settings_page' );


function acquisition_flow_render_plugin_settings_page() {
    ?>
    <h2>Acquisition Flow Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'aqfl_plugin_options' );
        do_settings_sections( 'aqfl_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}


function acquisition_flow_register_settings() {
    register_setting( 'aqfl_plugin_options', 'aqfl_plugin_options', 'aqfl_plugin_options_validate' );

    add_settings_section( 'general', 'General Settings', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_pagename', 'URL Path / Page Name', 'aqfl_plugin_setting_pagename', 'aqfl_plugin', 'general' );
    add_settings_field( 'aqfl_plugin_setting_bookcallurl', 'Book a call URL', 'aqfl_plugin_setting_bookcallurl', 'aqfl_plugin', 'general' );

    add_settings_section( 'quote_rates', 'Quote Calculation Monthly Base Rates', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_base_st', 'Sole Trader Base Rate', 'aqfl_plugin_setting_st_base', 'aqfl_plugin', 'quote_rates' );
    add_settings_field( 'aqfl_plugin_setting_base_pt', 'Partnership Base Rate', 'aqfl_plugin_setting_pt_base', 'aqfl_plugin', 'quote_rates' );
    add_settings_field( 'aqfl_plugin_setting_base_ltd', 'LTD Base Rate', 'aqfl_plugin_setting_ltd_base', 'aqfl_plugin', 'quote_rates' );
    add_settings_field( 'aqfl_plugin_setting_base_llp', 'LLP Base Rate', 'aqfl_plugin_setting_llp_base', 'aqfl_plugin', 'quote_rates' );

    add_settings_section( 'quote_payroll', 'Payroll', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_payroll_matrix', 'Payroll Fee Matrix', 'aqfl_plugin_setting_payroll_matrix', 'aqfl_plugin', 'quote_payroll' );

    add_settings_section( 'quote_fees', 'Additional Monthly Fees', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_vat', 'VAT registered', 'aqfl_plugin_setting_vat', 'aqfl_plugin', 'quote_fees' );

    add_settings_section( 'quote_onetime_fees', 'New Client Fees', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_setup', 'Setup fee', 'aqfl_plugin_setting_setup', 'aqfl_plugin', 'quote_onetime_fees' );
}
add_action( 'admin_init', 'acquisition_flow_register_settings' );


function aqfl_plugin_section_text() {
    echo '<p>Define the values used to calculate the quote</p>';
}

function aqfl_plugin_setting_pagename() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input style='min-width:50%' id='aqfl_plugin_setting_pagename' name='aqfl_plugin_options[pagename]' type='text' value='" . esc_attr( $options['pagename'] ) . "' />";
}
function aqfl_plugin_setting_bookcallurl() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input style='min-width:50%' id='aqfl_plugin_setting_bookcallurl' name='aqfl_plugin_options[bookcallurl]' type='text' value='" . esc_attr( $options['bookcallurl'] ) . "' />";
}


function aqfl_plugin_setting_st_base() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_st_base' name='aqfl_plugin_options[quote_st_base]' type='text' value='" . esc_attr( $options['quote_st_base'] ) . "' />";
}
function aqfl_plugin_setting_pt_base() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_pt_base' name='aqfl_plugin_options[quote_pt_base]' type='text' value='" . esc_attr( $options['quote_pt_base'] ) . "' />";
}
function aqfl_plugin_setting_ltd_base() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_ltd_base' name='aqfl_plugin_options[quote_ltd_base]' type='text' value='" . esc_attr( $options['quote_ltd_base'] ) . "' />";
}
function aqfl_plugin_setting_llp_base() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_llp_base' name='aqfl_plugin_options[quote_llp_base]' type='text' value='" . esc_attr( $options['quote_llp_base'] ) . "' />";
}

function aqfl_plugin_setting_payroll_matrix() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "Must be valid JSON<br/><br /><code>[[<= employees, fee in gbp]]</code><br /><br />
    <textarea id='aqfl_plugin_setting_payroll_matrix' name='aqfl_plugin_options[quote_payrollmatrix]' type='text'>".esc_attr( $options['quote_payrollmatrix'] )."</textarea>";
}

function aqfl_plugin_setting_vat() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_vat' name='aqfl_plugin_options[quote_fees_vat]' type='text' value='" . esc_attr( $options['quote_fees_vat'] ) . "' />";
}


function aqfl_plugin_setting_setup() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_setup' name='aqfl_plugin_options[quote_fees_setup]' type='text' value='" . esc_attr( $options['quote_fees_setup'] ) . "' />";
}