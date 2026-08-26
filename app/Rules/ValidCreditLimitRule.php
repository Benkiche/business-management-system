<?php

namespace App\Rules;

use App\Models\Customer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCreditLimitRule implements ValidationRule
{
    protected $customerId;
    protected $saleAmount;

    public function __construct($customerId, $saleAmount)
    {
        $this->customerId = $customerId;
        $this->saleAmount = $saleAmount;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $customer = Customer::find($this->customerId);

        if ($customer && $customer->hasExceededCreditLimit()) {
            $fail("Customer has exceeded credit limit of {$customer->credit_limit}");
        }
    }
}