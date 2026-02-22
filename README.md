# Simple WooCommerce POS

A lightweight, web-based Point of Sale (POS) system for WooCommerce with real-time inventory management, customer loyalty points, and comprehensive sales reporting.

## Features

### 🛒 Point of Sale
- **Intuitive Interface**: Easy-to-use POS interface optimized for touch devices
- **Product Search**: Search products by name or SKU with category filtering
- **Real-time Stock Management**: Automatically updates inventory levels on sale
- **Customer Management**: Search existing customers or create new ones on the fly
- **Flexible Discounts**: Apply fixed amount or percentage discounts
- **Multiple Payment Methods**: Support for Cash, Card, and External payments

### 👥 Customer Loyalty System
- **Points Per Dollar**: Automatic points calculation (configurable rate)
- **Points Balance**: View customer points balance during checkout
- **Automatic Awards**: Points awarded automatically on order completion
- **Transaction History**: Track all points transactions per customer

### 📊 Sales Reports & Analytics
- **Daily/Weekly/Monthly Reports**: Track sales over different time periods
- **Top Products**: See best-selling products with quantity and revenue
- **Payment Method Breakdown**: Analyze sales by payment method
- **Average Order Value**: Monitor business performance metrics

### 🔧 Technical Features
- **Vue.js 3 Frontend**: Modern, reactive UI without build step
- **REST API**: Secure API endpoints for all operations
- **WooCommerce Integration**: Seamless integration with existing WooCommerce setup
- **Responsive Design**: Works on tablets, desktops, and large screens
- **Print Receipts**: Built-in receipt printing functionality

## Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Method 2: Manual Installation

1. Download and extract the plugin files
2. Upload the `simple-wc-pos` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

### Method 3: Clone from GitHub

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/Jasoncheery/lightweight-pos.git simple-wc-pos
```

Then activate the plugin in WordPress Admin.

## Usage

### Accessing the POS

1. After activation, you'll see a new "POS" menu item in WordPress admin
2. Click on "POS" to open the Point of Sale interface
3. Only users with `manage_woocommerce` capability can access the POS

### Making a Sale

1. **Search Products**: Use the search bar or category filter to find products
2. **Add to Cart**: Click on products to add them to the cart
3. **Select Customer** (Optional): Search for existing customer or create new one
4. **Adjust Quantities**: Use +/- buttons or type quantity directly
5. **Apply Discount** (Optional): Enter discount amount and select type (fixed/percent)
6. **Select Payment Method**: Choose Cash, Card, or External
7. **Complete Sale**: Click "Complete Sale" button
8. **Print Receipt**: Print receipt from the success modal

### Managing Customers

**Search Existing Customer:**
- Type customer name, email, or phone in the search box
- Select from the dropdown results
- View their loyalty points balance

**Create New Customer:**
- Click "+ New Customer" button
- Fill in required fields (First Name, Email)
- Customer is automatically selected after creation

### Viewing Reports

1. Click the "Reports" tab in the POS interface
2. Select time period: Today, Yesterday, This Week, This Month
3. View:
   - Total Sales
   - Total Orders
   - Average Order Value
   - Top Selling Products
   - Payment Method Breakdown

### Configuring Settings

Go to **POS → Settings** to configure:

- **Receipt Header**: Custom text for receipt header
- **Receipt Footer**: Custom text for receipt footer
- **Loyalty Points Rate**: Points earned per currency unit (default: 1 point per $1)

### Managing Loyalty Points

**View Customer Points:**
- Go to Users → Edit User
- Scroll to "Loyalty Points" section
- View current balance and transaction history

**Manual Adjustment:**
- Edit the points balance field
- Save the user profile
- Transaction is logged automatically

## API Endpoints

The plugin provides REST API endpoints under the `simple-pos/v1` namespace:

### Products
```
GET /wp-json/simple-pos/v1/products
Parameters: search, category, per_page, page
```

### Customers
```
GET /wp-json/simple-pos/v1/customers
Parameters: search, per_page, page

POST /wp-json/simple-pos/v1/customers
Body: first_name, last_name, email, phone
```

### Orders
```
POST /wp-json/simple-pos/v1/orders
Body: customer_id, items[], payment_method, discount_amount, discount_type
```

### Reports
```
GET /wp-json/simple-pos/v1/reports
Parameters: period (today, yesterday, week, month)
```

### Categories
```
GET /wp-json/simple-pos/v1/categories
```

All endpoints require `manage_woocommerce` capability and use WordPress nonce for security.

## File Structure

```
simple-wc-pos/
├── simple-wc-pos.php          # Main plugin file
├── includes/
│   ├── class-swcpos-admin.php # Admin interface and menu
│   ├── class-swcpos-api.php   # REST API endpoints
│   └── class-swcpos-loyalty.php # Loyalty points system
├── assets/
│   ├── css/
│   │   └── pos-styles.css     # POS interface styles
│   └── js/
│       └── pos-app.js         # Vue.js application
├── README.md                   # This file
└── LICENSE                     # GPL v2 License
```

## Frequently Asked Questions

### Does this work with variable products?
Currently, the POS supports simple products. Variable product support is planned for a future release.

### Can I use this on a tablet?
Yes! The interface is optimized for touch devices and works great on tablets.

### How are loyalty points calculated?
Points are calculated based on the order total multiplied by the points rate (configurable in settings). Default is 1 point per $1 spent.

### Can I customize the receipt?
Yes, you can customize the header and footer text in POS → Settings. For more advanced customization, you can modify the receipt template in the Vue.js app.

### Does this sync with WooCommerce inventory?
Yes! All sales through the POS automatically update WooCommerce inventory in real-time.

### Can multiple users use the POS simultaneously?
Yes, multiple users can use the POS at the same time. Each sale is processed independently.

## Troubleshooting

### POS page shows "Loading POS..." indefinitely
- Check browser console for JavaScript errors
- Ensure WooCommerce is active
- Clear browser cache and reload

### Products not showing up
- Verify products are published in WooCommerce
- Check if products have prices set
- Try clearing the search filters

### API errors when creating orders
- Check WordPress REST API is working: Visit `/wp-json/` on your site
- Verify user has `manage_woocommerce` capability
- Check PHP error logs for detailed error messages

### Loyalty points not being awarded
- Verify order status is "Processing" or "Completed"
- Check if customer is registered (guest orders don't earn points)
- Review points rate setting in POS → Settings

## Changelog

### Version 1.0.0
- Initial release
- POS interface with product search and cart
- Customer management with loyalty points
- Sales reports and analytics
- Multiple payment methods
- Receipt printing
- REST API endpoints

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For support, please:
1. Check the FAQ section above
2. Search existing issues on GitHub
3. Create a new issue with detailed information

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Built with [Vue.js 3](https://vuejs.org/)
- Powered by [WooCommerce](https://woocommerce.com/)
- Developed for WordPress

## Author

Developed by AI Infinity Team

---

**Note**: This plugin is designed for use with WooCommerce and requires an active WooCommerce installation to function properly.
