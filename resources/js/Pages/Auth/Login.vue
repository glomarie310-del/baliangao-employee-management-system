<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen bg-slate-100">
        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- Left section -->
            <div
                class="relative hidden overflow-hidden bg-gradient-to-br from-blue-950 via-blue-800 to-blue-600 lg:flex lg:flex-col lg:justify-between"
            >
                <div
                    class="absolute -left-24 top-20 h-72 w-72 rounded-full bg-blue-400/20"
                ></div>

                <div
                    class="absolute -bottom-24 right-0 h-96 w-96 rounded-full bg-white/10"
                ></div>

                <div class="relative z-10 p-12">
                    <div class="flex items-center gap-4">
                        <img
                            src="/images/baliangao-logo.png"
                            alt="Municipality of Baliangao Logo"
                            class="h-20 w-20 rounded-full bg-white p-1 shadow-lg"
                        />

                        <div>
                            <p class="text-sm font-semibold uppercase tracking-widest text-blue-200">
                                Republic of the Philippines
                            </p>

                            <h2 class="text-xl font-bold text-white">
                                Municipality of Baliangao
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 px-12">
                    <p class="text-sm font-bold uppercase tracking-[0.3em] text-blue-200">
                        Human Resource Management Office
                    </p>

                    <h1 class="mt-5 max-w-xl text-5xl font-black leading-tight text-white">
                        Baliangao Employee Management System
                    </h1>

                    <p class="mt-6 max-w-lg text-lg leading-8 text-blue-100">
                        A secure and organized platform for maintaining regular
                        employee records, employment information, leave balances,
                        and official documents.
                    </p>
                </div>

                <div class="relative z-10 p-12 text-sm text-blue-200">
                    © {{ new Date().getFullYear() }} Municipality of Baliangao
                </div>
            </div>

            <!-- Login section -->
            <div class="flex items-center justify-center px-6 py-12 sm:px-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 text-center lg:hidden">
                        <img
                            src="/images/baliangao-logo.png"
                            alt="Municipality of Baliangao Logo"
                            class="mx-auto h-24 w-24 rounded-full bg-white p-1 shadow-md"
                        />

                        <h1 class="mt-4 text-2xl font-black text-blue-950">
                            Baliangao Employee Management System
                        </h1>

                        <p class="mt-2 text-sm text-gray-500">
                            Human Resource Management Office
                        </p>
                    </div>

                    <div class="rounded-3xl bg-white p-8 shadow-xl sm:p-10">
                        <div class="mb-8">
                            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">
                                Welcome back
                            </p>

                            <h2 class="mt-2 text-3xl font-black text-blue-950">
                                Sign in to BEMS
                            </h2>

                            <p class="mt-2 text-sm text-gray-500">
                                Enter your account credentials to continue.
                            </p>
                        </div>

                        <div
                            v-if="status"
                            class="mb-5 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                        >
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel
                                    for="email"
                                    value="Email address"
                                    class="font-bold text-gray-700"
                                />

                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-2 block w-full rounded-xl"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Enter your email"
                                />

                                <InputError
                                    class="mt-2"
                                    :message="form.errors.email"
                                />
                            </div>

                            <div class="mt-5">
                                <InputLabel
                                    for="password"
                                    value="Password"
                                    class="font-bold text-gray-700"
                                />

                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-2 block w-full rounded-xl"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                />

                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password"
                                />
                            </div>

                            <div class="mt-5 flex items-center justify-between">
                                <label class="flex items-center">
                                    <Checkbox
                                        v-model:checked="form.remember"
                                        name="remember"
                                    />

                                    <span class="ml-2 text-sm text-gray-600">
                                        Remember me
                                    </span>
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm font-bold text-blue-600 hover:text-blue-800"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            <PrimaryButton
                                class="mt-8 flex w-full justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-bold hover:bg-blue-800"
                                :class="{ 'opacity-50': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Signing in...' : 'Sign in' }}
                            </PrimaryButton>
                        </form>

                        <div class="mt-8 border-t border-gray-100 pt-6 text-center">
                            <p class="text-xs leading-5 text-gray-400">
                                Authorized HRMO personnel and municipal employees
                                only. Activity within this system may be monitored.
                            </p>
                        </div>
                    </div>

                    <p class="mt-6 text-center text-xs text-gray-400">
                        BEMS · Municipality of Baliangao, Misamis Occidental
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>