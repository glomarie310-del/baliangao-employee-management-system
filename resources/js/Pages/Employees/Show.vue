<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    employee: {
        type: Object,
        required: true,
    },
});

const formatDate = (date) => {
    if (!date) {
        return 'Not provided';
    }

    return new Intl.DateTimeFormat('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date(date));
};

const calculateAge = (birthDate) => {
    if (!birthDate) {
        return 'Not provided';
    }

    const today = new Date();
    const birth = new Date(birthDate);

    let age = today.getFullYear() - birth.getFullYear();

    const monthDifference = today.getMonth() - birth.getMonth();

    if (
        monthDifference < 0 ||
        (monthDifference === 0 && today.getDate() < birth.getDate())
    ) {
        age--;
    }

    return `${age} years old`;
};

const statusClass = (status) => {
    const classes = {
        Active: 'bg-emerald-100 text-emerald-700',
        Inactive: 'bg-gray-200 text-gray-700',
        Retired: 'bg-amber-100 text-amber-700',
        Separated: 'bg-red-100 text-red-700',
    };

    return classes[status] ?? 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="employee.full_name" />

    <div class="min-h-screen bg-slate-100">
        <header class="border-b border-gray-200 bg-white shadow-sm print:hidden">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
                <div class="flex items-center gap-4">
                    <img
                        src="/images/baliangao-logo.png"
                        alt="Municipality of Baliangao"
                        class="h-14 w-14 rounded-full bg-white p-1 shadow"
                    />

                    <div>
                        <h1 class="text-xl font-black text-blue-950">
                            Baliangao Employee Management System
                        </h1>

                        <p class="text-sm text-gray-500">
                            Human Resource Management Office
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="route('employees.index')"
                        class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-50"
                    >
                        ← Masterlist
                    </Link>

                    <button
                        type="button"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800"
                        @click="window.print()"
                    >
                        Print Profile
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-5 py-8 print:max-w-none print:p-0">
            <section class="overflow-hidden rounded-3xl bg-white shadow-sm print:shadow-none">
                <div class="bg-gradient-to-r from-blue-950 via-blue-800 to-blue-600 p-8 text-white">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border-4 border-white/30 bg-white text-4xl font-black text-blue-800 shadow-lg">
                            {{ employee.first_name.charAt(0) }}
                            {{ employee.last_name.charAt(0) }}
                        </div>

                        <div class="flex-1">
                            <p class="text-sm font-bold uppercase tracking-[0.25em] text-blue-200">
                                Regular Employee
                            </p>

                            <h2 class="mt-2 text-3xl font-black sm:text-4xl">
                                {{ employee.full_name }}
                            </h2>

                            <p class="mt-2 text-lg text-blue-100">
                                {{ employee.position }}
                            </p>

                            <p class="mt-1 text-sm text-blue-200">
                                {{ employee.department }}
                            </p>
                        </div>

                        <div class="sm:text-right">
                            <span
                                class="inline-flex rounded-full px-4 py-2 text-sm font-bold"
                                :class="statusClass(employee.status)"
                            >
                                {{ employee.status }}
                            </span>

                            <p class="mt-3 text-sm text-blue-100">
                                Employee No.
                            </p>

                            <p class="text-xl font-black">
                                {{ employee.employee_number }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-7 sm:p-9">
                    <section>
                        <div class="mb-5 flex items-center gap-3 border-b border-blue-100 pb-3">
                            <div class="rounded-xl bg-blue-100 p-2 text-blue-700">
                                👤
                            </div>

                            <h3 class="text-xl font-black text-blue-950">
                                Personal Information
                            </h3>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    First Name
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.first_name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Middle Name
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.middle_name || 'Not provided' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Last Name
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.last_name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Suffix
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.suffix || 'None' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Sex
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.sex }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Birth Date
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ formatDate(employee.birth_date) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Age
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ calculateAge(employee.birth_date) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Civil Status
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.civil_status || 'Not provided' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Contact Number
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.contact_number || 'Not provided' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Email
                                </p>
                                <p class="mt-1 break-all font-semibold text-gray-800">
                                    {{ employee.email || 'Not provided' }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Residential Address
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.address || 'Not provided' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="mt-10">
                        <div class="mb-5 flex items-center gap-3 border-b border-blue-100 pb-3">
                            <div class="rounded-xl bg-blue-100 p-2 text-blue-700">
                                🏛️
                            </div>

                            <h3 class="text-xl font-black text-blue-950">
                                Employment Information
                            </h3>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Department/Office
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.department }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Position
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.position }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Employment Status
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.employment_status }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Salary Grade
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    SG {{ employee.salary_grade || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Step
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ employee.step || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Date Hired
                                </p>
                                <p class="mt-1 font-semibold text-gray-800">
                                    {{ formatDate(employee.date_hired) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="mt-10">
                        <div class="mb-5 flex items-center gap-3 border-b border-blue-100 pb-3">
                            <div class="rounded-xl bg-blue-100 p-2 text-blue-700">
                                🪪
                            </div>

                            <h3 class="text-xl font-black text-blue-950">
                                Government Identification Numbers
                            </h3>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-gray-400">
                                    GSIS Number
                                </p>
                                <p class="mt-2 font-bold text-gray-800">
                                    {{ employee.gsis_number || 'Not provided' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-gray-400">
                                    Pag-IBIG Number
                                </p>
                                <p class="mt-2 font-bold text-gray-800">
                                    {{ employee.pagibig_number || 'Not provided' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-gray-400">
                                    PhilHealth Number
                                </p>
                                <p class="mt-2 font-bold text-gray-800">
                                    {{
                                        employee.philhealth_number ||
                                        'Not provided'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-gray-400">
                                    TIN
                                </p>
                                <p class="mt-2 font-bold text-gray-800">
                                    {{ employee.tin_number || 'Not provided' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="mt-12 hidden border-t pt-8 print:block">
                        <div class="grid grid-cols-2 gap-24 pt-16 text-center">
                            <div>
                                <div class="border-t border-black pt-2">
                                    Employee Signature
                                </div>
                            </div>

                            <div>
                                <div class="border-t border-black pt-2">
                                    HRMO Officer
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4;
        margin: 12mm;
    }

    body {
        background: white !important;
    }
}
</style>