<?php
/*
Plugin Name: Avrio - WooCommerce Gateway
Plugin URI: 
Description: Extends WooCommerce by adding the Avrio Gateway
Version: 0.3
Author: fexra
*/
if(!defined('ABSPATH')) {
	exit;
}

//Load Plugin
add_action('plugins_loaded', 'avrio_init', 0 );

function avrio_init() {
	if(!class_exists('WC_Payment_Gateway')) return;
	
	include_once('include/avrio_payments.php');
	require_once('library.php');

    add_filter( 'woocommerce_payment_gateways', 'avrio_gateway');
	function avrio_gateway( $methods ) {
		$methods[] = 'Avrio_Gateway';
		return $methods;
	}
}

//Add action link
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'avrio_payment');

function avrio_payment($links) {
	$plugin_links = array('<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'avrio_payment') . '</a>',);
	return array_merge($plugin_links, $links);	
}

//Configure currency
add_filter('woocommerce_currencies','add_my_currency');
add_filter('woocommerce_currency_symbol','add_my_currency_symbol', 10, 2);

function add_my_currency($currencies) {
     $currencies['AIO'] = __('Avrio','woocommerce');
     return $currencies;
}

function add_my_currency_symbol($currency_symbol, $currency) {
    switch($currency) {
        case 'AIO': $currency_symbol = 'AIO'; break;
    }
    return $currency_symbol;
}

//Create Database
register_activation_hook(__FILE__,'createDatabase');

function createDatabase() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'woocommerce_avriocommerce';
    
	$sql = "CREATE TABLE $table_name (
       `id` INT(32) NOT NULL AUTO_INCREMENT,
	   `oid` INT(32) NOT NULL,
       `pid` VARCHAR(64) NOT NULL,
       `hash` VARCHAR(120) NOT NULL,
       `amount` DECIMAL(12, 2) NOT NULL,
	   `conversion` DECIMAL(12,2) NOT NULL,
       `paid` INT(1) NOT NULL,
       UNIQUE KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
