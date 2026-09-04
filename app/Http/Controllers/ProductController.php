<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Constructor - require authentication.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['category', 'supplier'])
            ->latest();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category !== '') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('supplier') && $request->supplier !== '') {
            $query->where('supplier_id', $request->supplier);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('stock_status') && $request->stock_status !== '') {
            if ($request->stock_status === 'out_of_stock') {
                $query->outOfStock();
            } elseif ($request->stock_status === 'low_stock') {
                $query->lowStock();
            }
        }

        $products = $query->paginate(15);
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();

        return view('products.index', compact('products', 'categories', 'suppliers'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['product_code'] = Product::generateProductCode();

            // Handle product image upload
            if ($request->hasFile('product_image')) {
                $path = $request->file('product_image')->store('products', 'public');
                $data['product_image_path'] = $path;
            }

            Product::create($data);

            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create product. Please try again.');
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $product->load(['category', 'supplier']);

        return view('products.show', compact('product'));
    }

    /**
     * Display a product image from the public disk.
     */
    public function image(Product $product)
    {
        abort_unless($product->product_image_path, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($product->product_image_path), 404);

        return response()->file($disk->path($product->product_image_path));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Handle product image upload
            if ($request->hasFile('product_image')) {
                // Delete old image if exists
                if ($product->product_image_path) {
                    \Storage::disk('public')->delete($product->product_image_path);
                }

                $path = $request->file('product_image')->store('products', 'public');
                $data['product_image_path'] = $path;
            }

            $product->update($data);

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            // Delete product image if exists
            if ($product->product_image_path) {
                \Storage::disk('public')->delete($product->product_image_path);
            }

            $product->delete();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete product. Please try again.');
        }
    }
}