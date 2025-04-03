<script setup>
import AppLayout from '@/Layouts/PublicAppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: Object,
    products: Array,
});
const formatPrice = (price) => {
  if (!price) return '۰';
  const num = Number(price);
  return isNaN(num) ? '۰' : num.toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head :title="`${user.name}'s Profile`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Profile Header -->
                <div class="flex items-center gap-6 mb-8">
                    <img 
                        :src="user.profile_photo_path || 'https://ui-avatars.com/api/?name='+user.name" 
                        class="w-20 h-20 rounded-full object-cover"
                        :alt="user.name"
                    >
                    <div>
                        <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                        <p class="text-gray-600">@{{ user.username }}</p>
                        <p v-if="user.whatsapp" class="text-gray-600 mt-1">
                            <i class="fab fa-whatsapp mr-1"></i> {{ user.whatsapp }}
                        </p>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="products.length > 0">
                    <h2 class="text-xl font-semibold mb-4">Products</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div v-for="product in products" :key="product.id" class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <Link :href="route('products.show', product.id)">
                                <div class="relative">
                                    <img 
                                        :src="product.images.length > 0 ? product.images[0].image_path : '/placeholder-product.jpg'" 
                                        class="w-full h-48 object-cover"
                                        :alt="product.title"
                                    >
                                    <span v-if="product.type === 'digital'" class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                        Digital
                                    </span>
                                    <span v-else class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded">
                                        Physical
                                    </span>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-lg mb-1">{{ product.title }}</h3>
                                    <p class="text-gray-600 text-sm mb-2 line-clamp-2">{{ product.description }}</p>
                                    <p class="font-bold text-lg">$ {{ formatPrice(product.price) }}</p>
                                    <div v-if="product.type === 'physical'" class="flex items-center mt-2 text-sm text-gray-500">
                                        <span class="mr-3">Stock: {{ product.stock }}</span>
                                        <span>Weight: {{ product.weight }}kg</span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500">This user hasn't listed any products yet.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>