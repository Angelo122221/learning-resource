<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    districtStats: Array,
    schoolStats: Array,
    topFolders: Array,
    topFiles: Array,
    recentActivity: Array,
    usersByRole: Array,
});
</script>

<template>
    <div class="min-h-screen bg-[#f3f6f9] p-6 md:p-10 text-slate-900">
        <div class="max-w-[1800px] mx-auto">
            <header class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between mb-8 pb-6 border-b-4 border-slate-200">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">AN</div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black italic tracking-tighter uppercase leading-none">
                            Analytics <span class="text-blue-600">Dashboard</span>
                        </h1>
                        <p class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">District and School Tracking</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link href="/admin/resources" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
                        Resources
                    </Link>
                    <Link href="/admin/users" class="bg-emerald-600 text-white px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all">
                        Users
                    </Link>
                    <Link href="/logout" method="post" as="button" class="bg-red-100 text-red-700 px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                        Log Out
                    </Link>
                </div>
            </header>

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
                <article class="bg-white border-2 border-slate-100 rounded-2xl p-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Downloads</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ stats.total_downloads }}</p>
                </article>
                <article class="bg-white border-2 border-slate-100 rounded-2xl p-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">File Opens</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ stats.total_file_opens }}</p>
                </article>
                <article class="bg-white border-2 border-slate-100 rounded-2xl p-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Folder Opens</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ stats.total_folder_opens }}</p>
                </article>
                <article class="bg-white border-2 border-slate-100 rounded-2xl p-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Active Users</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ stats.active_users }}</p>
                </article>
                <article class="bg-white border-2 border-slate-100 rounded-2xl p-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Storage Used</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ stats.storage_used }}</p>
                </article>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                <article class="xl:col-span-6 bg-white border-2 border-slate-100 rounded-2xl p-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-3">By District</h2>
                    <div class="overflow-auto">
                        <table class="w-full min-w-[620px] text-sm">
                            <thead>
                                <tr class="text-left text-slate-400 uppercase text-[10px] tracking-widest border-b border-slate-100">
                                    <th class="py-2">District</th>
                                    <th class="py-2">Total</th>
                                    <th class="py-2">Folder Opens</th>
                                    <th class="py-2">File Opens</th>
                                    <th class="py-2">Downloads</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in districtStats" :key="row.district" class="border-b border-slate-50">
                                    <td class="py-2 font-bold">{{ row.district }}</td>
                                    <td class="py-2">{{ row.total_actions }}</td>
                                    <td class="py-2">{{ row.folders_opened }}</td>
                                    <td class="py-2">{{ row.files_opened }}</td>
                                    <td class="py-2">{{ row.files_downloaded }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="xl:col-span-6 bg-white border-2 border-slate-100 rounded-2xl p-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-3">By School</h2>
                    <div class="overflow-auto">
                        <table class="w-full min-w-[700px] text-sm">
                            <thead>
                                <tr class="text-left text-slate-400 uppercase text-[10px] tracking-widest border-b border-slate-100">
                                    <th class="py-2">District</th>
                                    <th class="py-2">School</th>
                                    <th class="py-2">Total</th>
                                    <th class="py-2">Folder Opens</th>
                                    <th class="py-2">File Opens</th>
                                    <th class="py-2">Downloads</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in schoolStats" :key="`${row.district}-${row.school_name}`" class="border-b border-slate-50">
                                    <td class="py-2 font-bold">{{ row.district }}</td>
                                    <td class="py-2">{{ row.school_name }}</td>
                                    <td class="py-2">{{ row.total_actions }}</td>
                                    <td class="py-2">{{ row.folders_opened }}</td>
                                    <td class="py-2">{{ row.files_opened }}</td>
                                    <td class="py-2">{{ row.files_downloaded }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="xl:col-span-4 bg-white border-2 border-slate-100 rounded-2xl p-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-3">Most Opened Folders</h2>
                    <div class="space-y-2">
                        <div v-for="row in topFolders" :key="row.folder_name" class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <p class="font-semibold text-sm truncate">{{ row.folder_name }}</p>
                            <p class="text-xs font-black text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                </article>

                <article class="xl:col-span-4 bg-white border-2 border-slate-100 rounded-2xl p-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-3">Most Opened/Downloaded Files</h2>
                    <div class="space-y-2">
                        <div v-for="row in topFiles" :key="row.file_title" class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <p class="font-semibold text-sm truncate">{{ row.file_title }}</p>
                            <p class="text-xs font-black text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                </article>

                <article class="xl:col-span-4 bg-white border-2 border-slate-100 rounded-2xl p-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-3">User Roles</h2>
                    <div class="space-y-2">
                        <div v-for="row in usersByRole" :key="row.role" class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <p class="font-semibold text-sm uppercase">{{ row.role }}</p>
                            <p class="text-xs font-black text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                </article>

                <article class="xl:col-span-12 bg-white border-2 border-slate-100 rounded-2xl p-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-3">Recent Activity</h2>
                    <div class="overflow-auto">
                        <table class="w-full min-w-[1000px] text-sm">
                            <thead>
                                <tr class="text-left text-slate-400 uppercase text-[10px] tracking-widest border-b border-slate-100">
                                    <th class="py-2">Time</th>
                                    <th class="py-2">Action</th>
                                    <th class="py-2">User</th>
                                    <th class="py-2">District</th>
                                    <th class="py-2">School</th>
                                    <th class="py-2">Target</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in recentActivity" :key="row.id" class="border-b border-slate-50">
                                    <td class="py-2">{{ row.time }}</td>
                                    <td class="py-2 font-bold">{{ row.action }}</td>
                                    <td class="py-2">{{ row.user_name }} <span class="text-slate-400">({{ row.user_email }})</span></td>
                                    <td class="py-2">{{ row.district }}</td>
                                    <td class="py-2">{{ row.school_name }}</td>
                                    <td class="py-2">{{ row.target }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </div>
    </div>
</template>
