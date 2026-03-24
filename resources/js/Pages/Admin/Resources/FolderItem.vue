<script setup>
import { ref } from 'vue';

const props = defineProps({
    folder: Object
});

const emit = defineEmits(['delete', 'lock']);

const isOpen = ref(false);
</script>

<template>
    <div class="mb-4 w-full">
        <div @click="isOpen = !isOpen" class="flex items-center justify-between p-6 bg-white border-2 border-slate-200 rounded-3xl hover:border-blue-500 cursor-pointer shadow-md transition-all group">
            <div class="flex items-center gap-6">
                <span class="text-4xl transition-transform duration-200" :class="{'rotate-90': isOpen}">
                    {{ isOpen ? '📂' : '📁' }}
                </span>
                <div class="flex flex-col">
                    <span class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ folder.name }}</span>
                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1">
                        {{ folder.children_recursive?.length || 0 }} Folders • {{ folder.files?.length || 0 }} Files
                    </span>
                </div>
            </div>
            <button @click.stop="emit('delete', 'folder', folder.id)" class="opacity-0 group-hover:opacity-100 p-4 text-slate-300 hover:text-red-500 transition-all text-2xl">
                🗑️
            </button>
        </div>

        <div v-if="isOpen" class="ml-10 mt-4 pl-8 border-l-4 border-slate-200 space-y-4">
            
            <FolderItem 
                v-for="sub in folder.children_recursive" 
                :key="sub.id" 
                :folder="sub" 
                @delete="(t, id) => emit('delete', t, id)"
                @lock="(id) => emit('lock', id)"
            />

            <div v-for="file in folder.files" :key="file.id" class="flex items-center justify-between bg-white border-2 border-slate-100 p-5 rounded-2xl shadow-sm hover:border-blue-200 transition-all group/file">
                <div class="flex items-center gap-5">
                    <span class="text-3xl">{{ file.is_locked ? '🔒' : '📄' }}</span>
                    <span class="text-lg font-bold text-slate-700">{{ file.title }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <button @click.stop="emit('lock', file.id)" 
                        :class="file.is_locked ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                        class="text-sm font-black uppercase px-4 py-2 rounded-xl transition-colors">
                        {{ file.is_locked ? 'Locked' : 'Lock' }}
                    </button>
                    <button @click.stop="emit('delete', 'file', file.id)" class="text-slate-300 hover:text-red-500 px-3 text-2xl font-bold opacity-0 group-hover/file:opacity-100 transition-opacity">
                        ✕
                    </button>
                </div>
            </div>

            <div v-if="!folder.children_recursive?.length && !folder.files?.length" class="p-5 text-sm text-slate-400 font-bold uppercase italic tracking-widest text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50">
                Empty Directory
            </div>
        </div>
    </div>
</template>