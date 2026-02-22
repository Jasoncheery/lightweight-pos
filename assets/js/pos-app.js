/**
 * Simple WooCommerce POS - Vue.js Application
 */

const { createApp } = Vue;

createApp({
    data() {
        return {
            // Active tab
            activeTab: 'pos',
            
            // Products
            products: [],
            categories: [],
            selectedCategory: '',
            searchQuery: '',
            loadingProducts: false,
            
            // Cart
            cart: [],
            selectedCustomer: null,
            customerSearch: '',
            customerResults: [],
            showCustomerResults: false,
            
            // Discount
            discountAmount: 0,
            discountType: 'fixed', // 'fixed' or 'percent'
            
            // Payment
            paymentMethod: 'cash',
            
            // Modals
            showNewCustomerModal: false,
            showReceiptModal: false,
            showSuccessModal: false,
            
            // New customer form
            newCustomer: {
                first_name: '',
                last_name: '',
                email: '',
                phone: ''
            },
            
            // Order details
            lastOrder: null,
            
            // Reports
            reports: null,
            reportPeriod: 'today',
            loadingReports: false,
        };
    },
    
    computed: {
        // Cart totals
        cartSubtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        
        cartDiscount() {
            if (this.discountType === 'percent') {
                return (this.cartSubtotal * this.discountAmount) / 100;
            }
            return this.discountAmount;
        },
        
        cartTotal() {
            return Math.max(0, this.cartSubtotal - this.cartDiscount);
        },
        
        cartItemCount() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },
        
        // Check if cart is ready for checkout
        canCheckout() {
            return this.cart.length > 0 && this.paymentMethod;
        }
    },
    
    methods: {
        // API request helper
        async apiRequest(endpoint, method = 'GET', data = null) {
            const url = swcposData.apiUrl + endpoint;
            const config = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': swcposData.nonce
                }
            };
            
            if (data) {
                config.data = data;
            }
            
            try {
                const response = await axios(url, config);
                return response.data;
            } catch (error) {
                console.error('API Error:', error);
                alert('Error: ' + (error.response?.data?.error || error.message));
                throw error;
            }
        },
        
        // Load products
        async loadProducts() {
            this.loadingProducts = true;
            try {
                const params = new URLSearchParams();
                if (this.searchQuery) params.append('search', this.searchQuery);
                if (this.selectedCategory) params.append('category', this.selectedCategory);
                
                const data = await this.apiRequest('products?' + params.toString());
                this.products = data.products;
            } catch (error) {
                console.error('Failed to load products:', error);
            } finally {
                this.loadingProducts = false;
            }
        },
        
        // Load categories
        async loadCategories() {
            try {
                const data = await this.apiRequest('categories');
                this.categories = data.categories;
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        },
        
        // Search customers
        async searchCustomers() {
            if (this.customerSearch.length < 2) {
                this.customerResults = [];
                this.showCustomerResults = false;
                return;
            }
            
            try {
                const data = await this.apiRequest('customers?search=' + encodeURIComponent(this.customerSearch));
                this.customerResults = data.customers;
                this.showCustomerResults = true;
            } catch (error) {
                console.error('Failed to search customers:', error);
            }
        },
        
        // Select customer
        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerSearch = customer.name;
            this.showCustomerResults = false;
        },
        
        // Clear customer
        clearCustomer() {
            this.selectedCustomer = null;
            this.customerSearch = '';
        },
        
        // Add product to cart
        addToCart(product) {
            if (product.stock_status === 'outofstock') {
                alert('Product is out of stock');
                return;
            }
            
            const existingItem = this.cart.find(item => item.product_id === product.id);
            
            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    stock_quantity: product.stock_quantity,
                    manage_stock: product.manage_stock
                });
            }
        },
        
        // Update cart item quantity
        updateQuantity(item, quantity) {
            quantity = parseInt(quantity);
            
            if (quantity < 1) {
                this.removeFromCart(item);
                return;
            }
            
            if (item.manage_stock && quantity > item.stock_quantity) {
                alert('Not enough stock available');
                return;
            }
            
            item.quantity = quantity;
        },
        
        // Remove item from cart
        removeFromCart(item) {
            const index = this.cart.indexOf(item);
            if (index > -1) {
                this.cart.splice(index, 1);
            }
        },
        
        // Clear cart
        clearCart() {
            this.cart = [];
            this.discountAmount = 0;
            this.discountType = 'fixed';
            this.paymentMethod = 'cash';
        },
        
        // Apply discount
        applyDiscount() {
            // Discount is computed automatically
        },
        
        // Create new customer
        async createCustomer() {
            if (!this.newCustomer.email || !this.newCustomer.first_name) {
                alert('Please fill in required fields');
                return;
            }
            
            try {
                const data = await this.apiRequest('customers', 'POST', this.newCustomer);
                
                if (data.success) {
                    this.selectCustomer(data.customer);
                    this.showNewCustomerModal = false;
                    this.newCustomer = {
                        first_name: '',
                        last_name: '',
                        email: '',
                        phone: ''
                    };
                }
            } catch (error) {
                console.error('Failed to create customer:', error);
            }
        },
        
        // Checkout
        async checkout() {
            if (!this.canCheckout) {
                return;
            }
            
            if (!confirm('Process this order?')) {
                return;
            }
            
            const orderData = {
                customer_id: this.selectedCustomer ? this.selectedCustomer.id : 0,
                items: this.cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    price: item.price
                })),
                payment_method: this.paymentMethod,
                discount_amount: this.discountAmount,
                discount_type: this.discountType
            };
            
            try {
                const data = await this.apiRequest('orders', 'POST', orderData);
                
                if (data.success) {
                    this.lastOrder = data;
                    this.showSuccessModal = true;
                    
                    // Clear cart after successful order
                    setTimeout(() => {
                        this.clearCart();
                        this.clearCustomer();
                    }, 500);
                }
            } catch (error) {
                console.error('Failed to create order:', error);
            }
        },
        
        // Print receipt
        printReceipt() {
            window.print();
        },
        
        // Load reports
        async loadReports() {
            this.loadingReports = true;
            try {
                const data = await this.apiRequest('reports?period=' + this.reportPeriod);
                this.reports = data;
            } catch (error) {
                console.error('Failed to load reports:', error);
            } finally {
                this.loadingReports = false;
            }
        },
        
        // Format currency
        formatCurrency(amount) {
            return swcposData.currency + parseFloat(amount).toFixed(2);
        },
        
        // Format date
        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        },
        
        // Switch tab
        switchTab(tab) {
            this.activeTab = tab;
            
            if (tab === 'reports') {
                this.loadReports();
            }
        }
    },
    
    mounted() {
        // Load initial data
        this.loadProducts();
        this.loadCategories();
    },
    
    template: `
        <div class="pos-container">
            <!-- Tabs -->
            <div class="pos-tabs" style="position: fixed; top: 32px; left: 160px; right: 0; background: white; z-index: 100; padding: 10px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <button class="pos-tab" :class="{ active: activeTab === 'pos' }" @click="switchTab('pos')">
                    POS
                </button>
                <button class="pos-tab" :class="{ active: activeTab === 'reports' }" @click="switchTab('reports')">
                    Reports
                </button>
            </div>
            
            <!-- POS View -->
            <div v-if="activeTab === 'pos'" style="display: flex; gap: 20px; width: 100%; margin-top: 60px;">
                <!-- Products Section -->
                <div class="pos-products">
                    <div class="pos-header">
                        <h2>Products</h2>
                    </div>
                    
                    <div class="pos-search">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            @input="loadProducts"
                            placeholder="Search products by name or SKU..."
                        />
                        <select v-model="selectedCategory" @change="loadProducts">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }} ({{ cat.count }})
                            </option>
                        </select>
                        <button @click="loadProducts">Search</button>
                    </div>
                    
                    <div v-if="loadingProducts" class="swcpos-loading">
                        <p>Loading products...</p>
                    </div>
                    
                    <div v-else class="product-grid">
                        <div 
                            v-for="product in products" 
                            :key="product.id"
                            class="product-card"
                            :class="{ 'out-of-stock': product.stock_status === 'outofstock' }"
                            @click="addToCart(product)"
                        >
                            <img 
                                v-if="product.image" 
                                :src="product.image" 
                                :alt="product.name"
                                class="product-image"
                            />
                            <div v-else class="product-image"></div>
                            <div class="product-name">{{ product.name }}</div>
                            <div class="product-price">{{ formatCurrency(product.price) }}</div>
                            <div class="product-stock">
                                <span v-if="product.stock_status === 'outofstock'" style="color: #e74c3c;">Out of Stock</span>
                                <span v-else-if="product.manage_stock">Stock: {{ product.stock_quantity }}</span>
                                <span v-else>In Stock</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Section -->
                <div class="pos-cart">
                    <div class="pos-header">
                        <h2>Cart ({{ cartItemCount }})</h2>
                    </div>
                    
                    <!-- Customer Selection -->
                    <div class="pos-customer">
                        <div v-if="!selectedCustomer">
                            <div class="customer-search">
                                <input 
                                    type="text"
                                    v-model="customerSearch"
                                    @input="searchCustomers"
                                    @focus="showCustomerResults = true"
                                    placeholder="Search customer..."
                                />
                                <div v-if="showCustomerResults && customerResults.length > 0" class="customer-results">
                                    <div 
                                        v-for="customer in customerResults"
                                        :key="customer.id"
                                        class="customer-result"
                                        @click="selectCustomer(customer)"
                                    >
                                        <div class="customer-name">{{ customer.name }}</div>
                                        <div style="font-size: 12px; color: #7f8c8d;">{{ customer.email }}</div>
                                        <div style="font-size: 12px; color: #f39c12;">Points: {{ customer.points }}</div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn-new-customer" @click="showNewCustomerModal = true">
                                + New Customer
                            </button>
                        </div>
                        <div v-else class="customer-info">
                            <div>
                                <div class="customer-name">{{ selectedCustomer.name }}</div>
                                <div class="customer-points">{{ selectedCustomer.points }} points</div>
                            </div>
                            <button @click="clearCustomer" class="btn-remove">×</button>
                        </div>
                    </div>
                    
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div v-if="cart.length === 0" class="cart-empty">
                            <p>Cart is empty</p>
                            <p style="font-size: 12px;">Click on products to add them</p>
                        </div>
                        
                        <div v-for="item in cart" :key="item.product_id" class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name">{{ item.name }}</div>
                                <div class="cart-item-price">{{ formatCurrency(item.price) }} each</div>
                            </div>
                            <div class="cart-item-qty">
                                <button class="qty-btn" @click="updateQuantity(item, item.quantity - 1)">-</button>
                                <input 
                                    type="number" 
                                    class="qty-input"
                                    :value="item.quantity"
                                    @input="updateQuantity(item, $event.target.value)"
                                    min="1"
                                />
                                <button class="qty-btn" @click="updateQuantity(item, item.quantity + 1)">+</button>
                            </div>
                            <button class="btn-remove" @click="removeFromCart(item)">×</button>
                        </div>
                    </div>
                    
                    <!-- Discount -->
                    <div v-if="cart.length > 0" class="cart-discount">
                        <strong>Discount</strong>
                        <div class="discount-input">
                            <input 
                                type="number" 
                                v-model="discountAmount" 
                                placeholder="Amount"
                                min="0"
                                step="0.01"
                            />
                            <select v-model="discountType">
                                <option value="fixed">Fixed</option>
                                <option value="percent">Percent</option>
                            </select>
                        </div>
                        <div v-if="discountAmount > 0" class="discount-applied">
                            <span>Discount Applied:</span>
                            <span>-{{ formatCurrency(cartDiscount) }}</span>
                        </div>
                    </div>
                    
                    <!-- Totals -->
                    <div v-if="cart.length > 0" class="cart-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>{{ formatCurrency(cartSubtotal) }}</span>
                        </div>
                        <div v-if="cartDiscount > 0" class="total-row">
                            <span>Discount:</span>
                            <span>-{{ formatCurrency(cartDiscount) }}</span>
                        </div>
                        <div class="total-row final">
                            <span>Total:</span>
                            <span>{{ formatCurrency(cartTotal) }}</span>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div v-if="cart.length > 0" class="cart-payment">
                        <strong>Payment Method</strong>
                        <div class="payment-methods">
                            <button 
                                class="payment-method"
                                :class="{ active: paymentMethod === 'cash' }"
                                @click="paymentMethod = 'cash'"
                            >
                                Cash
                            </button>
                            <button 
                                class="payment-method"
                                :class="{ active: paymentMethod === 'card' }"
                                @click="paymentMethod = 'card'"
                            >
                                Card
                            </button>
                            <button 
                                class="payment-method"
                                :class="{ active: paymentMethod === 'external' }"
                                @click="paymentMethod = 'external'"
                            >
                                Other
                            </button>
                        </div>
                    </div>
                    
                    <!-- Checkout Button -->
                    <button 
                        class="btn-checkout"
                        :disabled="!canCheckout"
                        @click="checkout"
                    >
                        Complete Sale - {{ formatCurrency(cartTotal) }}
                    </button>
                </div>
            </div>
            
            <!-- Reports View -->
            <div v-if="activeTab === 'reports'" class="reports-container" style="width: 100%; margin-top: 60px;">
                <div class="pos-header">
                    <h2>Sales Reports</h2>
                    <select v-model="reportPeriod" @change="loadReports">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                
                <div v-if="loadingReports" class="swcpos-loading">
                    <p>Loading reports...</p>
                </div>
                
                <div v-else-if="reports">
                    <div class="report-cards">
                        <div class="report-card">
                            <div class="report-card-label">Total Sales</div>
                            <div class="report-card-value">{{ formatCurrency(reports.total_sales) }}</div>
                        </div>
                        <div class="report-card">
                            <div class="report-card-label">Total Orders</div>
                            <div class="report-card-value">{{ reports.total_orders }}</div>
                        </div>
                        <div class="report-card">
                            <div class="report-card-label">Average Order</div>
                            <div class="report-card-value">{{ formatCurrency(reports.average_order) }}</div>
                        </div>
                    </div>
                    
                    <div class="report-table">
                        <h3>Top Selling Products</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity Sold</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, id) in reports.top_products" :key="id">
                                    <td>{{ product.name }}</td>
                                    <td>{{ product.quantity }}</td>
                                    <td>{{ formatCurrency(product.total) }}</td>
                                </tr>
                                <tr v-if="Object.keys(reports.top_products).length === 0">
                                    <td colspan="3" style="text-align: center; color: #95a5a6;">No sales data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="report-table" style="margin-top: 20px;">
                        <h3>Payment Methods</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Orders</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, method) in reports.payment_methods" :key="method">
                                    <td style="text-transform: capitalize;">{{ method }}</td>
                                    <td>{{ data.count }}</td>
                                    <td>{{ formatCurrency(data.total) }}</td>
                                </tr>
                                <tr v-if="Object.keys(reports.payment_methods).length === 0">
                                    <td colspan="3" style="text-align: center; color: #95a5a6;">No payment data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- New Customer Modal -->
            <div v-if="showNewCustomerModal" class="modal-overlay" @click.self="showNewCustomerModal = false">
                <div class="modal">
                    <div class="modal-header">
                        <h3>New Customer</h3>
                        <button class="modal-close" @click="showNewCustomerModal = false">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" v-model="newCustomer.first_name" required />
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" v-model="newCustomer.last_name" />
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" v-model="newCustomer.email" required />
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" v-model="newCustomer.phone" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="showNewCustomerModal = false">Cancel</button>
                        <button class="btn btn-primary" @click="createCustomer">Create Customer</button>
                    </div>
                </div>
            </div>
            
            <!-- Success Modal -->
            <div v-if="showSuccessModal" class="modal-overlay" @click.self="showSuccessModal = false">
                <div class="modal">
                    <div class="modal-header">
                        <h3>✓ Order Completed</h3>
                        <button class="modal-close" @click="showSuccessModal = false">×</button>
                    </div>
                    <div class="modal-body">
                        <div v-if="lastOrder" class="receipt">
                            <div class="receipt-header">
                                <h2>{{ swcposData.shopName }}</h2>
                                <p>Order #{{ lastOrder.order_number }}</p>
                                <p>{{ formatDate(new Date()) }}</p>
                            </div>
                            <div class="receipt-items">
                                <div v-for="item in cart" :key="item.product_id" class="receipt-item">
                                    <span>{{ item.quantity }}x {{ item.name }}</span>
                                    <span>{{ formatCurrency(item.price * item.quantity) }}</span>
                                </div>
                            </div>
                            <div class="receipt-totals">
                                <div class="receipt-total">
                                    <span>Subtotal:</span>
                                    <span>{{ formatCurrency(cartSubtotal) }}</span>
                                </div>
                                <div v-if="cartDiscount > 0" class="receipt-total">
                                    <span>Discount:</span>
                                    <span>-{{ formatCurrency(cartDiscount) }}</span>
                                </div>
                                <div class="receipt-total final">
                                    <span>TOTAL:</span>
                                    <span>{{ formatCurrency(lastOrder.total) }}</span>
                                </div>
                                <div class="receipt-total">
                                    <span>Payment:</span>
                                    <span style="text-transform: capitalize;">{{ paymentMethod }}</span>
                                </div>
                            </div>
                            <div class="receipt-footer">
                                <p>Thank you for your purchase!</p>
                                <p v-if="selectedCustomer">Points Earned: {{ Math.floor(lastOrder.total) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="showSuccessModal = false">Close</button>
                        <button class="btn btn-primary" @click="printReceipt">Print Receipt</button>
                    </div>
                </div>
            </div>
        </div>
    `
}).mount('#swcpos-app');
