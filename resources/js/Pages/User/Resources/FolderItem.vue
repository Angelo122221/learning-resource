<script>
export default { name: 'UserFolderItem' }
</script>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    folder: Object,
    // Determines if this is a top-level folder (Card) or a nested subfolder (Accordion)
    isRoot: {
        type: Boolean,
        default: true 
    }
});

const isOpen = ref(false);       // State for subfolder accordions
const showModal = ref(false);    // State for root folder modal

const trackFolderOpen = async () => {
    try {
        await axios.post(`/resources/folders/${props.folder.id}/open`);
    } catch (error) {
        // Tracking should not interrupt navigation.
    }
};

const handleFolderAction = () => {
    if (props.folder.is_locked) return;
    
    if (props.isRoot) {
        showModal.value = true;
        void trackFolderOpen();
    } else {
        const willOpen = !isOpen.value;
        isOpen.value = !isOpen.value;

        if (willOpen) {
            void trackFolderOpen();
        }
    }
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <div v-if="isRoot" class="w-full h-full">
        <div @click="handleFolderAction" 
            :class="folder.is_locked ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'bg-white hover:border-blue-400 hover:shadow-xl hover:-translate-y-1 cursor-pointer shadow-md group'"
            class="flex flex-col p-8 border-2 border-slate-200 rounded-[2.5rem] transition-all h-full relative overflow-hidden">
            
            <div class="flex justify-between items-start mb-6">
                <span class="text-6xl transition-transform duration-300 group-hover:scale-110" :class="{'grayscale opacity-50': folder.is_locked}">
                    {{ folder.is_locked ? '🔒' : '📁' }}
                </span>
                <span v-if="folder.is_locked" class="bg-slate-800 text-white text-[10px] px-3 py-1.5 rounded-lg font-black uppercase tracking-widest">Locked</span>
            </div>
            
            <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                {{ folder.name }}
            </h3>
            
            <div v-if="!folder.is_locked" class="mt-auto pt-5 border-t-2 border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    {{ folder.children_recursive?.length || 0 }} Folders
                </span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    {{ folder.files?.length || 0 }} Files
                </span>
            </div>
            <div v-else class="mt-auto pt-5 border-t-2 border-slate-100">
                <span class="text-xs font-bold text-red-400 uppercase tracking-widest">Access Restricted</span>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-12 bg-slate-900/60 backdrop-blur-sm" @click.self="closeModal">
                <div class="bg-white rounded-[2.5rem] w-full max-w-5xl max-h-full flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    
                    <div class="flex items-center justify-between p-8 md:p-10 border-b-2 border-slate-100 shrink-0">
                        <div class="flex items-center gap-5">
                            <span class="text-5xl">📂</span>
                            <div>
                                <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tight">{{ folder.name }}</h2>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1">Directory Contents</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="w-14 h-14 bg-slate-100 hover:bg-red-500 hover:text-white rounded-2xl flex items-center justify-center text-xl font-bold transition-all text-slate-500">
                            ✕
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 md:p-10 custom-scrollbar bg-slate-50/50 rounded-b-[2.5rem]">
                        
                        <div v-if="folder.children_recursive?.length" class="mb-10">
                            <h4 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4 ml-2">Subfolders</h4>
                            <div class="space-y-3">
                                <UserFolderItem v-for="sub in folder.children_recursive" :key="sub.id" :folder="sub" :is-root="false" />
                            </div>
                        </div>

                        <div v-if="folder.files?.length">
                            <h4 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4 ml-2">Files</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="file in folder.files" :key="file.id" 
                                    class="flex items-center justify-between bg-white border-2 border-slate-200 p-5 rounded-2xl shadow-sm transition-all"
                                    :class="file.is_locked ? 'opacity-60 bg-slate-50' : 'hover:border-blue-300 hover:shadow-md'">
                                    
                                    <div class="flex items-center gap-4 overflow-hidden">
                                        <span class="text-3xl shrink-0">{{ file.is_locked ? '🔒' : '📄' }}</span>
                                        <span class="text-base font-bold text-slate-700 truncate" :class="{'line-through text-slate-400': file.is_locked}">{{ file.title }}</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span v-if="file.is_locked" class="text-xs font-black text-slate-400 uppercase px-3 py-2">Locked</span>
                                        <template v-else>
                                            <a :href="`/resources/preview/${file.id}`" target="_blank"
                                                class="bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-black uppercase px-4 py-2 rounded-lg transition-colors shadow-sm">
                                                Preview
                                            </a>
                                            <a :href="`/resources/download/${file.id}`" target="_blank"
                                                class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-xs font-black uppercase px-4 py-2 rounded-lg transition-colors shadow-sm">
                                                Download
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!folder.children_recursive?.length && !folder.files?.length" class="p-16 text-center border-4 border-dashed border-slate-200 rounded-[2rem] bg-white">
                            <span class="text-6xl block mb-6 opacity-30">📭</span>
                            <span class="text-sm text-slate-400 font-black uppercase italic tracking-widest">Directory is empty</span>
                        </div>

                    </div>
                </div>
            </div>
        </Teleport>
    </div>

    <div v-else class="mb-3 w-full">
        <div @click="handleFolderAction" 
            :class="folder.is_locked ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'bg-white hover:border-blue-400 cursor-pointer shadow-sm group'"
            class="flex items-center justify-between p-5 border-2 border-slate-200 rounded-2xl transition-all">
            
            <div class="flex items-center gap-5">
                <span class="text-3xl transition-transform duration-200" :class="{'rotate-90': isOpen, 'grayscale opacity-50': folder.is_locked}">
                    {{ isOpen ? '📂' : '📁' }}
                </span>
                <div class="flex flex-col">
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-black text-slate-800 uppercase tracking-tight">{{ folder.name }}</span>
                        <span v-if="folder.is_locked" class="text-[10px] bg-slate-800 text-white px-2 py-1 rounded-md font-bold uppercase tracking-widest">Locked</span>
                    </div>
                    <span v-if="!folder.is_locked" class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                        {{ folder.children_recursive?.length || 0 }} Folders • {{ folder.files?.length || 0 }} Files
                    </span>
                </div>
            </div>
        </div>

        <div v-if="isOpen && !folder.is_locked" class="ml-8 mt-3 pl-6 border-l-4 border-slate-200 space-y-3">
            
            <UserFolderItem v-for="sub in folder.children_recursive" :key="sub.id" :folder="sub" :is-root="false" />

            <div v-for="file in folder.files" :key="file.id" 
                class="flex items-center justify-between bg-white border-2 border-slate-100 p-4 rounded-xl shadow-sm transition-all"
                :class="file.is_locked ? 'opacity-60 bg-slate-50' : 'hover:border-blue-200'">
                
                <div class="flex items-center gap-4">
                    <span class="text-2xl">{{ file.is_locked ? '🔒' : '📄' }}</span>
                    <span class="text-sm font-bold text-slate-700" :class="{'line-through text-slate-400': file.is_locked}">{{ file.title }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <span v-if="file.is_locked" class="text-xs font-black text-slate-400 uppercase px-3 py-1">Locked</span>
                    <template v-else>
                        <a :href="`/resources/preview/${file.id}`" target="_blank"
                            class="bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-black uppercase px-4 py-2 rounded-lg transition-colors shadow-sm">
                            Preview
                        </a>
                        <a :href="`/resources/download/${file.id}`" target="_blank"
                            class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-xs font-black uppercase px-4 py-2 rounded-lg transition-colors shadow-sm">
                            Download
                        </a>
                    </template>
                </div>
            </div>

            <div v-if="!folder.children_recursive?.length && !folder.files?.length" class="p-4 text-xs text-slate-400 font-bold uppercase italic tracking-widest text-center border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                Empty Directory
            </div>
        </div>
    </div>
</template>
