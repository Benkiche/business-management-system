<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SupplierController extends Controller
{
    /**
     * Constructor - require authentication.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): View
    {
        $query = Supplier::query()->withCount('products');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $suppliers = $query->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['supplier_code'] = Supplier::generateSupplierCode();

            Supplier::create($data);

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create supplier. Please try again.');
        }
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): View
    {
        $supplier->load('products');
        $products = $supplier->products()->paginate(10);

        return view('suppliers.show', compact('supplier', 'products'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        try {
            $supplier->update($request->validated());

            return redirect()
                ->route('suppliers.show', $supplier)
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update supplier. Please try again.');
        }
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        // Check if supplier has products
        if ($supplier->products()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete supplier with associated products.');
        }

        try {
            $supplier->delete();

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete supplier. Please try again.');
        }
    }
}