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

const navLinkBaseClass = 'inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2';
</script>

<template>
    <div class="app-shell">
        <nav class="sticky top-0 z-40 border-b border-white/60 bg-white/90 backdrop-blur">
            <div class="app-container py-4">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-4">
                        <Link :href="homeHref" class="flex min-w-0 items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 p-2 text-white shadow-lg shadow-blue-200 transition-transform duration-200 hover:scale-[1.03]">
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
                            {{ showingNavigationDropdown ? 'Close' : 'Menu' }}
                        </button>
                    </div>

                    <div class="hidden items-center justify-between gap-4 xl:flex">
                        <div class="admin-nav-scroll flex flex-1 items-center gap-2 overflow-x-auto pb-1 pr-1">
                            <Link
                                v-for="item in navItems"
                                :key="item.href"
                                :href="item.href"
                                :class="[
                                    navLinkBaseClass,
                                    route().current(item.active)
                                        ? 'bg-slate-900 text-white shadow-[0_10px_22px_rgba(15,23,42,0.16)]'
                                        : 'text-slate-600 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-900',
                                ]"
                            >
                                {{ item.label }}
                            </Link>
                        </div>

                        <div class="flex items-center gap-3">
                            <div v-if="user" class="min-w-0 text-right">
                                <p class="truncate text-sm font-black text-slate-900">{{ user.name }}</p>
                                <p class="truncate text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ user.email }}</p>
                            </div>
                            <slot name="nav-actions" />
                        </div>
                    </div>

                    <Transition
                        enter-active-class="transform-gpu transition-all duration-300 ease-out"
                        enter-from-class="translate-y-[-8px] scale-[0.985] opacity-0"
                        enter-to-class="translate-y-0 scale-100 opacity-100"
                        leave-active-class="transform-gpu transition-all duration-200 ease-in"
                        leave-from-class="translate-y-0 scale-100 opacity-100"
                        leave-to-class="translate-y-[-8px] scale-[0.985] opacity-0"
                    >
                        <div
                            v-if="showingNavigationDropdown"
                            class="rounded-2xl border border-slate-200 bg-white p-3 shadow-[0_18px_40px_rgba(15,23,42,0.08)] xl:hidden"
                        >
                            <div class="flex flex-col gap-2">
                                <Link
                                    v-for="item in navItems"
                                    :key="item.href"
                                    :href="item.href"
                                    :class="[
                                        navLinkBaseClass,
                                        route().current(item.active)
                                            ? 'bg-slate-900 text-white shadow-[0_10px_22px_rgba(15,23,42,0.16)]'
                                            : 'text-slate-600 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-900',
                                    ]"
                                >
                                    {{ item.label }}
                                </Link>
                            </div>

                            <div class="mt-3 border-t border-slate-100 pt-3">
                                <div v-if="user" class="mb-3 min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900">{{ user.name }}</p>
                                    <p class="truncate text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ user.email }}</p>
                                </div>
                                <slot name="nav-actions" />
                            </div>
                        </div>
                    </Transition>
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

<style scoped>
.admin-nav-scroll::-webkit-scrollbar {
    height: 6px;
}

.admin-nav-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}

.admin-nav-scroll::-webkit-scrollbar-track {
    background: transparent;
}
</style>
