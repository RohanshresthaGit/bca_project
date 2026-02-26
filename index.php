<?php
session_start();

// Redirect to landing page if not logged in and no hash
if (!isset($_SESSION['user_id']) && empty($_GET) && !isset($_SERVER['HTTP_REFERER'])) {
    header('Location: landing.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Login Page (First Page) -->
    <div id="loginPage" class="page active">
        <h1 class="page-title">Inventory Management System</h1>
        <div class="auth-container">
            <h2>Login</h2>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="loginUsername" placeholder="Enter username"
                    onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="loginPassword" placeholder="Enter password">
            </div>
            <div id="loginError" class="error-message"></div>
            <button onclick="handleLogin()" class="btn btn-primary btn-block">Login</button>
            <button onclick="showPage('signupPage')" class="btn btn-secondary btn-block">Sign Up</button>
        </div>
    </div>

    <!-- Signup Page -->
    <div id="signupPage" class="page">
        <h1 class="page-title">Inventory Management System</h1>
        <div class="auth-container signup-container">
            <h2>Sign Up</h2>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" id="signupFirstName" placeholder="Enter first name"
                    onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="signupLastName" placeholder="Enter last name"
                    onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" id="signupAddress" placeholder="Enter address">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="signupPhone" placeholder="Enter phone number"
                    onkeypress="return validateNumberInput(event)" onpaste="return validateNumberPaste(event)"
                    maxlength="15">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="signupUsername" placeholder="Choose a username"
                    onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="signupPassword" placeholder="Choose a password">
            </div>
            <div id="signupError" class="error-message"></div>
            <button onclick="handleSignup()" class="btn btn-primary btn-block">Create Account</button>
            <button onclick="showPage('loginPage')" class="btn btn-secondary btn-block">Back to Login</button>
        </div>
    </div>

    <!-- Dashboard Page -->
    <div id="dashboardPage" class="page">
        <div class="dashboard-layout">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="user-info">
                    <div class="profile-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3 id="currentUser"></h3>
                    <p id="userRole" class="user-role"></p>
                </div>
                <nav class="nav-menu" id="navMenu">
                    <!-- Navigation items will be added dynamically -->
                </nav>
                <button onclick="handleLogout()" class="btn btn-danger btn-block">Logout</button>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Dashboard Section -->
                <div id="dashboardSection" class="section active">
                    <h2>Inventory Dashboard</h2>
                    <div class="table-container">
                        <table id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Price (Rs)</th>
                                    <th>Total (Rs)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Item Section -->
                <div id="addItemSection" class="section">
                    <h2>Add New Item</h2>
                    <div class="form-container">
                        <div class="form-group">
                            <label>Item Name</label>
                            <input type="text" id="itemName" placeholder="Enter item name"
                                onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" id="itemQuantity" placeholder="Enter quantity"
                                onkeypress="return validateNumberInput(event)"
                                onpaste="return validateNumberPaste(event)">
                        </div>
                        <div class="form-group">
                            <label>Price (Rs)</label>
                            <input type="text" id="itemPrice" placeholder="Enter price"
                                onkeypress="return validateNumberInput(event)"
                                onpaste="return validateNumberPaste(event)">
                        </div>
                        <div class="form-group">
                            <label>Total (Rs)</label>
                            <input type="text" id="itemTotal" readonly class="readonly-input">
                        </div>
                        <button onclick="handleAddItem()" class="btn btn-primary btn-block">Add Item</button>
                    </div>
                </div>

                <div id="billingSection" class="section">
                    <h2>💰 Billing</h2>

                    <div class="form-container">

                        <!-- Customer Details -->
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" id="customerName" placeholder="Enter customer name"
                                onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" id="customerPhone" placeholder="Enter phone number"
                                onkeypress="return validateNumberInput(event)"
                                onpaste="return validateNumberPaste(event)" maxlength="15">
                        </div>

                        <!-- Items Heading -->
                        <h3>Items</h3>

                        <!-- Items Container -->
                        <div id="cartItemsContainer"></div>

                        <!-- Add Item Button -->
                        <button class="btn btn-secondary" onclick="addCartItemRow()">➕ Add Item</button>

                        <!-- Payment -->
                        <div class="form-group" style="margin-top:20px;">
                            <label>Payment Method</label>
                            <select id="paymentMethod" class="payment-select">
                                <option value="">Select payment method</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                            </select>
                        </div>

                        <!-- Generate Bill -->
                        <div class="button-group">
                            <button class="btn btn-primary" onclick="handleGenerateBill()">🧾 Generate Bill</button>
                        </div>

                    </div>
                </div>


                <!-- History Section -->
                <div id="historySection" class="section">
                    <h2>Bill History</h2>
                    <div id="historyContainer">
                        <p class="text-center">Loading...</p>
                    </div>
                </div>

                <!-- Admin Users Section -->
                <div id="adminUsersSection" class="section">
                    <h2>User Management</h2>
                    <div id="usersContainer">
                        <p class="text-center">Loading users...</p>
                    </div>
                </div>

                <!-- Admin User Details Section -->
                <div id="adminUserDetailsSection" class="section">
                    <button onclick="showSection('adminUsers')" class="btn btn-secondary" style="margin-bottom: 20px;">←
                        Back to Users</button>
                    <div id="userDetailsContainer">
                        <p class="text-center">Select a user to view details</p>
                    </div>
                </div>

                <!-- Admin Inventory View Section -->
                <div id="adminInventorySection" class="section">
                    <button onclick="showSection('adminUserDetails')" class="btn btn-secondary"
                        style="margin-bottom: 20px;">← Back to User Details</button>
                    <h2>User Inventory</h2>
                    <div id="adminInventoryContainer">
                        <p class="text-center">Loading inventory...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Preview Modal -->
    <div id="billModal" class="modal">
        <div class="modal-content">
            <div id="billContent"></div>
            <div class="button-group">
                <button onclick="printBill()" class="btn btn-primary">Print</button>
                <button onclick="closeBillModal()" class="btn btn-danger">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="notification-toast">
        <span id="notificationMessage"></span>
    </div>

    <script src="script.js"></script>
    <script>
        // Handle hash navigation from landing page
        window.addEventListener('load', function () {
            const hash = window.location.hash;
            if (hash === '#login') {
                showPage('loginPage');
            } else if (hash === '#signup') {
                showPage('signupPage');
            }
        });
    </script>
</body>

</html>