<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditService;

class LogAuditActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, $response)
    {
        // Only log authenticated users
        if (!auth()->check()) {
            return;
        }

        // Skip certain routes
        $skipRoutes = [
            'notifications.*',
            'api.*',
            'health-check',
        ];

        foreach ($skipRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return;
            }
        }

        // Determine action based on route and method
        $routeName = $request->route()?->getName() ?? 'unknown';

        if (str_contains($routeName, '.store')) {
            $action = 'created';
        } elseif (str_contains($routeName, '.update')) {
            $action = 'updated';
        } elseif (str_contains($routeName, '.destroy')) {
            $action = 'deleted';
        } elseif (str_contains($routeName, '.show')) {
            $action = 'viewed';
        } elseif (str_contains($routeName, 'approve')) {
            $action = 'approved';
        } elseif (str_contains($routeName, 'reject')) {
            $action = 'rejected';
        } elseif (str_contains($routeName, 'cancel')) {
            $action = 'cancelled';
        } elseif (str_contains($routeName, 'export')) {
            $action = 'exported';
        } else {
            return;
        }

        // Extract entity type from route
        $routeParts = explode('.', $routeName);
        $entityType = match($routeParts[0] ?? null) {
            'sales' => 'Sale',
            'payments' => 'Payment',
            'expenses' => 'Expense',
            'products' => 'Product',
            'customers' => 'Customer',
            'suppliers' => 'Supplier',
            'users' => 'User',
            'roles' => 'Role',
            'categories' => 'Category',
            'inventory' => 'Inventory',
            default => 'Unknown',
        };

        // Get entity ID
        $entityId = null;
        if ($request->route('sale')) {
            $entityId = $request->route('sale')->id;
        } elseif ($request->route('payment')) {
            $entityId = $request->route('payment')->id;
        } elseif ($request->route('expense')) {
            $entityId = $request->route('expense')->id;
        } elseif ($request->route('product')) {
            $entityId = $request->route('product')->id;
        } elseif ($request->route('customer')) {
            $entityId = $request->route('customer')->id;
        } elseif ($request->route('user')) {
            $entityId = $request->route('user')->id;
        }

        // Log the action
        AuditService::log(
            $action,
            $entityType,
            $entityId,
            $routeName,
            null,
            $action === 'created' || $action === 'updated' ? $request->all() : null
        );
    }
}