<script setup>
import AppEmptyState from '@/Components/AppEmptyState.vue';
import Modal from '@/Components/Modal.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import UserFolderItem from './FolderItem.vue';

const props = defineProps({
    folders: Array,
    announcements: Array,
    carouselImages: Array,
    featuredVideos: Array,
});

const isAnnouncementsModalOpen = ref(false);
const localAnnouncements = ref([]);
const expandedAnnouncementIds = ref([]);
const pendingReadIds = ref([]);
const pendingDismissIds = ref([]);
const isMarkingAllAnnouncementsRead = ref(false);
const showcaseSlides = computed(() => props.carouselImages ?? []);
const mediaUrl = (path) => `/media/${path}`;
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
const pressedShowcaseKey = ref(null);
const isShowcaseHovered = ref(false);
let showcasePressHandle = null;

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
    if (totalSlides <= 4) return [-1, 0, 1];

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

const resumeShowcaseAutoplayIfAllowed = () => {
    if (!isShowcaseHovered.value) {
        startShowcaseAutoplay();
    }
};

const pulseShowcaseCard = (key) => {
    if (showcasePressHandle) {
        clearTimeout(showcasePressHandle);
    }

    pressedShowcaseKey.value = key;
    showcasePressHandle = setTimeout(() => {
        pressedShowcaseKey.value = null;
        showcasePressHandle = null;
    }, 170);
};

const activateVisibleShowcase = (offset, key) => {
    pulseShowcaseCard(key);

    if (offset === 0) {
        resumeShowcaseAutoplayIfAllowed();
        return;
    }

    setActiveShowcase(activeShowcaseIndex.value + offset);
    resumeShowcaseAutoplayIfAllowed();
};

const goToNextShowcase = (options = {}) => {
    if (showcaseSlides.value.length <= 1) return;

    setActiveShowcase(activeShowcaseIndex.value + 1);

    if (options.restartAutoplay) {
        resumeShowcaseAutoplayIfAllowed();
    }
};

const goToPreviousShowcase = (options = {}) => {
    if (showcaseSlides.value.length <= 1) return;

    setActiveShowcase(activeShowcaseIndex.value - 1);

    if (options.restartAutoplay) {
        resumeShowcaseAutoplayIfAllowed();
    }
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

const handleShowcaseMouseEnter = () => {
    isShowcaseHovered.value = true;
    stopShowcaseAutoplay();
};

const handleShowcaseMouseLeave = () => {
    isShowcaseHovered.value = false;
    startShowcaseAutoplay();
};

const getDesktopCarouselCardClass = (offset) => {
    if (offset === 0) {
        return 'z-30 left-1/2 top-[46%] h-[28.5rem] w-[19.25rem] -translate-x-1/2 -translate-y-1/2 scale-[1.02] opacity-100';
    }

    if (offset === -1) {
        return 'z-20 left-[28.5%] top-[46%] h-[24rem] w-[15.25rem] -translate-x-1/2 -translate-y-1/2 scale-[0.87] opacity-100';
    }

    if (offset === 1) {
        return 'z-20 left-[71.5%] top-[46%] h-[24rem] w-[15.25rem] -translate-x-1/2 -translate-y-1/2 scale-[0.87] opacity-100';
    }

    if (offset === -2) {
        return 'z-10 left-[10.5%] top-[46%] h-[22rem] w-[13.9rem] -translate-x-1/2 -translate-y-1/2 scale-[0.82] opacity-100';
    }

    return 'z-10 left-[89.5%] top-[46%] h-[22rem] w-[13.9rem] -translate-x-1/2 -translate-y-1/2 scale-[0.82] opacity-100';
};

const getDesktopCarouselImageClass = (offset) => {
    if (offset === 0) {
        return 'brightness-100';
    }

    if (Math.abs(offset) === 1) {
        return 'brightness-[0.7]';
    }

    return 'brightness-[0.58]';
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

    if (showcasePressHandle) {
        clearTimeout(showcasePressHandle);
    }
});

const announcementDateFormatter = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
});

const normalizeAnnouncement = (announcement) => ({
    ...announcement,
    is_read: Boolean(announcement?.is_read),
});

const isAnnouncementExpanded = (announcementId) => expandedAnnouncementIds.value.includes(announcementId);
const isReadRequestPending = (announcementId) => pendingReadIds.value.includes(announcementId);
const isDismissRequestPending = (announcementId) => pendingDismissIds.value.includes(announcementId);

