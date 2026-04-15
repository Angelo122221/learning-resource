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

const truncateLabel = (value, maxLength = 12) => {
    const normalized = normalizeString(value);
    if (normalized.length <= maxLength) {
        return normalized;
    }

    return `${normalized.slice(0, Math.max(1, maxLength - 1))}…`;
};

const schoolBarRows = computed(() => {
    return schoolChartRows.value.map((row) => {
        const total = Math.max(0, Number(row.total_actions) || 0);

        return {
            ...row,
            total,
        };
    });
});

const schoolChartWidth = 520;
const schoolChartHeight = 220;
const schoolChartPaddingTop = 18;
const schoolChartPaddingRight = 14;
const schoolChartPaddingBottom = 44;
const schoolChartPaddingLeft = 34;

const schoolGridLines = computed(() => {
    const segments = 4;
    const drawableHeight = schoolChartHeight - schoolChartPaddingTop - schoolChartPaddingBottom;

    return Array.from({ length: segments + 1 }, (_, index) => {
        const ratio = index / segments;
        const y = schoolChartPaddingTop + (ratio * drawableHeight);
        const value = Math.round(maxSchoolTotal.value * (1 - ratio));

        return {
            key: `school-line-${index}`,
            y: Number(y.toFixed(2)),
            value,
        };
    });
});

const schoolBars = computed(() => {
    const rows = schoolBarRows.value;
    if (rows.length === 0) {
        return [];
    }

    const drawableWidth = schoolChartWidth - schoolChartPaddingLeft - schoolChartPaddingRight;
    const drawableHeight = schoolChartHeight - schoolChartPaddingTop - schoolChartPaddingBottom;
    const slotWidth = drawableWidth / rows.length;
    const barWidth = Math.min(44, slotWidth * 0.62);

    return rows.map((row, index) => {
        const x = schoolChartPaddingLeft + (index * slotWidth) + ((slotWidth - barWidth) / 2);
        const height = Math.max(8, (row.total / maxSchoolTotal.value) * drawableHeight);
        const y = schoolChartHeight - schoolChartPaddingBottom - height;

        return {
            ...row,
            x: Number(x.toFixed(2)),
            y: Number(y.toFixed(2)),
            width: Number(barWidth.toFixed(2)),
            height: Number(height.toFixed(2)),
            centerX: Number((x + (barWidth / 2)).toFixed(2)),
            shortLabel: truncateLabel(row.school_name, 10),
        };
    });
});

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

const districtBarRows = computed(() => {
    return districtChartRows.value.map((row) => {
        const total = Math.max(0, Number(row.total_actions) || 0);

        return {
            ...row,
            total,
            percent: (total / maxDistrictTotal.value) * 100,
        };
    });
});

const trendChartWidth = 720;
const trendChartHeight = 240;
const trendChartPaddingX = 42;
const trendChartPaddingY = 20;

const trendLinePoints = computed(() => {
    const buckets = trendBuckets.value;
    if (buckets.length === 0) {
        return [];
    }

    const drawableWidth = trendChartWidth - (trendChartPaddingX * 2);
    const drawableHeight = trendChartHeight - (trendChartPaddingY * 2);

    return buckets.map((bucket, index) => {
        const ratio = buckets.length === 1 ? 0.5 : index / (buckets.length - 1);
        const x = trendChartPaddingX + (ratio * drawableWidth);
        const y = trendChartHeight - trendChartPaddingY - ((bucket.total / maxTrendTotal.value) * drawableHeight);

        return {
            ...bucket,
            x: Number(x.toFixed(2)),
            y: Number(y.toFixed(2)),
        };
    });
});

