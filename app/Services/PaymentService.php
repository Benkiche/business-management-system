<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Schema;

class PaymentService
{
    public function recordPayment(Sale $sale, array $data): void
    {
        if (! Schema::hasTable('sales')) {
            throw new \RuntimeException('Payments cannot be recorded until the sales database tables are installed.');
        }

        $sale->increment('amount_paid', $data['amount']);
        $sale->update(['payment_status' => $sale->outstanding_balance <= 0 ? 'paid' : 'partial']);
    }

    public function recordGeneralPayment(Customer $customer, array $data): void
    {
        $customer->decrement('outstanding_balance', $data['amount']);
    }
}
