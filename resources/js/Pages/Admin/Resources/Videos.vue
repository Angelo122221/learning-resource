<script setup>
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    featuredVideos: {
        type: Array,
        default: () => [],
    },
});

const videoForm = useForm({
    youtube_link: '',
    description: '',
});

const videoEditForm = useForm({
    youtube_link: '',
    description: '',
});

const editingVideoId = ref(null);
const showVideoModal = ref(false);

const openVideoModal = () => {
    showVideoModal.value = true;
};

const closeVideoModal = () => {
    showVideoModal.value = false;
    videoForm.reset();
};

const submitVideo = () => {
    videoForm.post('/admin/videos', {
        preserveScroll: true,
        onSuccess: () => closeVideoModal(),
    });
};

const startEditVideo = (video) => {
    editingVideoId.value = video.id;
    videoEditForm.youtube_link = video.youtube_link;
    videoEditForm.description = video.description || '';
};

const cancelEditVideo = () => {
    editingVideoId.value = null;
    videoEditForm.reset();
};

const updateVideo = (videoId) => {
    videoEditForm.patch(`/admin/videos/${videoId}`, {
        preserveScroll: true,
        onSuccess: () => cancelEditVideo(),
    });
};

const deleteVideo = (videoId) => {
    if (!confirm('Delete this featured video?')) return;

    router.delete(`/admin/videos/${videoId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin Featured Videos" />

    <AdminLayout>
        <AppSectionCard title="Featured Videos" subtitle="Create, update, and remove featured portal videos.">
            <div class="mb-4">
                <button type="button" class="action-btn-primary w-full justify-center" @click="openVideoModal">
                    Add Video
                </button>
            </div>

            <div class="custom-scrollbar max-h-96 space-y-3 overflow-y-auto pr-1">
                <AppEmptyState v-if="featuredVideos.length === 0" title="No videos yet" message="Add the first featured video using the button above." />

                <div v-for="video in featuredVideos" :key="video.id" class="panel-muted border p-3">
                    <template v-if="editingVideoId === video.id">
                        <form @submit.prevent="updateVideo(video.id)" class="space-y-3">
                            <input v-model="videoEditForm.youtube_link" type="url" class="field-input" />
                            <textarea v-model="videoEditForm.description" rows="3" class="field-input" />
                            <InputError :message="videoEditForm.errors.youtube_link" />
                            <InputError :message="videoEditForm.errors.description" />
                            <div class="flex gap-2">
                                <button type="submit" class="action-btn-primary flex-1 justify-center">Save</button>
                                <button type="button" class="action-btn-secondary flex-1 justify-center" @click="cancelEditVideo">Cancel</button>
                            </div>
                        </form>
                    </template>

                    <template v-else>
                        <p class="break-all text-sm font-black text-slate-900">{{ video.youtube_link }}</p>
                        <p class="mt-2 text-sm font-medium text-slate-600">{{ video.description || 'No description' }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a :href="video.youtube_link" target="_blank" class="action-btn-secondary">View</a>
                            <button type="button" class="action-btn-secondary" @click="startEditVideo(video)">Edit</button>
                            <button type="button" class="action-btn-danger" @click="deleteVideo(video.id)">Delete</button>
                        </div>
                    </template>
                </div>
            </div>
        </AppSectionCard>

        <Modal :show="showVideoModal" max-width="lg" @close="closeVideoModal">
            <div class="p-5 md:p-6">
                <h3 class="text-lg font-black text-slate-900">Add Featured Video</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Add the YouTube link and supporting description.</p>

                <form @submit.prevent="submitVideo" class="mt-5 space-y-4">
                    <div>
                        <label class="field-label" for="video_link">YouTube URL</label>
                        <input id="video_link" v-model="videoForm.youtube_link" type="url" class="field-input" placeholder="https://youtube.com/watch?v=..." />
                        <InputError :message="videoForm.errors.youtube_link" />
                    </div>

                    <div>
                        <label class="field-label" for="video_description">Description</label>
                        <textarea id="video_description" v-model="videoForm.description" rows="4" class="field-input" placeholder="What should teachers expect from this video?" />
                        <InputError :message="videoForm.errors.description" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" class="action-btn-secondary" @click="closeVideoModal">Cancel</button>
                        <button type="submit" class="action-btn-primary">Save Video</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>
