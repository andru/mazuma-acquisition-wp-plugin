<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ACQUISITION_FLOW_VERSION', '1.0.0' );

function activate_acquisition_flow() {
}
function deactivate_acquisition_flow() {
}
function acquisition_flow_enqueue_scripts() {
	wp_enqueue_script( 'acquisition-flow', plugin_dir_url( __FILE__ ) . 'dist/assets/index-Clji0mLF.js', array(), '1.0.0', true);
	wp_enqueue_style( 'acquisition-flow', plugin_dir_url( __FILE__ ) . 'dist/assets/index-CtwxWV25.css');

}
add_action('wp_enqueue_scripts', 'acquisition_flow_enqueue_scripts');

register_activation_hook( __FILE__, 'activate_acquisition_flow' );
register_deactivation_hook( __FILE__, 'deactivate_acquisition_flow' );


add_filter( 'the_content', 'render_acquisition_flow' );
function render_acquisition_flow( $page_template )
{
    $options = get_option('aqfl_plugin_options');
    $page_name = $options['pagename'];
    if ( isset($page_name) && $page_name!=='' && is_singular() && is_page( $options['pagename'] ) ) {
        ?>
        <script type="text/javascript">
            var COMPANIESHOUSE_API_URL='/wp-content/plugins/acquisition-flow/api/companieshouse.php';
            var SALESFORCE_API_URL='/wp-content/plugins/acquisition-flow/api/salesforce.php';
            var MAILCHIMP_API_URL='/wp-content/plugins/acquisition-flow/api/mailchimp.php';
            var CALENDLY_URL="<?php echo $options['bookcallurl'] ?>";

            var WPAQFL_BASE_SOLE = "<?php echo $options['quote_st_base'] ?>";
            var WPAQFL_BASE_PARTNERSHIP = "<?php echo $options['quote_pt_base'] ?>";
            var WPAQFL_BASE_LTD = "<?php echo $options['quote_ltd_base'] ?>";
            var WPAQFL_BASE_LLP = "<?php echo $options['quote_llp_base'] ?>";
            var WPAQFL_PERPAYSLIP = "<?php echo $options['quote_perpayslip'] ?>";
            var WPAQFL_MINPAYROLL = "<?php echo $options['quote_minpayroll'] ?>";
            var WPAQFL_VAT = "<?php echo $options['quote_fees_vat'] ?>";
            var WPAQFL_SETUP = "<?php echo $options['quote_fees_setup'] ?>";
            var WPAQFL_CATCHUP = "<?php echo $options['quote_fees_catchup'] ?>";
        </script>
        <div id="mazuma-flow-root"></div>

        <?php
				// $content = "<iframe src=\"/wp-content/plugins/acquisition-flow/dist/index.html\" style=\"width: 100%; height: 200vh; border: none;\"></iframe>";
    }
}


function acquisition_flow_add_settings_page() {
    add_options_page( 'Acquisition Flow', 'Acquisition Flow', 'manage_options', 'aqfl_plugin', 'acquisition_flow_render_plugin_settings_page' );
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
    add_settings_field( 'aqfl_plugin_setting_perpayslip', 'Minimum payroll charge', 'aqfl_plugin_setting_perpayslip', 'aqfl_plugin', 'quote_payroll' );
    add_settings_field( 'aqfl_plugin_setting_minpayroll', 'Fee per payslip', 'aqfl_plugin_setting_minpayroll', 'aqfl_plugin', 'quote_payroll' );

    add_settings_section( 'quote_fees', 'Additional Monthly Fees', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_vat', 'VAT registered', 'aqfl_plugin_setting_vat', 'aqfl_plugin', 'quote_fees' );

    add_settings_section( 'quote_onetime_fees', 'New Client Fees', '', 'aqfl_plugin' );
    add_settings_field( 'aqfl_plugin_setting_setup', 'Setup fee', 'aqfl_plugin_setting_setup', 'aqfl_plugin', 'quote_onetime_fees' );
    add_settings_field( 'aqfl_plugin_setting_catchup', 'Catchup  fee', 'aqfl_plugin_setting_catchup', 'aqfl_plugin', 'quote_onetime_fees' );

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

function aqfl_plugin_setting_perpayslip() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_perpayslip' name='aqfl_plugin_options[quote_perpayslip]' type='text' value='" . esc_attr( $options['quote_perpayslip'] ) . "' />";
}
function aqfl_plugin_setting_minpayroll() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_minpayroll' name='aqfl_plugin_options[quote_minpayroll]' type='text' value='" . esc_attr( $options['quote_minpayroll'] ) . "' />";
}

function aqfl_plugin_setting_vat() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_vat' name='aqfl_plugin_options[quote_fees_vat]' type='text' value='" . esc_attr( $options['quote_fees_vat'] ) . "' />";
}


function aqfl_plugin_setting_setup() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_setup' name='aqfl_plugin_options[quote_fees_setup]' type='text' value='" . esc_attr( $options['quote_fees_setup'] ) . "' />";
}
function aqfl_plugin_setting_catchup() {
    $options = get_option( 'aqfl_plugin_options' );
    echo "<input id='aqfl_plugin_setting_catchup' name='aqfl_plugin_options[quote_fees_catchup]' type='text' value='" . esc_attr( $options['quote_fees_catchup'] ) . "' />";
}