const visibleAnnouncements = computed(() => [...localAnnouncements.value].sort((left, right) => {
    const leftTimestamp = left.created_at ? Date.parse(left.created_at) : 0;
    const rightTimestamp = right.created_at ? Date.parse(right.created_at) : 0;

    return rightTimestamp - leftTimestamp;
}));

const unreadAnnouncementCount = computed(() => visibleAnnouncements.value.filter((announcement) => !announcement.is_read).length);
const announcementCount = computed(() => visibleAnnouncements.value.length);
const hasAnnouncements = computed(() => announcementCount.value > 0);

const formatAnnouncementTimestamp = (timestamp) => {
    if (!timestamp) {
        return 'Date unavailable';
    }

    const parsedDate = new Date(timestamp);

    if (Number.isNaN(parsedDate.getTime())) {
        return 'Date unavailable';
    }

    return announcementDateFormatter.format(parsedDate);
};

const syncAnnouncementsFromProps = (incomingAnnouncements) => {
    localAnnouncements.value = (incomingAnnouncements ?? []).map(normalizeAnnouncement);

    const visibleIds = localAnnouncements.value.map((announcement) => announcement.id);
    expandedAnnouncementIds.value = expandedAnnouncementIds.value.filter((id) => visibleIds.includes(id));
    pendingReadIds.value = pendingReadIds.value.filter((id) => visibleIds.includes(id));
    pendingDismissIds.value = pendingDismissIds.value.filter((id) => visibleIds.includes(id));
};

const markAnnouncementAsReadLocally = (announcementId) => {
    localAnnouncements.value = localAnnouncements.value.map((announcement) => (
        announcement.id === announcementId
            ? {
                ...announcement,
                is_read: true,
                read_at: announcement.read_at ?? new Date().toISOString(),
            }
            : announcement
    ));
};

const persistAnnouncementRead = async (announcementId) => {
    if (isReadRequestPending(announcementId)) {
        return;
    }

    pendingReadIds.value = [...pendingReadIds.value, announcementId];

    try {
        await axios.post(`/resources/announcements/${announcementId}/read`);
    } finally {
        pendingReadIds.value = pendingReadIds.value.filter((id) => id !== announcementId);
    }
};

const toggleAnnouncementExpansion = (announcement) => {
    const isExpanded = isAnnouncementExpanded(announcement.id);

    if (isExpanded) {
        expandedAnnouncementIds.value = expandedAnnouncementIds.value.filter((id) => id !== announcement.id);
        return;
    }

    expandedAnnouncementIds.value = [...expandedAnnouncementIds.value, announcement.id];

    if (!announcement.is_read) {
        markAnnouncementAsReadLocally(announcement.id);
        void persistAnnouncementRead(announcement.id);
    }
};

const markAllAnnouncementsAsRead = async () => {
    if (unreadAnnouncementCount.value === 0 || isMarkingAllAnnouncementsRead.value) {
        return;
    }

    const unreadIds = visibleAnnouncements.value
        .filter((announcement) => !announcement.is_read)
        .map((announcement) => announcement.id);

    if (!unreadIds.length) {
        return;
    }

    unreadIds.forEach((announcementId) => {
        markAnnouncementAsReadLocally(announcementId);
    });

    isMarkingAllAnnouncementsRead.value = true;

    try {
        await axios.post('/resources/announcements/read-all');
    } finally {
        isMarkingAllAnnouncementsRead.value = false;
        pendingReadIds.value = pendingReadIds.value.filter((id) => !unreadIds.includes(id));
    }
};

const dismissAnnouncement = async (announcementId) => {
    if (isDismissRequestPending(announcementId)) {
        return;
    }

    pendingDismissIds.value = [...pendingDismissIds.value, announcementId];
    expandedAnnouncementIds.value = expandedAnnouncementIds.value.filter((id) => id !== announcementId);
    localAnnouncements.value = localAnnouncements.value.filter((announcement) => announcement.id !== announcementId);

    try {
        await axios.post(`/resources/announcements/${announcementId}/dismiss`);
    } finally {
        pendingDismissIds.value = pendingDismissIds.value.filter((id) => id !== announcementId);
    }
};

const getAnnouncementToggleLabel = (announcement) => (
    isAnnouncementExpanded(announcement.id)
        ? 'Collapse announcement'
        : announcement.is_read
            ? 'Expand announcement'
            : 'Expand and mark announcement as read'
);

