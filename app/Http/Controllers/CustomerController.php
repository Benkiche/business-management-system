<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Constructor - require authentication.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $query = Customer::query()->latest();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('debt_status') && $request->debt_status !== '') {
            if ($request->debt_status === 'with_debt') {
                $query->withOutstandingBalance();
            } elseif ($request->debt_status === 'exceeded_limit') {
                $query->whereRaw('outstanding_balance > credit_limit');
            }
        }

        $customers = $query->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['customer_code'] = Customer::generateCustomerCode();

            Customer::create($data);

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create customer. Please try again.');
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['sales', 'payments']);
        $sales = $customer->sales()->latest()->paginate(10);
        $payments = $customer->payments()->latest()->paginate(10);

        return view('customers.show', compact('customer', 'sales', 'payments'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        try {
            $customer->update($request->validated());

            return redirect()
                ->route('customers.show', $customer)
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update customer. Please try again.');
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        // Check if customer has sales
        if ($customer->sales()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete customer with associated sales.');
        }

        try {
            $customer->delete();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete customer. Please try again.');
        }
    }
}