<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import FolderItem from './FolderItem.vue';

const props = defineProps({
    folders: Array,
    allFolders: Array,
    stats: Object
});

const folderForm = useForm({ name: '', parent_id: null });
const fileForm = useForm({ title: '', file: null, folder_id: '' });

const selectFolder = (id) => {
    folderForm.parent_id = id;
    fileForm.folder_id = id;
};

const submitFolder = () => folderForm.post('/admin/folders', { onSuccess: () => folderForm.reset('name') });
const submitFile = () => fileForm.post('/admin/files', { 
    onSuccess: () => {
        fileForm.reset();
        document.getElementById('file_input').value = "";
    } 
});

const deleteItem = (type, id) => {
    if (confirm(`Permanently delete this ${type}?`)) {
        router.delete(`/admin/${type}s/${id}`, { preserveScroll: true });
    }
};

const toggleLock = (id) => {
    router.patch(`/admin/files/${id}/toggle-lock`, {}, { preserveScroll: true });
};
</script>

<template>
    <div class="min-h-screen bg-[#F3F6F9] p-10 text-slate-900 font-sans antialiased">
        <div class="max-w-[1800px] mx-auto">
            
            <header class="flex justify-between items-end mb-12 pb-8 border-b-4 border-slate-200">
                <div class="flex items-center gap-6">
                     <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-3xl font-black shadow-lg">LR</div>
                     <div>
                        <h1 class="text-4xl font-black italic tracking-tighter uppercase leading-none">LEARNING <span class="text-blue-600">RESOURCES</span></h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">Resource Manager</p>
                    </div>
                </div>
                
                <div class="flex gap-12 bg-white px-10 py-5 rounded-3xl shadow-sm border-2 border-slate-100 text-center">
                    <div><p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Folders</p><p class="text-3xl font-black">{{ stats.total_folders }}</p></div>
                    <div class="w-[2px] bg-slate-100"></div>
                    <div><p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Files</p><p class="text-3xl font-black">{{ stats.total_files }}</p></div>
                </div>
            </header>

            <div class="grid grid-cols-12 gap-12 items-start">
                
                <div class="col-span-5">
                    <h2 class="text-lg font-black uppercase text-slate-400 tracking-[0.4em] mb-8 px-2 italic">Visual Explorer</h2>
                    
                    <div v-if="folders.length === 0" class="p-24 border-4 border-dashed border-slate-200 rounded-[3rem] text-center bg-white">
                        <span class="text-7xl block mb-6 opacity-30">📂</span>
                        <p class="text-lg text-slate-400 font-black uppercase tracking-widest italic">Drive is empty</p>
                    </div>

                    <div class="space-y-2">
                        <FolderItem 
                            v-for="folder in folders" 
                            :key="folder.id" 
                            :folder="folder" 
                            @delete="deleteItem" 
                            @lock="toggleLock"
                        />
                    </div>
                </div>

                <div class="col-span-7 grid grid-cols-2 gap-8 items-stretch sticky top-8">
                    
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border-2 border-slate-100 flex flex-col h-full">
                        <h2 class="text-lg font-black uppercase text-slate-400 mb-8 border-b-2 pb-4 tracking-widest text-center">Management</h2>
                        
                        <div class="flex-1 flex flex-col justify-between">
                            <form @submit.prevent="submitFolder" class="space-y-4 mb-8">
                                <label class="text-sm font-black uppercase text-slate-500 ml-2">Create Folder</label>
                                <input v-model="folderForm.name" type="text" placeholder="Folder Name..." class="w-full border-2 border-slate-200 rounded-2xl p-5 text-lg bg-slate-50 outline-none focus:border-blue-400 transition-all">
                                <select v-model="folderForm.parent_id" class="w-full border-2 border-slate-200 rounded-2xl p-5 text-lg bg-slate-50 font-bold text-slate-600 outline-none">
                                    <option :value="null">Root Directory</option>
                                    <option v-for="f in allFolders" :key="f.id" :value="f.id">{{ f.full_path }}</option>
                                </select>
                                <button class="w-full bg-slate-900 text-white py-5 rounded-2xl text-xl font-black uppercase hover:bg-blue-600 transition-all shadow-lg">+ Add Folder</button>
                            </form>

                            <div class="h-[2px] bg-slate-100 w-full mb-8"></div>

                            <form @submit.prevent="submitFile" class="space-y-4">
                                <label class="text-sm font-black uppercase text-slate-500 ml-2">Upload Asset</label>
                                <select v-model="fileForm.folder_id" class="w-full border-2 border-slate-200 rounded-2xl p-5 text-lg bg-slate-50 font-bold outline-none">
                                    <option value="">Select Target Folder</option>
                                    <option v-for="f in allFolders" :key="f.id" :value="f.id">{{ f.full_path }}</option>
                                </select>
                                <input v-model="fileForm.title" type="text" placeholder="File Title" class="w-full border-2 border-slate-200 rounded-2xl p-5 text-lg bg-slate-50 outline-none">
                                <input id="file_input" type="file" @input="fileForm.file = $event.target.files[0]" class="text-sm font-bold text-slate-400 w-full p-2">
                                <button class="w-full bg-blue-600 text-white py-6 rounded-[2rem] text-lg font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-xl shadow-blue-100">Upload to Drive</button>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white p-10 rounded-[2.5rem] border-2 border-slate-100 shadow-sm flex flex-col h-full">
                        <h2 class="text-lg font-black uppercase text-slate-400 mb-8 border-b-2 pb-4 tracking-widest">Quick Map Selection</h2>
                        
                        <div class="flex-1 overflow-y-auto min-h-0 space-y-3 custom-scrollbar pr-4 -mr-4">
                            <div 
                                v-for="f in allFolders" :key="f.id" 
                                @click="selectFolder(f.id)"
                                :class="folderForm.parent_id === f.id ? 'bg-blue-50 border-blue-400 shadow-md' : 'bg-slate-50 border-transparent hover:border-blue-200'"
                                class="flex items-center gap-5 p-5 rounded-2xl border-2 transition-all cursor-pointer overflow-hidden group"
                            >
                                <span class="text-3xl">📁</span>
                                <div class="flex flex-col truncate w-full">
                                    <span class="text-lg font-black text-slate-800 uppercase truncate group-hover:text-blue-600 transition-colors">{{ f.name }}</span>
                                    <span class="text-xs text-slate-400 font-bold uppercase truncate mt-1">{{ f.full_path }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 8px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
</style>