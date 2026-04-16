<?php
/**
 * Admin functionality for Simple WooCommerce POS
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWCPOS_Admin {
    
    /**
     * Initialize the admin class
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add POS menu to WordPress admin
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('POS', 'simple-wc-pos'),
            __('POS', 'simple-wc-pos'),
            'manage_woocommerce', // Capability required
            'simple-wc-pos',
            array(__CLASS__, 'render_pos_page'),
            'dashicons-cart',
            56 // Position after WooCommerce
        );
        
        // Add submenu for settings (future use)
        add_submenu_page(
            'simple-wc-pos',
            __('POS Settings', 'simple-wc-pos'),
            __('Settings', 'simple-wc-pos'),
            'manage_woocommerce',
            'simple-wc-pos-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue scripts and styles for POS page
     */
    public static function enqueue_admin_scripts($hook) {
        // Only load on our POS page
        if ($hook !== 'toplevel_page_simple-wc-pos') {
            return;
        }

        // Disable WP Heartbeat on POS screen to avoid admin-ajax interruptions.
        // The POS page is a standalone SPA and does not rely on autosave/locks.
        wp_dequeue_script('heartbeat');
        wp_deregister_script('heartbeat');
        
        // Enqueue Vue.js from CDN
        wp_enqueue_script(
            'vue-js',
            'https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js',
            array(),
            '3.3.4',
            true
        );
        
        // Enqueue Axios for API requests
        wp_enqueue_script(
            'axios-js',
            'https://cdn.jsdelivr.net/npm/axios@1.5.0/dist/axios.min.js',
            array(),
            '1.5.0',
            true
        );
        
        // Enqueue our POS app
        wp_enqueue_script(
            'swcpos-app',
            SWCPOS_PLUGIN_URL . 'assets/js/pos-app.js',
            array('vue-js', 'axios-js'),
            SWCPOS_VERSION,
            true
        );
        
        // Enqueue POS styles
        wp_enqueue_style(
            'swcpos-styles',
            SWCPOS_PLUGIN_URL . 'assets/css/pos-styles.css',
            array(),
            SWCPOS_VERSION
        );
        
        // Localize script with API endpoints and nonce
        wp_localize_script('swcpos-app', 'swcposData', array(
            'apiUrl' => rest_url('simple-pos/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'currency' => get_woocommerce_currency_symbol(),
            'currencyCode' => get_woocommerce_currency(),
            'dateFormat' => get_option('date_format'),
            'timeFormat' => get_option('time_format'),
            'shopName' => get_bloginfo('name'),
            'shopAddress' => get_option('woocommerce_store_address'),
            'shopCity' => get_option('woocommerce_store_city'),
            'shopPostcode' => get_option('woocommerce_store_postcode'),
            'taxEnabled' => wc_tax_enabled(),
        ));
    }
    
    /**
     * Render the POS page
     */
    public static function render_pos_page() {
        ?>
        <div class="wrap swcpos-wrap">
            <div id="swcpos-app">
                <div class="swcpos-loading">
                    <p><?php _e('Loading POS...', 'simple-wc-pos'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render the settings page
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('POS Settings', 'simple-wc-pos'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('swcpos_settings');
                do_settings_sections('swcpos_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="swcpos_receipt_header"><?php _e('Receipt Header', 'simple-wc-pos'); ?></label>
                        </th>
                        <td>
                            <textarea id="swcpos_receipt_header" name="swcpos_receipt_header" rows="3" cols="50"><?php echo esc_textarea(get_option('swcpos_receipt_header', '')); ?></textarea>
                            <p class="description"><?php _e('Text to display at the top of receipts', 'simple-wc-pos'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="swcpos_receipt_footer"><?php _e('Receipt Footer', 'simple-wc-pos'); ?></label>
                        </th>
                        <td>
                            <textarea id="swcpos_receipt_footer" name="swcpos_receipt_footer" rows="3" cols="50"><?php echo esc_textarea(get_option('swcpos_receipt_footer', '')); ?></textarea>
                            <p class="description"><?php _e('Text to display at the bottom of receipts', 'simple-wc-pos'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="swcpos_points_rate"><?php _e('Loyalty Points Rate', 'simple-wc-pos'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="swcpos_points_rate" name="swcpos_points_rate" value="<?php echo esc_attr(get_option('swcpos_points_rate', '1')); ?>" min="0" step="0.01" />
                            <p class="description"><?php _e('Points earned per currency unit spent (e.g., 1 = 1 point per $1)', 'simple-wc-pos'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
