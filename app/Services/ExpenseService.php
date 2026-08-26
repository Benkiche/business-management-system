<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ExpenseService
{
    /**
     * Create a new expense.
     *
     * @throws Exception
     */
    public function createExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $expenseData = [
                'expense_code' => Expense::generateExpenseCode(),
                'expense_category_id' => $data['expense_category_id'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'expense_date' => $data['expense_date'],
                'recorded_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'pending',
            ];

            // Handle receipt upload
            if (isset($data['receipt']) && $data['receipt']) {
                $path = $data['receipt']->store('expenses', 'public');
                $expenseData['receipt_path'] = $path;
            }

            return Expense::create($expenseData);
        });
    }

    /**
     * Update an expense.
     *
     * @throws Exception
     */
    public function updateExpense(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $updateData = [
                'expense_category_id' => $data['expense_category_id'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'expense_date' => $data['expense_date'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? $expense->status,
            ];

            // Handle receipt upload
            if (isset($data['receipt']) && $data['receipt']) {
                // Delete old receipt if exists
                if ($expense->receipt_path) {
                    Storage::disk('public')->delete($expense->receipt_path);
                }
                $path = $data['receipt']->store('expenses', 'public');
                $updateData['receipt_path'] = $path;
            }

            $expense->update($updateData);
            return $expense->refresh();
        });
    }

    /**
     * Delete an expense.
     *
     * @throws Exception
     */
    public function deleteExpense(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            // Delete receipt if exists
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }

            $expense->delete();
        });
    }

    /**
     * Get expense summary for date range.
     */
    public function getExpenseSummary($fromDate, $toDate): array
    {
        $expenses = Expense::approved()
            ->dateRange($fromDate, $toDate)
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // Group by category
        $byCategory = $expenses->groupBy('expense_category_id')
            ->map(function ($items, $categoryId) {
                $category = ExpenseCategory::find($categoryId);
                return [
                    'category' => $category->name ?? 'Unknown',
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ];
            })
            ->values()
            ->toArray();

        // Group by payment method
        $byMethod = $expenses->groupBy('payment_method')
            ->map(function ($items, $method) {
                return [
                    'method' => $method,
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ];
            })
            ->values()
            ->toArray();

        // Group by day
        $dailyExpenses = [];
        $currentDate = \Carbon\Carbon::parse($fromDate);
        $endDate = \Carbon\Carbon::parse($toDate);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $dayExpenses = $expenses->filter(function ($expense) use ($dateStr) {
                return \Carbon\Carbon::parse($expense->expense_date)->toDateString() === $dateStr;
            });

            $dailyExpenses[$dateStr] = [
                'date' => $dateStr,
                'count' => $dayExpenses->count(),
                'total' => $dayExpenses->sum('amount'),
            ];

            $currentDate->addDay();
        }

        return [
            'total_expenses' => $totalExpenses,
            'count' => $expenses->count(),
            'by_category' => $byCategory,
            'by_method' => $byMethod,
            'daily' => $dailyExpenses,
            'period_from' => $fromDate,
            'period_to' => $toDate,
        ];
    }

    /**
     * Get pending expenses requiring approval.
     */
    public function getPendingExpenses(): array
    {
        return Expense::pending()
            ->with(['category', 'recordedBy'])
            ->orderBy('expense_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get expenses by category.
     */
    public function getExpensesByCategory($fromDate, $toDate): array
    {
        $categories = ExpenseCategory::active()
            ->with(['expenses' => function ($query) use ($fromDate, $toDate) {
                $query->approved()
                    ->dateRange($fromDate, $toDate);
            }])
            ->get();

        return $categories->map(function ($category) {
            return [
                'category' => $category->name,
                'count' => $category->expenses->count(),
                'total' => $category->expenses->sum('amount'),
                'average' => $category->expenses->count() > 0 
                    ? $category->expenses->sum('amount') / $category->expenses->count() 
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Approve an expense.
     */
    public function approveExpense(Expense $expense): void
    {
        $expense->update(['status' => 'approved']);
    }

    /**
     * Reject an expense.
     */
    public function rejectExpense(Expense $expense, string $reason = null): void
    {
        $expense->update([
            'status' => 'rejected',
            'notes' => ($expense->notes ? $expense->notes . "\n" : "") . "Rejected: " . ($reason ?? "No reason provided"),
        ]);
    }
}