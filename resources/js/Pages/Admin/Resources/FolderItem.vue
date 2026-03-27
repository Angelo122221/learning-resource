<script>
export default { name: 'FolderItem' };
</script>

<script setup>
import AppStatusBadge from '@/Components/AppStatusBadge.vue';
import { ref } from 'vue';

defineProps({
    folder: Object,
});

defineEmits(['delete', 'lock']);

const isOpen = ref(false);
</script>

<template>
    <div class="w-full">
        <button
            type="button"
            class="panel-muted flex w-full items-center justify-between border p-5 text-left transition-all hover:border-blue-200 hover:bg-white"
            @click="isOpen = !isOpen"
        >
            <div class="flex items-center gap-4">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-xs font-black uppercase tracking-[0.18em] text-white">
                    {{ isOpen ? 'OPEN' : 'DIR' }}
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-base font-black uppercase tracking-tight text-slate-900">{{ folder.name }}</span>
                        <AppStatusBadge v-if="folder.is_locked" label="Locked" variant="locked" />
                    </div>
                    <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                        {{ folder.children_recursive?.length || 0 }} subfolders / {{ folder.files?.length || 0 }} files
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2" @click.stop>
                <button
                    type="button"
                    class="action-btn-secondary"
                    :class="folder.is_locked ? 'bg-slate-900 text-white hover:bg-slate-700' : ''"
                    @click="$emit('lock', 'folder', folder.id)"
                >
                    {{ folder.is_locked ? 'Unlock' : 'Lock' }}
                </button>
                <button
                    type="button"
                    class="action-btn-danger"
                    @click="$emit('delete', 'folder', folder.id)"
                >
                    Delete
                </button>
            </div>
        </button>

        <div v-if="isOpen" class="mt-3 space-y-3 border-l-4 border-slate-200 pl-5 md:ml-6 md:pl-6">
            <FolderItem
                v-for="sub in folder.children_recursive"
                :key="sub.id"
                :folder="sub"
                @delete="(type, id) => $emit('delete', type, id)"
                @lock="(type, id) => $emit('lock', type, id)"
            />

            <div
                v-for="file in folder.files"
                :key="file.id"
                class="panel-muted flex items-center justify-between border p-4"
            >
                <div class="flex min-w-0 items-center gap-4">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-900 text-[11px] font-black uppercase tracking-[0.18em] text-white">
                        {{ file.file_type === 'pdf' ? 'PDF' : 'FILE' }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-slate-800" :class="{ 'line-through text-slate-400': file.is_locked }">
                            {{ file.title }}
                        </p>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ file.file_type }}
                        </p>
                    </div>
                    <AppStatusBadge v-if="file.is_locked" label="Locked" variant="locked" />
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="action-btn-secondary"
                        :class="file.is_locked ? 'bg-slate-900 text-white hover:bg-slate-700' : ''"
                        @click="$emit('lock', 'file', file.id)"
                    >
                        {{ file.is_locked ? 'Unlock' : 'Lock' }}
                    </button>
                    <button
                        type="button"
                        class="action-btn-danger"
                        @click="$emit('delete', 'file', file.id)"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div
                v-if="!folder.children_recursive?.length && !folder.files?.length"
                class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 text-xs font-black uppercase tracking-[0.18em] text-slate-400"
            >
                Directory is empty
            </div>
        </div>
    </div>
</template>
