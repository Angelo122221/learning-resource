<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;

const profileForm = useForm({
    name: user?.name || '',
    email: user?.email || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({
    password: '',
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'));
};

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        onSuccess: () => passwordForm.reset(),
    });
};

const deleteAccount = () => {
    if (!confirm('Delete your account permanently?')) return;

    deleteForm.delete(route('profile.destroy'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Profile" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <section class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">Update Profile</h3>
                    <form @submit.prevent="updateProfile" class="mt-4 space-y-4">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput id="name" class="mt-1 block w-full" v-model="profileForm.name" required autofocus />
                            <InputError class="mt-2" :message="profileForm.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" class="mt-1 block w-full" v-model="profileForm.email" required />
                            <InputError class="mt-2" :message="profileForm.errors.email" />
                        </div>

                        <PrimaryButton :disabled="profileForm.processing">Save</PrimaryButton>
                    </form>
                </section>

                <section class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">Update Password</h3>
                    <form @submit.prevent="updatePassword" class="mt-4 space-y-4">
                        <div>
                            <InputLabel for="current_password" value="Current Password" />
                            <TextInput id="current_password" type="password" class="mt-1 block w-full" v-model="passwordForm.current_password" required />
                            <InputError class="mt-2" :message="passwordForm.errors.current_password" />
                        </div>
                        <div>
                            <InputLabel for="password" value="New Password" />
                            <TextInput id="password" type="password" class="mt-1 block w-full" v-model="passwordForm.password" required />
                            <InputError class="mt-2" :message="passwordForm.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="passwordForm.password_confirmation" required />
                            <InputError class="mt-2" :message="passwordForm.errors.password_confirmation" />
                        </div>
                        <PrimaryButton :disabled="passwordForm.processing">Update Password</PrimaryButton>
                    </form>
                </section>

                <section class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">Delete Account</h3>
                    <p class="mt-1 text-sm text-gray-600">Enter your password to permanently delete your account.</p>
                    <form @submit.prevent="deleteAccount" class="mt-4 space-y-4">
                        <div>
                            <InputLabel for="delete_password" value="Password" />
                            <TextInput id="delete_password" type="password" class="mt-1 block w-full" v-model="deleteForm.password" required />
                            <InputError class="mt-2" :message="deleteForm.errors.password" />
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-700">
                            Delete Account
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
