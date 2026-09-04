
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

// Public routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users management (admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');
        Route::resource('roles', RoleController::class);
    });

    Route::get('products/{product}/image', [ProductController::class, 'image'])->name('products.image');

    // Expenses
Route::middleware('auth')->group(function () {
    Route::resource('expenses', ExpenseController::class);
    Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');

    // Financial Reports
    Route::get('financial/dashboard', [FinancialReportController::class, 'dashboard'])->name('financial.dashboard');
    Route::get('financial/profit-loss', [FinancialReportController::class, 'profitAndLoss'])->name('financial.profit-loss');
    Route::get('financial/expenses', [FinancialReportController::class, 'expenseReport'])->name('financial.expenses');
    Route::get('financial/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('financial.balance-sheet');
    Route::get('financial/compare', [FinancialReportController::class, 'comparePeriods'])->name('financial.compare');
    Route::get('financial/export', [FinancialReportController::class, 'exportReport'])->name('financial.export');
});


// Inventory management
Route::middleware('auth')->group(function () {
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    
    // Stock operations
    Route::get('inventory/stock-in', [InventoryController::class, 'showStockInForm'])->name('inventory.stock-in.form');
    Route::post('inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
    
    Route::get('inventory/stock-out', [InventoryController::class, 'showStockOutForm'])->name('inventory.stock-out.form');
    Route::post('inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    
    Route::get('inventory/adjustment', [InventoryController::class, 'showAdjustmentForm'])->name('inventory.adjustment.form');
    Route::post('inventory/adjustment', [InventoryController::class, 'adjust'])->name('inventory.adjustment');
    
    // Reports
    Route::get('inventory/valuation', [InventoryController::class, 'valuationReport'])->name('inventory.valuation-report');
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStockReport'])->name('inventory.low-stock-report');
    Route::get('inventory/movements', [InventoryController::class, 'allMovements'])->name('inventory.movements');
    Route::get('inventory/product/{product}/history', [InventoryController::class, 'productHistory'])->name('inventory.product-history');
    
    // Exports
    Route::get('inventory/valuation/export-csv', [InventoryController::class, 'exportValuationCsv'])->name('inventory.valuation-export-csv');
});
    // Categories management
    Route::resource('categories', CategoryController::class);

    // Suppliers management
    Route::resource('suppliers', SupplierController::class);

    // Products management
    Route::resource('products', ProductController::class);

    // Customers management
    Route::resource('customers', CustomerController::class);
});

// Fallback
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

// Dashboard & Analytics
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Analytics
    Route::get('/analytics/sales', [AnalyticsController::class, 'sales'])->name('analytics.sales');
    Route::get('/analytics/inventory', [AnalyticsController::class, 'inventory'])->name('analytics.inventory');
    Route::get('/analytics/customers', [AnalyticsController::class, 'customers'])->name('analytics.customers');

    // Reports
    Route::get('/reports/sales', [AnalyticsController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/customers', [AnalyticsController::class, 'customerReport'])->name('reports.customers');
    Route::get('/reports/inventory', [AnalyticsController::class, 'inventoryReport'])->name('reports.inventory');

    // Export
    Route::get('/reports/export', [AnalyticsController::class, 'exportReport'])->name('reports.export');
});

// Notifications
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'delete'])->name('notifications.delete');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.updatePreferences');
});

Route::middleware('auth')->group(function () {
    // Sales routes
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::post('/sales/{sale}/payment', [SaleController::class, 'recordPayment'])->name('sales.recordPayment');
    Route::get('/sales/report/view', [SaleController::class, 'report'])->name('sales.report');
    Route::get('/sales/export/csv', [SaleController::class, 'export'])->name('sales.export');

    // Payment routes
    Route::get('/payments/sale/{sale}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/sale/{sale}', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/general', [PaymentController::class, 'storeGeneral'])->name('payments.storeGeneral');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/customer/{customer}', [PaymentController::class, 'history'])->name('payments.history');
    Route::get('/payments/export/csv', [PaymentController::class, 'export'])->name('payments.export');
});

Route::middleware('auth')->group(function () {
    // Conversation routes
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/close', [ConversationController::class, 'close'])->name('conversations.close');
    Route::post('/conversations/{conversation}/reopen', [ConversationController::class, 'reopen'])->name('conversations.reopen');
    Route::post('/conversations/{conversation}/participants', [ConversationController::class, 'addParticipant'])->name('conversations.addParticipant');
    Route::delete('/conversations/{conversation}/participants/{user}', [ConversationController::class, 'removeParticipant'])->name('conversations.removeParticipant');
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy'])->name('conversations.destroy');
    Route::get('/conversations/unread/count', [ConversationController::class, 'getUnreadCount'])->name('conversations.unreadCount');

    // Message routes
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
    Route::put('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');
    Route::get('/messages/{conversation}', [MessageController::class, 'getMessages'])->name('messages.getMessages');
    Route::get('/messages/{message}/reads', [MessageController::class, 'getReadReceipts'])->name('messages.getReadReceipts');
});