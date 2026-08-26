<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

class AlertService
{
    /**
     * Check for low stock products and notify.
     */
    public static function checkLowStockProducts(): int
    {
        $lowStockProducts = Product::where('quantity_on_hand', '<=', 'minimum_stock_level')
            ->where('status', 'active')
            ->get();

        $count = 0;
        $storeKeepers = User::whereHas('role', function ($q) {
            $q->where('name', 'storekeeper');
        })->get();

        foreach ($lowStockProducts as $product) {
            foreach ($storeKeepers as $user) {
                NotificationService::notify(
                    $user,
                    'warning',
                    'Low Stock Alert',
                    "Product '{$product->name}' is running low. Current stock: {$product->quantity_on_hand}, Minimum: {$product->minimum_stock_level}",
                    'low_stock',
                    route('products.show', $product)
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check for overdue payments and notify.
     */
    public static function checkOverduePayments(): int
    {
        $overduePayments = Sale::where('status', 'completed')
            ->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->with('customer')
            ->get();

        $count = 0;
        $accountants = User::whereHas('role', function ($q) {
            $q->where('name', 'accountant');
        })->get();

        foreach ($overduePayments as $sale) {
            $outstanding = $sale->outstanding_balance;
            foreach ($accountants as $user) {
                NotificationService::notify(
                    $user,
                    'danger',
                    'Overdue Payment',
                    "Invoice {$sale->invoice_number} from {$sale->customer->name} is overdue. Outstanding: \${$outstanding}",
                    'overdue_payment',
                    route('sales.show', $sale)
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check for out of stock products.
     */
    public static function checkOutOfStockProducts(): int
    {
        $outOfStockProducts = Product::where('quantity_on_hand', 0)
            ->where('status', 'active')
            ->get();

        $count = 0;
        $managers = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['manager', 'admin']);
        })->get();

        foreach ($outOfStockProducts as $product) {
            foreach ($managers as $user) {
                NotificationService::notify(
                    $user,
                    'danger',
                    'Out of Stock',
                    "Product '{$product->name}' is out of stock and needs replenishment.",
                    'low_stock',
                    route('products.show', $product)
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notify about payment received.
     */
    public static function notifyPaymentReceived(Sale $sale, $amount): void
    {
        $salesperson = $sale->salesperson;
        $customer = $sale->customer;

        NotificationService::notify(
            $salesperson,
            'success',
            'Payment Received',
            "Payment of \${$amount} received from {$customer->name} for invoice {$sale->invoice_number}",
            'payment_received',
            route('sales.show', $sale)
        );
    }

    /**
     * Notify about sale created.
     */
    public static function notifySaleCreated(Sale $sale): void
    {
        $managers = User::whereHas('role', function ($q) {
            $q->where('name', 'manager');
        })->get();

        foreach ($managers as $user) {
            NotificationService::notify(
                $user,
                'info',
                'New Sale',
                "New sale {$sale->invoice_number} created by {$sale->salesperson->name} for \${$sale->grand_total}",
                'sale_created',
                route('sales.show', $sale)
            );
        }
    }

    /**
     * Notify about expense approved.
     */
    public static function notifyExpenseApproved($expense): void
    {
        $recordedBy = $expense->recordedBy;

        NotificationService::notify(
            $recordedBy,
            'success',
            'Expense Approved',
            "Your expense {$expense->expense_code} of \${$expense->amount} has been approved.",
            'expense_approved',
            route('expenses.show', $expense)
        );
    }

    /**
     * Run all system alerts.
     */
    public static function runAllAlerts(): array
    {
        return [
            'low_stock' => self::checkLowStockProducts(),
            'out_of_stock' => self::checkOutOfStockProducts(),
            'overdue_payments' => self::checkOverduePayments(),
        ];
    }
}