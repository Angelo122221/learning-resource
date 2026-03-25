<script setup>
import { Head, Link } from '@inertiajs/vue3';
import UserFolderItem from './FolderItem.vue';

defineProps({
    folders: Array,
    carouselImages: Array,
    featuredVideos: Array,
});
</script>

<template>
    <Head title="Learning Resources" />

    <div class="min-h-screen bg-[#f3f6f9] p-6 md:p-10 text-slate-900">
        <div class="max-w-[1400px] mx-auto">
            <header class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between mb-8 pb-6 border-b-4 border-slate-200">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">LR</div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black italic tracking-tighter uppercase leading-none">
                            Learning <span class="text-blue-600">Resources</span>
                        </h1>
                        <p class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">Teacher Portal</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link href="/dashboard" class="bg-white border-2 border-slate-100 text-slate-700 px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:border-blue-400 transition-all">
                        Dashboard
                    </Link>
                    <Link href="/logout" method="post" as="button" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                        Log Out
                    </Link>
                </div>
            </header>

            <section class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
                <article class="xl:col-span-8 bg-white border-2 border-slate-100 rounded-2xl p-5 shadow-sm">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-4">Homepage Carousel</h2>
                    <div v-if="carouselImages.length === 0" class="text-sm text-slate-400 font-semibold">No carousel slides yet.</div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div v-for="slide in carouselImages" :key="slide.id" class="rounded-xl overflow-hidden border-2 border-slate-100 bg-slate-50">
                            <img :src="`/storage/${slide.image_path}`" class="w-full h-36 object-cover" :alt="slide.title">
                            <p class="text-sm font-bold p-3">{{ slide.title }}</p>
                        </div>
                    </div>
                </article>

                <article class="xl:col-span-4 bg-white border-2 border-slate-100 rounded-2xl p-5 shadow-sm">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-4">Featured Videos</h2>
                    <div v-if="featuredVideos.length === 0" class="text-sm text-slate-400 font-semibold">No featured videos yet.</div>
                    <div v-else class="space-y-3 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                        <a v-for="video in featuredVideos" :key="video.id" :href="video.youtube_link" target="_blank" class="block border-2 border-slate-100 rounded-xl p-3 bg-slate-50 hover:border-blue-300 transition-all">
                            <p class="text-sm font-black text-blue-600 break-all">{{ video.youtube_link }}</p>
                            <p class="text-xs text-slate-600 mt-2">{{ video.description || 'No description' }}</p>
                        </a>
                    </div>
                </article>
            </section>

            <section class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border-2 border-slate-100">
                <h2 class="text-sm font-black uppercase text-slate-400 tracking-[0.3em] mb-6">Available Folders</h2>

                <div v-if="folders.length === 0" class="p-12 border-4 border-dashed border-slate-200 rounded-[2rem] text-center bg-white">
                    <span class="text-6xl block mb-4 opacity-30">📂</span>
                    <p class="text-base text-slate-400 font-black uppercase tracking-widest italic">Drive is currently empty</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <UserFolderItem v-for="folder in folders" :key="folder.id" :folder="folder" :is-root="true" />
                </div>
            </section>
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
