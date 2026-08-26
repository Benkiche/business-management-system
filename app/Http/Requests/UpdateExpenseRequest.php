<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermission('expenses.edit');
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,credit_card,check,bank_transfer'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }

    public function messages(): array
    {
        return [
            'expense_category_id.required' => 'Please select an expense category.',
            'description.required' => 'Description is required.',
            'amount.required' => 'Amount is required.',
            'receipt.mimes' => 'Receipt must be a PDF or image file.',
        ];
    }
}