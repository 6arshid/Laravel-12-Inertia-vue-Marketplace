<!-- resources/js/Pages/Products/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/PublicAppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    product: Object,
    auth: Object,
    cart: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const showPopup = ref(false);
const popupMessage = ref('');
const isAdding = ref(false);

// Check if cart has items from a different seller
const hasDifferentSeller = computed(() => {
    if (!props.cart?.seller_id || !props.product?.user_id) return false;
    return props.cart.seller_id !== props.product.user_id;
});

const addToCart = async () => {
    if (hasDifferentSeller.value) {
        showPopup.value = true;
        popupMessage.value = 'You can only have products from one seller in your cart. Please checkout or empty your current cart first.';
        return;
    }

    isAdding.value = true;
    
    try {
        await router.post(route('cart.store', { product: props.product.id }), {}, {
            onSuccess: () => {
                showPopup.value = true;
                popupMessage.value = 'Product added to cart! Would you like to proceed to checkout or continue shopping?';
            },
            onError: (errors) => {
                showPopup.value = true;
                popupMessage.value = errors.message || 'Failed to add product to cart.';
            }
        });
    } finally {
        isAdding.value = false;
    }
};

const proceedToCheckout = () => {
    router.visit(route('cart.index'));
    showPopup.value = false;
};

const continueShopping = () => {
    showPopup.value = false;
};
</script>

<template>
    <AppLayout title="Product Details">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Product Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">{{ product.title }}</h3>
                            <div class="flex space-x-2">
                                <Link :href="route('products.edit', product.id)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                    Edit
                                </Link>
                                <Link :href="route('products.index')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md">
                                    Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Product Images -->
                            <div>
                                <div class="mb-4">
                                    <div class="relative h-80 w-full rounded-lg overflow-hidden">
                                        <img v-if="product.main_image" :src="'/storage/' + product.main_image.image_path" class="h-full w-full object-cover" />
                                        <div v-else class="h-full w-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500">No Image</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="image in product.images" :key="image.id" class="h-20 cursor-pointer border rounded-md overflow-hidden">
                                        <img :src="'/storage/' + image.image_path" class="h-full w-full object-cover" />
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details -->
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-semibold">Description</h4>
                                        <p class="text-gray-600">{{ product.description }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="text-lg font-semibold">Price</h4>
                                            <p class="text-gray-600">{{ formatPrice(product.price) }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold">Category</h4>
                                            <p class="text-gray-600">{{ product.category.name }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="text-lg font-semibold">Type</h4>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                  :class="{'bg-blue-100 text-blue-800': product.type === 'digital', 
                                                           'bg-green-100 text-green-800': product.type === 'physical'}">
                                                {{ product.type }}
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold">Status</h4>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                  :class="{'bg-green-100 text-green-800': product.is_active, 
                                                           'bg-red-100 text-red-800': !product.is_active}">
                                                {{ product.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Digital Product Details -->
                                    <div v-if="product.type === 'digital'">
                                        <h4 class="text-lg font-semibold">Digital Product</h4>
                                        <div class="mt-2 p-3 bg-gray-50 rounded-md">
                                            <p class="text-sm text-gray-600">File: 
                                                <a v-if="product.file_path" :href="'/storage/' + product.file_path" class="text-indigo-600 hover:text-indigo-800" download>
                                                    {{ product.file_path.split('/').pop() }}
                                                </a>
                                                <span v-else class="text-gray-500">No file uploaded</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Physical Product Details -->
                                    <div v-if="product.type === 'physical'">
                                        <h4 class="text-lg font-semibold">Physical Product</h4>
                                        <div class="mt-2 grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Stock</p>
                                                <p class="font-medium">{{ product.stock }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Weight</p>
                                                <p class="font-medium">{{ product.weight }} kg</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add to Cart Button -->
                                    <div class="pt-4">
                                        <button 
                                            @click="addToCart" 
                                            :disabled="isAdding || hasDifferentSeller"
                                            class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 disabled:bg-indigo-300"
                                        >
                                            <span v-if="isAdding">Adding...</span>
                                            <span v-else-if="hasDifferentSeller">Different Seller - Checkout First</span>
                                            <span v-else>Add to Cart</span>
                                        </button>
                                    </div>

                                    <div>
                                        <h4 class="text-lg font-semibold">Created By</h4>
                                        <p class="text-gray-600">{{ product.user.name }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-lg font-semibold">Created At</h4>
                                        <p class="text-gray-600">{{ new Date(product.created_at).toLocaleString() }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-lg font-semibold">Last Updated</h4>
                                        <p class="text-gray-600">{{ new Date(product.updated_at).toLocaleString() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add to Cart Popup -->
        <div v-if="showPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">{{ popupMessage }}</h3>
                <div class="flex justify-end space-x-4" v-if="popupMessage.includes('checkout')">
                    <button @click="continueShopping" class="px-4 py-2 bg-gray-200 rounded-md">
                        Continue Shopping
                    </button>
                    <button @click="proceedToCheckout" class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                        Proceed to Checkout
                    </button>
                </div>
                <div class="flex justify-end" v-else>
                    <button @click="showPopup = false" class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>