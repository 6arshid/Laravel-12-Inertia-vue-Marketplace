<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show($username)
    {
        // 1. ابتدا کاربر را پیدا کنید
    $user = User::where('username', $username)->firstOrFail();
    
    // 2. محصولات را جداگانه دریافت کنید
    $products = Product::with(['images' => function($q) {
            $q->orderBy('is_main', 'desc')->orderBy('order', 'asc');
        }])
        ->where('user_id', $user->id)
        ->where('is_active', true)
        ->get();
    

    return Inertia::render('Profile/PublicShow', [
        'user' => $user,
        'products' => $products
    ]);
    }
}
