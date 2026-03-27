<script setup>
import AppFlashBanner from '@/Components/AppFlashBanner.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    navItems: {
        type: Array,
        default: () => [],
    },
    homeHref: {
        type: String,
        default: '/dashboard',
    },
    brandLabel: {
        type: String,
        default: 'Learning System',
    },
    brandCaption: {
        type: String,
        default: 'DepEd Resource Platform',
    },
});

const page = usePage();
const showingNavigationDropdown = ref(false);

const flashSuccess = computed(() => page.props.flash?.success ?? '');
const flashError = computed(() => page.props.flash?.error ?? '');
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
    <div class="app-shell">
        <nav class="border-b border-white/60 bg-white/85 backdrop-blur">
            <div class="app-container py-4">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center justify-between gap-4">
                        <Link :href="homeHref" class="flex min-w-0 items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 p-2 text-white shadow-lg shadow-blue-200">
                                <ApplicationLogo class="h-7 w-7 fill-current" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black uppercase tracking-[0.28em] text-slate-400">{{ brandCaption }}</p>
                                <p class="truncate text-lg font-black text-slate-900">{{ brandLabel }}</p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            class="action-btn-secondary xl:hidden"
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                        >
                            Menu
                        </button>
                    </div>

                    <div :class="showingNavigationDropdown ? 'flex' : 'hidden'" class="flex-col gap-4 xl:flex xl:flex-1 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex flex-col gap-2 xl:flex-row xl:flex-wrap xl:items-center">
                            <Link
                                v-for="item in navItems"
                                :key="item.href"
                                :href="item.href"
                                class="rounded-2xl px-4 py-2 text-sm font-black uppercase tracking-[0.18em] transition"
                                :class="route().current(item.active) ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'"
                            >
                                {{ item.label }}
                            </Link>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 xl:flex-row xl:items-center xl:border-t-0 xl:pt-0">
                            <div v-if="user" class="min-w-0">
                                <p class="truncate text-sm font-black text-slate-900">{{ user.name }}</p>
                                <p class="truncate text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ user.email }}</p>
                            </div>
                            <slot name="nav-actions" />
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="app-container">
            <AppFlashBanner tone="success" :message="flashSuccess" />
            <AppFlashBanner tone="error" :message="flashError" />
            <slot />
        </main>
    </div>
</template>