const trendLinePath = computed(() => {
    if (trendLinePoints.value.length === 0) {
        return '';
    }

    return trendLinePoints.value
        .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`)
        .join(' ');
});

const trendAreaPath = computed(() => {
    if (trendLinePoints.value.length === 0) {
        return '';
    }

    const firstPoint = trendLinePoints.value[0];
    const lastPoint = trendLinePoints.value[trendLinePoints.value.length - 1];
    const baselineY = trendChartHeight - trendChartPaddingY;

    return `${trendLinePath.value} L ${lastPoint.x} ${baselineY} L ${firstPoint.x} ${baselineY} Z`;
});

const trendGridLines = computed(() => {
    const segments = 4;
    const drawableHeight = trendChartHeight - (trendChartPaddingY * 2);

    return Array.from({ length: segments + 1 }, (_, index) => {
        const ratio = index / segments;
        const y = trendChartPaddingY + (ratio * drawableHeight);
        const value = Math.round(maxTrendTotal.value * (1 - ratio));

        return {
            key: `line-${index}`,
            y: Number(y.toFixed(2)),
            value,
        };
    });
});

const trendTableRows = computed(() => {
    return trendBuckets.value.map((bucket, index) => {
        const previous = index > 0 ? trendBuckets.value[index - 1].total : null;
        const delta = previous === null ? null : bucket.total - previous;

        return {
            ...bucket,
            delta,
        };
    });
});

const trendLabelGridStyle = computed(() => ({
    gridTemplateColumns: `repeat(${Math.max(1, trendBuckets.value.length)}, minmax(0, 1fr))`,
}));

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

const formatTrendDelta = (delta) => {
    if (delta === null) {
        return 'Baseline';
    }

    if (delta > 0) {
        return `+${delta}`;
    }

    return `${delta}`;
};

const trendDeltaClass = (delta) => {
    if (delta === null || delta === 0) {
        return 'text-slate-500';
    }

    return delta > 0 ? 'text-emerald-600' : 'text-rose-500';
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
        <div class="mx-auto w-full max-w-[1320px]">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Resource Usage</p>
                </div>

                <div class="flex flex-wrap gap-2">
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
            <div class="mb-4 grid grid-cols-1 gap-4 border-b-2 border-slate-100 pb-4 xl:grid-cols-[minmax(0,1fr)_auto]">
                <div class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-4 text-white">
                    <h2 class="text-xs font-black uppercase tracking-[0.24em] text-blue-100">Filters</h2>
                    <p class="mt-2 text-sm font-semibold text-white/95">Apply filters to keep every chart and table in sync.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Districts: {{ filterSummary.districts }}</span>
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Schools: {{ filterSummary.schools }}</span>
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Activity Rows: {{ filterSummary.activityRows }}</span>
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Folders: {{ filterSummary.folders }}</span>
                    <span class="rounded-xl bg-slate-100 px-3 py-2">Files: {{ filterSummary.files }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="field-label" for="filter_district">District</label>
                    <select id="filter_district" v-model="filters.district" class="field-input mt-1 bg-white">
                        <option value="all">All Districts</option>
                        <option v-for="district in districtOptions" :key="district" :value="district">{{ district }}</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="field-label" for="filter_school">School</label>
                    <select id="filter_school" v-model="filters.school" class="field-input mt-1 bg-white">
                        <option value="all">All Schools</option>
                        <option v-for="school in schoolOptions" :key="school" :value="school">{{ school }}</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="field-label" for="filter_action">Activity Action</label>
                    <select id="filter_action" v-model="filters.action" class="field-input mt-1 bg-white">
                        <option value="all">All Actions</option>
                        <option v-for="action in actionOptions" :key="action" :value="action">{{ action }}</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="field-label" for="filter_time">Time Window</label>
                    <select id="filter_time" v-model="filters.timeWindow" class="field-input mt-1 bg-white">
                        <option value="all">All Time (Loaded Data)</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3 xl:col-span-2">
                    <label class="field-label" for="filter_search">Search</label>
                    <input
                        id="filter_search"
                        v-model="filters.search"
                        type="text"
                        class="field-input mt-1 bg-white"
                        placeholder="Search user, file, folder, district..."
                    />
                </div>
            </div>
            </section>

            <section class="grid grid-cols-1 gap-6">
            <AppSectionCard class="flex h-[430px] flex-col" title="District Bar Chart" subtitle="Top districts by tracked actions with a matching ranking table.">
                <AppEmptyState v-if="districtBarRows.length === 0" title="No district data for selected filters." />

                <div v-else class="grid min-h-0 flex-1 gap-4 lg:grid-cols-[minmax(0,1.65fr)_minmax(0,1fr)]">
                    <div class="custom-scrollbar min-h-0 overflow-y-auto pr-1">
                        <div class="space-y-3">
                            <article v-for="row in districtBarRows" :key="row.district" class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-black text-slate-900">{{ row.district }}</p>
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">{{ row.total }}</p>
                                </div>

                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full rounded-full bg-blue-600" :style="{ width: `${Math.max(6, row.percent)}%` }" />
                                </div>

                                <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold text-slate-500">
                                    <span>Folder Opens: {{ row.folders_opened }}</span>
                                    <span>File Opens: {{ row.files_opened }}</span>
                                    <span>Downloads: {{ row.files_downloaded }}</span>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="panel-muted min-h-0 overflow-hidden border p-3">
                        <p class="mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">District Ranking Table</p>
                        <div class="custom-scrollbar h-full overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 bg-slate-100 text-left text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2">District</th>
                                        <th class="px-2 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in districtBarRows" :key="`rank-${row.district}`" class="border-b border-slate-200/70 text-slate-700">
                                        <td class="truncate px-2 py-2 font-semibold">{{ row.district }}</td>
                                        <td class="px-2 py-2 text-right font-black">{{ row.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </AppSectionCard>

            <AppSectionCard class="flex h-[430px] flex-col" title="Activity Line Chart" subtitle="Daily activity counts for the selected time window.">
                <AppEmptyState v-if="trendBuckets.length === 0" title="No activity trend data." />

                <div v-else class="flex min-h-0 flex-1 flex-col gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                        <svg class="h-52 w-full" :viewBox="`0 0 ${trendChartWidth} ${trendChartHeight}`" preserveAspectRatio="xMidYMid meet">
                            <defs>
                                <linearGradient id="trend-fill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.05" />
                                </linearGradient>
                            </defs>

                            <g>
                                <line
                                    v-for="line in trendGridLines"
                                    :key="line.key"
                                    :x1="trendChartPaddingX"
                                    :x2="trendChartWidth - trendChartPaddingX"
                                    :y1="line.y"
                                    :y2="line.y"
                                    stroke="#e2e8f0"
                                    stroke-width="1"
                                />
                                <text
                                    v-for="line in trendGridLines"
                                    :key="`label-${line.key}`"
                                    x="8"
                                    :y="line.y + 4"
                                    font-size="10"
                                    font-weight="700"
                                    fill="#64748b"
                                >
                                    {{ line.value }}
                                </text>
                            </g>

                            <path v-if="trendAreaPath" :d="trendAreaPath" fill="url(#trend-fill)" />
                            <path v-if="trendLinePath" :d="trendLinePath" fill="none" stroke="#2563eb" stroke-width="3" />

                            <circle
                                v-for="point in trendLinePoints"
                                :key="`point-${point.key}`"
                                :cx="point.x"
                                :cy="point.y"
                                r="3.5"
                                fill="#1d4ed8"
                            />
                        </svg>

                        <div class="mt-2 grid gap-1 text-[10px] font-semibold text-slate-500" :style="trendLabelGridStyle">
                            <p v-for="bucket in trendBuckets" :key="`bucket-label-${bucket.key}`" class="truncate text-center">
                                {{ bucket.label }}
                            </p>
                        </div>
                    </div>

                    <div class="panel-muted min-h-0 flex-1 overflow-hidden border p-3">
                        <p class="mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Line Chart Table</p>
                        <div class="custom-scrollbar h-full overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 bg-slate-100 text-left text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2">Date</th>
                                        <th class="px-2 py-2 text-right">Total</th>
                                        <th class="px-2 py-2 text-right">Change</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in trendTableRows" :key="`trend-row-${row.key}`" class="border-b border-slate-200/70 text-slate-700">
                                        <td class="px-2 py-2 font-semibold">{{ row.label }}</td>
                                        <td class="px-2 py-2 text-right font-black">{{ row.total }}</td>
                                        <td class="px-2 py-2 text-right font-black" :class="trendDeltaClass(row.delta)">{{ formatTrendDelta(row.delta) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </AppSectionCard>

            <AppSectionCard class="flex h-[430px] flex-col" title="School Bar Chart" subtitle="Most active schools for the current filter context.">
                <AppEmptyState v-if="schoolBarRows.length === 0" title="No school data for selected filters." />

                <div v-else class="custom-scrollbar min-h-0 flex-1 overflow-y-auto pr-1">
                    <div class="flex min-h-0 flex-col gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                        <div class="custom-scrollbar overflow-x-auto">
                        <svg class="h-52 min-w-[560px] w-full" :viewBox="`0 0 ${schoolChartWidth} ${schoolChartHeight}`" preserveAspectRatio="xMidYMid meet">
                            <g>
                                <line
                                    v-for="line in schoolGridLines"
                                    :key="line.key"
                                    :x1="schoolChartPaddingLeft"
                                    :x2="schoolChartWidth - schoolChartPaddingRight"
                                    :y1="line.y"
                                    :y2="line.y"
                                    stroke="#e2e8f0"
                                    stroke-width="1"
                                />
                                <text
                                    v-for="line in schoolGridLines"
                                    :key="`school-label-${line.key}`"
                                    x="6"
                                    :y="line.y + 4"
                                    font-size="10"
                                    font-weight="700"
                                    fill="#64748b"
                                >
                                    {{ line.value }}
                                </text>
                            </g>

                            <g v-for="bar in schoolBars" :key="`school-bar-${bar.district}-${bar.school_name}`">
                                <title>{{ bar.school_name }} ({{ bar.district }}) - {{ bar.total }}</title>
                                <rect
                                    :x="bar.x"
                                    :y="bar.y"
                                    :width="bar.width"
                                    :height="bar.height"
                                    rx="6"
                                    fill="#10b981"
                                />
                                <text
                                    :x="bar.centerX"
                                    :y="bar.y - 6"
                                    text-anchor="middle"
                                    font-size="10"
                                    font-weight="800"
                                    fill="#334155"
                                >
                                    {{ bar.total }}
                                </text>
                                <text
                                    :x="bar.centerX"
                                    :y="schoolChartHeight - 12"
                                    text-anchor="middle"
                                    font-size="9"
                                    font-weight="700"
                                    fill="#64748b"
                                >
                                    {{ bar.shortLabel }}
                                </text>
                            </g>
                        </svg>
                        </div>
                    </div>

                    <div class="panel-muted min-h-0 flex-1 overflow-hidden border p-3">
                        <p class="mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">School Ranking Table</p>
                        <div class="custom-scrollbar h-full overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 bg-slate-100 text-left text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2">School</th>
                                        <th class="px-2 py-2">District</th>
                                        <th class="px-2 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in schoolBarRows" :key="`school-row-${row.district}-${row.school_name}`" class="border-b border-slate-200/70 text-slate-700">
                                        <td class="truncate px-2 py-2 font-semibold">{{ row.school_name }}</td>
                                        <td class="truncate px-2 py-2 text-slate-500">{{ row.district }}</td>
                                        <td class="px-2 py-2 text-right font-black">{{ row.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            </AppSectionCard>

            <AppSectionCard class="flex h-[430px] flex-col" title="Top Content Tables" subtitle="Most opened folders and files for current filters.">
                <div class="custom-scrollbar min-h-0 flex-1 space-y-4 overflow-y-auto pr-1">
                    <div class="panel-muted border p-3">
                        <p class="mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Folders</p>
                        <table class="w-full text-xs">
                            <thead class="text-left text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                                <tr>
                                    <th class="px-2 py-2">Folder</th>
                                    <th class="px-2 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in filteredTopFolders" :key="row.folder_name" class="border-t border-slate-200/70 text-slate-700">
                                    <td class="truncate px-2 py-2 font-semibold">{{ row.folder_name }}</td>
                                    <td class="px-2 py-2 text-right font-black">{{ row.total }}</td>
                                </tr>
                                <tr v-if="filteredTopFolders.length === 0">
                                    <td colspan="2" class="px-2 py-3 text-center text-xs font-semibold text-slate-500">No folder openings for current filters.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="panel-muted border p-3">
                        <p class="mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Files</p>
                        <table class="w-full text-xs">
                            <thead class="text-left text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                                <tr>
                                    <th class="px-2 py-2">File</th>
                                    <th class="px-2 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in filteredTopFiles" :key="row.file_title" class="border-t border-slate-200/70 text-slate-700">
                                    <td class="truncate px-2 py-2 font-semibold">{{ row.file_title }}</td>
                                    <td class="px-2 py-2 text-right font-black">{{ row.total }}</td>
                                </tr>
                                <tr v-if="filteredTopFiles.length === 0">
                                    <td colspan="2" class="px-2 py-3 text-center text-xs font-semibold text-slate-500">No file interactions for current filters.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </AppSectionCard>

            <AppSectionCard class="flex h-[470px] flex-col" title="By District" subtitle="Detailed district breakdown for current filters.">
                <div class="min-h-0 flex-1 overflow-hidden">
                    <div class="custom-scrollbar h-full overflow-y-auto">
                        <AppDataTable :headers="districtHeaders" :rows="filteredDistrictStats" min-width="min-w-[620px]" empty-text="No district activity yet.">
                            <tr v-for="row in filteredDistrictStats" :key="row.district">
                                <td class="font-bold">{{ row.district }}</td>
                                <td>{{ row.total_actions }}</td>
                                <td>{{ row.folders_opened }}</td>
                                <td>{{ row.files_opened }}</td>
                                <td>{{ row.files_downloaded }}</td>
                            </tr>
                        </AppDataTable>
                    </div>
                </div>
            </AppSectionCard>

            <AppSectionCard class="flex h-[470px] flex-col" title="By School" subtitle="Detailed school breakdown for current filters.">
                <div class="min-h-0 flex-1 overflow-hidden">
                    <div class="custom-scrollbar h-full overflow-y-auto">
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
                    </div>
                </div>
            </AppSectionCard>

            <AppSectionCard class="flex h-[560px] flex-col" title="Recent Activity" subtitle="Use the toggle to show or hide the detailed activity table.">
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

                <div class="min-h-0 flex-1 overflow-hidden">
                    <div v-if="showRecentActivity" class="custom-scrollbar h-full overflow-y-auto">
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

                    <div v-else class="flex h-full items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center">
                        <div>
                            <p class="text-sm font-semibold text-slate-600">Recent activity is hidden.</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">Use the Show Data button to expand the table.</p>
                        </div>
                    </div>
                </div>
            </AppSectionCard>
            </section>
        </div>
    </AdminLayout>
</template>
