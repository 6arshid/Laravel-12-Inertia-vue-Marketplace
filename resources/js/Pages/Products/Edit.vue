<!-- resources/js/Pages/Products/Edit.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    title: props.product.title,
    description: props.product.description,
    price: props.product.price,
    type: props.product.type,
    category_id: props.product.category_id,
    file: null,
    stock: props.product.stock,
    weight: props.product.weight,
    images: [],
    deleted_images: [],
    is_active: props.product.is_active,
});

const previewImages = ref(props.product.images.map(img => '/storage/' + img.image_path));
const previewFile = ref(props.product.file_path ? '/storage/' + props.product.file_path : null);

const handleImageChange = (e) => {
    const files = e.target.files;
    form.images = [...form.images, ...files];
    
    for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push(e.target.result);
        };
        reader.readAsDataURL(files[i]);
    }
};

const removeImage = (index) => {
    // If it's an existing image, add to deleted_images
    if (index < props.product.images.length) {
        form.deleted_images.push(props.product.images[index].id);
    } else {
        // If it's a new image, remove from the images array
        const newIndex = index - props.product.images.length;
        form.images.splice(newIndex, 1);
    }
    
    previewImages.value.splice(index, 1);
};

const handleFileChange = (e) => {
    form.file = e.target.files[0];
    const reader = new FileReader();
    reader.onload = (e) => {
        previewFile.value = e.target.result;
    };
    reader.readAsDataURL(form.file);
};

const isPhysical = computed(() => form.type === 'physical');
const isDigital = computed(() => form.type === 'digital');
</script>

<template>
    <AppLayout title="Edit Product">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="form.put(route('products.update', product.id))">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="col-span-2">
                                <h3 class="text-lg font-medium mb-4">Basic Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                        <input type="text" id="title" v-model="form.title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                                        <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                        <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                                    </div>

                                    <div>
                                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                                        <select id="category_id" v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                        </select>
                                        <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">{{ form.errors.category_id }}</p>
                                    </div>

                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700">Price *</label>
                                        <input type="number" step="0.01" id="price" v-model="form.price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        <p v-if="form.errors.price" class="mt-2 text-sm text-red-600">{{ form.errors.price }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type *</label>
                                        <div class="mt-1 space-x-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.type" value="physical" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" />
                                                <span class="ml-2 text-sm text-gray-700">Physical Product</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.type" value="digital" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" />
                                                <span class="ml-2 text-sm text-gray-700">Digital Product</span>
                                            </label>
                                        </div>
                                        <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">{{ form.errors.type }}</p>
                                    </div>

                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" v-model="form.is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Physical Product Fields -->
                            <div v-if="isPhysical" class="col-span-2 md:col-span-1">
                                <h3 class="text-lg font-medium mb-4">Physical Product Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="stock" class="block text-sm font-medium text-gray-700">Stock *</label>
                                        <input type="number" id="stock" v-model="form.stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        <p v-if="form.errors.stock" class="mt-2 text-sm text-red-600">{{ form.errors.stock }}</p>
                                    </div>

                                    <div>
                                        <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg) *</label>
                                        <input type="number" step="0.01" id="weight" v-model="form.weight" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        <p v-if="form.errors.weight" class="mt-2 text-sm text-red-600">{{ form.errors.weight }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Digital Product Fields -->
                            <div v-if="isDigital" class="col-span-2 md:col-span-1">
                                <h3 class="text-lg font-medium mb-4">Digital Product Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="file" class="block text-sm font-medium text-gray-700">File</label>
                                        <input type="file" id="file" @change="handleFileChange" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                        <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
                                        <div v-if="previewFile" class="mt-2 p-2 border rounded-md">
                                            <p class="text-sm text-gray-600">Current file: {{ product.file_path.split('/').pop() }}</p>
                                            <p v-if="form.file" class="text-sm text-gray-600 mt-1">New file ready for upload: {{ form.file.name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Images -->
                            <div class="col-span-2">
                                <h3 class="text-lg font-medium mb-4">Product Images</h3>
                                <div>
                                    <label for="images" class="block text-sm font-medium text-gray-700">Upload Additional Images</label>
                                    <input type="file" id="images" @change="handleImageChange" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    <p v-if="form.errors.images" class="mt-2 text-sm text-red-600">{{ form.errors.images }}</p>
                                    
                                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(image, index) in previewImages" :key="index" class="relative group">
                                            <img :src="image" class="h-32 w-full object-cover rounded-md" />
                                            <button type="button" @click="removeImage(index)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="$inertia.visit(route('products.index'))" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Product</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>