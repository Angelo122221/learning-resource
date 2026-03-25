<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import FolderItem from './FolderItem.vue';

const props = defineProps({
    folders: Array,
    allFolders: Array,
    stats: Object,
    carouselImages: Array,
    featuredVideos: Array,
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
    <div class="min-h-screen bg-[#f3f6f9] p-6 md:p-10 text-slate-900">
        <div class="max-w-[1800px] mx-auto">
            <header class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between mb-10 pb-6 border-b-4 border-slate-200">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">LR</div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black italic tracking-tighter uppercase leading-none">
                            Learning <span class="text-blue-600">Resources</span>
                        </h1>
                        <p class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">Admin Control Center</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 md:gap-4">
                    <div class="flex gap-6 bg-white px-5 py-4 rounded-2xl shadow-sm border-2 border-slate-100 text-center">
                        <div>
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Folders</p>
                            <p class="text-2xl font-black">{{ stats.total_folders }}</p>
                        </div>
                        <div class="w-[2px] bg-slate-100" />
                        <div>
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Files</p>
                            <p class="text-2xl font-black">{{ stats.total_files }}</p>
                        </div>
                        <div class="w-[2px] bg-slate-100" />
                        <div>
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Teachers</p>
                            <p class="text-2xl font-black">{{ stats.total_teachers }}</p>
                        </div>
                    </div>

                    <Link href="/admin/users" class="bg-emerald-600 text-white px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-md">
                        Users
                    </Link>
                    <a href="/admin/analytics" class="bg-slate-900 text-white px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-md">
                        Analytics
                    </a>
                    <Link href="/logout" method="post" as="button" class="bg-slate-200 text-slate-600 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all shadow-sm">
                        Log Out
                    </Link>
                </div>
            </header>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
                <section class="xl:col-span-5 sticky top-6 h-[72vh]">
                    <div class="bg-white p-6 rounded-[2rem] border-2 border-slate-100 shadow-sm h-full flex flex-col">
                        <h2 class="text-sm font-black uppercase text-slate-400 tracking-[0.3em] mb-4 pb-3 border-b-2 border-slate-100">Visual Explorer</h2>
                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
                            <div v-if="folders.length === 0" class="p-10 border-2 border-dashed border-slate-200 rounded-2xl text-center bg-slate-50">
                                <p class="text-sm text-slate-400 font-black uppercase tracking-widest">No folders yet</p>
                            </div>

                            <div class="space-y-2">
                                <FolderItem
                                    v-for="folder in folders"
                                    :key="folder.id"
                                    :folder="folder"
                                    @delete="deleteItem"
                                    @lock="toggleLock"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="xl:col-span-7 flex flex-col gap-8 h-[72vh] overflow-y-auto custom-scrollbar pr-1 pb-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-2 border-slate-100">
                            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4">Create Folder</h3>
                            <form @submit.prevent="submitFolder" class="space-y-3">
                                <input v-model="folderForm.name" type="text" placeholder="Folder Name" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                                <select v-model="folderForm.parent_id" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                                    <option :value="null">Root Directory</option>
                                    <option v-for="f in allFolders" :key="f.id" :value="f.id">{{ f.full_path }}</option>
                                </select>
                                <textarea v-model="folderForm.description" placeholder="Description (optional)" rows="2" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50" />
                                <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all">
                                    Add Folder
                                </button>
                            </form>
                        </div>

                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-2 border-slate-100">
                            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4">Upload File</h3>
                            <form @submit.prevent="submitFile" class="space-y-3">
                                <select v-model="fileForm.folder_id" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                                    <option value="">Select Folder</option>
                                    <option v-for="f in allFolders" :key="f.id" :value="f.id">{{ f.full_path }}</option>
                                </select>
                                <input v-model="fileForm.title" type="text" placeholder="File Title" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                                <input type="file" @input="fileForm.file = $event.target.files[0]" class="w-full text-xs font-semibold">
                                <input type="file" accept="image/*" @input="fileForm.preview_image = $event.target.files[0]" class="w-full text-xs font-semibold">
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-900 transition-all">
                                    Upload
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border-2 border-slate-100">
                        <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4">Quick Folder Select</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-52 overflow-y-auto custom-scrollbar pr-1">
                            <button
                                v-for="f in allFolders"
                                :key="f.id"
                                type="button"
                                @click="selectFolder(f.id)"
                                class="text-left p-3 rounded-xl border-2 transition-all"
                                :class="folderForm.parent_id === f.id ? 'bg-blue-50 border-blue-300' : 'bg-slate-50 border-slate-100 hover:border-blue-200'"
                            >
                                <p class="text-sm font-black uppercase truncate">{{ f.name }}</p>
                                <p class="text-[10px] font-semibold text-slate-400 uppercase truncate mt-1">{{ f.full_path }}</p>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-2 border-slate-100">
                            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4">Homepage Carousel</h3>
                            <form @submit.prevent="submitCarousel" class="space-y-3 mb-4">
                                <input v-model="carouselForm.title" type="text" placeholder="Slide Title" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                                <input type="file" accept="image/*" @input="carouselForm.image = $event.target.files[0]" class="w-full text-xs font-semibold">
                                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-900 transition-all">
                                    Add Slide
                                </button>
                            </form>

                            <div class="space-y-3 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                <div v-for="carousel in carouselImages" :key="carousel.id" class="border-2 border-slate-100 rounded-xl p-3">
                                    <img :src="`/storage/${carousel.image_path}`" class="w-full h-28 object-cover rounded-lg mb-2" alt="Carousel image">
                                    <template v-if="editingCarouselId === carousel.id">
                                        <form @submit.prevent="updateCarousel(carousel.id)" class="space-y-2">
                                            <input v-model="carouselEditForm.title" type="text" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                            <input type="file" accept="image/*" @input="carouselEditForm.image = $event.target.files[0]" class="w-full text-xs">
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 bg-blue-600 text-white text-xs font-black uppercase py-2 rounded-lg">Save</button>
                                                <button type="button" @click="cancelEditCarousel" class="flex-1 bg-slate-200 text-slate-700 text-xs font-black uppercase py-2 rounded-lg">Cancel</button>
                                            </div>
                                        </form>
                                    </template>
                                    <template v-else>
                                        <p class="text-sm font-black">{{ carousel.title }}</p>
                                        <div class="flex gap-2 mt-2">
                                            <a :href="`/storage/${carousel.image_path}`" target="_blank" class="flex-1 text-center bg-slate-100 text-slate-700 text-xs font-black uppercase py-2 rounded-lg">View</a>
                                            <button type="button" @click="startEditCarousel(carousel)" class="flex-1 bg-slate-900 text-white text-xs font-black uppercase py-2 rounded-lg">Edit</button>
                                            <button type="button" @click="deleteCarousel(carousel.id)" class="flex-1 bg-red-100 text-red-700 text-xs font-black uppercase py-2 rounded-lg">Delete</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-2 border-slate-100">
                            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4">Featured Videos</h3>
                            <form @submit.prevent="submitVideo" class="space-y-3 mb-4">
                                <input v-model="videoForm.youtube_link" type="url" placeholder="YouTube URL" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                                <textarea v-model="videoForm.description" rows="2" placeholder="Description" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50" />
                                <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-900 transition-all">
                                    Add Video
                                </button>
                            </form>

                            <div class="space-y-3 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                <div v-for="video in featuredVideos" :key="video.id" class="border-2 border-slate-100 rounded-xl p-3">
                                    <template v-if="editingVideoId === video.id">
                                        <form @submit.prevent="updateVideo(video.id)" class="space-y-2">
                                            <input v-model="videoEditForm.youtube_link" type="url" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                            <textarea v-model="videoEditForm.description" rows="2" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm" />
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 bg-blue-600 text-white text-xs font-black uppercase py-2 rounded-lg">Save</button>
                                                <button type="button" @click="cancelEditVideo" class="flex-1 bg-slate-200 text-slate-700 text-xs font-black uppercase py-2 rounded-lg">Cancel</button>
                                            </div>
                                        </form>
                                    </template>
                                    <template v-else>
                                        <p class="text-xs font-black uppercase text-slate-400">Video Link</p>
                                        <a :href="video.youtube_link" target="_blank" class="text-sm text-blue-600 font-bold break-all">{{ video.youtube_link }}</a>
                                        <p class="text-sm text-slate-600 mt-2">{{ video.description || 'No description' }}</p>
                                        <div class="flex gap-2 mt-3">
                                            <a :href="video.youtube_link" target="_blank" class="flex-1 text-center bg-slate-100 text-slate-700 text-xs font-black uppercase py-2 rounded-lg">View</a>
                                            <button type="button" @click="startEditVideo(video)" class="flex-1 bg-slate-900 text-white text-xs font-black uppercase py-2 rounded-lg">Edit</button>
                                            <button type="button" @click="deleteVideo(video.id)" class="flex-1 bg-red-100 text-red-700 text-xs font-black uppercase py-2 rounded-lg">Delete</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
</style>
