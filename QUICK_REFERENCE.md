# Quick Reference Guide - Simple WooCommerce POS

## 🚀 Quick Start

1. **Access POS**: WordPress Admin → POS menu
2. **Search Products**: Type in search box or select category
3. **Add to Cart**: Click on product
4. **Select Customer**: Search or create new (optional)
5. **Apply Discount**: Enter amount and select type (optional)
6. **Choose Payment**: Cash, Card, or External
7. **Complete Sale**: Click "Complete Sale" button

---

## 🛒 Making a Sale (Step-by-Step)

### Basic Sale (No Customer)
1. Search for product
2. Click product to add to cart
3. Adjust quantity if needed (+/- buttons)
4. Select payment method
5. Click "Complete Sale"
6. Print receipt (optional)

### Sale with Customer
1. Type customer name/email in customer search
2. Select customer from dropdown
3. Add products to cart
4. Complete sale as usual
5. Customer earns loyalty points automatically

### Sale with Discount
1. Add products to cart
2. Enter discount amount
3. Select discount type (Fixed or Percent)
4. Discount applies automatically
5. Complete sale

---

## 👥 Customer Management

### Search Existing Customer
- Type at least 2 characters
- Results show: Name, Email, Points
- Click to select

### Create New Customer
1. Click "+ New Customer"
2. Fill in:
   - First Name (required)
   - Last Name
   - Email (required)
   - Phone
3. Click "Create Customer"
4. Customer is auto-selected

### Clear Customer
- Click "×" button next to customer name

---

## 🛍️ Cart Operations

### Add Product
- Click product card in product grid

### Adjust Quantity
- Click "+" to increase
- Click "-" to decrease
- Type number directly

### Remove Item
- Click "×" button on cart item

### Clear Cart
- Remove all items individually
- Or complete sale (cart clears automatically)

---

## 💰 Discounts

### Fixed Amount
1. Enter amount (e.g., 10 for $10 off)
2. Select "Fixed"
3. Discount applies to total

### Percentage
1. Enter percentage (e.g., 20 for 20% off)
2. Select "Percent"
3. Discount calculates automatically

### Remove Discount
- Set amount to 0

---

## 💳 Payment Methods

### Cash
- Select "Cash" button
- Complete sale
- Order marked as paid

### Card
- Select "Card" button
- Process card externally
- Complete sale in POS

### External/Other
- Select "Other" button
- For bank transfers, checks, etc.
- Complete sale in POS

---

## 📊 Reports

### Access Reports
- Click "Reports" tab at top

### Select Period
- Today
- Yesterday
- This Week
- This Month

### View Metrics
- **Total Sales**: Revenue for period
- **Total Orders**: Number of orders
- **Average Order**: Average order value
- **Top Products**: Best sellers
- **Payment Methods**: Sales by payment type

---

## 🎁 Loyalty Points

### How Points Work
- Customers earn points on every purchase
- Default: 1 point per $1 spent
- Points show in customer info during sale
- Points awarded automatically after sale

### View Points
- Search customer in POS
- Points balance shows in dropdown
- Or check WordPress → Users → Edit User

### Manual Adjustment
1. Go to Users → Edit User
2. Scroll to "Loyalty Points"
3. Change balance
4. Save user

---

## 🖨️ Receipts

### Print Receipt
1. Complete sale
2. Success modal appears
3. Click "Print Receipt"
4. Browser print dialog opens
5. Print or save as PDF

### Receipt Contents
- Store name
- Order number
- Date/time
- Items with quantities and prices
- Subtotal
- Discount (if applied)
- Total
- Payment method
- Points earned (if customer selected)

---

## 🔍 Product Search

### Search Methods
- **By Name**: Type product name
- **By SKU**: Type product SKU
- **By Category**: Select from dropdown

### Search Tips
- Search is case-insensitive
- Partial matches work
- Clear search to see all products
- Use category filter to narrow results

---

## ⚙️ Settings

### Access Settings
- POS → Settings in WordPress admin

### Configure
- **Receipt Header**: Custom text for top of receipt
- **Receipt Footer**: Custom text for bottom of receipt
- **Points Rate**: Points per dollar spent

### Save
- Click "Save Changes" at bottom

---

## 🔐 Permissions

### Who Can Access POS?
- Administrators
- Shop Managers
- Users with `manage_woocommerce` capability

### Grant Access
1. Go to Users → Edit User
2. Change role to "Shop Manager"
3. Save

---

## 🐛 Troubleshooting

### Products Not Showing
- Check products are published
- Verify prices are set
- Clear category filter
- Refresh page

### Can't Complete Sale
- Check cart has items
- Verify payment method selected
- Check for JavaScript errors (F12)

### Customer Search Not Working
- Type at least 2 characters
- Check customer exists in WordPress
- Verify customer has email set

### Points Not Awarded
- Check customer is selected
- Verify order status is "Processing" or "Completed"
- Check points rate in settings

### Receipt Won't Print
- Check browser allows pop-ups
- Try "Save as PDF" instead
- Check printer is connected

---

## ⌨️ Keyboard Shortcuts

Currently no keyboard shortcuts implemented.

**Future Enhancement**: Add shortcuts for:
- Search focus: `/`
- Complete sale: `Ctrl+Enter`
- Clear cart: `Ctrl+X`
- New customer: `Ctrl+N`

---

## 📱 Device Compatibility

### Recommended Devices
- **Desktop**: Full functionality
- **Tablet**: Optimized for touch
- **Large Screen**: Best experience

### Browser Requirements
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Not Recommended
- Mobile phones (screen too small)
- Internet Explorer (not supported)
- Very old browsers

---

## 💡 Tips & Best Practices

### For Cashiers
1. Always select customer if known (for points)
2. Double-check quantities before completing
3. Verify payment method is correct
4. Print receipt for customer records

### For Managers
1. Review reports daily
2. Monitor top-selling products
3. Check for stock issues
4. Adjust points rate seasonally

### For Administrators
1. Backup database regularly
2. Keep WordPress/WooCommerce updated
3. Test POS after updates
4. Train staff on POS usage

---

## 🆘 Quick Help

### Need Help?
1. Check this Quick Reference
2. Read full README.md
3. See INSTALLATION_GUIDE.md
4. Check GitHub Issues
5. Contact support

### Report Issues
- GitHub: https://github.com/Jasoncheery/lightweight-pos/issues
- Include: WordPress version, WooCommerce version, error message

---

## 📞 Support Contacts

- **Documentation**: See README.md
- **Installation**: See INSTALLATION_GUIDE.md
- **GitHub**: https://github.com/Jasoncheery/lightweight-pos
- **Issues**: Create issue on GitHub

---

## ✅ Daily Checklist

### Opening
- [ ] Log in to WordPress
- [ ] Open POS interface
- [ ] Verify products are loading
- [ ] Check internet connection
- [ ] Test a sample transaction

### During Day
- [ ] Process sales normally
- [ ] Create customers as needed
- [ ] Apply discounts when appropriate
- [ ] Print receipts for customers

### Closing
- [ ] Review today's reports
- [ ] Check for any errors
- [ ] Note any stock issues
- [ ] Log out of WordPress

---

**Quick Reference Version 1.0**
**Last Updated**: February 2026
**Plugin Version**: 1.0.0
