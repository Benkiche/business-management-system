@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products Management')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Products List</h5>
        @if(auth()->user()->hasPermission('products.create'))
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> New Product
            </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Search by name, SKU, barcode..."
                        value="{{ request('search') }}"
                    >
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="supplier" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stock_status" class="form-select">
                        <option value="">All Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU/Barcode</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->product_image_path)
                                            <img 
                                                src="{{ route('products.image', $product) }}"
                                                alt="{{ $product->name }}"
                                                width="40"
                                                height="40"
                                                class="rounded me-3"
                                            >
                                        @else
                                            <div class="bg-light rounded me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $product->product_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($product->sku)
                                        <small class="d-block"><strong>{{ $product->sku }}</strong></small>
                                    @endif
                                    @if($product->barcode)
                                        <small class="text-muted">{{ $product->barcode }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->category->name }}</td>
                                <td>
                                    <small class="d-block">Cost: TZS {{ number_format($product->purchase_price, 2) }}</small>
                                    <strong>TZS {{ number_format($product->selling_price, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ 
                                        $product->quantity_on_hand === 0 ? 'bg-danger' : 
                                        ($product->quantity_on_hand <= $product->minimum_stock_level ? 'bg-warning' : 'bg-success')
                                    }}">
                                        {{ $product->quantity_on_hand }}
                                    </span>
                                    <small class="d-block text-muted">Min: {{ $product->minimum_stock_level }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('products.edit'))
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('products.delete'))
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Delete"
                                                onclick="confirmDelete('{{ route('products.destroy', $product) }}', '{{ $product->name }}')"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $products->links('pagination::bootstrap-5') }}
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No products found.</p>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete product <strong id="deleteName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
function confirmDelete(url, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = url;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
@endsection