<?php
/**
 * Loyalty Points System for Simple WooCommerce POS
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWCPOS_Loyalty {
    
    /**
     * Initialize the loyalty class
     */
    public static function init() {
        // Hook into order completion to award points
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'award_points_on_order'));
        add_action('woocommerce_order_status_processing', array(__CLASS__, 'award_points_on_order'));
        
        // Add points display to user profile
        add_action('show_user_profile', array(__CLASS__, 'display_user_points'));
        add_action('edit_user_profile', array(__CLASS__, 'display_user_points'));
        
        // Save points when profile is updated
        add_action('personal_options_update', array(__CLASS__, 'save_user_points'));
        add_action('edit_user_profile_update', array(__CLASS__, 'save_user_points'));
    }
    
    /**
     * Get user's points balance
     * 
     * @param int $user_id User ID
     * @return float Points balance
     */
    public static function get_user_points($user_id) {
        $points = get_user_meta($user_id, 'swcpos_points_balance', true);
        return $points ? floatval($points) : 0;
    }
    
    /**
     * Add points to user's balance
     * 
     * @param int $user_id User ID
     * @param float $points Points to add
     * @return float New balance
     */
    public static function add_points($user_id, $points) {
        $current_points = self::get_user_points($user_id);
        $new_balance = $current_points + floatval($points);
        update_user_meta($user_id, 'swcpos_points_balance', $new_balance);
        
        // Log the transaction
        self::log_points_transaction($user_id, $points, 'earned', 'Order purchase');
        
        return $new_balance;
    }
    
    /**
     * Subtract points from user's balance
     * 
     * @param int $user_id User ID
     * @param float $points Points to subtract
     * @return float|WP_Error New balance or error if insufficient points
     */
    public static function subtract_points($user_id, $points) {
        $current_points = self::get_user_points($user_id);
        
        if ($current_points < $points) {
            return new WP_Error('insufficient_points', __('Insufficient points balance', 'simple-wc-pos'));
        }
        
        $new_balance = $current_points - floatval($points);
        update_user_meta($user_id, 'swcpos_points_balance', $new_balance);
        
        // Log the transaction
        self::log_points_transaction($user_id, -$points, 'redeemed', 'Points redemption');
        
        return $new_balance;
    }
    
    /**
     * Set user's points balance
     * 
     * @param int $user_id User ID
     * @param float $points New points balance
     * @return float New balance
     */
    public static function set_points($user_id, $points) {
        $new_balance = floatval($points);
        update_user_meta($user_id, 'swcpos_points_balance', $new_balance);
        
        // Log the transaction
        self::log_points_transaction($user_id, $points, 'adjusted', 'Manual adjustment');
        
        return $new_balance;
    }
    
    /**
     * Calculate points to award based on order total
     * 
     * @param float $order_total Order total amount
     * @return float Points to award
     */
    public static function calculate_points($order_total) {
        $points_rate = get_option('swcpos_points_rate', 1);
        return floatval($order_total) * floatval($points_rate);
    }
    
    /**
     * Award points when order is completed
     * 
     * @param int $order_id Order ID
     */
    public static function award_points_on_order($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Check if points already awarded
        if ($order->get_meta('_swcpos_points_awarded')) {
            return;
        }
        
        $user_id = $order->get_customer_id();
        
        // Only award points to registered users
        if (!$user_id) {
            return;
        }
        
        // Calculate points based on order total
        $order_total = $order->get_total();
        $points = self::calculate_points($order_total);
        
        // Award points
        $new_balance = self::add_points($user_id, $points);
        
        // Mark order as points awarded
        $order->update_meta_data('_swcpos_points_awarded', true);
        $order->update_meta_data('_swcpos_points_amount', $points);
        $order->save();
        
        // Add order note
        $order->add_order_note(
            sprintf(
                __('Loyalty points awarded: %s points. New balance: %s', 'simple-wc-pos'),
                number_format($points, 2),
                number_format($new_balance, 2)
            )
        );
    }
    
    /**
     * Log points transaction
     * 
     * @param int $user_id User ID
     * @param float $points Points amount (positive or negative)
     * @param string $type Transaction type (earned, redeemed, adjusted)
     * @param string $description Transaction description
     */
    private static function log_points_transaction($user_id, $points, $type, $description) {
        $transactions = get_user_meta($user_id, 'swcpos_points_transactions', true);
        
        if (!is_array($transactions)) {
            $transactions = array();
        }
        
        $transactions[] = array(
            'date' => current_time('mysql'),
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'balance' => self::get_user_points($user_id)
        );
        
        // Keep only last 100 transactions
        if (count($transactions) > 100) {
            $transactions = array_slice($transactions, -100);
        }
        
        update_user_meta($user_id, 'swcpos_points_transactions', $transactions);
    }
    
    /**
     * Display user points in profile
     * 
     * @param WP_User $user User object
     */
    public static function display_user_points($user) {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        $points = self::get_user_points($user->ID);
        $transactions = get_user_meta($user->ID, 'swcpos_points_transactions', true);
        
        ?>
        <h2><?php _e('Loyalty Points', 'simple-wc-pos'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="swcpos_points_balance"><?php _e('Points Balance', 'simple-wc-pos'); ?></label></th>
                <td>
                    <input type="number" name="swcpos_points_balance" id="swcpos_points_balance" value="<?php echo esc_attr($points); ?>" class="regular-text" step="0.01" />
                    <p class="description"><?php _e('Manually adjust the user\'s points balance', 'simple-wc-pos'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php if (is_array($transactions) && !empty($transactions)): ?>
            <h3><?php _e('Recent Transactions', 'simple-wc-pos'); ?></h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'simple-wc-pos'); ?></th>
                        <th><?php _e('Points', 'simple-wc-pos'); ?></th>
                        <th><?php _e('Type', 'simple-wc-pos'); ?></th>
                        <th><?php _e('Description', 'simple-wc-pos'); ?></th>
                        <th><?php _e('Balance', 'simple-wc-pos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse(array_slice($transactions, -10)) as $transaction): ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($transaction['date']))); ?></td>
                            <td><?php echo esc_html(number_format($transaction['points'], 2)); ?></td>
                            <td><?php echo esc_html(ucfirst($transaction['type'])); ?></td>
                            <td><?php echo esc_html($transaction['description']); ?></td>
                            <td><?php echo esc_html(number_format($transaction['balance'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Save user points when profile is updated
     * 
     * @param int $user_id User ID
     */
    public static function save_user_points($user_id) {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        if (isset($_POST['swcpos_points_balance'])) {
            $new_points = floatval($_POST['swcpos_points_balance']);
            self::set_points($user_id, $new_points);
        }
    }
}
