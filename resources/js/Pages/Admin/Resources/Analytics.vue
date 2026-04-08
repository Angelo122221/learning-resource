<script setup>
import AppDataTable from '@/Components/AppDataTable.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppSectionCard from '@/Components/AppSectionCard.vue';
import AppStatCard from '@/Components/AppStatCard.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
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

const filters = ref({
    district: 'all',
    school: 'all',
    action: 'all',
    timeWindow: 'all',
    search: '',
});

const showRecentActivity = ref(false);

const normalizeString = (value) => (value ?? '').toString().trim();

const uniqueSortedValues = (values) => {
    return [...new Set(values.map(normalizeString).filter((item) => item !== ''))]
        .sort((a, b) => a.localeCompare(b));
};

const parseActivityDate = (value) => {
    const formatted = normalizeString(value);
    if (!formatted) {
        return null;
    }

    const parsed = new Date(formatted.includes('T') ? formatted : formatted.replace(' ', 'T'));
    return Number.isNaN(parsed.getTime()) ? null : parsed;
};

const districtOptions = computed(() => {
    return uniqueSortedValues([
        ...(props.districtStats ?? []).map((row) => row.district),
        ...(props.recentActivity ?? []).map((row) => row.district),
    ]);
});

const schoolOptions = computed(() => {
    const selectedDistrict = filters.value.district;

    const schoolsFromStats = (props.schoolStats ?? [])
        .filter((row) => selectedDistrict === 'all' || row.district === selectedDistrict)
        .map((row) => row.school_name);

    const schoolsFromRecent = (props.recentActivity ?? [])
        .filter((row) => selectedDistrict === 'all' || row.district === selectedDistrict)
        .map((row) => row.school_name);

    return uniqueSortedValues([...schoolsFromStats, ...schoolsFromRecent]);
});

const actionOptions = computed(() => {
    return uniqueSortedValues((props.recentActivity ?? []).map((row) => row.action));
});

watch(
    () => filters.value.district,
    () => {
        filters.value.school = 'all';
    },
);

watch(
    schoolOptions,
    (options) => {
        if (filters.value.school !== 'all' && !options.includes(filters.value.school)) {
            filters.value.school = 'all';
        }
    },
);

const filteredDistrictStats = computed(() => {
    const selectedDistrict = filters.value.district;

    return (props.districtStats ?? []).filter((row) => {
        return selectedDistrict === 'all' || row.district === selectedDistrict;
    });
});

const filteredSchoolStats = computed(() => {
    const selectedDistrict = filters.value.district;
    const selectedSchool = filters.value.school;

    return (props.schoolStats ?? []).filter((row) => {
        const districtMatch = selectedDistrict === 'all' || row.district === selectedDistrict;
        const schoolMatch = selectedSchool === 'all' || row.school_name === selectedSchool;

        return districtMatch && schoolMatch;
    });
});

const isWithinTimeWindow = (dateValue) => {
    const selectedWindow = filters.value.timeWindow;

    if (selectedWindow === 'all') {
        return true;
    }

    const parsedDate = parseActivityDate(dateValue);
    if (!parsedDate) {
        return false;
    }

    const days = selectedWindow === '7d' ? 7 : 30;
    const thresholdDate = new Date();
    thresholdDate.setHours(0, 0, 0, 0);
    thresholdDate.setDate(thresholdDate.getDate() - days + 1);

    return parsedDate >= thresholdDate;
};

const filteredRecentActivity = computed(() => {
    const selectedDistrict = filters.value.district;
    const selectedSchool = filters.value.school;
    const selectedAction = filters.value.action;
    const searchText = normalizeString(filters.value.search).toLowerCase();

    return (props.recentActivity ?? []).filter((row) => {
        const districtMatch = selectedDistrict === 'all' || row.district === selectedDistrict;
        const schoolMatch = selectedSchool === 'all' || row.school_name === selectedSchool;
        const actionMatch = selectedAction === 'all' || row.action === selectedAction;
        const timeMatch = isWithinTimeWindow(row.time);

        if (!districtMatch || !schoolMatch || !actionMatch || !timeMatch) {
            return false;
        }

        if (!searchText) {
            return true;
        }

        const searchable = [
            row.user_name,
            row.user_email,
            row.target,
            row.action,
            row.district,
            row.school_name,
        ]
            .map(normalizeString)
            .join(' ')
            .toLowerCase();

        return searchable.includes(searchText);
    });
});

