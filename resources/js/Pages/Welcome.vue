<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Crystal Portal" />

    <div class="app-shell">
        <div class="app-container py-10">
            <section class="panel overflow-hidden p-8 md:p-10">
                <div class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="eyebrow">DepEd Resource Platform</p>
                        <h1 class="mt-4 text-4xl font-black uppercase tracking-[-0.05em] text-slate-950 md:text-6xl">
                            Learning resources,
                            <span class="text-blue-600">teacher access, and analytics</span>
                            in one system
                        </h1>
                        <p class="mt-5 max-w-2xl text-base font-medium leading-8 text-slate-600 md:text-lg">
                            A unified portal for storing instructional materials, supporting teacher access, and monitoring usage across districts and schools.
                        </p>
                    </div>

                    <div v-if="props.canLogin" class="flex flex-wrap items-center gap-3">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="action-btn-primary">
                            Go to Dashboard
                        </Link>

                        <template v-else>
                            <Link :href="route('login')" class="action-btn-primary">
                                Log In
                            </Link>
                            <Link v-if="props.canRegister" :href="route('register')" class="action-btn-secondary">
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </section>

            <section class="mt-8 grid gap-6 lg:grid-cols-3">
                <article class="panel p-6">
                    <p class="eyebrow">Manage</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-950">Centralized resource control</h2>
                    <p class="mt-3 text-sm font-medium leading-7 text-slate-600">
                        Organize folders, upload files, manage carousel content, and curate featured videos from one admin workspace.
                    </p>
                </article>

                <article class="panel p-6">
                    <p class="eyebrow">Monitor</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-950">District-level analytics</h2>
                    <p class="mt-3 text-sm font-medium leading-7 text-slate-600">
                        Track downloads, file opens, folder activity, and user engagement across districts and schools.
                    </p>
                </article>

                <article class="panel p-6">
                    <p class="eyebrow">Build</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-950">Ready for your team</h2>
                    <p class="mt-3 text-sm font-medium leading-7 text-slate-600">
                        Frontend runs on Vue and Inertia with Laravel on the backend. Current stack: Laravel {{ props.laravelVersion }}, PHP {{ props.phpVersion }}.
                    </p>
                </article>
            </section>
        </div>
    </div>
</template>
