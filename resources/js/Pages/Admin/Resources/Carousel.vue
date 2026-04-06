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
const showPreviewModal = ref(false);
const previewCarousel = ref(null);
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

const openPreviewModal = (carousel) => {
    previewCarousel.value = carousel;
    showPreviewModal.value = true;
};

const closePreviewModal = () => {
    showPreviewModal.value = false;
    previewCarousel.value = null;
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

                <div v-for="(carousel, index) in carouselImages" :key="carousel.id" class="panel-muted border p-3">
                    <template v-if="editingCarouselId === carousel.id">
                        <form @submit.prevent="updateCarousel(carousel.id)" class="space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
                                <div class="flex min-w-0 items-center gap-3">
                                    <img
                                        :src="mediaUrl(carousel.image_path)"
                                        class="h-12 w-16 rounded-lg object-cover"
                                        alt="Carousel image thumbnail"
                                    />
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Current image</p>
                                        <p class="truncate text-sm font-semibold text-slate-600">{{ carousel.title }}</p>
                                    </div>
                                </div>
                                <button type="button" class="action-btn-secondary" @click="openPreviewModal(carousel)">Preview</button>
                            </div>
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
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="shrink-0 rounded-lg border border-slate-200 bg-white p-1">
                                    <img
                                        :src="mediaUrl(carousel.image_path)"
                                        class="h-12 w-16 rounded-md object-cover"
                                        alt="Carousel image thumbnail"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900">{{ carousel.title }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                                        Slide #{{ carouselImages.length - index }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 sm:justify-end">
                                <button type="button" class="action-btn-secondary" @click="openPreviewModal(carousel)">Preview</button>
                                <button type="button" class="action-btn-secondary" @click="startEditCarousel(carousel)">Edit</button>
                                <button type="button" class="action-btn-danger" @click="deleteCarousel(carousel.id)">Delete</button>
                            </div>
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

        <Modal :show="showPreviewModal" max-width="2xl" position="center" @close="closePreviewModal">
            <div v-if="previewCarousel" class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-black text-slate-900">{{ previewCarousel.title }}</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Carousel slide preview</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                        @click="closePreviewModal"
                    >
                        Close
                    </button>
                </div>

                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                    <img
                        :src="mediaUrl(previewCarousel.image_path)"
                        :alt="previewCarousel.title"
                        class="max-h-[70vh] w-full object-contain"
                    />
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
