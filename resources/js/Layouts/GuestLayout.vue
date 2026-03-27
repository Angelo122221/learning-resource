<script setup>
import AppFlashBanner from '@/Components/AppFlashBanner.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const flashError = computed(() => page.props.flash?.error ?? '');
</script>

<template>
    <div class="app-shell">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col justify-center gap-10 px-4 py-10 sm:px-6 lg:flex-row lg:items-center lg:gap-16 lg:px-10">
            <section class="max-w-xl">
                <Link href="/" class="inline-flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-blue-600 p-3 text-white shadow-xl shadow-blue-200">
                        <ApplicationLogo class="h-9 w-9 fill-current" />
                    </div>
                    <div>
                        <p class="eyebrow">DepEd Resource Platform</p>
                        <p class="text-2xl font-black text-slate-950 md:text-3xl">Learning System</p>
                    </div>
                </Link>

                <div class="mt-8 space-y-5">
                    <p class="eyebrow">Unified Access</p>
                    <h1 class="hero-title">
                        One portal for
                        <span class="hero-accent">resources, users, and analytics</span>
                    </h1>
                    <p class="max-w-lg text-base font-medium leading-7 text-slate-600">
                        Sign in to manage learning materials, support teacher access, and track activity across districts and schools.
                    </p>
                </div>
            </section>

            <div class="panel w-full max-w-xl overflow-hidden p-6 sm:p-8">
                <AppFlashBanner tone="success" :message="flashSuccess" />
                <AppFlashBanner tone="error" :message="flashError" />
                <slot />
            </div>
        </div>
    </div>
</template>
