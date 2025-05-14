<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
public function index()
{
    $cart = auth()->user()->cart()
        ->with([
            'items.product.user',
            'items.product.mainImage',
            'seller'
        ])
        ->first();

    return Inertia::render('Checkout', [
        'cart' => $cart ? [
            'id' => $cart->id,
            'seller_id' => $cart->seller_id,
            'seller_name' => $cart->seller->name,
            'seller_whatsapp' => $cart->seller->whatsapp, 
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'id' => $item->product->id,
                        'title' => $item->product->title,
                        'price' => $item->product->price,
                        'main_image' => $item->product->mainImage ? [
                            'image_path' => $item->product->mainImage->image_path
                        ] : null,
                        'user' => [
                            'name' => $item->product->user->name
                        ]
                    ]
                ];
            }),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ] : null,
    ]);
}


    public function store(Product $product)
    {
        $user = Auth::user();
    
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to add items to cart.');
        }
        
        // Check if user already has a cart with a different seller
        $existingCart = $user->cart()->first();
      
        
        // Create new cart if doesn't exist
        $cart = $existingCart ?? $user->cart()->create([
            'seller_id' => $product->user_id
        ]);
        
        // Add or update item
        CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id],
            ['quantity' => \DB::raw('quantity + 1')]
        );
        
        return back()->with('success', 'Product added to cart!');
    }

    public function update(CartItem $item, Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        if ($item->cart->user_id !== Auth::id()) {
            abort(403);
        }
        
        $item->update(['quantity' => $request->quantity]);
        
        return back()->with('success', 'Cart updated!');
    }

    public function destroyItem(CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) {
            abort(403);
        }
        
        $item->delete();
        
        // Delete cart if empty
        if ($item->cart->items()->count() === 0) {
            $item->cart()->delete();
        }
        
        return back()->with('success', 'Item removed from cart.');
    }

    public function destroy()
    {
        $cart = Auth::user()->cart;
        
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }
        
        return back()->with('success', 'Cart emptied.');
    }
}