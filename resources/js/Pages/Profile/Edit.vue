<script setup>
import AppFormSection from '@/Components/AppFormSection.vue';
import AppPageHeader from '@/Components/AppPageHeader.vue';
import DangerButton from '@/Components/DangerButton.vue';
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

        <AppPageHeader
            badge="PR"
            title="Profile"
            subtitle="Keep your account details accurate so the system can identify your role and access correctly."
        />

        <div class="mx-auto max-w-5xl space-y-6">
            <AppFormSection title="Update Profile" subtitle="Edit your core account details.">
                <form @submit.prevent="updateProfile" class="space-y-5">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="profileForm.name" class="mt-1 block w-full" required autofocus />
                        <InputError class="mt-2" :message="profileForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="profileForm.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="profileForm.errors.email" />
                    </div>

                    <PrimaryButton :disabled="profileForm.processing">Save</PrimaryButton>
                </form>
            </AppFormSection>

            <AppFormSection title="Update Password" subtitle="Choose a strong password and keep your account secure.">
                <form @submit.prevent="updatePassword" class="space-y-5">
                    <div>
                        <InputLabel for="current_password" value="Current Password" />
                        <TextInput id="current_password" v-model="passwordForm.current_password" type="password" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="passwordForm.errors.current_password" />
                    </div>
                    <div>
                        <InputLabel for="password" value="New Password" />
                        <TextInput id="password" v-model="passwordForm.password" type="password" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="passwordForm.errors.password" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="passwordForm.errors.password_confirmation" />
                    </div>
                    <PrimaryButton :disabled="passwordForm.processing">Update Password</PrimaryButton>
                </form>
            </AppFormSection>

            <AppFormSection title="Delete Account" subtitle="This action is permanent and cannot be undone.">
                <form @submit.prevent="deleteAccount" class="space-y-5">
                    <div>
                        <InputLabel for="delete_password" value="Password" />
                        <TextInput id="delete_password" v-model="deleteForm.password" type="password" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="deleteForm.errors.password" />
                    </div>
                    <DangerButton type="submit">
                        Delete Account
                    </DangerButton>
                </form>
            </AppFormSection>
        </div>
    </AuthenticatedLayout>
</template>
