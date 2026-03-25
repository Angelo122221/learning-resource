<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Thanks for signing up. Before getting started, verify your email address by clicking the link we just emailed to you.
        </div>

        <div v-if="props.status === 'verification-link-sent'" class="mb-4 text-sm font-medium text-green-600">
            A new verification link has been sent to your email address.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Resend Verification Email
                </PrimaryButton>

                <Link :href="route('logout')" method="post" as="button" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900">
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
