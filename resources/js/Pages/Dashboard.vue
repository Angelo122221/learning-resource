<script setup>
import AppPageHeader from '@/Components/AppPageHeader.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <AppPageHeader
            badge="DB"
            title="Welcome back"
            :accent="page.props.auth.user?.name || ''"
            subtitle="Your account is active and ready to access the platform."
        />

        <div class="grid gap-6 xl:grid-cols-3">
            <AppSectionCard
                title="Access Status"
                subtitle="Your account is signed in and ready to use the latest learning resources."
            >
                <p class="text-base font-semibold leading-7 text-slate-600">
                    Use the navigation above to open resources, review your profile, or move into the admin tools if your account has elevated access.
                </p>
            </AppSectionCard>

            <AppSectionCard title="Account" subtitle="Current signed-in identity">
                <dl class="space-y-4 text-sm font-semibold text-slate-600">
                    <div>
                        <dt class="field-label">Name</dt>
                        <dd class="mt-2 text-base font-black text-slate-900">{{ page.props.auth.user?.name }}</dd>
                    </div>
                    <div>
                        <dt class="field-label">Email</dt>
                        <dd class="mt-2 break-all text-base font-black text-slate-900">{{ page.props.auth.user?.email }}</dd>
                    </div>
                </dl>
            </AppSectionCard>

            <AppSectionCard title="Role" subtitle="How the system currently sees your account">
                <p class="text-base font-black uppercase tracking-[0.18em] text-blue-600">
                    {{ page.props.auth.user?.is_admin ? 'Administrator' : page.props.auth.user?.role || 'User' }}
                </p>
                <p class="mt-4 text-sm font-medium leading-7 text-slate-500">
                    Teachers can browse and download materials. Admins can also manage folders, files, users, and analytics.
                </p>
            </AppSectionCard>
        </div>
    </AuthenticatedLayout>
</template>
