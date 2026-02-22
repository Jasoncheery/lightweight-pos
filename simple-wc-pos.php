<?php
/**
 * Plugin Name: Simple WooCommerce POS
 * Plugin URI: https://github.com/yourusername/simple-wc-pos
 * Description: A lightweight, web-based Point of Sale system for WooCommerce with inventory management, customer loyalty points, and sales reporting.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: simple-wc-pos
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('SWCPOS_VERSION', '1.0.0');
define('SWCPOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWCPOS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SWCPOS_PLUGIN_FILE', __FILE__);

/**
 * Check if WooCommerce is active
 */
function swcpos_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'swcpos_woocommerce_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__));
        return false;
    }
    return true;
}

/**
 * Admin notice if WooCommerce is not active
 */
function swcpos_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('Simple WooCommerce POS requires WooCommerce to be installed and active.', 'simple-wc-pos'); ?></p>
    </div>
    <?php
}

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'swcpos_activate');
function swcpos_activate() {
    if (!swcpos_check_woocommerce()) {
        return;
    }
    
    // Create custom database tables if needed (for future use)
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'swcpos_deactivate');
function swcpos_deactivate() {
    flush_rewrite_rules();
}

/**
 * Initialize the plugin
 */
add_action('plugins_loaded', 'swcpos_init');
function swcpos_init() {
    if (!swcpos_check_woocommerce()) {
        return;
    }
    
    // Load text domain for translations
    load_plugin_textdomain('simple-wc-pos', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Include required files
    require_once SWCPOS_PLUGIN_DIR . 'includes/class-swcpos-admin.php';
    require_once SWCPOS_PLUGIN_DIR . 'includes/class-swcpos-api.php';
    require_once SWCPOS_PLUGIN_DIR . 'includes/class-swcpos-loyalty.php';
    
    // Initialize classes
    SWCPOS_Admin::init();
    SWCPOS_API::init();
    SWCPOS_Loyalty::init();
}

/**
 * Add settings link on plugin page
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'swcpos_plugin_action_links');
function swcpos_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=simple-wc-pos') . '">' . __('Open POS', 'simple-wc-pos') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
