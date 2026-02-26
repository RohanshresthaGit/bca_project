// Global variables
let currentUser = null;
let isAdmin = false;
let selectedUserId = null;
let currentEditItemId = null;

// Input Validation Functions
function validateTextInput(event) {
    const char = String.fromCharCode(event.which);
    const regex = /^[a-zA-Z\s'-]$/;
    if (!regex.test(char)) {
        event.preventDefault();
        return false;
    }
    return true;
}

function validateNumberInput(event) {
    const char = String.fromCharCode(event.which);
    const input = event.target;
    const currentValue = input.value;
    
    if ([8, 9, 13, 27, 46].indexOf(event.which) !== -1 ||
        (event.which === 65 && event.ctrlKey === true) ||
        (event.which === 67 && event.ctrlKey === true) ||
        (event.which === 86 && event.ctrlKey === true) ||
        (event.which === 88 && event.ctrlKey === true)) {
        return true;
    }
    
    if (char === '.' && currentValue.indexOf('.') !== -1) {
        event.preventDefault();
        return false;
    }
    
    if (!/^\d$/.test(char) && char !== '.') {
        event.preventDefault();
        return false;
    }
    return true;
}

function validateTextPaste(event) {
    event.preventDefault();
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');
    const cleanedText = pastedText.replace(/[^a-zA-Z\s'-]/g, '');
    const input = event.target;
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const currentValue = input.value;
    input.value = currentValue.substring(0, start) + cleanedText + currentValue.substring(end);
    const newPosition = start + cleanedText.length;
    input.setSelectionRange(newPosition, newPosition);
    return false;
}

function validateNumberPaste(event) {
    event.preventDefault();
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');
    let cleanedText = pastedText.replace(/[^\d.]/g, '');
    const decimalCount = (cleanedText.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = cleanedText.split('.');
        cleanedText = parts[0] + '.' + parts.slice(1).join('');
    }
    const input = event.target;
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const currentValue = input.value;
    input.value = currentValue.substring(0, start) + cleanedText + currentValue.substring(end);
    const newPosition = start + cleanedText.length;
    input.setSelectionRange(newPosition, newPosition);
    return false;
}

function showPage(pageId) {
    document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
    document.getElementById(pageId).classList.add('active');
}

function showSection(sectionName) {
    document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
    document.getElementById(sectionName + 'Section').classList.add('active');
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    const navItem = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
    if (navItem) navItem.classList.add('active');
    if (sectionName === 'dashboard') loadInventory();
    else if (sectionName === 'history') loadHistory();
    else if (sectionName === 'adminUsers') loadUsers();
}

function showNotification(message, type = 'success') {
    const toast = document.getElementById('notificationToast');
    const messageEl = document.getElementById('notificationMessage');
    messageEl.textContent = message;
    toast.className = `notification-toast ${type}`;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

async function handleLogin() {
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    const errorDiv = document.getElementById('loginError');
    if (!username || !password) {
        errorDiv.textContent = 'Please fill in all fields';
        return;
    }
    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        const data = await response.json();
        if (data.success) {
            currentUser = data.username;
            isAdmin = data.is_admin == 1;
            initializeDashboard();
        } else {
            errorDiv.textContent = data.message;
        }
    } catch (error) {
        errorDiv.textContent = 'Connection error. Please try again.';
    }
}

async function handleSignup() {
    const firstName = document.getElementById('signupFirstName').value.trim();
    const lastName = document.getElementById('signupLastName').value.trim();
    const address = document.getElementById('signupAddress').value.trim();
    const phone = document.getElementById('signupPhone').value.trim();
    const username = document.getElementById('signupUsername').value.trim();
    const password = document.getElementById('signupPassword').value;
    const errorDiv = document.getElementById('signupError');
    if (!firstName || !lastName || !address || !phone || !username || !password) {
        errorDiv.textContent = 'Please fill in all fields';
        return;
    }
    try {
        const response = await fetch('signup.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({firstName, lastName, address, phone, username, password})
        });
        const data = await response.json();
        if (data.success) {
            currentUser = data.username;
            isAdmin = data.is_admin == 1;
            initializeDashboard();
        } else {
            errorDiv.textContent = data.message;
        }
    } catch (error) {
        errorDiv.textContent = 'Connection error. Please try again.';
    }
}

async function handleLogout() {
    await fetch('logout.php');
    currentUser = null;
    isAdmin = false;
    window.location.href = 'landing.php';
}

function initializeDashboard() {
    document.getElementById('currentUser').textContent = currentUser;
    document.getElementById('userRole').textContent = isAdmin ? 'Administrator' : 'User';
    const navMenu = document.getElementById('navMenu');
    if (isAdmin) {
        navMenu.innerHTML = '<button class="nav-item active" onclick="showSection(\'adminUsers\')">👥 User Management</button>';
    } else {
        navMenu.innerHTML = `
            <button class="nav-item active" onclick="showSection('dashboard')">📊 Dashboard</button>
            <button class="nav-item" onclick="showSection('addItem')">➕ Add Item</button>
            <button class="nav-item" onclick="showSection('billing')">💰 Billing</button>
            <button class="nav-item" onclick="showSection('history')">📜 History</button>
        `;
    }
    showPage('dashboardPage');
    showSection(isAdmin ? 'adminUsers' : 'dashboard');
}

async function loadInventory() {
    try {
        const response = await fetch('get_inventory.php');
        const inventory = await response.json();
        const tbody = document.getElementById('inventoryTableBody');
        if (inventory.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No items in inventory</td></tr>';
        } else {
            tbody.innerHTML = inventory.map(item => `
                <tr>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${item.quantity}</td>
                    <td>Rs ${parseFloat(item.price).toFixed(2)}</td>
                    <td>Rs ${parseFloat(item.total).toFixed(2)}</td>
                    <td>
                        <button onclick="openEditModal(${item.id}, '${escapeHtml(item.name).replace(/'/g, "\\'")}', ${item.quantity}, ${item.price})" class="btn btn-sm btn-primary" style="margin-right: 5px;">✏️ Edit</button>
                        <button onclick="deleteItem(${item.id}, '${escapeHtml(item.name).replace(/'/g, "\\'")}'))" class="btn btn-sm btn-danger">🗑️ Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openEditModal(id, name, quantity, price) {
    currentEditItemId = id;
    const modalHTML = `
        <div class="user-modal-overlay" id="editModalOverlay" onclick="closeEditModal()">
            <div class="user-modal-content" onclick="event.stopPropagation()">
                <div class="user-modal-header">
                    <h2>✏️ Edit Item</h2>
                    <button class="close-modal-btn" onclick="closeEditModal()">✕</button>
                </div>
                <div class="user-modal-body">
                    <div class="form-group">
                        <label>Item Name</label>
                        <input type="text" id="editItemName" value="${escapeHtml(name)}" placeholder="Enter item name" onkeypress="return validateTextInput(event)" onpaste="return validateTextPaste(event)">
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="text" id="editItemQuantity" value="${quantity}" placeholder="Enter quantity" onkeypress="return validateNumberInput(event)" onpaste="return validateNumberPaste(event)">
                    </div>
                    <div class="form-group">
                        <label>Price (Rs)</label>
                        <input type="text" id="editItemPrice" value="${price}" placeholder="Enter price" onkeypress="return validateNumberInput(event)" onpaste="return validateNumberPaste(event)">
                    </div>
                    <div class="form-group">
                        <label>Total (Rs)</label>
                        <input type="text" id="editItemTotal" readonly class="readonly-input" value="Rs ${(quantity * price).toFixed(2)}">
                    </div>
                    <div class="button-group" style="margin-top: 20px;">
                        <button onclick="handleEditItem()" class="btn btn-primary">💾 Save Changes</button>
                        <button onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    const existingModal = document.getElementById('editModalOverlay');
    if (existingModal) existingModal.remove();
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    setTimeout(() => document.getElementById('editModalOverlay').classList.add('active'), 10);
    const quantityInput = document.getElementById('editItemQuantity');
    const priceInput = document.getElementById('editItemPrice');
    const totalInput = document.getElementById('editItemTotal');
    function updateTotal() {
        const qty = parseFloat(quantityInput.value) || 0;
        const prc = parseFloat(priceInput.value) || 0;
        totalInput.value = `Rs ${(qty * prc).toFixed(2)}`;
    }
    quantityInput.addEventListener('input', updateTotal);
    priceInput.addEventListener('input', updateTotal);
}

function closeEditModal() {
    const modal = document.getElementById('editModalOverlay');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
    currentEditItemId = null;
}

async function handleEditItem() {
    const name = document.getElementById('editItemName').value.trim();
    const quantity = parseFloat(document.getElementById('editItemQuantity').value);
    const price = parseFloat(document.getElementById('editItemPrice').value);
    if (!name || !quantity || !price) {
        showNotification('Please fill in all fields', 'error');
        return;
    }
    if (quantity <= 0 || price <= 0) {
        showNotification('Quantity and price must be greater than 0', 'error');
        return;
    }
    try {
        const response = await fetch('edit_item.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: currentEditItemId, name, quantity, price})
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Item updated successfully!', 'success');
            closeEditModal();
            loadInventory();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error updating item', 'error');
    }
}

async function deleteItem(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) return;
    try {
        const response = await fetch('delete_item.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Item deleted successfully!', 'success');
            loadInventory();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error deleting item', 'error');
    }
}

async function handleAddItem() {
    const name = document.getElementById('itemName').value.trim();
    const quantity = parseFloat(document.getElementById('itemQuantity').value);
    const price = parseFloat(document.getElementById('itemPrice').value);
    if (!name || !quantity || !price) {
        showNotification('Please fill in all fields', 'error');
        return;
    }
    try {
        const response = await fetch('add_item.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({name, quantity, price})
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Item added successfully!', 'success');
            document.getElementById('itemName').value = '';
            document.getElementById('itemQuantity').value = '';
            document.getElementById('itemPrice').value = '';
            document.getElementById('itemTotal').value = '';
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error adding item', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('itemQuantity');
    const priceInput = document.getElementById('itemPrice');
    const totalInput = document.getElementById('itemTotal');
    if (quantityInput && priceInput && totalInput) {
        function updateTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            totalInput.value = `Rs ${(quantity * price).toFixed(2)}`;
        }
        quantityInput.addEventListener('input', updateTotal);
        priceInput.addEventListener('input', updateTotal);
    }
});

async function addCartItemRow() {
    try {
        const response = await fetch('get_inventory.php');
        const inventory = await response.json();
        if (inventory.length === 0) {
            showNotification('No items in inventory', 'error');
            return;
        }
        const container = document.getElementById('cartItemsContainer');
        const row = document.createElement('div');
        row.className = 'cart-item-row';
        row.innerHTML = `
            <select class="item-select" onchange="updateItemPrice(this)">
                <option value="">Select Item</option>
                ${inventory.map(item => `<option value="${item.id}" data-price="${item.price}" data-max="${item.quantity}">${escapeHtml(item.name)} (Available: ${item.quantity})</option>`).join('')}
            </select>
            <input type="text" class="item-quantity" placeholder="Quantity" onchange="updateItemTotal(this)" onkeypress="return validateNumberInput(event)" onpaste="return validateNumberPaste(event)">
            <input type="text" class="item-price" placeholder="Price" readonly>
            <input type="text" class="item-total" placeholder="Total" readonly>
        `;
        container.appendChild(row);
    } catch (error) {
        showNotification('Error loading inventory', 'error');
    }
}

function updateItemPrice(selectEl) {
    const row = selectEl.closest('.cart-item-row');
    const priceInput = row.querySelector('.item-price');
    const option = selectEl.options[selectEl.selectedIndex];
    if (option.value) {
        priceInput.value = option.dataset.price;
        updateItemTotal(row.querySelector('.item-quantity'));
    }
}

function updateItemTotal(input) {
    const row = input.closest('.cart-item-row');
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    row.querySelector('.item-total').value = `Rs ${(quantity * price).toFixed(2)}`;
}

async function handleGenerateBill() {
    const customerName = document.getElementById('customerName').value.trim();
    const customerPhone = document.getElementById('customerPhone').value.trim();
    const paymentMethod = document.getElementById('paymentMethod').value;
    if (!customerName || !customerPhone || !paymentMethod) {
        showNotification('Please fill in all customer details', 'error');
        return;
    }
    const rows = document.querySelectorAll('.cart-item-row');
    if (rows.length === 0) {
        showNotification('Please add items to the bill', 'error');
        return;
    }
    const items = [];
    for (const row of rows) {
        const select = row.querySelector('.item-select');
        const quantity = parseFloat(row.querySelector('.item-quantity').value);
        const price = parseFloat(row.querySelector('.item-price').value);
        if (!select.value || !quantity || !price) {
            showNotification('Please complete all item fields', 'error');
            return;
        }
        const option = select.options[select.selectedIndex];
        const maxQuantity = parseFloat(option.dataset.max);
        if (quantity > maxQuantity) {
            showNotification(`Insufficient stock for ${option.text}`, 'error');
            return;
        }
        items.push({
            itemId: select.value,
            itemName: option.text.split('(')[0].trim(),
            quantity,
            price
        });
    }
    try {
        const response = await fetch('generate_bill.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({customerName, customerPhone, paymentMethod, items})
        });
        const data = await response.json();
        if (data.success) {
            showBillPreview(data.billId, customerName, customerPhone, paymentMethod, items);
            document.getElementById('customerName').value = '';
            document.getElementById('customerPhone').value = '';
            document.getElementById('paymentMethod').value = '';
            document.getElementById('cartItemsContainer').innerHTML = '';
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error generating bill', 'error');
    }
}

function showBillPreview(billId, customerName, customerPhone, paymentMethod, items) {
    let grandTotal = 0;
    items.forEach(item => grandTotal += item.quantity * item.price);
    const billContent = document.getElementById('billContent');
    billContent.innerHTML = `
        <div class="bill-preview">
            <h2>INVOICE</h2>
            <div class="bill-info">
                <p><strong>Bill #:</strong> ${billId}</p>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Customer:</strong> ${escapeHtml(customerName)}</p>
                <p><strong>Phone:</strong> ${escapeHtml(customerPhone)}</p>
                <p><strong>Payment:</strong> ${paymentMethod.toUpperCase()}</p>
            </div>
            <table class="bill-items-table">
                <thead><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>
                <tbody>
                    ${items.map(item => `<tr><td>${escapeHtml(item.itemName)}</td><td>${item.quantity}</td><td>Rs ${item.price.toFixed(2)}</td><td>Rs ${(item.quantity * item.price).toFixed(2)}</td></tr>`).join('')}
                </tbody>
            </table>
            <div class="bill-summary"><p>Grand Total: Rs ${grandTotal.toFixed(2)}</p></div>
        </div>
    `;
    document.getElementById('billModal').classList.add('active');
}

function closeBillModal() {
    document.getElementById('billModal').classList.remove('active');
}

function printBill() {
    window.print();
}

async function loadHistory() {
    try {
        const response = await fetch('get_history.php');
        const bills = await response.json();
        const container = document.getElementById('historyContainer');
        if (bills.length === 0) {
            container.innerHTML = '<p class="text-center">No bills found</p>';
        } else {
            container.innerHTML = bills.map(bill => `
                <div class="bill-card">
                    <div class="bill-header">
                        <div>
                            <div class="bill-customer">${escapeHtml(bill.customer_name)}</div>
                            <div class="bill-phone">📞 ${escapeHtml(bill.customer_phone)}</div>
                            <div class="bill-date">📅 ${new Date(bill.created_at).toLocaleString()}</div>
                        </div>
                        <div class="bill-total">Rs ${parseFloat(bill.grand_total).toFixed(2)}</div>
                    </div>
                    <table class="bill-table">
                        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                        <tbody>
                            ${bill.items.map(item => `<tr><td>${escapeHtml(item.item_name)}</td><td>${item.quantity}</td><td>Rs ${parseFloat(item.price).toFixed(2)}</td><td>Rs ${parseFloat(item.total).toFixed(2)}</td></tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading history:', error);
    }
}

async function loadUsers() {
    try {
        const response = await fetch('admin_get_users.php');
        const users = await response.json();
        const container = document.getElementById('usersContainer');
        if (!Array.isArray(users) || users.length === 0) {
            container.innerHTML = '<p class="text-center">No users registered yet</p>';
            return;
        }
        let html = '<div class="users-grid">';
        users.forEach(function(user) {
            const isBanned = user.is_banned == 1;
            html += '<div class="user-card ' + (isBanned ? 'user-card--banned' : '') + '">';
            html += '<div class="user-card-header">';
            html += '<div class="user-avatar">' + escapeHtml(user.first_name.charAt(0)) + escapeHtml(user.last_name.charAt(0)) + '</div>';
            html += '<div class="user-card-info">';
            html += '<h3>' + escapeHtml(user.first_name) + ' ' + escapeHtml(user.last_name) + '</h3>';
            html += '<p class="user-username">@' + escapeHtml(user.username) + '</p>';
            html += '</div>';
            html += isBanned ? '<span class="ban-badge">🚫 Banned</span>' : '<span class="active-badge">✅ Active</span>';
            html += '</div>';
            html += '<div class="user-card-details">';
            html += '<p><strong>📞</strong> ' + escapeHtml(user.phone) + '</p>';
            html += '<p><strong>📍</strong> ' + escapeHtml(user.address) + '</p>';
            html += '<p class="user-date"><strong>📅</strong> Joined ' + new Date(user.created_at).toLocaleDateString() + '</p>';
            html += '</div>';
            html += '<div style="padding: 0 20px 20px; display:flex; flex-direction:column; gap:10px;">';
            html += '<button class="btn btn-primary btn-sm" onclick="showUserInventory(' + user.id + ')">📦 View Inventory</button>';
            html += '<button class="btn btn-sm ' + (isBanned ? 'btn-unban' : 'btn-ban') + '" onclick="toggleBanUser(' + user.id + ', ' + (isBanned ? 0 : 1) + ')">' + (isBanned ? '✅ Unban User' : '🚫 Ban User') + '</button>';
            html += '</div>';
            html += '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('usersContainer').innerHTML = '<p class="text-center">Error loading users</p>';
    }
}

async function showUserModal(userId) {
    selectedUserId = userId;
    try {
        const response = await fetch(`admin_get_user_details.php?user_id=${userId}`);
        const data = await response.json();
        if (!data.success) {
            showNotification(data.message, 'error');
            return;
        }
        const user = data.user;
        const isBanned = user.is_banned == 1;
        const modalHTML = `
            <div class="user-modal-overlay" id="userModalOverlay" onclick="closeUserModal()">
                <div class="user-modal-content" onclick="event.stopPropagation()">
                    <div class="user-modal-header ${isBanned ? 'header--banned' : ''}">
                        <div>
                            <h2>${escapeHtml(user.first_name)} ${escapeHtml(user.last_name)}</h2>
                            <span class="modal-status-badge ${isBanned ? 'badge--banned' : 'badge--active'}">${isBanned ? '🚫 Banned' : '✅ Active'}</span>
                        </div>
                        <button class="close-modal-btn" onclick="closeUserModal()">✕</button>
                    </div>
                    <div class="user-modal-body">
                        <div class="user-info-section">
                            <div class="info-row"><span class="info-label">Username:</span><span class="info-value">${escapeHtml(user.username)}</span></div>
                            <div class="info-row"><span class="info-label">Phone:</span><span class="info-value">${escapeHtml(user.phone)}</span></div>
                            <div class="info-row"><span class="info-label">Address:</span><span class="info-value">${escapeHtml(user.address)}</span></div>
                            <div class="info-row"><span class="info-label">Member Since:</span><span class="info-value">${new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
                            <div class="info-row"><span class="info-label">Status:</span><span class="info-value">${isBanned ? '<strong style="color:#dc2626">Banned</strong>' : '<strong style="color:#16a34a">Active</strong>'}</span></div>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:12px; margin-top:20px;">
                            <button onclick="showUserInventory(${userId})" class="btn btn-primary btn-block" style="margin-top:0;">📦 View User Inventory</button>
                            <button onclick="toggleBanUser(${userId}, ${isBanned ? 0 : 1})" class="btn btn-block ${isBanned ? 'btn-unban' : 'btn-ban'}" style="margin-top:0;">
                                ${isBanned ? '✅ Unban User' : '🚫 Ban User'}
                            </button>
                        </div>
                        ${isBanned ? '<div class="ban-notice">⚠️ This user is currently banned and cannot log in.</div>' : ''}
                    </div>
                </div>
            </div>
        `;
        const existingModal = document.getElementById('userModalOverlay');
        if (existingModal) existingModal.remove();
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        setTimeout(() => document.getElementById('userModalOverlay').classList.add('active'), 10);
    } catch (error) {
        console.error('Error loading user details:', error);
        showNotification('Error loading user details', 'error');
    }
}

async function toggleBanUser(userId, banAction) {
    const actionWord = banAction === 1 ? 'ban' : 'unban';
    const confirmMsg = banAction === 1
        ? 'Are you sure you want to BAN this user? They will not be able to log in.'
        : 'Are you sure you want to UNBAN this user? They will regain access.';
    if (!confirm(confirmMsg)) return;
    try {
        const response = await fetch('Admin_ban_user_.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, action: actionWord })
        });
        const data = await response.json();
        if (data.success) {
            showNotification(data.message, 'success');
            closeUserModal();
            loadUsers();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error updating user status', 'error');
    }
}

function closeUserModal() {
    const modal = document.getElementById('userModalOverlay');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
}

async function showUserInventory(userId) {
    try {
        const response = await fetch(`admin_get_user_Inventory.php?user_id=${userId}`);
        const data = await response.json();
        if (!data.success) {
            showNotification(data.message, 'error');
            return;
        }
        const inventory = data.inventory;
        const user = data.user;
        const inventoryHTML = `
            <div class="user-modal-overlay" id="inventoryModalOverlay" onclick="closeInventoryModal()">
                <div class="user-modal-content large" onclick="event.stopPropagation()">
                    <div class="user-modal-header">
                        <div>
                            <h2>📦 ${escapeHtml(user.first_name)} ${escapeHtml(user.last_name)}'s Inventory</h2>
                            <p style="color:rgba(255,255,255,0.75);margin-top:4px;font-size:0.9rem;">${inventory.length} item(s)</p>
                        </div>
                        <button class="close-modal-btn" onclick="closeInventoryModal()">✕</button>
                    </div>
                    <div class="user-modal-body">
                        ${inventory.length === 0
                            ? '<div class="empty-inventory"><p>📭 No items in this user\'s inventory</p></div>'
                            : `<div class="admin-inventory-table-wrapper">
                                <table class="admin-inventory-table">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="adminInventoryTableBody">
                                        ${inventory.map(item => `
                                        <tr id="inv-row-${item.id}">
                                            <td><strong>${escapeHtml(item.name)}</strong></td>
                                            <td>${item.quantity}</td>
                                            <td>Rs ${parseFloat(item.price).toFixed(2)}</td>
                                            <td>Rs ${parseFloat(item.total).toFixed(2)}</td>
                                            <td>
                                                <button onclick="adminRemoveInventoryItem(${item.id}, ${userId}, '${escapeHtml(item.name).replace(/'/g, "\\'")}')"
                                                    class="btn btn-danger btn-sm">
                                                    🗑️ Remove
                                                </button>
                                            </td>
                                        </tr>`).join('')}
                                    </tbody>
                                </table>
                              </div>`
                        }
                    </div>
                </div>
            </div>
        `;
        const existingModal = document.getElementById('inventoryModalOverlay');
        if (existingModal) existingModal.remove();
        document.body.insertAdjacentHTML('beforeend', inventoryHTML);
        setTimeout(() => document.getElementById('inventoryModalOverlay').classList.add('active'), 10);
    } catch (error) {
        console.error('Error loading user inventory:', error);
        showNotification('Error loading user inventory', 'error');
    }
}

async function adminRemoveInventoryItem(itemId, userId, itemName) {
    if (!confirm(`Remove "${itemName}" from this user's inventory?\n\nThis cannot be undone.`)) return;
    try {
        const response = await fetch('Admin_remove_inventory_item.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ item_id: itemId, user_id: userId })
        });
        const data = await response.json();
        if (data.success) {
            const row = document.getElementById(`inv-row-${itemId}`);
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    const tbody = document.getElementById('adminInventoryTableBody');
                    if (tbody && tbody.children.length === 0) {
                        const wrapper = document.querySelector('.admin-inventory-table-wrapper');
                        if (wrapper) wrapper.innerHTML = '<div class="empty-inventory"><p>📭 No items left in this user\'s inventory</p></div>';
                    }
                }, 400);
            }
            showNotification(`"${itemName}" removed successfully`, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error removing item', 'error');
    }
}

function closeInventoryModal() {
    const modal = document.getElementById('inventoryModalOverlay');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
}