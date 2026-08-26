<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salaries & Wages', 'description' => 'Employee salaries and wages'],
            ['name' => 'Rent', 'description' => 'Office and store rent'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, gas'],
            ['name' => 'Office Supplies', 'description' => 'Supplies and consumables'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotional costs'],
            ['name' => 'Transportation', 'description' => 'Fuel and vehicle maintenance'],
            ['name' => 'Insurance', 'description' => 'Business insurance premiums'],
            ['name' => 'Professional Services', 'description' => 'Consulting, accounting, legal'],
            ['name' => 'Repairs & Maintenance', 'description' => 'Building and equipment maintenance'],
            ['name' => 'Technology', 'description' => 'Software, licenses, IT services'],
            ['name' => 'Miscellaneous', 'description' => 'Other business expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}