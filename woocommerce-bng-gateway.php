<?php

if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/*
Plugin Name: BNG Gateway for WooCommerce
Plugin URI: https://bizztoolz.com/plugins
Description: Add the BNG Gateway for WooCommerce.
Version: 1.6.10
Author: BizZToolz
Author URI: https://bizztoolz.com/plugins

WC requires at least: 4.6.2
WC tested up to: 4.7.0

License: GPLv2
*/
//freemius

if ( function_exists( 'bgfw_fs' ) ) {
    bgfw_fs()->set_basename( true, __FILE__ );
} else {
    if ( !function_exists( 'bgfw_fs' ) ) {
        // Create a helper function for easy SDK access.
        function bgfw_fs() {
            global $bgfw_fs;

            if ( ! isset( $bgfw_fs ) ) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';

                $bgfw_fs = fs_dynamic_init( array(
                    'id'                  => '1122',
                    'slug'                => 'bng-gateway-for-woocommerce',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_83f1e78ec646392a1fb29d4661ee9',
                    'is_premium'          => false,
                    'has_addons'          => false,
                    'has_paid_plans'      => false,
                    'menu'                => array(
                        'slug'           => 'wc-settings',
                        'override_exact' => true,
                        'contact'        => false,
                        'support'        => false,
                        'parent'         => array(
                            'slug' => 'woocommerce',
                        ),
                    ),
                ) );
            }

            return $bgfw_fs;
        }

        // Init Freemius.
        bgfw_fs();
        // Signal that SDK was initiated.
        do_action( 'bgfw_fs_loaded' );

        function bgfw_fs_settings_url() {
            return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bng_gateway' );
        }

        bgfw_fs()->add_filter('connect_url', 'bgfw_fs_settings_url');
        bgfw_fs()->add_filter('after_skip_url', 'bgfw_fs_settings_url');
        bgfw_fs()->add_filter('after_connect_url', 'bgfw_fs_settings_url');
        bgfw_fs()->add_filter('after_pending_connect_url', 'bgfw_fs_settings_url');

        //end freemius

        /* WooCommerce fallback notice. */
        function woocommerce_bng_fallback_notice() {
            echo  '<div class="error"><p>' . sprintf( __( 'WooCommerce Custom Payment Gateways depends on the last version of %s to work!', 'bng_gateway' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>' ;
        }

        /* Load functions. */
        function bng_gateway_load() {
            if ( !class_exists( 'WC_Payment_Gateway' ) ) {
                add_action( 'admin_notices', 'woocommerce_bng_fallback_notice' );
                return;
            }
            
            function wc_Custom_add_bng_gateway( $methods ) {
                $methods[] = 'BNG_Custom_Payment_Gateway';
                return $methods;
            }
            add_filter( 'woocommerce_payment_gateways', 'wc_Custom_add_bng_gateway' );

            // Include the WooCommerce Custom Payment Gateways classes.
            require_once plugin_dir_path( __FILE__ ) . 'bng_gateway_functions.php';
        }

        add_action( 'plugins_loaded', 'bng_gateway_load', 0 );

        /* Adds custom settings url in plugins page. */
        function bng_gateway_action_links( $links )
        {
            $settings = array(
                'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bng_gateway' ), __( 'Manage BNG Gateway', 'bng_gateway' ) ),
            );
            return array_merge( $settings, $links );
        }
        
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bng_gateway_action_links' );
    }
}
?>