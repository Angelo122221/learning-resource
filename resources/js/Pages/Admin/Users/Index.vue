<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    stats: Object,
});

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'teacher',
    district: '',
    school_name: '',
});

const editForm = useForm({
    name: '',
    email: '',
    role: 'teacher',
    district: '',
    school_name: '',
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const editingUserId = ref(null);
const passwordUserId = ref(null);

const sortedUsers = computed(() =>
    [...props.users].sort((a, b) => {
        if (a.is_admin !== b.is_admin) return a.is_admin ? -1 : 1;
        return a.name.localeCompare(b.name);
    }),
);

const submitCreate = () => {
    createForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const startEdit = (user) => {
    editingUserId.value = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role || (user.is_admin ? 'admin' : 'teacher');
    editForm.district = user.district || '';
    editForm.school_name = user.school_name || '';
};

const cancelEdit = () => {
    editingUserId.value = null;
    editForm.reset();
};

const saveEdit = (userId) => {
    editForm.patch(`/admin/users/${userId}`, {
        preserveScroll: true,
        onSuccess: () => cancelEdit(),
    });
};

const startPasswordChange = (userId) => {
    passwordUserId.value = userId;
    passwordForm.reset();
};

const cancelPasswordChange = () => {
    passwordUserId.value = null;
    passwordForm.reset();
};

const savePassword = (userId) => {
    passwordForm.patch(`/admin/users/${userId}/password`, {
        preserveScroll: true,
        onSuccess: () => cancelPasswordChange(),
    });
};

const deleteUser = (userId) => {
    if (!confirm('Delete this account?')) return;

    router.delete(`/admin/users/${userId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="min-h-screen bg-[#f3f6f9] p-6 md:p-10 text-slate-900">
        <div class="max-w-[1600px] mx-auto">
            <header class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between mb-8 pb-6 border-b-4 border-slate-200">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">UM</div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black italic tracking-tighter uppercase leading-none">
                            User <span class="text-emerald-600">Management</span>
                        </h1>
                        <p class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">DepEd Accounts and Password Control</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-white border-2 border-slate-100 rounded-2xl px-4 py-3 flex gap-5 text-center">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Users</p>
                            <p class="text-xl font-black">{{ stats.total_users }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Teachers</p>
                            <p class="text-xl font-black">{{ stats.total_teachers }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Admins</p>
                            <p class="text-xl font-black">{{ stats.total_admins }}</p>
                        </div>
                    </div>
                    <Link href="/admin/resources" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
                        Resources
                    </Link>
                    <a href="/admin/analytics" class="bg-slate-900 text-white px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all">
                        Analytics
                    </a>
                    <Link href="/logout" method="post" as="button" class="bg-red-100 text-red-700 px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                        Log Out
                    </Link>
                </div>
            </header>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
                <section class="xl:col-span-4 bg-white border-2 border-slate-100 rounded-[2rem] p-6 shadow-sm">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-4">Create User / Teacher</h2>
                    <p class="text-xs text-slate-500 mb-4">Only DepEd emails are allowed. Teachers must include district and school.</p>
                    <form @submit.prevent="submitCreate" class="space-y-3">
                        <input v-model="createForm.name" type="text" placeholder="Full Name" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                        <input v-model="createForm.email" type="email" placeholder="deped email (e.g. juan@deped.gov.ph)" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                        <select v-model="createForm.role" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                            <option value="teacher">Teacher</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input v-model="createForm.password" type="password" placeholder="Password" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                            <input v-model="createForm.password_confirmation" type="password" placeholder="Confirm Password" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                        </div>

                        <input v-model="createForm.district" type="text" :required="createForm.role === 'teacher'" placeholder="District" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">
                        <input v-model="createForm.school_name" type="text" :required="createForm.role === 'teacher'" placeholder="School Name" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-semibold bg-slate-50">

                        <button type="submit" class="w-full bg-emerald-600 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all">
                            Create Account
                        </button>
                    </form>
                </section>

                <section class="xl:col-span-8 bg-white border-2 border-slate-100 rounded-[2rem] p-6 shadow-sm">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-4">Accounts</h2>

                    <div class="space-y-4 max-h-[72vh] overflow-y-auto custom-scrollbar pr-2">
                        <article v-for="user in sortedUsers" :key="user.id" class="border-2 border-slate-100 rounded-xl p-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-base font-black uppercase">
                                        {{ user.name }}
                                        <span v-if="user.is_admin" class="text-[10px] bg-slate-900 text-white px-2 py-1 rounded ml-2">Admin</span>
                                        <span v-else-if="user.role === 'teacher'" class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded ml-2">Teacher</span>
                                        <span v-else class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded ml-2">User</span>
                                    </p>
                                    <p class="text-sm text-slate-500 font-semibold">{{ user.email }}</p>
                                    <p class="text-xs text-slate-400 font-semibold mt-1">
                                        {{ user.district || 'No district' }} • {{ user.school_name || 'No school' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="startEdit(user)" class="bg-slate-900 text-white px-3 py-2 rounded-lg text-xs font-black uppercase">Edit</button>
                                    <button type="button" @click="startPasswordChange(user.id)" class="bg-amber-500 text-white px-3 py-2 rounded-lg text-xs font-black uppercase">Password</button>
                                    <button type="button" @click="deleteUser(user.id)" class="bg-red-100 text-red-700 px-3 py-2 rounded-lg text-xs font-black uppercase">Delete</button>
                                </div>
                            </div>

                            <form v-if="editingUserId === user.id" @submit.prevent="saveEdit(user.id)" class="mt-4 border-t border-slate-100 pt-4 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <input v-model="editForm.name" type="text" placeholder="Name" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                    <input v-model="editForm.email" type="email" placeholder="DepEd email" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <select v-model="editForm.role" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                        <option value="teacher">Teacher</option>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <input v-model="editForm.district" :required="editForm.role === 'teacher'" type="text" placeholder="District" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                    <input v-model="editForm.school_name" :required="editForm.role === 'teacher'" type="text" placeholder="School Name" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-black uppercase">Save</button>
                                    <button type="button" @click="cancelEdit" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-xs font-black uppercase">Cancel</button>
                                </div>
                            </form>

                            <form v-if="passwordUserId === user.id" @submit.prevent="savePassword(user.id)" class="mt-4 border-t border-slate-100 pt-4 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <input v-model="passwordForm.password" type="password" placeholder="New Password" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                    <input v-model="passwordForm.password_confirmation" type="password" placeholder="Confirm Password" class="w-full border-2 border-slate-200 rounded-lg p-2 text-sm">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-lg text-xs font-black uppercase">Update Password</button>
                                    <button type="button" @click="cancelPasswordChange" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-xs font-black uppercase">Cancel</button>
                                </div>
                            </form>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
</style>
