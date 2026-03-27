<script setup>
import AppEmptyState from '@/Components/AppEmptyState.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import UserFolderItem from './FolderItem.vue';

const props = defineProps({
    folders: Array,
    carouselImages: Array,
    featuredVideos: Array,
});

const showcaseSlides = computed(() => props.carouselImages ?? []);
const mainVideo = computed(() => props.featuredVideos[0] ?? null);
const mainVideoEmbedUrl = computed(() => {
    const link = mainVideo.value?.youtube_link;

    if (!link) {
        return '';
    }

    try {
        const parsedUrl = new URL(link);

        if (parsedUrl.hostname.includes('youtu.be')) {
            const videoId = parsedUrl.pathname.split('/').filter(Boolean)[0];
            return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
        }

        const videoId = parsedUrl.searchParams.get('v');
        return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
    } catch {
        return '';
    }
});
const activeShowcaseIndex = ref(0);
const autoplayHandle = ref(null);

const wrapSlideIndex = (index) => {
    const totalSlides = showcaseSlides.value.length;

    if (!totalSlides) {
        return 0;
    }

    return (index % totalSlides + totalSlides) % totalSlides;
};

const visibleCarouselOffsets = computed(() => {
    const totalSlides = showcaseSlides.value.length;

    if (totalSlides <= 1) return [0];
    if (totalSlides === 2) return [0, 1];
    if (totalSlides === 3) return [-1, 0, 1];
    if (totalSlides === 4) return [-1, 0, 1, 2];

    return [-2, -1, 0, 1, 2];
});

const visibleCarouselSlides = computed(() => visibleCarouselOffsets.value.map((offset) => ({
    offset,
    slide: showcaseSlides.value[wrapSlideIndex(activeShowcaseIndex.value + offset)],
    key: `${showcaseSlides.value[wrapSlideIndex(activeShowcaseIndex.value + offset)]?.id ?? 'slide'}-${offset}`,
})));

const setActiveShowcase = (index) => {
    activeShowcaseIndex.value = wrapSlideIndex(index);
};

const goToNextShowcase = () => {
    if (showcaseSlides.value.length <= 1) return;
    setActiveShowcase(activeShowcaseIndex.value + 1);
};

const goToPreviousShowcase = () => {
    if (showcaseSlides.value.length <= 1) return;
    setActiveShowcase(activeShowcaseIndex.value - 1);
};

const stopShowcaseAutoplay = () => {
    if (autoplayHandle.value) {
        clearInterval(autoplayHandle.value);
        autoplayHandle.value = null;
    }
};

const startShowcaseAutoplay = () => {
    stopShowcaseAutoplay();

    if (showcaseSlides.value.length <= 1) {
        return;
    }

    autoplayHandle.value = setInterval(() => {
        goToNextShowcase();
    }, 4000);
};

const getDesktopCarouselCardClass = (offset) => {
    if (offset === 0) {
        return 'z-30 left-1/2 top-1/2 h-[23rem] w-[17rem] -translate-x-1/2 -translate-y-1/2 scale-100 opacity-100';
    }

    if (offset === -1) {
        return 'z-20 left-[28%] top-1/2 h-[19rem] w-[14rem] -translate-x-1/2 -translate-y-1/2 scale-[0.94] opacity-100';
    }

    if (offset === 1) {
        return 'z-20 left-[72%] top-1/2 h-[19rem] w-[14rem] -translate-x-1/2 -translate-y-1/2 scale-[0.94] opacity-100';
    }

    if (offset === -2) {
        return 'z-10 left-[8%] top-1/2 h-[15rem] w-[10.5rem] -translate-x-1/2 -translate-y-1/2 scale-[0.84] opacity-80';
    }

    return 'z-10 left-[92%] top-1/2 h-[15rem] w-[10.5rem] -translate-x-1/2 -translate-y-1/2 scale-[0.84] opacity-80';
};

watch(
    () => showcaseSlides.value.length,
    (length) => {
        if (!length) {
            activeShowcaseIndex.value = 0;
            stopShowcaseAutoplay();
            return;
        }

        activeShowcaseIndex.value = wrapSlideIndex(activeShowcaseIndex.value);
        startShowcaseAutoplay();
    },
    { immediate: true },
);

onMounted(() => {
    startShowcaseAutoplay();
});

