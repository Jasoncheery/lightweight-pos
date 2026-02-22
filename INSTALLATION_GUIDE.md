# Installation Guide - Simple WooCommerce POS

## Quick Start (5 Minutes)

### Prerequisites
- WordPress 5.8+ installed
- WooCommerce 5.0+ installed and activated
- Admin access to WordPress

### Step 1: Download the Plugin

**Option A: Clone from GitHub**
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/Jasoncheery/lightweight-pos.git simple-wc-pos
```

**Option B: Download ZIP**
1. Go to https://github.com/Jasoncheery/lightweight-pos
2. Click "Code" → "Download ZIP"
3. Extract the ZIP file

### Step 2: Install the Plugin

**If you cloned via Git:**
- The plugin is already in the correct location
- Skip to Step 3

**If you downloaded ZIP:**
1. Go to WordPress Admin → Plugins → Add New
2. Click "Upload Plugin"
3. Choose the ZIP file
4. Click "Install Now"

### Step 3: Activate

1. Go to WordPress Admin → Plugins
2. Find "Simple WooCommerce POS"
3. Click "Activate"

### Step 4: Access the POS

1. Look for the new "POS" menu item in WordPress admin sidebar
2. Click "POS" to open the Point of Sale interface
3. Start selling!

## Detailed Setup

### Configuring Settings

1. Go to **POS → Settings** in WordPress admin
2. Configure:
   - **Receipt Header**: Add your store name or custom message
   - **Receipt Footer**: Add thank you message or terms
   - **Loyalty Points Rate**: Set how many points per dollar (default: 1)
3. Click "Save Changes"

### Setting Up Products

The POS uses your existing WooCommerce products. Make sure:

1. Products are **Published**
2. Products have **Prices** set
3. Products have **Stock** managed (optional but recommended)
4. Products have **Categories** assigned (for easier filtering)

To add products:
1. Go to **Products → Add New** in WordPress
2. Fill in product details
3. Set price and stock
4. Publish

### Setting Up Customers

**Option 1: Import Existing Customers**
- The POS automatically has access to all WordPress users with customer role
- No additional setup needed

**Option 2: Create Customers via POS**
- Click "+ New Customer" in the POS interface
- Fill in customer details
- Customer is created and ready to use

### Testing the POS

1. **Add a Test Product**:
   - Go to Products → Add New
   - Name: "Test Product"
   - Price: $10.00
   - Stock: 100
   - Publish

2. **Make a Test Sale**:
   - Go to POS interface
   - Search for "Test Product"
   - Click to add to cart
   - Select payment method (Cash)
   - Click "Complete Sale"

3. **Verify Order**:
   - Go to WooCommerce → Orders
   - You should see the new order
   - Check that stock was reduced

4. **Check Reports**:
   - Click "Reports" tab in POS
   - Verify the sale appears in reports

## Troubleshooting Installation

### Plugin Won't Activate

**Error: "Plugin requires WooCommerce"**
- Solution: Install and activate WooCommerce first
- Go to Plugins → Add New → Search "WooCommerce" → Install & Activate

**Error: "PHP version too old"**
- Solution: Upgrade PHP to 7.4 or higher
- Contact your hosting provider for PHP upgrade

### POS Page Not Loading

**Blank Page or "Loading POS..." Forever**

1. Check browser console (F12) for errors
2. Common fixes:
   - Clear browser cache (Ctrl+Shift+Delete)
   - Disable conflicting plugins temporarily
   - Check if REST API is working: Visit `yoursite.com/wp-json/`

**Products Not Showing**

1. Verify WooCommerce products exist and are published
2. Check product prices are set
3. Try clearing category filter
4. Refresh the page

### Permission Issues

**Error: "You don't have permission"**

1. Make sure you're logged in as Administrator or Shop Manager
2. Go to Users → Your Profile
3. Verify role is "Administrator" or "Shop Manager"

### API Errors

**Error: "REST API Error"**

1. Check WordPress REST API is enabled:
   - Visit `yoursite.com/wp-json/`
   - Should see JSON data, not error

2. Check permalink settings:
   - Go to Settings → Permalinks
   - Choose "Post name" or any option except "Plain"
   - Click "Save Changes"

3. Check .htaccess file:
   - Make sure it's writable
   - Regenerate by saving permalinks again

## Upgrading

### From GitHub

```bash
cd /path/to/wordpress/wp-content/plugins/simple-wc-pos/
git pull origin main
```

### From ZIP

1. Deactivate the plugin (don't delete)
2. Upload new version
3. Reactivate

**Note**: Your settings and data are stored in the database and won't be lost.

## Uninstallation

### Temporary Deactivation

1. Go to Plugins
2. Click "Deactivate" under Simple WooCommerce POS
3. Plugin is disabled but data is preserved

### Complete Removal

1. Deactivate the plugin
2. Click "Delete"
3. Confirm deletion

**Note**: This will remove:
- Plugin files
- Settings (receipt text, points rate)

**This will NOT remove**:
- Orders created via POS (they're regular WooCommerce orders)
- Customer data
- Loyalty points (stored in user meta)

To remove loyalty points data:
```sql
DELETE FROM wp_usermeta WHERE meta_key LIKE 'swcpos_%';
```

## Next Steps

After installation:

1. ✅ Configure settings (receipt text, points rate)
2. ✅ Set up products in WooCommerce
3. ✅ Create or import customers
4. ✅ Train staff on POS usage
5. ✅ Make test sales to verify everything works
6. ✅ Start using POS for real sales!

## Getting Help

- **Documentation**: See README.md for full feature list
- **Issues**: Report bugs on GitHub Issues
- **Support**: Check FAQ in README.md first

## System Requirements Checklist

- [ ] WordPress 5.8 or higher
- [ ] WooCommerce 5.0 or higher
- [ ] PHP 7.4 or higher
- [ ] Modern browser (Chrome, Firefox, Safari, Edge)
- [ ] HTTPS recommended (for security)
- [ ] Permalink structure set (not "Plain")

## Recommended Hosting Requirements

- **RAM**: 512MB minimum, 1GB+ recommended
- **PHP Memory Limit**: 256MB minimum
- **Max Execution Time**: 30 seconds minimum
- **Storage**: 50MB for plugin + space for products/orders

---

**Installation Complete!** 🎉

You're now ready to use the Simple WooCommerce POS. Visit the POS page from your WordPress admin menu to get started.
