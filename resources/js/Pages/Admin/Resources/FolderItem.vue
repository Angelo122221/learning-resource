<script>
// We need this name defined so the component can call itself recursively for subfolders
export default { name: 'FolderItem' }
</script>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    folder: Object
});

// Explicitly define the events we emit up to Index.vue
defineEmits(['delete', 'lock']);

const isOpen = ref(false);
</script>

<template>
    <div class="mb-3 w-full">
        <div class="flex items-center justify-between p-5 bg-white rounded-2xl shadow-sm hover:bg-slate-50 transition-all cursor-pointer group outline-none focus:outline-none" @click="isOpen = !isOpen">
            <div class="flex items-center gap-4">
                <span class="text-3xl transition-transform duration-200" :class="{'rotate-90': isOpen}">
                    {{ isOpen ? '📂' : '📁' }}
                </span>
                <div class="flex flex-col">
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-black text-slate-800 uppercase tracking-tight group-hover:text-blue-600 transition-colors">{{ folder.name }}</span>
                        <span v-if="folder.is_locked" class="text-xs bg-slate-800 text-white px-2 py-1 rounded-md font-bold uppercase tracking-widest">Locked</span>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2" @click.stop>
                <button @click="$emit('lock', 'folder', folder.id)" 
                    type="button"
                    class="px-5 py-2 text-xs font-black uppercase tracking-widest rounded-xl transition-all outline-none focus:outline-none" 
                    :class="folder.is_locked ? 'bg-slate-800 text-white hover:bg-slate-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    {{ folder.is_locked ? 'Unlock' : 'Lock' }}
                </button>
                <button @click="$emit('delete', 'folder', folder.id)" 
                    type="button"
                    class="px-5 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all outline-none focus:outline-none">
                    Delete
                </button>
            </div>
        </div>

        <div v-if="isOpen" class="ml-8 mt-3 pl-6 border-l-4 border-slate-200 space-y-3">
            
            <FolderItem 
                v-for="sub in folder.children_recursive" 
                :key="sub.id" 
                :folder="sub" 
                @delete="(type, id) => $emit('delete', type, id)"
                @lock="(type, id) => $emit('lock', type, id)"
            />

            <div v-for="file in folder.files" :key="file.id" 
                class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm outline-none">
                
                <div class="flex items-center gap-4">
                    <span class="text-2xl" v-if="file.is_locked">🔒</span>
                    <span class="text-2xl" v-else-if="file.file_type === 'pdf'">📕</span>
                    <span class="text-2xl" v-else>📄</span>
                    
                    <span class="text-md font-bold text-slate-700" :class="{'line-through text-slate-400': file.is_locked}">
                        {{ file.title }}
                        <span class="text-xs text-slate-400 ml-2 uppercase tracking-widest">{{ file.file_type }}</span>
                    </span>
                </div>
                
                <div class="flex gap-2">
                    <button @click="$emit('lock', 'file', file.id)" 
                        type="button"
                        class="px-5 py-2 text-xs font-black uppercase tracking-widest rounded-xl transition-all outline-none focus:outline-none" 
                        :class="file.is_locked ? 'bg-slate-800 text-white hover:bg-slate-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        {{ file.is_locked ? 'Unlock' : 'Lock' }}
                    </button>
                    <button @click="$emit('delete', 'file', file.id)" 
                        type="button"
                        class="px-5 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all outline-none focus:outline-none">
                        Delete
                    </button>
                </div>
            </div>

            <div v-if="!folder.children_recursive?.length && !folder.files?.length" 
                class="p-4 text-xs text-slate-400 font-bold uppercase italic tracking-widest rounded-xl bg-slate-50">
                Directory is empty
            </div>
        </div>
    </div>
</template>
