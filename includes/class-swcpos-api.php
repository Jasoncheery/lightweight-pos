<?php
/**
 * REST API endpoints for Simple WooCommerce POS
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWCPOS_API {
    
    /**
     * Initialize the API class
     */
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public static function register_routes() {
        // Products endpoint
        register_rest_route('simple-pos/v1', '/products', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_products'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
        
        // Customers endpoint
        register_rest_route('simple-pos/v1', '/customers', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_customers'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
        
        // Create customer endpoint
        register_rest_route('simple-pos/v1', '/customers', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'create_customer'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
        
        // Orders endpoint
        register_rest_route('simple-pos/v1', '/orders', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'create_order'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
        
        // Reports endpoint
        register_rest_route('simple-pos/v1', '/reports', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_reports'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
        
        // Product categories endpoint
        register_rest_route('simple-pos/v1', '/categories', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_categories'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
    }
    
    /**
     * Check if user has permission to access API
     */
    public static function check_permission() {
        return current_user_can('manage_woocommerce');
    }
    
    /**
     * Get products
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public static function get_products($request) {
        $search = $request->get_param('search');
        $category = $request->get_param('category');
        $per_page = $request->get_param('per_page') ?: 50;
        $page = $request->get_param('page') ?: 1;
        
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        // Add search query
        if ($search) {
            $args['s'] = $search;
            
            // Also search by SKU
            add_filter('posts_search', array(__CLASS__, 'search_by_sku'), 10, 2);
        }
        
        // Filter by category
        if ($category) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category,
                )
            );
        }
        
        $query = new WP_Query($args);
        $products = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                
                if (!$product) {
                    continue;
                }
                
                $products[] = array(
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'sku' => $product->get_sku(),
                    'price' => floatval($product->get_price()),
                    'regular_price' => floatval($product->get_regular_price()),
                    'sale_price' => $product->get_sale_price() ? floatval($product->get_sale_price()) : null,
                    'stock_status' => $product->get_stock_status(),
                    'stock_quantity' => $product->get_stock_quantity(),
                    'manage_stock' => $product->get_manage_stock(),
                    'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                    'type' => $product->get_type(),
                    'categories' => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')),
                );
            }
            wp_reset_postdata();
        }
        
        // Remove SKU search filter
        if ($search) {
            remove_filter('posts_search', array(__CLASS__, 'search_by_sku'), 10);
        }
        
        return new WP_REST_Response(array(
            'products' => $products,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ), 200);
    }
    
    /**
     * Add SKU to search query
     */
    public static function search_by_sku($search, $query) {
        global $wpdb;
        
        if (empty($search)) {
            return $search;
        }
        
        $search_term = $query->get('s');
        
        if (empty($search_term)) {
            return $search;
        }
        
        $search .= " OR {$wpdb->postmeta}.meta_key = '_sku' AND {$wpdb->postmeta}.meta_value LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%'";
        
        return $search;
    }
    
    /**
     * Get customers
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public static function get_customers($request) {
        $search = $request->get_param('search');
        $per_page = $request->get_param('per_page') ?: 50;
        $page = $request->get_param('page') ?: 1;
        
        $args = array(
            'role__in' => array('customer', 'administrator', 'shop_manager'),
            'number' => $per_page,
            'paged' => $page,
            'orderby' => 'display_name',
            'order' => 'ASC',
        );
        
        // Add search query
        if ($search) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = array('user_login', 'user_email', 'display_name');
        }
        
        $user_query = new WP_User_Query($args);
        $customers = array();
        
        if (!empty($user_query->get_results())) {
            foreach ($user_query->get_results() as $user) {
                $customer = new WC_Customer($user->ID);
                
                $customers[] = array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'phone' => $customer->get_billing_phone(),
                    'points' => SWCPOS_Loyalty::get_user_points($user->ID),
                    'billing_address' => array(
                        'first_name' => $customer->get_billing_first_name(),
                        'last_name' => $customer->get_billing_last_name(),
                        'address_1' => $customer->get_billing_address_1(),
                        'address_2' => $customer->get_billing_address_2(),
                        'city' => $customer->get_billing_city(),
                        'state' => $customer->get_billing_state(),
                        'postcode' => $customer->get_billing_postcode(),
                        'country' => $customer->get_billing_country(),
                    ),
                );
            }
        }
        
        return new WP_REST_Response(array(
            'customers' => $customers,
            'total' => $user_query->get_total(),
        ), 200);
    }
    
    /**
     * Create a new customer
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public static function create_customer($request) {
        $email = sanitize_email($request->get_param('email'));
        $first_name = sanitize_text_field($request->get_param('first_name'));
        $last_name = sanitize_text_field($request->get_param('last_name'));
        $phone = sanitize_text_field($request->get_param('phone'));
        
        // Validate email
        if (empty($email) || !is_email($email)) {
            return new WP_REST_Response(array(
                'error' => __('Valid email address is required', 'simple-wc-pos')
            ), 400);
        }
        
        // Check if email already exists
        if (email_exists($email)) {
            return new WP_REST_Response(array(
                'error' => __('Email address already exists', 'simple-wc-pos')
            ), 400);
        }
        
        // Create user
        $username = sanitize_user(current(explode('@', $email)));
        
        // Make username unique if it exists
        if (username_exists($username)) {
            $username = $username . '_' . rand(100, 999);
        }
        
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($user_id)) {
            return new WP_REST_Response(array(
                'error' => $user_id->get_error_message()
            ), 400);
        }
        
        // Set user role to customer
        $user = new WP_User($user_id);
        $user->set_role('customer');
        
        // Update user meta
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'billing_first_name', $first_name);
        update_user_meta($user_id, 'billing_last_name', $last_name);
        update_user_meta($user_id, 'billing_email', $email);
        update_user_meta($user_id, 'billing_phone', $phone);
        
        // Initialize points balance
        SWCPOS_Loyalty::set_points($user_id, 0);
        
        return new WP_REST_Response(array(
            'success' => true,
            'customer' => array(
                'id' => $user_id,
                'name' => $first_name . ' ' . $last_name,
                'email' => $email,
                'phone' => $phone,
                'points' => 0,
            )
        ), 201);
    }
    
    /**
     * Create an order
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public static function create_order($request) {
        try {
            $customer_id = $request->get_param('customer_id');
            $items = $request->get_param('items');
            $payment_method = sanitize_text_field($request->get_param('payment_method'));
            $discount_amount = floatval($request->get_param('discount_amount'));
            $discount_type = sanitize_text_field($request->get_param('discount_type')); // 'fixed' or 'percent'
            
            // Validate items
            if (empty($items) || !is_array($items)) {
                return new WP_REST_Response(array(
                    'error' => __('No items provided', 'simple-wc-pos')
                ), 400);
            }
            
            // Create order
            $order = wc_create_order(array(
                'customer_id' => $customer_id ?: 0,
                'created_via' => 'pos',
            ));
            
            if (is_wp_error($order)) {
                return new WP_REST_Response(array(
                    'error' => $order->get_error_message()
                ), 400);
            }
            
            // Add items to order
            foreach ($items as $item) {
                $product_id = intval($item['product_id']);
                $quantity = intval($item['quantity']);
                $price = isset($item['price']) ? floatval($item['price']) : null;
                
                $product = wc_get_product($product_id);
                
                if (!$product) {
                    continue;
                }
                
                $item_id = $order->add_product($product, $quantity, array(
                    'subtotal' => $price ? ($price * $quantity) : '',
                    'total' => $price ? ($price * $quantity) : '',
                ));
            }
            
            // Apply discount if provided
            if ($discount_amount > 0) {
                $discount_label = $discount_type === 'percent' 
                    ? sprintf(__('%s%% Discount', 'simple-wc-pos'), $discount_amount)
                    : __('Discount', 'simple-wc-pos');
                
                if ($discount_type === 'percent') {
                    $discount_amount = ($order->get_subtotal() * $discount_amount) / 100;
                }
                
                $order->add_item(new WC_Order_Item_Fee(array(
                    'name' => $discount_label,
                    'amount' => -$discount_amount,
                    'total' => -$discount_amount,
                )));
            }
            
            // Set payment method
            $order->set_payment_method($payment_method);
            $order->set_payment_method_title(ucfirst($payment_method));
            
            // Calculate totals
            $order->calculate_totals();
            
            // Mark as paid for cash/card payments
            if (in_array($payment_method, array('cash', 'card', 'external'))) {
                $order->payment_complete();
            }
            
            // Add order note
            $order->add_order_note(__('Order created via POS', 'simple-wc-pos'));
            
            // Mark as POS order
            $order->update_meta_data('_pos_order', true);
            $order->update_meta_data('_pos_created_by', get_current_user_id());
            $order->save();
            
            return new WP_REST_Response(array(
                'success' => true,
                'order_id' => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'total' => $order->get_total(),
                'status' => $order->get_status(),
            ), 201);
            
        } catch (Exception $e) {
            return new WP_REST_Response(array(
                'error' => $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * Get sales reports
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public static function get_reports($request) {
        $period = $request->get_param('period') ?: 'today';
        
        // Determine date range
        switch ($period) {
            case 'today':
                $start_date = strtotime('today midnight');
                $end_date = strtotime('tomorrow midnight') - 1;
                break;
            case 'yesterday':
                $start_date = strtotime('yesterday midnight');
                $end_date = strtotime('today midnight') - 1;
                break;
            case 'week':
                $start_date = strtotime('monday this week midnight');
                $end_date = strtotime('tomorrow midnight') - 1;
                break;
            case 'month':
                $start_date = strtotime('first day of this month midnight');
                $end_date = strtotime('tomorrow midnight') - 1;
                break;
            default:
                $start_date = strtotime('today midnight');
                $end_date = strtotime('tomorrow midnight') - 1;
        }
        
        // Get orders in date range
        $args = array(
            'limit' => -1,
            'status' => array('processing', 'completed'),
            'date_created' => $start_date . '...' . $end_date,
            'meta_key' => '_pos_order',
            'meta_value' => true,
        );
        
        $orders = wc_get_orders($args);
        
        $total_sales = 0;
        $total_orders = count($orders);
        $products_sold = array();
        $payment_methods = array();
        
        foreach ($orders as $order) {
            $total_sales += $order->get_total();
            
            // Count payment methods
            $payment_method = $order->get_payment_method();
            if (!isset($payment_methods[$payment_method])) {
                $payment_methods[$payment_method] = array(
                    'count' => 0,
                    'total' => 0,
                );
            }
            $payment_methods[$payment_method]['count']++;
            $payment_methods[$payment_method]['total'] += $order->get_total();
            
            // Count products
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                
                if (!isset($products_sold[$product_id])) {
                    $products_sold[$product_id] = array(
                        'name' => $product_name,
                        'quantity' => 0,
                        'total' => 0,
                    );
                }
                
                $products_sold[$product_id]['quantity'] += $quantity;
                $products_sold[$product_id]['total'] += $item->get_total();
            }
        }
        
        // Sort products by quantity sold
        uasort($products_sold, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        
        // Get top 10 products
        $top_products = array_slice($products_sold, 0, 10, true);
        
        return new WP_REST_Response(array(
            'period' => $period,
            'start_date' => date('Y-m-d H:i:s', $start_date),
            'end_date' => date('Y-m-d H:i:s', $end_date),
            'total_sales' => $total_sales,
            'total_orders' => $total_orders,
            'average_order' => $total_orders > 0 ? $total_sales / $total_orders : 0,
            'payment_methods' => $payment_methods,
            'top_products' => $top_products,
        ), 200);
    }
    
    /**
     * Get product categories
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public static function get_categories($request) {
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        
        $formatted_categories = array();
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $formatted_categories[] = array(
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->count,
                );
            }
        }
        
        return new WP_REST_Response(array(
            'categories' => $formatted_categories,
        ), 200);
    }
}
