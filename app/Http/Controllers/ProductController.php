<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    

    public function index()
    {
        $products = Product::where('user_id', auth()->id())
            ->with(['category', 'images'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
{
    // فقط دسته‌بندی‌های فعالی که توسط کاربر جاری ایجاد شده‌اند
    $categories = Category::where('user_id', auth()->id())->get();

    return Inertia::render('Products/Create', [
        'categories' => $categories,
    ]);
}

    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:physical,digital',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ];

        // Conditional validation
        if ($request->type === 'digital') {
            $rules['file'] = 'required|file|mimes:zip,pdf,doc,docx,ppt,pptx,xls,xlsx|max:20480';
        } else {
            $rules['stock'] = 'required|integer|min:0';
            $rules['weight'] = 'required|numeric|min:0';
        }

        // Validate request
        $validated = $request->validate($rules);

        // Handle file upload for digital products
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('products/files', 'public');
        }

        // Create product with authenticated user's ID
        $product = auth()->user()->products()->create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/images', 'public');
                $product->images()->create(['image_path' => $path]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        // // Check if the product belongs to the authenticated user
        // if ($product->user_id !== auth()->id()) {
        //     abort(403, 'Unauthorized action.');
        // }

        $product->load(['category', 'images', 'user']);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::where('user_id', auth()->id())->get();
        $product->load('images');

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:digital,physical',
            'category_id' => 'required|exists:categories,id',
            'file' => 'nullable|file|mimes:zip,pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'stock' => 'required_if:type,physical|nullable|integer|min:0',
            'weight' => 'required_if:type,physical|nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'exists:product_images,id',
            'is_active' => 'boolean',
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'type' => $validated['type'],
            'stock' => $validated['stock'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->hasFile('file') && $validated['type'] === 'digital') {
            // Delete old file if exists
            if ($product->file_path) {
                Storage::disk('public')->delete($product->file_path);
            }
            
            $filePath = $request->file('file')->store('products/files', 'public');
            $product->update(['file_path' => $filePath]);
        }

        // Handle deleted images
        if (!empty($validated['deleted_images'])) {
            $imagesToDelete = ProductImage::whereIn('id', $validated['deleted_images'])
                ->where('product_id', $product->id)
                ->get();

            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // Handle new images
        if ($request->hasFile('images')) {
            $currentMaxOrder = $product->images()->max('order') ?? -1;
            
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'order' => $currentMaxOrder + $index + 1,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete associated files
        if ($product->file_path) {
            Storage::disk('public')->delete($product->file_path);
        }

        // Delete images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}