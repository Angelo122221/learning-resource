<script setup>
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    carouselImages: {
        type: Array,
        default: () => [],
    },
});

const carouselForm = useForm({
    title: '',
    image: null,
});

const carouselEditForm = useForm({
    title: '',
    image: null,
});

const editingCarouselId = ref(null);
const showCarouselModal = ref(false);
const carouselFileInput = ref(null);
const mediaUrl = (path) => `/media/${path}`;

const openCarouselModal = () => {
    showCarouselModal.value = true;
};

const closeCarouselModal = () => {
    showCarouselModal.value = false;
    carouselForm.reset();
    if (carouselFileInput.value) carouselFileInput.value.value = '';
};

const submitCarousel = () => {
    carouselForm.post('/admin/carousel', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => closeCarouselModal(),
    });
};

const startEditCarousel = (carousel) => {
    editingCarouselId.value = carousel.id;
    carouselEditForm.title = carousel.title;
    carouselEditForm.image = null;
};

const cancelEditCarousel = () => {
    editingCarouselId.value = null;
    carouselEditForm.reset();
};

const updateCarousel = (carouselId) => {
    carouselEditForm
        .transform((data) => ({
            ...data,
            _method: 'patch',
        }))
        .post(`/admin/carousel/${carouselId}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => cancelEditCarousel(),
        });
};

const deleteCarousel = (carouselId) => {
    if (!confirm('Delete this carousel image?')) return;

    router.post(`/admin/carousel/${carouselId}`, {
        _method: 'delete',
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin Carousel" />

    <AdminLayout>
        <AppSectionCard title="Homepage Carousel" subtitle="Create, update, and remove carousel slides.">
            <div class="mb-4">
                <button type="button" class="action-btn-primary w-full justify-center" @click="openCarouselModal">
                    Add Slide
                </button>
            </div>

            <div class="custom-scrollbar max-h-96 space-y-3 overflow-y-auto pr-1">
                <AppEmptyState v-if="carouselImages.length === 0" title="No slides yet" message="Add the first slide using the button above." />

                <div v-for="carousel in carouselImages" :key="carousel.id" class="panel-muted overflow-hidden border p-3">
                    <img :src="mediaUrl(carousel.image_path)" class="mb-3 h-40 w-full rounded-xl object-cover" alt="Carousel image" />

                    <template v-if="editingCarouselId === carousel.id">
                        <form @submit.prevent="updateCarousel(carousel.id)" class="space-y-3">
                            <input v-model="carouselEditForm.title" type="text" class="field-input" />
                            <input
                                type="file"
                                accept="image/*"
                                class="field-input"
                                @change="carouselEditForm.image = $event.target.files[0]"
                            />
                            <InputError :message="carouselEditForm.errors.title" />
                            <InputError :message="carouselEditForm.errors.image" />
                            <div class="flex gap-2">
                                <button type="submit" class="action-btn-primary flex-1 justify-center">Save</button>
                                <button type="button" class="action-btn-secondary flex-1 justify-center" @click="cancelEditCarousel">Cancel</button>
                            </div>
                        </form>
                    </template>

                    <template v-else>
                        <p class="text-sm font-black text-slate-900">{{ carousel.title }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a :href="mediaUrl(carousel.image_path)" target="_blank" class="action-btn-secondary">View</a>
                            <button type="button" class="action-btn-secondary" @click="startEditCarousel(carousel)">Edit</button>
                            <button type="button" class="action-btn-danger" @click="deleteCarousel(carousel.id)">Delete</button>
                        </div>
                    </template>
                </div>
            </div>
        </AppSectionCard>

        <Modal :show="showCarouselModal" max-width="lg" @close="closeCarouselModal">
            <div class="p-5 md:p-6">
                <h3 class="text-lg font-black text-slate-900">Add Carousel Slide</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Upload an image and title for the homepage carousel.</p>

                <form @submit.prevent="submitCarousel" class="mt-5 space-y-4">
                    <div>
                        <label class="field-label" for="carousel_title">Slide Title</label>
                        <input id="carousel_title" v-model="carouselForm.title" type="text" class="field-input" placeholder="New curriculum launch" />
                        <InputError :message="carouselForm.errors.title" />
                    </div>

                    <div>
                        <label class="field-label" for="carousel_image">Slide Image</label>
                        <input
                            id="carousel_image"
                            ref="carouselFileInput"
                            type="file"
                            accept="image/*"
                            class="field-input"
                            @change="carouselForm.image = $event.target.files[0]"
                        />
                        <InputError :message="carouselForm.errors.image" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" class="action-btn-secondary" @click="closeCarouselModal">Cancel</button>
                        <button type="submit" class="action-btn-primary">Save Slide</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>
