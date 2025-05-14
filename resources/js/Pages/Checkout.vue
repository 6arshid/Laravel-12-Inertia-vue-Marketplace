<!-- resources/js/Pages/Checkout.vue -->
<script setup>
import AppLayout from '@/Layouts/PublicAppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    cart: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const total = computed(() => {
    if (!props.cart?.items) return 0;
    return props.cart.items.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
});

const updateQuantity = (item, newQuantity) => {
    if (newQuantity < 1) return;
    
    router.patch(route('cart.update', { item: item.id }), {
        quantity: newQuantity
    }, {
        preserveScroll: true,
        onError: (errors) => {
            alert(errors.message || 'Failed to update quantity');
        }
    });
};

const removeItem = (item) => {
    if (!confirm('Are you sure you want to remove this item?')) return;
    
    router.delete(route('cart.destroyItem', { item: item.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success if needed
        }
    });
};

const emptyCart = () => {
    if (!confirm('Are you sure you want to empty your cart?')) return;
    
    router.delete(route('cart.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success if needed
        }
    });
};

const showWhatsappModal = ref(false);

onMounted(() => {
    if (!props.cart?.seller_whatsapp) {
        showWhatsappModal.value = true;
    }
});

const sendOrderToWhatsApp = () => {
    if (!props.cart?.items || !props.cart?.seller_whatsapp) {
        showWhatsappModal.value = true;
        return;
    }

    let message = `🛒 New Order Request:\n\n`;

    props.cart.items.forEach(item => {
        message += `📦 ${item.product.title} x${item.quantity} = ${formatPrice(item.product.price * item.quantity)}\n`;
    });

    message += `\n💰 Total: ${formatPrice(total.value)}`;

    const phone = props.cart.seller_whatsapp.replace(/^0/, '+98'); // تبدیل 0 به +98
    const encodedMessage = encodeURIComponent(message);

    window.open(`https://wa.me/${phone}?text=${encodedMessage}`, '_blank');
};
</script>

<template>
    <AppLayout title="Checkout">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Shopping Cart
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-6">Your Cart</h3>
                        
                        <div v-if="cart" class="space-y-6">
                            <div class="border-b pb-4">
                                <h4 class="text-lg font-semibold">
                                    Seller: {{ cart.seller_name }}
                                </h4>
                            </div>
                            
                            <div v-for="item in cart.items" :key="item.id" class="flex justify-between items-center py-4 border-b">
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-20 rounded-md overflow-hidden">
                                        <img v-if="item.product.main_image" 
                                             :src="'/storage/' + item.product.main_image.image_path" 
                                             class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 text-xs">No Image</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ item.product.title }}</h4>
                                        <p class="text-gray-600">{{ formatPrice(item.product.price) }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center border rounded-md">
                                        <button @click="updateQuantity(item, item.quantity - 1)" 
                                                class="px-3 py-1 hover:bg-gray-100"
                                                :disabled="item.quantity <= 1">
                                            -
                                        </button>
                                        <span class="px-3">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(item, item.quantity + 1)" 
                                                class="px-3 py-1 hover:bg-gray-100">
                                            +
                                        </button>
                                    </div>
                                    <button @click="removeItem(item)" 
                                            class="text-red-500 hover:text-red-700">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center pt-6">
                                <button @click="emptyCart" 
                                        class="text-red-500 hover:text-red-700">
                                    Empty Cart
                                </button>
                                <div class="text-right">
                                    <p class="text-lg font-semibold">
                                        Total: {{ formatPrice(total) }}
                                    </p>
                                    <button @click="sendOrderToWhatsApp"
                                            class="mt-4 inline-block bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700">
                                        Send Order via WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 mb-4">Your cart is empty</p>
                            <Link :href="route('products.index')" 
                                  class="inline-block bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                                Continue Shopping
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Not Available Modal -->
        <div v-if="showWhatsappModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md text-center">
                <h2 class="text-lg font-semibold mb-4">Seller WhatsApp Not Available</h2>
                <p class="mb-4">The seller has not provided their WhatsApp number yet. Please try again later or contact support.</p>
                <button @click="showWhatsappModal = false"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    OK
                </button>
            </div>
        </div>
    </AppLayout>
</template>
