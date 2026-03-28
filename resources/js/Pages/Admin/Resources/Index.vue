<script setup>
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppPageHeader from '@/Components/AppPageHeader.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import AppStatCard from '@/Components/AppStatCard.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
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
    announcements: Array,
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

const announcementForm = useForm({
    title: '',
    content: '',
    image: null,
});

const announcementEditForm = useForm({
    title: '',
    content: '',
    image: null,
    remove_image: false,
});

const editingCarouselId = ref(null);
const editingVideoId = ref(null);
const editingAnnouncementId = ref(null);

const showCarouselModal = ref(false);
const showVideoModal = ref(false);

const uploadFileInput = ref(null);
const previewImageInput = ref(null);
const carouselFileInput = ref(null);
const announcementFileInput = ref(null);

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

const normalizeFolderId = (value) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const parsed = Number(value);
    return Number.isNaN(parsed) ? null : parsed;
};

const isFolderSelected = (folderId) => {
    return normalizeFolderId(folderForm.parent_id) === folderId
        || normalizeFolderId(fileForm.folder_id) === folderId;
};

const selectedFolderName = computed(() => {
    const selectedFolder = props.allFolders.find((folder) => isFolderSelected(folder.id));

    return selectedFolder?.full_path ?? 'No folder selected';
});

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
        preserveScroll: true,
        onSuccess: () => folderForm.reset(),
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

    router.delete(`/admin/carousel/${carouselId}`, {
        preserveScroll: true,
    });
};

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

const submitAnnouncement = () => {
    announcementForm.post('/admin/announcements', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            announcementForm.reset();
            if (announcementFileInput.value) announcementFileInput.value.value = '';
        },
    });
};

const startEditAnnouncement = (announcement) => {
    editingAnnouncementId.value = announcement.id;
    announcementEditForm.title = announcement.title;
    announcementEditForm.content = announcement.content;
    announcementEditForm.image = null;
    announcementEditForm.remove_image = false;
};

const cancelEditAnnouncement = () => {
    editingAnnouncementId.value = null;
    announcementEditForm.reset();
};

