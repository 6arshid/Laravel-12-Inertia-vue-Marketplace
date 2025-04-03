<!-- resources/js/Pages/Products/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    products: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
</script>

<template>
    <AppLayout title="Products">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Products
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Product List</h3>
                        <Link :href="route('products.create')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            Add New Product
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img v-if="product.main_image" :src="'/storage/' + product.main_image.image_path" class="h-10 w-10 rounded-full object-cover" />
                                        <div v-else class="h-10 w-10 rounded-full bg-gray-200"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ product.title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ product.category.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="{'bg-blue-100 text-blue-800': product.type === 'digital', 
                                                       'bg-green-100 text-green-800': product.type === 'physical'}">
                                            {{ product.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatPrice(product.price) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="{'bg-green-100 text-green-800': product.is_active, 
                                                       'bg-red-100 text-red-800': !product.is_active}">
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('products.show', product.id)" class="text-blue-600 hover:text-blue-900 mr-3">View</Link>
                                        <Link :href="route('products.edit', product.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                        <Link :href="route('products.destroy', product.id)" method="delete" as="button" class="text-red-600 hover:text-red-900">Delete</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Showing {{ products.from }} to {{ products.to }} of {{ products.total }} entries
                        </div>
                        <div class="flex space-x-2">
                            <Link v-for="link in products.links" :key="link.label" 
                                  :href="link.url || '#'" 
                                  :class="{'bg-blue-500 text-white': link.active, 
                                           'bg-white text-gray-700': !link.active}" 
                                  class="px-3 py-1 rounded-md text-sm font-medium" 
                                  v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>