const isAnnouncementDimmed = (announcement) => announcement.is_read && !isAnnouncementExpanded(announcement.id);
const getAnnouncementContentStateClass = (announcementId) => (
    isAnnouncementExpanded(announcementId)
        ? 'announcement-content-expanded'
        : 'announcement-content-collapsed'
);

watch(
    () => props.announcements,
    (incomingAnnouncements) => {
        syncAnnouncementsFromProps(incomingAnnouncements);
    },
    { immediate: true },
);

const partnerOrganizations = [
    {
        name: 'Enhanced Basic Education Information Systems (EBEIS)',
        image: '/images/partners/ebeis.png',
    },
    {
        name: 'Learner Information System (LIS)',
        image: '/images/partners/lis.png',
    },
    {
        name: 'Learning Resource Management and Development System (LRMDS)',
        image: '/images/partners/lrmds.png',
    },
    {
        name: 'DepEd Partnership Database System',
        image: '/images/partners/deped-partnership-database-system.png',
    },
];
</script>

<template>
    <Head title="Crystal Portal" />

    <UserLayout>
        <section class="relative isolate -mt-2 md:-mt-3">
            <div class="pointer-events-none absolute inset-y-0 left-1/2 w-screen -translate-x-1/2 overflow-hidden" aria-hidden="true">
                <div class="absolute inset-x-0 top-0 h-[46%] bg-[radial-gradient(circle_at_50%_18%,rgba(191,219,254,0.52)_0%,rgba(219,234,254,0.26)_34%,rgba(255,255,255,0)_72%)]" />

                <svg
                    class="absolute inset-x-0 top-[20%] h-[50%] w-full md:top-[18%] md:h-[54%]"
                    viewBox="0 0 1440 420"
                    preserveAspectRatio="none"
                >
                    <defs>
                        <linearGradient id="learning-resource-wave-blue" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#0057ff" />
                            <stop offset="48%" stop-color="#006dff" />
                            <stop offset="100%" stop-color="#0043d6" />
                        </linearGradient>
                        <linearGradient id="learning-resource-wave-orange" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#ff9f1a" />
                            <stop offset="42%" stop-color="#ff8c00" />
                            <stop offset="76%" stop-color="#ff7400" />
                            <stop offset="100%" stop-color="#ff9812" />
                        </linearGradient>
                    </defs>
                    <path
                        d="M0 188C114 226 236 222 361 168C486 113 602 28 732 32C874 36 992 152 1115 211C1223 262 1334 263 1440 220L1440 252C1328 278 1214 274 1091 239C965 206 855 101 732 90C604 80 490 165 364 224C239 278 117 276 0 226Z"
                        fill="url(#learning-resource-wave-blue)"
                    />
                    <path
                        d="M0 226C117 276 239 278 364 224C490 165 604 80 732 90C855 101 965 206 1091 239C1214 274 1328 278 1440 252L1440 304C1327 329 1213 333 1088 320C963 289 853 220 732 223C604 226 490 290 363 334C239 377 118 362 0 302Z"
                        fill="url(#learning-resource-wave-orange)"
                        fill-opacity="0.72"
                    />
                </svg>
            </div>

            <AppEmptyState
                v-if="showcaseSlides.length === 0"
                title="No featured resources yet"
                message="Carousel items added by admins will appear here."
            />

            <div
                v-else
                class="relative z-10 mt-2 md:mt-3"
                @mouseenter="handleShowcaseMouseEnter"
                @mouseleave="handleShowcaseMouseLeave"
            >
                <div class="relative pt-1 md:hidden">
                    <article
                        v-if="showcaseSlides[activeShowcaseIndex]"
                        class="mx-auto max-w-[20.5rem] overflow-hidden rounded-[0.85rem] border border-white/70 bg-white"
                    >
                        <div class="relative">
                            <img
                                :src="mediaUrl(showcaseSlides[activeShowcaseIndex].image_path)"
                                :alt="showcaseSlides[activeShowcaseIndex].title"
                                class="h-[23.5rem] w-full object-cover"
                            />
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/82 via-slate-950/42 to-transparent px-4 pb-5 pt-16">
                                <p class="line-clamp-2 text-sm font-black uppercase tracking-[0.12em] text-white">
                                    {{ showcaseSlides[activeShowcaseIndex].title }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="relative hidden h-[31.5rem] overflow-hidden md:block">
                    <button
                        v-for="item in visibleCarouselSlides"
                        :key="item.key"
                        type="button"
                        class="group absolute overflow-hidden rounded-[0.9rem] border border-white/80 bg-white text-left transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-4"
                        :class="getDesktopCarouselCardClass(item.offset)"
                        :aria-label="`Show featured book ${item.slide.title}`"
                        @click="activateVisibleShowcase(item.offset, item.key)"
                    >
                        <div class="h-full w-full transition-transform duration-150 ease-out" :class="pressedShowcaseKey === item.key ? 'scale-[0.97]' : ''">
                            <img
                                :src="mediaUrl(item.slide.image_path)"
                                :alt="item.slide.title"
                                class="h-full w-full object-cover transition-[filter] duration-300"
                                :class="getDesktopCarouselImageClass(item.offset)"
                            />
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-3 bg-gradient-to-t from-slate-950/82 via-slate-950/28 to-transparent px-5 pb-5 pt-20 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 group-focus-visible:translate-y-0 group-focus-visible:opacity-100">
                                <p class="line-clamp-2 text-sm font-black uppercase tracking-[0.12em] text-white">
                                    {{ item.slide.title }}
                                </p>
                            </div>
                        </div>
                    </button>
                </div>

                <div v-if="showcaseSlides.length > 1" class="relative -mt-6 flex items-center justify-center gap-3 md:-mt-6 md:gap-4">
                    <button
                        type="button"
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200/80 bg-white/96 text-slate-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                        aria-label="Previous featured book"
                        @click="goToPreviousShowcase({ restartAutoplay: true })"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M11.75 4.5 6.25 10l5.5 5.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                        </svg>
                    </button>

                    <div class="flex items-center justify-center gap-2 rounded-full border border-slate-200/70 bg-white/96 px-4 py-3">
                        <button
                            v-for="(slide, index) in showcaseSlides"
                            :key="slide.id"
                            type="button"
                            class="h-2.5 w-2.5 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                            :class="index === activeShowcaseIndex ? 'scale-125 bg-slate-950 shadow-[0_0_0_6px_rgba(59,130,246,0.14)]' : 'bg-slate-300 hover:bg-slate-400'"
                            :aria-label="`Go to featured book ${index + 1}`"
                            :aria-current="index === activeShowcaseIndex ? 'true' : 'false'"
                            @click="setActiveShowcase(index); resumeShowcaseAutoplayIfAllowed()"
                        />
                    </div>

                    <button
                        type="button"
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200/80 bg-white/96 text-slate-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                        aria-label="Next featured book"
                        @click="goToNextShowcase({ restartAutoplay: true })"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M8.25 4.5 13.75 10l-5.5 5.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                        </svg>
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
                        <div class="flex min-h-[6rem] items-center justify-center">
                            <img
                                :src="partner.image"
                                :alt="partner.name"
                                class="h-auto max-h-24 w-auto max-w-[7rem] object-contain"
                            />
                </div>
                        <p class="mt-4 text-sm font-black uppercase tracking-[0.08em] text-slate-900">{{ partner.name }}</p>
                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">{{ partner.description }}</p>
                    </article>
        </div>
    </div>
        </section>

        <button
            type="button"
            class="fixed bottom-5 right-5 z-40 inline-flex h-14 w-14 items-center justify-center rounded-full border border-blue-200 bg-white text-blue-600 shadow-[0_12px_26px_rgba(15,23,42,0.10)] transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-700 hover:shadow-[0_16px_30px_rgba(37,99,235,0.14)] focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 active:translate-y-0 active:scale-[0.96] sm:bottom-6 sm:right-6 sm:h-[3.75rem] sm:w-[3.75rem]"
            :aria-label="hasAnnouncements
                ? unreadAnnouncementCount
                    ? `Open announcements. ${unreadAnnouncementCount} unread.`
                    : 'Open announcements. All caught up.'
                : 'Open announcements. No announcements yet.'"
            @click="isAnnouncementsModalOpen = true"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="h-7 w-7 sm:h-8 sm:w-8"
                aria-hidden="true"
            >
                <path
                    d="M12 4.75a4.25 4.25 0 0 0-4.25 4.25v2.254c0 .72-.214 1.425-.615 2.023L5.75 15.25h12.5l-1.385-1.973a3.5 3.5 0 0 1-.615-2.023V9A4.25 4.25 0 0 0 12 4.75Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path
                    d="M9.5 18a2.5 2.5 0 0 0 5 0"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <span
                v-if="unreadAnnouncementCount > 0"
                class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full border-2 border-white bg-orange-500 px-1 text-[10px] font-black leading-none text-white shadow-[0_8px_18px_rgba(242,140,40,0.24)]"
                aria-label="Unread announcement count"
            >
                {{ unreadAnnouncementCount > 9 ? '9+' : unreadAnnouncementCount }}
            </span>
        </button>

        <Modal :show="isAnnouncementsModalOpen" max-width="xl" position="center" @close="isAnnouncementsModalOpen = false">
            <div class="bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)]">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-blue-500">Announcements</p>
                        <h2 class="mt-2 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">Latest updates</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                            Stay updated with the latest announcements
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button
                            v-if="unreadAnnouncementCount > 0"
                            type="button"
                            class="inline-flex items-center justify-center rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isMarkingAllAnnouncementsRead"
                            @click="markAllAnnouncementsAsRead"
                        >
                            {{ isMarkingAllAnnouncementsRead ? 'Updating...' : 'Mark all as read' }}
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 active:scale-[0.97]"
                            aria-label="Close announcements"
                            @click="isAnnouncementsModalOpen = false"
                        >
                            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" aria-hidden="true">
                                <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[min(68vh,38rem)] overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
                    <AppEmptyState
                        v-if="!hasAnnouncements"
                        title="No announcements yet"
                        message="Check back later for important updates"
                    />

                    <div v-else class="space-y-4">
                        <article
                            v-for="announcement in visibleAnnouncements"
                            :key="announcement.id"
                            class="overflow-hidden rounded-[1.6rem] border shadow-[0_14px_30px_rgba(15,23,42,0.06)] transition-colors"
                            :class="isAnnouncementDimmed(announcement) ? 'border-slate-200 bg-slate-50/95' : 'border-blue-200 bg-white'"
                        >
                            <img
                                v-if="announcement.image_path"
                                :src="mediaUrl(announcement.image_path)"
                                :alt="announcement.title"
                                class="h-44 w-full object-cover sm:h-52"
                            />
                            <div class="p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span
                                                v-if="!announcement.is_read"
                                                class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"
                                                aria-label="Unread announcement"
                                            />
                                            <h3
                                                class="text-base font-black sm:text-lg"
                                                :class="isAnnouncementDimmed(announcement) ? 'text-slate-600' : 'text-slate-950'"
                                            >
                                                {{ announcement.title }}
                                            </h3>
                                        </div>
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em]" :class="isAnnouncementDimmed(announcement) ? 'text-slate-400' : 'text-slate-500'">
                                            {{ formatAnnouncementTimestamp(announcement.created_at) }}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100"
                                        :class="isAnnouncementDimmed(announcement)
                                            ? 'border-slate-200 bg-slate-100 text-slate-400 hover:border-slate-300 hover:text-slate-600'
                                            : 'border-blue-200 bg-blue-50 text-blue-600 hover:border-blue-300 hover:text-blue-700'"
                                        :aria-label="getAnnouncementToggleLabel(announcement)"
                                        @click="toggleAnnouncementExpansion(announcement)"
                                    >
                                        <svg
                                            viewBox="0 0 20 20"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 transition-transform duration-200"
                                            :class="isAnnouncementExpanded(announcement.id) ? 'rotate-180' : ''"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M5 7.5 10 12.5l5-5"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div
                                    class="announcement-content-shell mt-3"
                                    :class="getAnnouncementContentStateClass(announcement.id)"
                                >
                                    <p
                                        class="whitespace-pre-line text-sm font-medium leading-6 transition-colors duration-200"
                                        :class="isAnnouncementDimmed(announcement) ? 'text-slate-400' : 'text-slate-700'"
                                    >
                                        {{ announcement.content }}
                                    </p>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button
                                        type="button"
                                        class="inline-flex w-fit items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="isDismissRequestPending(announcement.id)"
                                        @click="dismissAnnouncement(announcement.id)"
                                    >
                                        {{ isDismissRequestPending(announcement.id) ? 'Dismissing...' : 'Dismiss' }}
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </Modal>
    </UserLayout>
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

.announcement-content-shell {
    overflow: hidden;
    transition: max-height 260ms ease, opacity 220ms ease;
}

.announcement-content-collapsed {
    max-height: 4.5rem;
    opacity: 0.92;
}

.announcement-content-expanded {
    max-height: 40rem;
    opacity: 1;
}
</style>