const updateAnnouncement = (announcementId) => {
    announcementEditForm
        .transform((data) => ({
            ...data,
            _method: 'patch',
        }))
        .post(`/admin/announcements/${announcementId}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => cancelEditAnnouncement(),
        });
};

const deleteAnnouncement = (announcementId) => {
    if (!confirm('Delete this announcement?')) return;

    router.delete(`/admin/announcements/${announcementId}`, {
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
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <AppPageHeader
            badge="AD"
            title="Admin"
            accent="Dashboard"
            subtitle="Manage folders, file uploads, announcements, carousel slides, and featured videos."
        >
            <template #stats>
                <div class="grid w-full gap-3 sm:grid-cols-2 xl:w-auto xl:grid-cols-4">
                    <AppStatCard label="Folders" :value="stats.total_folders" tone="slate" />
                    <AppStatCard label="Files" :value="stats.total_files" tone="blue" />
                    <AppStatCard label="Teachers" :value="stats.total_teachers" tone="emerald" />
                    <AppStatCard label="Announcements" :value="announcements.length" tone="amber" />
                </div>
            </template>
        </AppPageHeader>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 xl:items-start">
            <div class="xl:col-span-5">
                <AppSectionCard title="Resource Explorer" subtitle="Review the current folder structure and lock or delete items.">
                    <div class="custom-scrollbar max-h-[72vh] overflow-y-auto pr-2">
                        <AppEmptyState
                            v-if="folders.length === 0"
                            title="No folders yet"
                            message="Create your first folder to start publishing resources."
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
                    <AppSectionCard title="Create Folder" subtitle="Set a root folder or nested folder for resource organization.">
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
                                <textarea id="folder_description" v-model="folderForm.description" rows="3" class="field-input" placeholder="Short context for teachers." />
                                <InputError :message="folderForm.errors.description" />
                            </div>

                            <button type="submit" class="action-btn-primary w-full justify-center">Add Folder</button>
                        </form>
                    </AppSectionCard>

                    <AppSectionCard title="Upload File" subtitle="Upload one file and an optional preview image.">
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
                                    class="field-input"
                                    @change="setSingleFile('file', $event)"
                                />
                                <p class="field-help mt-2">Max file size: {{ fileMaxSizeLabel }}</p>
                                <InputError :message="fileForm.errors.file" />
                            </div>

                            <div>
                                <label class="field-label" for="preview_image">Preview Image</label>
                                <input
                                    id="preview_image"
                                    ref="previewImageInput"
                                    type="file"
                                    accept="image/*"
                                    class="field-input"
                                    @change="setSingleFile('preview_image', $event)"
                                />
                                <p class="field-help mt-2">Max preview image size: {{ previewMaxSizeLabel }}</p>
                                <InputError :message="fileForm.errors.preview_image" />
                            </div>

                            <button type="submit" class="action-btn-primary w-full justify-center">Upload File</button>
                        </form>
                    </AppSectionCard>
                </div>

                <AppSectionCard title="Quick Folder Select" subtitle="Select a destination once, then continue creating folders or uploading files.">
                    <p class="mb-4 text-sm font-semibold text-slate-600">
                        Active folder: <span class="font-black text-slate-900">{{ selectedFolderName }}</span>
                    </p>

                    <div class="grid max-h-56 grid-cols-1 gap-3 overflow-y-auto pr-1 md:grid-cols-2 custom-scrollbar">
                        <button
                            v-for="f in allFolders"
                            :key="f.id"
                            type="button"
                            @click="selectFolder(f.id)"
                            class="rounded-2xl border p-3 text-left transition"
                            :class="isFolderSelected(f.id) ? 'border-blue-300 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300'"
                        >
                            <p class="truncate text-sm font-black text-slate-900">{{ f.name }}</p>
                            <p class="mt-1 truncate text-xs font-semibold text-slate-500">{{ f.full_path }}</p>
                        </button>
                    </div>
                </AppSectionCard>

                <AppSectionCard title="Announcements" subtitle="Publish text updates for teachers, with an optional image.">
                    <form @submit.prevent="submitAnnouncement" class="space-y-4 border-b border-slate-200 pb-5">
                        <div>
                            <label class="field-label" for="announcement_title">Title</label>
                            <input id="announcement_title" v-model="announcementForm.title" type="text" class="field-input" placeholder="Enrollment timeline update" />
                            <InputError :message="announcementForm.errors.title" />
                        </div>

                        <div>
                            <label class="field-label" for="announcement_content">Announcement Text</label>
                            <textarea
                                id="announcement_content"
                                v-model="announcementForm.content"
                                rows="4"
                                class="field-input"
                                placeholder="Enter your full announcement for teachers and staff."
                            />
                            <InputError :message="announcementForm.errors.content" />
                        </div>

                        <div>
                            <label class="field-label" for="announcement_image">Announcement Image</label>
                            <input
                                id="announcement_image"
                                ref="announcementFileInput"
                                type="file"
                                accept="image/*"
                                class="field-input"
                                @change="announcementForm.image = $event.target.files[0]"
                            />
                            <InputError :message="announcementForm.errors.image" />
                        </div>

                        <button type="submit" class="action-btn-primary w-full justify-center">Publish Announcement</button>
                    </form>

                    <div class="custom-scrollbar mt-5 max-h-80 space-y-3 overflow-y-auto pr-1">
                        <AppEmptyState v-if="announcements.length === 0" title="No announcements yet" message="Published announcements will appear here." />

                        <article v-for="announcement in announcements" :key="announcement.id" class="panel-muted border p-4">
                            <template v-if="editingAnnouncementId === announcement.id">
                                <form @submit.prevent="updateAnnouncement(announcement.id)" class="space-y-3">
                                    <input v-model="announcementEditForm.title" type="text" class="field-input" placeholder="Announcement title" />
                                    <textarea v-model="announcementEditForm.content" rows="4" class="field-input" placeholder="Announcement text" />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="field-input"
                                        @change="announcementEditForm.image = $event.target.files[0]"
                                    />
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                        <input v-model="announcementEditForm.remove_image" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                        Remove current image
                                    </label>
                                    <InputError :message="announcementEditForm.errors.title" />
                                    <InputError :message="announcementEditForm.errors.content" />
                                    <InputError :message="announcementEditForm.errors.image" />
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="action-btn-primary">Save</button>
                                        <button type="button" class="action-btn-secondary" @click="cancelEditAnnouncement">Cancel</button>
                                    </div>
                                </form>
                            </template>

                            <template v-else>
                                <img
                                    v-if="announcement.image_path"
                                    :src="`/storage/${announcement.image_path}`"
                                    alt="Announcement image"
                                    class="mb-3 h-40 w-full rounded-xl object-cover"
                                />
                                <h3 class="text-base font-black text-slate-900">{{ announcement.title }}</h3>
                                <p class="mt-2 whitespace-pre-line text-sm font-medium leading-6 text-slate-600">{{ announcement.content }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button type="button" class="action-btn-secondary" @click="startEditAnnouncement(announcement)">Edit</button>
                                    <button type="button" class="action-btn-danger" @click="deleteAnnouncement(announcement.id)">Delete</button>
                                </div>
                            </template>
                        </article>
                    </div>
                </AppSectionCard>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <AppSectionCard title="Homepage Carousel" subtitle="Manage live slides shown on the teacher portal.">
                        <div class="mb-4">
                            <button type="button" class="action-btn-primary w-full justify-center" @click="openCarouselModal">
                                Add Slide
                            </button>
                        </div>

                        <div class="custom-scrollbar max-h-72 space-y-3 overflow-y-auto pr-1">
                            <AppEmptyState v-if="carouselImages.length === 0" title="No slides yet" message="Add the first slide using the button above." />

                            <div v-for="carousel in carouselImages" :key="carousel.id" class="panel-muted overflow-hidden border p-3">
                                <img :src="`/storage/${carousel.image_path}`" class="mb-3 h-32 w-full rounded-xl object-cover" alt="Carousel image" />

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
                                        <a :href="`/storage/${carousel.image_path}`" target="_blank" class="action-btn-secondary">View</a>
                                        <button type="button" class="action-btn-secondary" @click="startEditCarousel(carousel)">Edit</button>
                                        <button type="button" class="action-btn-danger" @click="deleteCarousel(carousel.id)">Delete</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </AppSectionCard>

                    <AppSectionCard title="Featured Videos" subtitle="Manage video links shown to teachers.">
                        <div class="mb-4">
                            <button type="button" class="action-btn-primary w-full justify-center" @click="openVideoModal">
                                Add Video
                            </button>
                        </div>

                        <div class="custom-scrollbar max-h-72 space-y-3 overflow-y-auto pr-1">
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
                                    <p class="text-sm font-black text-slate-900 break-all">{{ video.youtube_link }}</p>
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
                </div>
            </div>
        </div>

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
