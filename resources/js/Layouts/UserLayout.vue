<script setup>
import AppFlashBanner from '@/Components/AppFlashBanner.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const flashError = computed(() => page.props.flash?.error ?? '');
const showingNavigationDropdown = ref(false);
const supportEmail = 'cid.ozamiz@depedozamiz.net';
const emailCopied = ref(false);
const copyResetHandle = ref(null);

const resetCopiedState = () => {
    if (copyResetHandle.value) {
        clearTimeout(copyResetHandle.value);
        copyResetHandle.value = null;
    }
};

const fallbackCopySupportEmail = () => {
    const copySource = document.createElement('textarea');
    copySource.value = supportEmail;
    copySource.setAttribute('readonly', '');
    copySource.style.position = 'absolute';
    copySource.style.left = '-9999px';
    document.body.appendChild(copySource);
    copySource.select();
    document.execCommand('copy');
    document.body.removeChild(copySource);
};

const copySupportEmail = async () => {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(supportEmail);
        } else {
            fallbackCopySupportEmail();
        }

        emailCopied.value = true;
        resetCopiedState();
        copyResetHandle.value = setTimeout(() => {
            emailCopied.value = false;
            copyResetHandle.value = null;
        }, 2000);
    } catch {
        fallbackCopySupportEmail();
        emailCopied.value = true;
        resetCopiedState();
        copyResetHandle.value = setTimeout(() => {
            emailCopied.value = false;
            copyResetHandle.value = null;
        }, 2000);
    }
};

onBeforeUnmount(() => {
    resetCopiedState();
});
</script>

<template>
    <div class="user-portal-shell min-h-screen bg-[#f5f6f8]">
        <header class="user-portal-header border-b border-slate-200 bg-white shadow-[0_14px_36px_rgba(15,23,42,0.08)]">
            <div class="bg-[#f28c28] text-white">
                <div class="mx-auto flex w-full max-w-[1440px] items-center justify-between gap-3 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.18em] sm:px-6 lg:px-8">
                    <a
                        href="https://www.gov.ph/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="transition hover:text-white/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#f28c28]"
                    >
                        GovPH
                    </a>
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
                                    Complete Resources for Year-Round Systematized Teaching and Learning
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

            <div class="border-t border-slate-200/75 bg-[linear-gradient(180deg,#f9fbff_0%,#f2f6fc_100%)]">
                <div class="mx-auto flex w-full max-w-[1440px] px-4 py-1.5 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                            <svg
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            >
                                <path
                                    d="M3.333 5.833h13.334A1.667 1.667 0 0 1 18.333 7.5v5A1.667 1.667 0 0 1 16.667 14.167H3.333A1.667 1.667 0 0 1 1.667 12.5v-5A1.667 1.667 0 0 1 3.333 5.833Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="m2.083 6.667 6.936 4.855a1.667 1.667 0 0 0 1.962 0l6.936-4.855"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>

                        <button
                            type="button"
                            class="group flex min-w-0 items-center gap-2 rounded-full text-sm font-semibold text-slate-700 transition hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                            @click="copySupportEmail"
                        >
                            <span class="truncate underline decoration-slate-300 underline-offset-4 transition group-hover:decoration-blue-400">
                                {{ supportEmail }}
                            </span>
                            <svg
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:text-blue-500"
                                aria-hidden="true"
                            >
                                <path
                                    d="M7.5 6.667A1.667 1.667 0 0 1 9.167 5h5A1.667 1.667 0 0 1 15.833 6.667v6.666A1.667 1.667 0 0 1 14.167 15h-5A1.667 1.667 0 0 1 7.5 13.333V6.667Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M5.833 11.667H5A1.667 1.667 0 0 1 3.333 10V5A1.667 1.667 0 0 1 5 3.333h5A1.667 1.667 0 0 1 11.667 5v.833"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                        <p
                            v-if="emailCopied"
                            class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700 ring-1 ring-emerald-100"
                            aria-live="polite"
                        >
                            Email copied
                        </p>
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
