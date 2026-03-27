<script setup>
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppPageHeader from '@/Components/AppPageHeader.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import AppStatCard from '@/Components/AppStatCard.vue';
import InputError from '@/Components/InputError.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FolderItem from './FolderItem.vue';

const props = defineProps({
    folders: Array,
    allFolders: Array,
    stats: Object,
    carouselImages: Array,
    featuredVideos: Array,
    uploadLimits: Object,
});

const folderForm = useForm({
    name: '',
    parent_id: null,
    description: '',
});

const fileForm = useForm({
    title: '',
    folder_id: '',
    file: null,
    preview_image: null,
});

const carouselForm = useForm({
    title: '',
    image: null,
});

const carouselEditForm = useForm({
    title: '',
    image: null,
});

const videoForm = useForm({
    youtube_link: '',
    description: '',
});

const videoEditForm = useForm({
    youtube_link: '',
    description: '',
});

const editingCarouselId = ref(null);
const editingVideoId = ref(null);
const uploadFileInput = ref(null);
const previewImageInput = ref(null);

const FILE_LIMIT_FALLBACK_BYTES = 10 * 1024 * 1024 * 1024;
const PREVIEW_LIMIT_FALLBACK_BYTES = 5 * 1024 * 1024;

const toReadableSize = (bytes) => {
    if (bytes >= 1024 * 1024 * 1024) {
        return `${(bytes / (1024 * 1024 * 1024)).toFixed(1)} GB`;
    }

    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    if (bytes >= 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${bytes} B`;
};

const maxBytesByField = (field) => {
    if (field === 'preview_image') {
        return props.uploadLimits?.max_preview_bytes ?? PREVIEW_LIMIT_FALLBACK_BYTES;
    }

    return props.uploadLimits?.max_file_bytes ?? FILE_LIMIT_FALLBACK_BYTES;
};

const maxSizeLabelByField = (field) => {
    if (field === 'preview_image') {
        return props.uploadLimits?.max_preview_label ?? toReadableSize(maxBytesByField(field));
    }

    return props.uploadLimits?.max_file_label ?? toReadableSize(maxBytesByField(field));
};

const fileMaxSizeLabel = computed(() => maxSizeLabelByField('file'));
const previewMaxSizeLabel = computed(() => maxSizeLabelByField('preview_image'));

const setSingleFile = (field, event) => {
    const files = Array.from(event.target?.files || []);

    if (files.length > 1) {
        fileForm[field] = null;
        fileForm.setError(field, 'Please select only one file.');
        event.target.value = '';
        return;
    }

    const selectedFile = files[0] || null;
    if (selectedFile && selectedFile.size > maxBytesByField(field)) {
        fileForm[field] = null;
        fileForm.setError(field, `File exceeds the limit (${maxSizeLabelByField(field)} max).`);
        event.target.value = '';
        return;
    }

    fileForm.clearErrors(field);
    fileForm[field] = selectedFile;
};

const selectFolder = (id) => {
    folderForm.parent_id = id;
    fileForm.folder_id = id;
};

const submitFolder = () => {
    folderForm.post('/admin/folders', {
        onSuccess: () => folderForm.reset(),
        preserveScroll: true,
    });
};

const submitFile = () => {
    fileForm.post('/admin/files', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            fileForm.reset();
            if (uploadFileInput.value) uploadFileInput.value.value = '';
            if (previewImageInput.value) previewImageInput.value.value = '';
        },
    });
};

const submitCarousel = () => {
    carouselForm.post('/admin/carousel', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            carouselForm.reset();
        },
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

    router.delete(`/admin/carousel/${carouselId}`, {
        preserveScroll: true,
    });
};

const submitVideo = () => {
    videoForm.post('/admin/videos', {
        preserveScroll: true,
        onSuccess: () => videoForm.reset(),
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

const buildResourceActionPath = (type, id, action = 'delete') => {
    if (!id && id !== 0) {
        return null;
    }

    const baseMap = {
        folder: `/admin/folders/${id}`,
        file: `/admin/files/${id}`,
    };

    const basePath = baseMap[type];
    if (!basePath) {
        return null;
    }

    if (action === 'toggle-lock') {
        return `${basePath}/toggle-lock`;
    }

    return basePath;
};

const deleteItem = (type, id) => {
    if (!confirm(`Permanently delete this ${type}?`)) return;

    const path = buildResourceActionPath(type, id);
    if (!path) {
        console.error('Invalid delete action target:', { type, id });
        return;
    }

    router.delete(path, { preserveScroll: true });
};

const toggleLock = (type, id) => {
    const path = buildResourceActionPath(type, id, 'toggle-lock');
    if (!path) {
        console.error('Invalid lock action target:', { type, id });
        return;
    }

    router.patch(path, {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Admin Resources" />

    <AdminLayout>
        <AppPageHeader
            badge="LR"
            title="Learning"
            accent="Resources"
            subtitle="Manage folders, uploads, homepage highlights, and the teacher-facing resource library."
        >
            <template #stats>
                <div class="grid w-full gap-3 md:grid-cols-3 xl:w-auto">
                    <AppStatCard label="Folders" :value="stats.total_folders" tone="slate" />
                    <AppStatCard label="Files" :value="stats.total_files" tone="blue" />
                    <AppStatCard label="Teachers" :value="stats.total_teachers" tone="emerald" />
                </div>
            </template>
        </AppPageHeader>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 xl:items-start">
            <div class="xl:col-span-5">
                <AppSectionCard title="Visual Explorer" subtitle="Browse the live resource structure, then lock or remove items as needed.">
                    <div class="custom-scrollbar max-h-[72vh] overflow-y-auto pr-2">
                        <AppEmptyState
                            v-if="folders.length === 0"
                            title="No folders yet"
                            message="Create a root folder to start organizing materials for teachers."
                        />

                        <div v-else class="space-y-3">
                            <FolderItem
                                v-for="folder in folders"
                                :key="folder.id"
                                :folder="folder"
                                @delete="deleteItem"
                                @lock="toggleLock"
                            />
                        </div>
                    </div>
                </AppSectionCard>
            </div>

            <div class="space-y-6 xl:col-span-7">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <AppSectionCard title="Create Folder" subtitle="Add a root or nested directory before uploading files.">
                        <form @submit.prevent="submitFolder" class="space-y-4">
                            <div>
                                <label class="field-label" for="folder_name">Folder Name</label>
                                <input id="folder_name" v-model="folderForm.name" type="text" class="field-input" placeholder="Quarter 1 Materials" />
                                <InputError :message="folderForm.errors.name" />
                            </div>

                            <div>
                                <label class="field-label" for="parent_folder">Parent Folder</label>
                                <select id="parent_folder" v-model="folderForm.parent_id" class="field-input">
                                    <option :value="null">Root Directory</option>
                                    <option v-for="f in allFolders" :key="f.id" :value="f.id">{{ f.full_path }}</option>
                                </select>
                                <InputError :message="folderForm.errors.parent_id" />
                            </div>

                            <div>
                                <label class="field-label" for="folder_description">Description</label>
                                <textarea id="folder_description" v-model="folderForm.description" rows="3" class="field-input" placeholder="Short context for teachers or admins." />
                                <InputError :message="folderForm.errors.description" />
                            </div>

                            <button type="submit" class="action-btn-secondary w-full justify-center bg-slate-900 text-white hover:bg-blue-600">
                                Add Folder
                            </button>
                        </form>
                    </AppSectionCard>

                    <AppSectionCard title="Upload File" subtitle="Attach a document and optional preview image to a selected folder.">
                        <form @submit.prevent="submitFile" class="space-y-4">
                            <div>
                                <label class="field-label" for="upload_folder">Folder</label>
                                <select id="upload_folder" v-model="fileForm.folder_id" class="field-input">
                                    <option value="">Select Folder</option>
                                    <option v-for="f in allFolders" :key="f.id" :value="f.id">{{ f.full_path }}</option>
                                </select>
                                <InputError :message="fileForm.errors.folder_id" />
                            </div>

                            <div>
                                <label class="field-label" for="file_title">File Title</label>
                                <input id="file_title" v-model="fileForm.title" type="text" class="field-input" placeholder="Mathematics Curriculum Guide" />
                                <InputError :message="fileForm.errors.title" />
                            </div>

                            <div>
                                <label class="field-label" for="resource_file">Main File</label>
                                <input
                                    id="resource_file"
                                    ref="uploadFileInput"
                                    type="file"
                                    accept="video/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.zip,.rar,.7z,.jpg,.jpeg,.png,.gif,.webp"
                                    class="field-input file:mr-3 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-white"
                                    @change="setSingleFile('file', $event)"
                                />
                                <p class="mt-2 text-[11px] font-semibold text-slate-500">
                                    Max file size: {{ fileMaxSizeLabel }} (one file per upload)
                                </p>
                                <InputError :message="fileForm.errors.file" />
                            </div>

                            <div>
                                <label class="field-label" for="preview_image">Preview Image</label>
                                <input
                                    id="preview_image"
                                    ref="previewImageInput"
                                    type="file"
                                    accept="image/*"
                                    class="field-input file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-white"
                                    @change="setSingleFile('preview_image', $event)"
                                />
                                <p class="mt-2 text-[11px] font-semibold text-slate-500">
                                    Preview image max size: {{ previewMaxSizeLabel }}
                                </p>
                                <InputError :message="fileForm.errors.preview_image" />
                            </div>

                            <button type="submit" class="action-btn-primary w-full justify-center">
                                Upload File
                            </button>
                        </form>
                    </AppSectionCard>
                </div>

                <AppSectionCard title="Quick Folder Select" subtitle="Speed up repeated uploads by selecting the destination folder here first.">
                    <div class="grid max-h-56 grid-cols-1 gap-3 overflow-y-auto pr-1 md:grid-cols-2 custom-scrollbar">
                        <button
                            v-for="f in allFolders"
                            :key="f.id"
                            type="button"
                            @click="selectFolder(f.id)"
                            class="rounded-2xl border-2 p-4 text-left transition-all"
                            :class="folderForm.parent_id === f.id || fileForm.folder_id === f.id ? 'border-blue-300 bg-blue-50' : 'border-slate-100 bg-slate-50 hover:border-blue-200'"
                        >
                            <p class="truncate text-sm font-black uppercase text-slate-900">{{ f.name }}</p>
                            <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ f.full_path }}</p>
                        </button>
                    </div>
                </AppSectionCard>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <AppSectionCard title="Homepage Carousel" subtitle="Highlight important visuals on the teacher landing page.">
                        <form @submit.prevent="submitCarousel" class="space-y-4">
                            <div>
                                <label class="field-label" for="carousel_title">Slide Title</label>
                                <input id="carousel_title" v-model="carouselForm.title" type="text" class="field-input" placeholder="New curriculum launch" />
                                <InputError :message="carouselForm.errors.title" />
                            </div>

                            <div>
                                <label class="field-label" for="carousel_image">Slide Image</label>
                                <input id="carousel_image" type="file" accept="image/*" class="field-input file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-white" @input="carouselForm.image = $event.target.files[0]" />
                                <InputError :message="carouselForm.errors.image" />
                            </div>

                            <button type="submit" class="action-btn-primary w-full justify-center bg-indigo-600 hover:bg-slate-900">
                                Add Slide
                            </button>
                        </form>

                        <div class="custom-scrollbar mt-5 max-h-72 space-y-3 overflow-y-auto pr-1">
                            <div v-for="carousel in carouselImages" :key="carousel.id" class="panel-muted overflow-hidden border p-3">
                                <img :src="`/storage/${carousel.image_path}`" class="mb-3 h-32 w-full rounded-xl object-cover" alt="Carousel image" />
                                <template v-if="editingCarouselId === carousel.id">
                                    <form @submit.prevent="updateCarousel(carousel.id)" class="space-y-3">
                                        <input v-model="carouselEditForm.title" type="text" class="field-input" />
                                        <input type="file" accept="image/*" class="field-input file:mr-3 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-white" @input="carouselEditForm.image = $event.target.files[0]" />
                                        <div class="flex gap-2">
                                            <button type="submit" class="action-btn-primary flex-1 justify-center">Save</button>
                                            <button type="button" class="action-btn-secondary flex-1 justify-center" @click="cancelEditCarousel">Cancel</button>
                                        </div>
                                    </form>
                                </template>
                                <template v-else>
                                    <p class="text-sm font-black text-slate-900">{{ carousel.title }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a :href="`/storage/${carousel.image_path}`" target="_blank" class="action-btn-secondary">View</a>
                                        <button type="button" class="action-btn-secondary bg-slate-900 text-white hover:bg-blue-600" @click="startEditCarousel(carousel)">Edit</button>
                                        <button type="button" class="action-btn-danger" @click="deleteCarousel(carousel.id)">Delete</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </AppSectionCard>

                    <AppSectionCard title="Featured Videos" subtitle="Pin useful video resources for teachers to open quickly.">
                        <form @submit.prevent="submitVideo" class="space-y-4">
                            <div>
                                <label class="field-label" for="video_link">YouTube URL</label>
                                <input id="video_link" v-model="videoForm.youtube_link" type="url" class="field-input" placeholder="https://youtube.com/watch?v=..." />
                                <InputError :message="videoForm.errors.youtube_link" />
                            </div>

                            <div>
                                <label class="field-label" for="video_description">Description</label>
                                <textarea id="video_description" v-model="videoForm.description" rows="3" class="field-input" placeholder="What should teachers expect from this video?" />
                                <InputError :message="videoForm.errors.description" />
                            </div>

                            <button type="submit" class="action-btn-primary w-full justify-center bg-red-600 hover:bg-slate-900">
                                Add Video
                            </button>
                        </form>

                        <div class="custom-scrollbar mt-5 max-h-72 space-y-3 overflow-y-auto pr-1">
                            <div v-for="video in featuredVideos" :key="video.id" class="panel-muted border p-3">
                                <template v-if="editingVideoId === video.id">
                                    <form @submit.prevent="updateVideo(video.id)" class="space-y-3">
                                        <input v-model="videoEditForm.youtube_link" type="url" class="field-input" />
                                        <textarea v-model="videoEditForm.description" rows="3" class="field-input" />
                                        <div class="flex gap-2">
                                            <button type="submit" class="action-btn-primary flex-1 justify-center">Save</button>
                                            <button type="button" class="action-btn-secondary flex-1 justify-center" @click="cancelEditVideo">Cancel</button>
                                        </div>
                                    </form>
                                </template>
                                <template v-else>
                                    <p class="field-label">Video Link</p>
                                    <a :href="video.youtube_link" target="_blank" class="mt-2 block break-all text-sm font-black text-blue-600">{{ video.youtube_link }}</a>
                                    <p class="mt-2 text-sm font-medium text-slate-600">{{ video.description || 'No description' }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a :href="video.youtube_link" target="_blank" class="action-btn-secondary">View</a>
                                        <button type="button" class="action-btn-secondary bg-slate-900 text-white hover:bg-blue-600" @click="startEditVideo(video)">Edit</button>
                                        <button type="button" class="action-btn-danger" @click="deleteVideo(video.id)">Delete</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </AppSectionCard>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