const filteredTopFolders = computed(() => {
    const query = normalizeString(filters.value.search).toLowerCase();

    if (!query) {
        return props.topFolders ?? [];
    }

    return (props.topFolders ?? []).filter((row) => {
        return normalizeString(row.folder_name).toLowerCase().includes(query);
    });
});

const filteredTopFiles = computed(() => {
    const query = normalizeString(filters.value.search).toLowerCase();

    if (!query) {
        return props.topFiles ?? [];
    }

    return (props.topFiles ?? []).filter((row) => {
        return normalizeString(row.file_title).toLowerCase().includes(query);
    });
});

const districtChartRows = computed(() => {
    return [...filteredDistrictStats.value]
        .sort((a, b) => (b.total_actions ?? 0) - (a.total_actions ?? 0))
        .slice(0, 8);
});

const maxDistrictTotal = computed(() => {
    return Math.max(1, ...districtChartRows.value.map((row) => Number(row.total_actions) || 0));
});

const schoolChartRows = computed(() => {
    return [...filteredSchoolStats.value]
        .sort((a, b) => (b.total_actions ?? 0) - (a.total_actions ?? 0))
        .slice(0, 8);
});

const maxSchoolTotal = computed(() => {
    return Math.max(1, ...schoolChartRows.value.map((row) => Number(row.total_actions) || 0));
});

const roleChartRows = computed(() => {
    return [...(props.usersByRole ?? [])].sort((a, b) => (b.total ?? 0) - (a.total ?? 0));
});

const maxRoleTotal = computed(() => {
    return Math.max(1, ...roleChartRows.value.map((row) => Number(row.total) || 0));
});

const roleToneClass = (index) => {
    const tones = ['bg-blue-600', 'bg-emerald-500', 'bg-amber-500', 'bg-slate-500', 'bg-rose-500'];
    return tones[index % tones.length];
};

const trendBuckets = computed(() => {
    const bucketCount = filters.value.timeWindow === '30d' ? 10 : 7;
    const bucketMap = new Map();
    const bucketLabels = [];

    for (let index = bucketCount - 1; index >= 0; index -= 1) {
        const date = new Date();
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() - index);

        const key = date.toISOString().slice(0, 10);
        const label = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });

        bucketMap.set(key, 0);
        bucketLabels.push({ key, label });
    }

    filteredRecentActivity.value.forEach((row) => {
        const parsedDate = parseActivityDate(row.time);
        if (!parsedDate) {
            return;
        }

        const key = parsedDate.toISOString().slice(0, 10);

        if (bucketMap.has(key)) {
            bucketMap.set(key, (bucketMap.get(key) || 0) + 1);
        }
    });

    return bucketLabels.map((bucket) => ({
        key: bucket.key,
        label: bucket.label,
        total: bucketMap.get(bucket.key) || 0,
    }));
});

const maxTrendTotal = computed(() => {
    return Math.max(1, ...trendBuckets.value.map((bucket) => bucket.total));
});

const filterSummary = computed(() => {
    return {
        districts: filteredDistrictStats.value.length,
        schools: filteredSchoolStats.value.length,
        activityRows: filteredRecentActivity.value.length,
        folders: filteredTopFolders.value.length,
        files: filteredTopFiles.value.length,
    };
});

const hasActiveFilters = computed(() => {
    return filters.value.district !== 'all'
        || filters.value.school !== 'all'
        || filters.value.action !== 'all'
        || filters.value.timeWindow !== 'all'
        || normalizeString(filters.value.search) !== '';
});

