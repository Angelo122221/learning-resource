<script setup>
import AppSectionCard from '@/Components/AppSectionCard.vue';
import InputError from '@/Components/InputError.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    announcements: {
        type: Array,
        default: () => [],
    },
});

const announcementForm = useForm({
    title: '',
    content: '',
    image: null,
});

const announcementEditForm = useForm({
    title: '',
    content: '',
    image: null,
    remove_image: false,
});

const editingAnnouncementId = ref(null);
const announcementFileInput = ref(null);

const submitAnnouncement = () => {
    announcementForm.post('/admin/announcements', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            announcementForm.reset();
            if (announcementFileInput.value) announcementFileInput.value.value = '';
        },
    });
};

const startEditAnnouncement = (announcement) => {
    editingAnnouncementId.value = announcement.id;
    announcementEditForm.title = announcement.title;
    announcementEditForm.content = announcement.content;
    announcementEditForm.image = null;
    announcementEditForm.remove_image = false;
};

const cancelEditAnnouncement = () => {
    editingAnnouncementId.value = null;
    announcementEditForm.reset();
};

const updateAnnouncement = (announcementId) => {
    announcementEditForm
        .transform((data) => ({
            ...data,
            _method: 'patch',
        }))
        .post(`/admin/announcements/${announcementId}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => cancelEditAnnouncement(),
        });
};

const deleteAnnouncement = (announcementId) => {
    if (!confirm('Delete this announcement?')) return;

    router.delete(`/admin/announcements/${announcementId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin Announcements" />

    <AdminLayout>
        <AppSectionCard title="Announcements" subtitle="Publish text updates for teachers, with an optional image.">
            <form @submit.prevent="submitAnnouncement" class="space-y-4 border-b border-slate-200 pb-5">
                <div>
                    <label class="field-label" for="announcement_title">Title</label>
                    <input id="announcement_title" v-model="announcementForm.title" type="text" class="field-input" placeholder="Enrollment timeline update" />
                    <InputError :message="announcementForm.errors.title" />
                </div>

                <div>
                    <label class="field-label" for="announcement_content">Announcement Text</label>
                    <textarea
                        id="announcement_content"
                        v-model="announcementForm.content"
                        rows="4"
                        class="field-input"
                        placeholder="Enter your full announcement for teachers and staff."
                    />
                    <InputError :message="announcementForm.errors.content" />
                </div>

                <div>
                    <label class="field-label" for="announcement_image">Announcement Image</label>
                    <input
                        id="announcement_image"
                        ref="announcementFileInput"
                        type="file"
                        accept="image/*"
                        class="field-input"
                        @change="announcementForm.image = $event.target.files[0]"
                    />
                    <InputError :message="announcementForm.errors.image" />
                </div>

                <button type="submit" class="action-btn-primary w-full justify-center">Publish Announcement</button>
            </form>

            <div v-if="announcements.length" class="custom-scrollbar mt-5 max-h-80 space-y-3 overflow-y-auto pr-1">
                <article v-for="announcement in announcements" :key="announcement.id" class="panel-muted border p-4">
                    <template v-if="editingAnnouncementId === announcement.id">
                        <form @submit.prevent="updateAnnouncement(announcement.id)" class="space-y-3">
                            <input v-model="announcementEditForm.title" type="text" class="field-input" placeholder="Announcement title" />
                            <textarea v-model="announcementEditForm.content" rows="4" class="field-input" placeholder="Announcement text" />
                            <input
                                type="file"
                                accept="image/*"
                                class="field-input"
                                @change="announcementEditForm.image = $event.target.files[0]"
                            />
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                <input v-model="announcementEditForm.remove_image" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                Remove current image
                            </label>
                            <InputError :message="announcementEditForm.errors.title" />
                            <InputError :message="announcementEditForm.errors.content" />
                            <InputError :message="announcementEditForm.errors.image" />
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" class="action-btn-primary">Save</button>
                                <button type="button" class="action-btn-secondary" @click="cancelEditAnnouncement">Cancel</button>
                            </div>
                        </form>
                    </template>

                    <template v-else>
                        <img
                            v-if="announcement.image_path"
                            :src="`/storage/${announcement.image_path}`"
                            alt="Announcement image"
                            class="mb-3 h-40 w-full rounded-xl object-cover"
                        />
                        <h3 class="text-base font-black text-slate-900">{{ announcement.title }}</h3>
                        <p class="mt-2 whitespace-pre-line text-sm font-medium leading-6 text-slate-600">{{ announcement.content }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" class="action-btn-secondary" @click="startEditAnnouncement(announcement)">Edit</button>
                            <button type="button" class="action-btn-danger" @click="deleteAnnouncement(announcement.id)">Delete</button>
                        </div>
                    </template>
                </article>
            </div>
        </AppSectionCard>
    </AdminLayout>
</template>
