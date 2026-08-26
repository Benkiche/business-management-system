<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;

class ExpenseServiceTest extends TestCase
{
    protected ExpenseService $expenseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expenseService = app(ExpenseService::class);
    }

    /** @test */
    public function it_can_create_an_expense()
    {
        $category = ExpenseCategory::factory()->create();

        $expenseData = [
            'expense_category_id' => $category->id,
            'description' => 'Office supplies',
            'amount' => 150.00,
            'payment_method' => 'cash',
            'expense_date' => now()->toDateString(),
        ];

        $expense = $this->expenseService->createExpense($expenseData);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals(150.00, $expense->amount);
        $this->assertEquals('pending', $expense->status);
    }

    /** @test */
    public function it_can_approve_an_expense()
    {
        $expense = Expense::factory()->create(['status' => 'pending']);

        $this->expenseService->approveExpense($expense);

        $expense->refresh();
        $this->assertEquals('approved', $expense->status);
    }

    /** @test */
    public function it_can_reject_an_expense()
    {
        $expense = Expense::factory()->create(['status' => 'pending']);

        $this->expenseService->rejectExpense($expense, 'Invalid receipt');

        $expense->refresh();
        $this->assertEquals('rejected', $expense->status);
        $this->assertStringContainsString('Invalid receipt', $expense->notes);
    }

    /** @test */
    public function it_calculates_expense_summary()
    {
        Expense::factory()->count(3)->approved()->create([
            'expense_date' => now()->toDateString(),
            'amount' => 100,
        ]);

        $summary = $this->expenseService->getExpenseSummary(
            now()->toDateString(),
            now()->toDateString()
        );

        $this->assertEquals(3, $summary['count']);
        $this->assertEquals(300, $summary['total_expenses']);
    }

    /** @test */
    public function it_gets_expenses_by_category()
    {
        $category1 = ExpenseCategory::factory()->create();
        $category2 = ExpenseCategory::factory()->create();

        Expense::factory()->count(2)->approved()->create([
            'expense_category_id' => $category1->id,
            'amount' => 100,
        ]);

        Expense::factory()->count(3)->approved()->create([
            'expense_category_id' => $category2->id,
            'amount' => 50,
        ]);

        $summary = $this->expenseService->getExpensesByCategory(
            now()->startOfMonth()->toDateString(),
            now()->toDateString()
        );

        $this->assertCount(2, $summary);
    }
}