<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
        default: () => []
    }
});

const form = useForm({
    title: '',
    description: '',
    price: '',
    type: 'physical',
    category_id: props.categories.length > 0 ? props.categories[0].id : null,
    file: null,
    stock: null,
    weight: null,
    images: [],
    is_active: true,
});

const previewImages = ref([]);
const previewFile = ref(null);

// Ensure category is selected when component mounts
onMounted(() => {
    if (props.categories.length > 0 && !form.category_id) {
        form.category_id = props.categories[0].id;
    }
});

const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    form.images = [...form.images, ...files];
    
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    form.images.splice(index, 1);
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

const submit = () => {
    if (props.categories.length === 0) {
        alert('You need to create at least one category first');
        return;
    }

    const formData = new FormData();
    
    Object.entries({
        title: form.title,
        description: form.description,
        price: form.price,
        type: form.type,
        category_id: form.category_id,
        is_active: form.is_active ? 1 : 0,
        stock: isPhysical.value ? form.stock || 0 : null,
        weight: isPhysical.value ? form.weight || 0 : null,
    }).forEach(([key, value]) => {
        if (value !== null) {
            formData.append(key, value);
        }
    });
    
    if (isDigital.value && form.file) {
        formData.append('file', form.file);
    }
    
    form.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
    });

    form.post(route('products.store'), {
        data: formData,
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            previewImages.value = [];
            previewFile.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Create Product">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="categories.length === 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                    <div class="p-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No Categories Found</h3>
                        <p class="mt-2 text-sm text-gray-500">You need to create at least one category before you can add products.</p>
                        <div class="mt-6">
                            <Link :href="route('categories.index')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Create New Category
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="col-span-2">
                                <h3 class="text-lg font-medium mb-4">Basic Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                        <input 
                                            type="text" 
                                            id="title" 
                                            v-model="form.title" 
                                            required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                        />
                                        <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.title }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                                        <textarea 
                                            id="description" 
                                            v-model="form.description" 
                                            required 
                                            rows="3" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        ></textarea>
                                        <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.description }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                                        <div class="flex items-center space-x-2">
                                            <select 
                                                id="category_id" 
                                                v-model="form.category_id" 
                                                required 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option 
                                                    v-for="category in categories" 
                                                    :key="category.id" 
                                                    :value="category.id"
                                                >
                                                    {{ category.name }}
                                                </option>
                                            </select>
                                            <Link :href="route('categories.index')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                + New
                                            </Link>
                                        </div>
                                        <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.category_id }}
                                        </p>
                                    </div>

                                    <!-- Rest of your form fields... -->
                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700">Price *</label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0" 
                                            id="price" 
                                            v-model="form.price" 
                                            required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                        />
                                        <p v-if="form.errors.price" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.price }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type *</label>
                                        <div class="mt-1 space-x-4">
                                            <label class="inline-flex items-center">
                                                <input 
                                                    type="radio" 
                                                    v-model="form.type" 
                                                    value="physical" 
                                                    required 
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" 
                                                />
                                                <span class="ml-2 text-sm text-gray-700">Physical Product</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input 
                                                    type="radio" 
                                                    v-model="form.type" 
                                                    value="digital" 
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" 
                                                />
                                                <span class="ml-2 text-sm text-gray-700">Digital Product</span>
                                            </label>
                                        </div>
                                        <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.type }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="inline-flex items-center">
                                            <input 
                                                type="checkbox" 
                                                v-model="form.is_active" 
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                            />
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
                                        <input 
                                            type="number" 
                                            min="0" 
                                            id="stock" 
                                            v-model="form.stock" 
                                            required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                        />
                                        <p v-if="form.errors.stock" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.stock }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg) *</label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0" 
                                            id="weight" 
                                            v-model="form.weight" 
                                            required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                        />
                                        <p v-if="form.errors.weight" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.weight }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Digital Product Fields -->
                            <div v-if="isDigital" class="col-span-2 md:col-span-1">
                                <h3 class="text-lg font-medium mb-4">Digital Product Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="file" class="block text-sm font-medium text-gray-700">File *</label>
                                        <input 
                                            type="file" 
                                            id="file" 
                                            @change="handleFileChange" 
                                            :required="isDigital" 
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                                        />
                                        <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.file }}
                                        </p>
                                        <div v-if="previewFile" class="mt-2 p-2 border rounded-md">
                                            <p class="text-sm text-gray-600">File ready for upload: {{ form.file.name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Images -->
                            <div class="col-span-2">
                                <h3 class="text-lg font-medium mb-4">Product Images</h3>
                                <div>
                                    <label for="images" class="block text-sm font-medium text-gray-700">Upload Images</label>
                                    <input 
                                        type="file" 
                                        id="images" 
                                        @change="handleImageChange" 
                                        multiple 
                                        accept="image/*" 
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                                    />
                                    <p v-if="form.errors.images" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.images }}
                                    </p>
                                    
                                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(image, index) in previewImages" :key="index" class="relative group">
                                            <img :src="image" class="h-32 w-full object-cover rounded-md" />
                                            <button 
                                                type="button" 
                                                @click="removeImage(index)" 
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
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
                            <button 
                                type="button" 
                                @click="$inertia.visit(route('products.index'))" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Product</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>