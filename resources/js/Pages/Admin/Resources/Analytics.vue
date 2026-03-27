<script setup>
import AppDataTable from '@/Components/AppDataTable.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppPageHeader from '@/Components/AppPageHeader.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import AppStatCard from '@/Components/AppStatCard.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    districtStats: Array,
    schoolStats: Array,
    topFolders: Array,
    topFiles: Array,
    recentActivity: Array,
    usersByRole: Array,
    loadError: String,
});

const districtHeaders = [
    { key: 'district', label: 'District' },
    { key: 'total', label: 'Total' },
    { key: 'folder_opens', label: 'Folder Opens' },
    { key: 'file_opens', label: 'File Opens' },
    { key: 'downloads', label: 'Downloads' },
];

const schoolHeaders = [
    { key: 'district', label: 'District' },
    { key: 'school', label: 'School' },
    { key: 'total', label: 'Total' },
    { key: 'folder_opens', label: 'Folder Opens' },
    { key: 'file_opens', label: 'File Opens' },
    { key: 'downloads', label: 'Downloads' },
];

const activityHeaders = [
    { key: 'time', label: 'Time' },
    { key: 'action', label: 'Action' },
    { key: 'user', label: 'User' },
    { key: 'district', label: 'District' },
    { key: 'school', label: 'School' },
    { key: 'target', label: 'Target' },
];
</script>

<template>
    <Head title="Analytics" />

    <AdminLayout>
        <AppPageHeader
            badge="AN"
            title="Analytics"
            accent="Dashboard"
            subtitle="Monitor resource usage across districts, schools, and account roles."
        />

        <div v-if="loadError" class="mb-6 rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
            {{ loadError }}
        </div>

        <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <AppStatCard label="Downloads" :value="stats.total_downloads" tone="blue" />
            <AppStatCard label="File Opens" :value="stats.total_file_opens" tone="blue" />
            <AppStatCard label="Folder Opens" :value="stats.total_folder_opens" tone="blue" />
            <AppStatCard label="Active Users" :value="stats.active_users" tone="emerald" />
            <AppStatCard label="Storage Used" :value="stats.storage_used" tone="slate" />
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-6">
                <AppSectionCard title="By District" subtitle="Compare folder opens, file opens, and downloads by district.">
                    <AppDataTable :headers="districtHeaders" :rows="districtStats" min-width="min-w-[620px]" empty-text="No district activity yet.">
                        <tr v-for="row in districtStats" :key="row.district">
                            <td class="font-bold">{{ row.district }}</td>
                            <td>{{ row.total_actions }}</td>
                            <td>{{ row.folders_opened }}</td>
                            <td>{{ row.files_opened }}</td>
                            <td>{{ row.files_downloaded }}</td>
                        </tr>
                    </AppDataTable>
                </AppSectionCard>
            </div>

            <div class="xl:col-span-6">
                <AppSectionCard title="By School" subtitle="Review engagement across schools within each district.">
                    <AppDataTable :headers="schoolHeaders" :rows="schoolStats" min-width="min-w-[700px]" empty-text="No school activity yet.">
                        <tr v-for="row in schoolStats" :key="`${row.district}-${row.school_name}`">
                            <td class="font-bold">{{ row.district }}</td>
                            <td>{{ row.school_name }}</td>
                            <td>{{ row.total_actions }}</td>
                            <td>{{ row.folders_opened }}</td>
                            <td>{{ row.files_opened }}</td>
                            <td>{{ row.files_downloaded }}</td>
                        </tr>
                    </AppDataTable>
                </AppSectionCard>
            </div>

            <div class="xl:col-span-4">
                <AppSectionCard title="Most Opened Folders" subtitle="Top folders by interaction count.">
                    <div v-if="topFolders.length" class="space-y-2">
                        <div v-for="row in topFolders" :key="row.folder_name" class="panel-muted flex items-center justify-between border px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ row.folder_name }}</p>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                    <AppEmptyState v-else title="No folder openings tracked yet." />
                </AppSectionCard>
            </div>

            <div class="xl:col-span-4">
                <AppSectionCard title="Most Opened Files" subtitle="Top files by open or download activity.">
                    <div v-if="topFiles.length" class="space-y-2">
                        <div v-for="row in topFiles" :key="row.file_title" class="panel-muted flex items-center justify-between border px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ row.file_title }}</p>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                    <AppEmptyState v-else title="No file interactions tracked yet." />
                </AppSectionCard>
            </div>

            <div class="xl:col-span-4">
                <AppSectionCard title="User Roles" subtitle="Current account totals by role type.">
                    <div v-if="usersByRole.length" class="space-y-2">
                        <div v-for="row in usersByRole" :key="row.role" class="panel-muted flex items-center justify-between border px-3 py-2">
                            <p class="text-sm font-semibold uppercase">{{ row.role }}</p>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                    <AppEmptyState v-else title="No users found." />
                </AppSectionCard>
            </div>

            <div class="xl:col-span-12">
                <AppSectionCard title="Recent Activity" subtitle="Most recent tracked actions across the platform.">
                    <AppDataTable :headers="activityHeaders" :rows="recentActivity" min-width="min-w-[1000px]" empty-text="No tracked activity yet.">
                        <tr v-for="row in recentActivity" :key="row.id">
                            <td>{{ row.time }}</td>
                            <td class="font-bold">{{ row.action }}</td>
                            <td>
                                {{ row.user_name }}
                                <span class="text-slate-400">({{ row.user_email }})</span>
                            </td>
                            <td>{{ row.district }}</td>
                            <td>{{ row.school_name }}</td>
                            <td>{{ row.target }}</td>
                        </tr>
                    </AppDataTable>
                </AppSectionCard>
            </div>
        </section>
    </AdminLayout>
</template>
