<script setup>
import AppFlashBanner from '@/Components/AppFlashBanner.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const flashError = computed(() => page.props.flash?.error ?? '');
const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="user-portal-shell min-h-screen bg-[#f5f6f8]">
        <header class="user-portal-header border-b border-slate-200 bg-white shadow-[0_14px_36px_rgba(15,23,42,0.08)]">
            <div class="bg-[#f28c28] text-white">
                <div class="mx-auto flex w-full max-w-[1440px] items-center justify-between gap-3 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.18em] sm:px-6 lg:px-8">
                    <p>GovPH</p>
                    <div class="flex items-center gap-2">
                        <p class="hidden sm:block">Public learning resource portal</p>
                        <Link href="/logout" method="post" as="button" class="rounded-full border border-white/45 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-white transition hover:bg-white hover:text-[#183f95] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#f28c28]">
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>

            <div class="bg-[linear-gradient(90deg,#0f4ba8_0%,#214dc1_38%,#5b35c6_72%,#2b2a8f_100%)] text-white">
                <div class="mx-auto flex w-full max-w-[1440px] flex-col gap-5 px-4 py-4 sm:px-6 lg:px-8 lg:py-5">
                    <div class="flex items-start justify-between gap-4">
                        <Link href="/resources" class="flex min-w-0 items-start gap-3 sm:gap-4">
                            <img
                                src="/images/crystal-login-logo.png"
                                alt="CRYSTAL Portal official logo"
                                class="mt-2 h-auto w-14 shrink-0 object-contain sm:w-16"
                            />
                            <div class="min-w-0 pt-1">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/75 sm:text-[11px]">
                                    Republic of the Philippines
                                </p>
                                <h1 class="truncate text-lg font-black tracking-tight sm:text-2xl">
                                    DepEd Ozamiz - CRYSTAL Portal
                                </h1>
                                <p class="mt-1 max-w-3xl text-xs font-medium leading-5 text-white/82 sm:text-sm">
                                    Learning resource portal for classroom-ready materials, featured media, and curated educational categories.
                                </p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            class="rounded-xl border border-white/35 bg-white/10 px-3 py-2 text-xs font-black uppercase tracking-[0.18em] text-white transition hover:bg-white/16 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#214dc1] lg:hidden"
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                        >
                            Menu
                        </button>
                    </div>

                    <div
                        v-if="showingNavigationDropdown && user"
                        class="rounded-[1.25rem] border border-white/18 bg-white/8 px-4 py-4 backdrop-blur lg:hidden"
                    >
                        <p class="truncate text-sm font-black text-white">{{ user.name }}</p>
                        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-white/68">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 md:py-8 lg:px-8">
            <AppFlashBanner tone="success" :message="flashSuccess" />
            <AppFlashBanner tone="error" :message="flashError" />
            <slot />
        </main>
    </div>
</template>