const toCsvCell = (value) => {
    const stringValue = value === null || value === undefined ? '' : String(value);
    const escaped = stringValue.replace(/"/g, '""');

    return /[",\r\n]/.test(escaped) ? `"${escaped}"` : escaped;
};

const toCsvLine = (values) => values.map((value) => toCsvCell(value)).join(',');

const cleanExportValue = (value, fallback = '') => {
    const normalized = normalizeString(value).replace(/\s+/g, ' ');
    return normalized === '' ? fallback : normalized;
};

const downloadFilteredCsv = () => {
    const now = new Date();
    const csvRows = [
        ['TIME', 'ACTION', 'USER', 'DISTRICT', 'SCHOOL', 'TARGET'],
        ...filteredRecentActivity.value.map((row) => [
            cleanExportValue(row.time, 'N/A'),
            cleanExportValue(row.action, 'N/A'),
            cleanExportValue(row.user_name, 'Unknown User'),
            cleanExportValue(row.district, 'Unknown District'),
            cleanExportValue(row.school_name, 'Unknown School'),
            cleanExportValue(row.target, 'N/A'),
        ]),
    ];

    const csvContent = `\uFEFF${csvRows.map((row) => toCsvLine(row)).join('\r\n')}`;
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const blobUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = blobUrl;
    link.download = `recent-activity-${now.toISOString().slice(0, 19).replace(/:/g, '-').replace('T', '_')}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(blobUrl);
};

const resetFilters = () => {
    filters.value = {
        district: 'all',
        school: 'all',
        action: 'all',
        timeWindow: 'all',
        search: '',
    };
};
</script>

<template>
    <Head title="Analytics" />

    <AdminLayout>
        <div class="mb-6 flex flex-wrap justify-end gap-2">
            <button
                type="button"
                class="action-btn-primary"
                @click="downloadFilteredCsv"
            >
                Download Recent Activity CSV
            </button>
            <button
                type="button"
                class="action-btn-secondary"
                :disabled="!hasActiveFilters"
                @click="resetFilters"
            >
                Clear Filters
            </button>
        </div>

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

        <section class="mb-6 panel p-5 md:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b-2 border-slate-100 pb-4">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Filters</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500">Apply filters to charts and tables for focused analysis.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Districts: {{ filterSummary.districts }}</span>
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Schools: {{ filterSummary.schools }}</span>
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Activity Rows: {{ filterSummary.activityRows }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label class="field-label" for="filter_district">District</label>
                    <select id="filter_district" v-model="filters.district" class="field-input">
                        <option value="all">All Districts</option>
                        <option v-for="district in districtOptions" :key="district" :value="district">{{ district }}</option>
                    </select>
                </div>

                <div>
                    <label class="field-label" for="filter_school">School</label>
                    <select id="filter_school" v-model="filters.school" class="field-input">
                        <option value="all">All Schools</option>
                        <option v-for="school in schoolOptions" :key="school" :value="school">{{ school }}</option>
                    </select>
                </div>

                <div>
                    <label class="field-label" for="filter_action">Activity Action</label>
                    <select id="filter_action" v-model="filters.action" class="field-input">
                        <option value="all">All Actions</option>
                        <option v-for="action in actionOptions" :key="action" :value="action">{{ action }}</option>
                    </select>
                </div>

                <div>
                    <label class="field-label" for="filter_time">Time Window</label>
                    <select id="filter_time" v-model="filters.timeWindow" class="field-input">
                        <option value="all">All Time (Loaded Data)</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                </div>

                <div>
                    <label class="field-label" for="filter_search">Search</label>
                    <input
                        id="filter_search"
                        v-model="filters.search"
                        type="text"
                        class="field-input"
                        placeholder="Search user, file, folder, district..."
                    />
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-6">
                <AppSectionCard title="District Activity Chart" subtitle="Top districts by total tracked actions.">
                    <AppEmptyState v-if="districtChartRows.length === 0" title="No district data for selected filters." />

                    <div v-else class="space-y-3">
                        <div v-for="row in districtChartRows" :key="row.district" class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-black text-slate-900">{{ row.district }}</p>
                                <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">{{ row.total_actions }}</p>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                <div
                                    class="h-full rounded-full bg-blue-600"
                                    :style="{ width: `${(Math.max(0, Number(row.total_actions) || 0) / maxDistrictTotal) * 100}%` }"
                                />
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold text-slate-500">
                                <span>Folder Opens: {{ row.folders_opened }}</span>
                                <span>File Opens: {{ row.files_opened }}</span>
                                <span>Downloads: {{ row.files_downloaded }}</span>
                            </div>
                        </div>
                    </div>
                </AppSectionCard>
            </div>

            <div class="xl:col-span-6">
                <AppSectionCard title="School Activity Chart" subtitle="Most active schools for the current filter context.">
                    <AppEmptyState v-if="schoolChartRows.length === 0" title="No school data for selected filters." />

                    <div v-else class="space-y-3">
                        <div v-for="row in schoolChartRows" :key="`${row.district}-${row.school_name}`" class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900">{{ row.school_name }}</p>
                                    <p class="truncate text-xs font-semibold text-slate-500">{{ row.district }}</p>
                                </div>
                                <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">{{ row.total_actions }}</p>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    :style="{ width: `${(Math.max(0, Number(row.total_actions) || 0) / maxSchoolTotal) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </AppSectionCard>
            </div>

            <div class="xl:col-span-7">
                <AppSectionCard title="Activity Trend" subtitle="Recent activity volume for the selected window.">
                    <AppEmptyState v-if="trendBuckets.length === 0" title="No activity trend data." />

                    <div v-else class="space-y-3">
                        <div class="grid grid-cols-7 gap-2 md:grid-cols-10">
                            <div
                                v-for="bucket in trendBuckets"
                                :key="bucket.key"
                                class="flex flex-col items-center justify-end rounded-xl border border-slate-200 bg-slate-50 px-2 py-3"
                            >
                                <div class="mb-2 flex h-24 w-full items-end">
                                    <div
                                        class="w-full rounded-md bg-blue-500"
                                        :style="{ height: `${Math.max(8, (bucket.total / maxTrendTotal) * 100)}%` }"
                                    />
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">{{ bucket.total }}</p>
                                <p class="mt-1 text-[10px] font-semibold text-slate-400">{{ bucket.label }}</p>
                            </div>
                        </div>
                    </div>
                </AppSectionCard>
            </div>

            <div class="xl:col-span-5">
                <AppSectionCard title="Role Distribution" subtitle="Account totals by role.">
                    <AppEmptyState v-if="roleChartRows.length === 0" title="No user roles found." />

                    <div v-else class="space-y-3">
                        <div v-for="(row, index) in roleChartRows" :key="row.role" class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <p class="text-sm font-black uppercase text-slate-900">{{ row.role }}</p>
                                <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">{{ row.total }}</p>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                <div
                                    class="h-full rounded-full"
                                    :class="roleToneClass(index)"
                                    :style="{ width: `${(Math.max(0, Number(row.total) || 0) / maxRoleTotal) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </AppSectionCard>
            </div>

            <div class="xl:col-span-6">
                <AppSectionCard title="Most Opened Folders" subtitle="Top folders by interaction count.">
                    <div v-if="filteredTopFolders.length" class="space-y-2">
                        <div v-for="row in filteredTopFolders" :key="row.folder_name" class="panel-muted flex items-center justify-between border px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ row.folder_name }}</p>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                    <AppEmptyState v-else title="No folder openings for current filters." />
                </AppSectionCard>
            </div>

            <div class="xl:col-span-6">
                <AppSectionCard title="Most Opened Files" subtitle="Top files by open or download activity.">
                    <div v-if="filteredTopFiles.length" class="space-y-2">
                        <div v-for="row in filteredTopFiles" :key="row.file_title" class="panel-muted flex items-center justify-between border px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ row.file_title }}</p>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ row.total }}</p>
                        </div>
                    </div>
                    <AppEmptyState v-else title="No file interactions for current filters." />
                </AppSectionCard>
            </div>

            <div class="xl:col-span-6">
                <AppSectionCard title="By District" subtitle="Detailed district breakdown for current filters.">
                    <AppDataTable :headers="districtHeaders" :rows="filteredDistrictStats" min-width="min-w-[620px]" empty-text="No district activity yet.">
                        <tr v-for="row in filteredDistrictStats" :key="row.district">
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
                <AppSectionCard title="By School" subtitle="Detailed school breakdown for current filters.">
                    <AppDataTable :headers="schoolHeaders" :rows="filteredSchoolStats" min-width="min-w-[700px]" empty-text="No school activity yet.">
                        <tr v-for="row in filteredSchoolStats" :key="`${row.district}-${row.school_name}`">
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

            <div class="xl:col-span-12">
                <AppSectionCard title="Recent Activity" subtitle="Use the dropdown toggle to show or hide activity table data.">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-black text-slate-900">Recent Activity Data</p>
                            <p class="text-xs font-medium text-slate-500">{{ filteredRecentActivity.length }} rows match your current filters.</p>
                        </div>
                        <button
                            type="button"
                            class="action-btn-secondary"
                            @click="showRecentActivity = !showRecentActivity"
                        >
                            {{ showRecentActivity ? 'Hide Data' : 'Show Data' }}
                        </button>
                    </div>

                    <div v-if="showRecentActivity">
                        <AppDataTable :headers="activityHeaders" :rows="filteredRecentActivity" min-width="min-w-[1000px]" empty-text="No tracked activity yet.">
                            <tr v-for="row in filteredRecentActivity" :key="row.id">
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
                    </div>

                    <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center">
                        <p class="text-sm font-semibold text-slate-600">Recent activity is hidden.</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Use the Show Data dropdown button to expand the table.</p>
                    </div>
                </AppSectionCard>
            </div>
        </section>
    </AdminLayout>
</template>