onBeforeUnmount(() => {
    stopShowcaseAutoplay();
});

const partnerOrganizations = [
    {
        name: 'Schools Division Office',
        short: 'SDO',
        description: 'Division leadership and implementation support',
        tone: 'from-sky-500 to-blue-700',
    },
    {
        name: 'DepEd Region',
        short: 'REG',
        description: 'Regional coordination and resource alignment',
        tone: 'from-indigo-500 to-indigo-700',
    },
    {
        name: 'Learning Resource Team',
        short: 'LRT',
        description: 'Content curation and teacher-facing updates',
        tone: 'from-emerald-500 to-emerald-700',
    },
    {
        name: 'Partner Schools',
        short: 'SCH',
        description: 'Collaborative classroom implementation network',
        tone: 'from-amber-500 to-orange-600',
    },
];
</script>

<template>
    <Head title="Learning Resources" />

    <UserLayout>
        <section>
            <div class="mb-6 text-center">
                <p class="eyebrow text-slate-500">Featured Collection</p>
                <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-slate-950 md:text-4xl">
                    Learning Resource Center
                </h2>
            </div>

            <AppEmptyState
                v-if="showcaseSlides.length === 0"
                title="No featured resources yet"
                message="Carousel items added by admins will appear here."
            />

            <div
                v-else
                class="rounded-[2.25rem] bg-[linear-gradient(180deg,#fbfcfe_0%,#eef3ff_100%)] px-4 py-6 shadow-[0_24px_60px_rgba(37,99,235,0.08)] sm:px-6 md:px-8 md:py-8"
                @mouseenter="stopShowcaseAutoplay"
                @mouseleave="startShowcaseAutoplay"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Auto-playing carousel</p>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Featured books move every 4 seconds. Use the arrows or dots to browse manually.
                        </p>
                    </div>

                    <div class="hidden items-center gap-2 md:flex">
                        <button
                            type="button"
                            class="action-btn-secondary !rounded-full !px-4"
                            aria-label="Previous featured book"
                            @click="goToPreviousShowcase"
                        >
                            Prev
                        </button>
                        <button
                            type="button"
                            class="action-btn-primary !rounded-full !px-4"
                            aria-label="Next featured book"
                            @click="goToNextShowcase"
                        >
                            Next
                        </button>
                    </div>
                </div>

                <div class="mt-6 md:hidden">
                    <article
                        v-if="showcaseSlides[activeShowcaseIndex]"
                        class="mx-auto max-w-[20rem] overflow-hidden rounded-[1.9rem] border-2 border-white/80 bg-white shadow-[0_24px_48px_rgba(15,23,42,0.12)]"
                    >
                        <img
                            :src="`/storage/${showcaseSlides[activeShowcaseIndex].image_path}`"
                            :alt="showcaseSlides[activeShowcaseIndex].title"
                            class="h-[22rem] w-full object-cover"
                        />
                        <div class="border-t border-slate-100 px-4 py-4">
                            <p class="line-clamp-2 text-center text-sm font-black uppercase tracking-tight text-slate-900">
                                {{ showcaseSlides[activeShowcaseIndex].title }}
                            </p>
                        </div>
                    </article>
                </div>

                <div class="relative mt-6 hidden h-[27rem] overflow-hidden md:block">
                    <article
                        v-for="item in visibleCarouselSlides"
                        :key="item.key"
                        class="absolute overflow-hidden rounded-[2rem] border-2 border-white/85 bg-white shadow-[0_24px_52px_rgba(15,23,42,0.14)] transition-all duration-700 ease-out"
                        :class="getDesktopCarouselCardClass(item.offset)"
                    >
                        <div class="absolute inset-x-0 top-0 z-10 h-20 bg-gradient-to-b from-slate-950/16 to-transparent" />
                        <img
                            :src="`/storage/${item.slide.image_path}`"
                            :alt="item.slide.title"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-if="item.offset === 0"
                            class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-white via-white/92 to-transparent px-5 pb-5 pt-16"
                        >
                            <p class="line-clamp-2 text-center text-base font-black uppercase tracking-tight text-slate-900">
                                {{ item.slide.title }}
                            </p>
                        </div>
                    </article>
                </div>

                <div class="mt-6 flex items-center justify-center gap-2">
                    <button
                        v-for="(slide, index) in showcaseSlides"
                        :key="slide.id"
                        type="button"
                        class="h-3 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                        :class="index === activeShowcaseIndex ? 'w-10 bg-blue-600' : 'w-3 bg-slate-300 hover:bg-slate-400'"
                        :aria-label="`Go to featured book ${index + 1}`"
                        @click="setActiveShowcase(index)"
                    />
                </div>

                <div class="mt-5 flex items-center justify-center gap-2 md:hidden">
                    <button
                        type="button"
                        class="action-btn-secondary !rounded-full !px-4"
                        aria-label="Previous featured book"
                        @click="goToPreviousShowcase"
                    >
                        Prev
                    </button>
                    <button
                        type="button"
                        class="action-btn-primary !rounded-full !px-4"
                        aria-label="Next featured book"
                        @click="goToNextShowcase"
                    >
                        Next
                    </button>
                </div>
            </div>
        </section>

        <section class="mt-10 rounded-[2rem] border-2 border-slate-200 bg-white px-4 py-6 shadow-[0_18px_35px_rgba(15,23,42,0.07)] md:px-6 md:py-8">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-black uppercase tracking-tight text-slate-950">Resource Categories</h2>
                <p class="mt-2 text-sm font-medium text-slate-500">Explore our extensive library of educational materials.</p>
            </div>

            <AppEmptyState
                v-if="folders.length === 0"
                title="No resource categories yet"
                message="Once folders are published by admins, they will appear here as learning resource categories."
            />

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <UserFolderItem
                    v-for="folder in folders"
                    :key="folder.id"
                    :folder="folder"
                    :is-root="true"
                />
            </div>

            <div v-if="folders.length" class="mt-6 flex justify-center">
                <button type="button" class="action-btn-primary">
                    View All Resources
                </button>
            </div>
        </section>

        <section class="mt-10">
            <div class="mb-5 flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-orange-400" />
                <h2 class="text-xl font-black text-slate-950">Featured Video</h2>
            </div>

            <div class="rounded-[2rem] border-2 border-slate-200 bg-white p-4 shadow-[0_18px_35px_rgba(15,23,42,0.07)] md:p-6">
                <AppEmptyState
                    v-if="!mainVideo"
                    title="No featured video yet"
                    message="When admins publish a featured video, it will appear here."
                />

                <div v-else class="grid gap-6 lg:grid-cols-[1fr_1.4fr] lg:items-center">
                    <!-- Left Side: Source & Description -->
                    <div class="space-y-4">
                        <!-- Source Badge -->
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                <ApplicationLogo class="h-8 w-8 fill-current" />
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900">DEPED</p>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Ozamiz - CRYSTAL Portal</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <h3 class="text-base font-black text-slate-900">Comprehensive Resource for Year-Round Systemazide Teaching and Learning</h3>
                            <p class="text-sm font-medium leading-6 text-slate-600">
                                {{ mainVideo.description || 'Featured classroom support video for teacher reference and resource-based learning.' }}
                            </p>
                        </div>

                        <!-- CTA Button -->
                        <a :href="mainVideo.youtube_link" class="action-btn-primary w-full justify-center">
                            Watch Video
                        </a>
                    </div>

                    <!-- Right Side: Video Thumbnail -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-black shadow-lg">
                        <iframe
                            v-if="mainVideoEmbedUrl"
                            :src="mainVideoEmbedUrl"
                            class="aspect-video w-full"
                            title="Featured video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        />
                        <div v-else class="aspect-video w-full bg-slate-900" />
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-10">
            <div class="mb-5 flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-orange-400" />
                <h2 class="text-xl font-black text-slate-950">Partner Organization</h2>
            </div>

            <div class="rounded-[2rem] border-2 border-slate-200 bg-white p-4 shadow-[0_18px_35px_rgba(15,23,42,0.07)] md:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="partner in partnerOrganizations"
                        :key="partner.name"
                        class="rounded-[1.6rem] border border-slate-100 bg-slate-50 p-4 text-center"
                    >
                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br text-lg font-black text-white shadow-lg"
                            :class="partner.tone"
                        >
                            {{ partner.short }}
                        </div>
                        <p class="mt-4 text-sm font-black uppercase tracking-[0.12em] text-slate-900">{{ partner.name }}</p>
                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">{{ partner.description }}</p>
                    </article>
                </div>
            </div>
        </section>
    </UserLayout>
</template>
