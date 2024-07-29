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
	wp_enqueue_script( 'acquisition-flow', plugin_dir_url( __FILE__ ) . 'dist/assets/index-BP0BIOwh.js', array(), '1.0.0', true);
	wp_enqueue_style( 'acquisition-flow', plugin_dir_url( __FILE__ ) . 'dist/assets/index-CtwxWV25.css');

}
add_action('wp_enqueue_scripts', 'acquisition_flow_enqueue_scripts');

register_activation_hook( __FILE__, 'activate_acquisition_flow' );
register_deactivation_hook( __FILE__, 'deactivate_acquisition_flow' );


add_filter( 'the_content', 'render_acquisition_flow' );
function render_acquisition_flow( $page_template )
{
    if ( is_singular() && is_page( 'get-a-quote' ) ) {
        $content = "<div id=\"mazuma-flow-root\"></div>";
    }
    return $content;
}
