<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
        default: () => [],
        validator: (value) => {
            return Array.isArray(value) && 
                   value.every(item => item && typeof item === 'object' && 'id' in item);
        }
    },
});

// Initialize forms with proper validation
const form = useForm({
    name: '',
    description: '',
});

const editingCategory = ref(null);
const editForm = useForm({
    name: '',
    description: '',
});

// Safe categories access with computed property
const safeCategories = computed(() => {
    return Array.isArray(props.categories) ? props.categories : [];
});

// Form submission with error handling
const submit = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.error('Category creation failed:', errors);
        }
    });
};

// Safe category editing
const editCategory = (category) => {
    try {
        if (!category?.id) throw new Error('Invalid category');
        editingCategory.value = category.id;
        editForm.name = category.name || '';
        editForm.description = category.description || '';
    } catch (error) {
        console.error('Error editing category:', error);
        editingCategory.value = null;
    }
};

// Safe category update
const updateCategory = (categoryId) => {
    if (!categoryId) return;
    
    editForm.put(route('categories.update', { category: categoryId }), {
        preserveScroll: true,
        onSuccess: () => {
            editingCategory.value = null;
            editForm.reset();
        },
        onError: (errors) => {
            console.error('Category update failed:', errors);
        }
    });
};

const cancelEdit = () => {
    editingCategory.value = null;
    editForm.reset();
};

// Safe category deletion
const deleteCategory = (categoryId) => {
    if (!categoryId) return;
    
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('categories.destroy', { category: categoryId }), {
            preserveScroll: true,
            onSuccess: () => {
                editingCategory.value = null;
            },
            onError: (error) => {
                console.error('Category deletion failed:', error);
            }
        });
    }
};
</script>

<template>
    <AppLayout title="Categories">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Categories Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Add Category Form -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Category</h3>
                                <form @submit.prevent="submit">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">
                                                Name *
                                            </label>
                                            <input 
                                                v-model="form.name" 
                                                type="text" 
                                                id="name"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                            />
                                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                                {{ form.errors.name }}
                                            </p>
                                        </div>

                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700">
                                                Description
                                            </label>
                                            <textarea 
                                                v-model="form.description" 
                                                id="description" 
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            ></textarea>
                                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                                {{ form.errors.description }}
                                            </p>
                                        </div>

                                        <div class="flex justify-end">
                                            <button 
                                                type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                                :disabled="form.processing"
                                            >
                                                <span v-if="form.processing">Processing...</span>
                                                <span v-else>Add Category</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Categories List -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Categories List</h3>
                                
                                <div v-if="safeCategories.length === 0" class="text-center py-8 bg-gray-50 rounded-lg">
                                    <p class="text-gray-500">No categories found. Create your first category.</p>
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Name
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Description
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="category in safeCategories" :key="category.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div v-if="editingCategory !== category.id" class="text-sm font-medium text-gray-900">
                                                        {{ category.name }}
                                                    </div>
                                                    <input 
                                                        v-else 
                                                        v-model="editForm.name" 
                                                        type="text"
                                                        required
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                                    />
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div v-if="editingCategory !== category.id" class="text-sm text-gray-500">
                                                        {{ category.description || 'No description' }}
                                                    </div>
                                                    <textarea 
                                                        v-else 
                                                        v-model="editForm.description" 
                                                        rows="2"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    ></textarea>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div v-if="editingCategory !== category.id" class="flex space-x-2">
                                                        <button 
                                                            @click="editCategory(category)" 
                                                            class="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Edit
                                                        </button>
                                                        <button 
                                                            @click="deleteCategory(category.id)"
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            Delete
                                                        </button>
                                                    </div>
                                                    <div v-else class="flex space-x-2">
                                                        <button 
                                                            @click="updateCategory(category.id)" 
                                                            class="text-green-600 hover:text-green-900"
                                                        >
                                                            Save
                                                        </button>
                                                        <button 
                                                            @click="cancelEdit" 
                                                            class="text-gray-600 hover:text-gray-900"
                                                        >
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>