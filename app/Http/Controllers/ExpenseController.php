<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use App\Services\AuditService;
use App\Services\AlertService;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->middleware('auth');
        $this->expenseService = $expenseService;
    }

    /**
     * Display all expenses.
     */
    public function index(Request $request): View
    {
        $query = Expense::with(['category', 'recordedBy'])->latest('expense_date');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category !== '') {
            $query->where('expense_category_id', $request->category);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->paginate(20);
        $categories = ExpenseCategory::active()->get();

        // Log viewing expenses
        AuditService::log(
            'viewed',
            'Expense',
            null,
            'Viewed expenses list with filters: ' . json_encode($request->all())
        );

        return view('expenses.index', compact('expenses', 'categories'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(): View
    {
        $categories = ExpenseCategory::active()->get();

        return view('expenses.create', compact('categories'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        try {
            $expense = $this->expenseService->createExpense($request->validated());

            // Log the creation
            AuditService::log(
                'created',
                'Expense',
                $expense->id,
                "Created new expense {$expense->expense_code} in {$expense->category->name}",
                null,
                [
                    'expense_code' => $expense->expense_code,
                    'category_id' => $expense->expense_category_id,
                    'amount' => $expense->amount,
                    'status' => $expense->status,
                ]
            );

            return redirect()
                ->route('expenses.show', $expense)
                ->with('success', 'Expense recorded successfully! Code: ' . $expense->expense_code);
        } catch (\Exception $e) {
            // Log the error
            AuditService::log(
                'created',
                'Expense',
                null,
                'Failed to create expense',
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()
                ->withInput()
                ->with('error', 'Failed to record expense: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense): View
    {
        $expense->load('category', 'recordedBy');

        // Log viewing the expense
        AuditService::log(
            'viewed',
            'Expense',
            $expense->id,
            "Viewed expense {$expense->expense_code}"
        );

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense): View
    {
        $categories = ExpenseCategory::active()->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        try {
            $oldValues = [
                'amount' => $expense->amount,
                'category_id' => $expense->expense_category_id,
                'status' => $expense->status,
            ];

            $this->expenseService->updateExpense($expense, $request->validated());

            $expense->refresh();

            // Log the update
            AuditService::log(
                'updated',
                'Expense',
                $expense->id,
                "Updated expense {$expense->expense_code}",
                $oldValues,
                [
                    'amount' => $expense->amount,
                    'category_id' => $expense->expense_category_id,
                    'status' => $expense->status,
                ]
            );

            return redirect()
                ->route('expenses.show', $expense)
                ->with('success', 'Expense updated successfully!');
        } catch (\Exception $e) {
            // Log the error
            AuditService::log(
                'updated',
                'Expense',
                $expense->id,
                "Failed to update expense {$expense->expense_code}",
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()
                ->withInput()
                ->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        try {
            $expenseCode = $expense->expense_code;
            $amount = $expense->amount;

            $this->expenseService->deleteExpense($expense);

            // Log the deletion
            AuditService::log(
                'deleted',
                'Expense',
                $expense->id,
                "Deleted expense {$expenseCode} (Amount: \${$amount})",
                [
                    'expense_code' => $expenseCode,
                    'amount' => $amount,
                ],
                null
            );

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            // Log the error
            AuditService::log(
                'deleted',
                'Expense',
                $expense->id,
                "Failed to delete expense {$expense->expense_code}",
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()
                ->with('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    /**
     * Approve an expense.
     */
    public function approve(Expense $expense): RedirectResponse
    {
        try {
            $this->expenseService->approveExpense($expense);

            // Log the approval
            AuditService::log(
                'approved',
                'Expense',
                $expense->id,
                "Approved expense {$expense->expense_code}",
                ['status' => 'pending'],
                ['status' => 'approved']
            );

            // Send notification to recorder
            AlertService::notifyExpenseApproved($expense);

            return back()
                ->with('success', 'Expense approved.');
        } catch (\Exception $e) {
            // Log the error
            AuditService::log(
                'approved',
                'Expense',
                $expense->id,
                "Failed to approve expense {$expense->expense_code}",
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()
                ->with('error', 'Failed to approve expense: ' . $e->getMessage());
        }
    }

    /**
     * Reject an expense.
     */
    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        try {
            $reason = $request->input('reason', 'No reason provided');

            $this->expenseService->rejectExpense($expense, $reason);

            // Log the rejection
            AuditService::log(
                'rejected',
                'Expense',
                $expense->id,
                "Rejected expense {$expense->expense_code}: {$reason}",
                ['status' => 'pending'],
                ['status' => 'rejected']
            );

            return back()
                ->with('success', 'Expense rejected.');
        } catch (\Exception $e) {
            // Log the error
            AuditService::log(
                'rejected',
                'Expense',
                $expense->id,
                "Failed to reject expense {$expense->expense_code}",
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()
                ->with('error', 'Failed to reject expense: ' . $e->getMessage());
        }
    }
}