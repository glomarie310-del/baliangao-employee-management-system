<script setup>
import {
    Head,
    Link,
    router,
    useForm,
    usePage,
} from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    employee: {
        type: Object,
        default: null,
    },

    initialTab: {
        type: String,
        default: 'profile',
    },
});

const page = usePage();

const activeTab = ref(props.initialTab);
const leaveModalOpen = ref(false);

const successMessage = computed(
    () => page.props.flash?.success,
);

const errorMessage = computed(
    () => page.props.flash?.error,
);

const leaveTypes = [
    'Vacation Leave',
    'Mandatory/Forced Leave',
    'Sick Leave',
    'Maternity Leave',
    'Paternity Leave',
    'Special Privilege Leave',
    'Solo Parent Leave',
    'Study Leave',
    'VAWC Leave',
    'Rehabilitation Privilege',
    'Special Leave Benefits for Women',
    'Special Emergency Leave',
    'Adoption Leave',
    'Other',
];

const leaveForm = useForm({
    leave_type: '',
    date_from: '',
    date_to: '',
    number_of_days: '',
    reason: '',
    with_pay: true,
});

watch(
    () => [
        leaveForm.date_from,
        leaveForm.date_to,
    ],
    () => {
        if (
            ! leaveForm.date_from ||
            ! leaveForm.date_to
        ) {
            leaveForm.number_of_days = '';
            return;
        }

        const start = new Date(
            `${leaveForm.date_from}T00:00:00`
        );

        const end = new Date(
            `${leaveForm.date_to}T00:00:00`
        );

        if (end < start) {
            leaveForm.number_of_days = '';
            return;
        }

        let workingDays = 0;
        const current = new Date(start);

        while (current <= end) {
            const day = current.getDay();

            if (day !== 0 && day !== 6) {
                workingDays++;
            }

            current.setDate(
                current.getDate() + 1
            );
        }

        leaveForm.number_of_days = workingDays;
    },
);

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

const statusClass = (status) => {
    const classes = {
        Active: 'bg-emerald-100 text-emerald-700',
        Pending: 'bg-amber-100 text-amber-700',
        Approved: 'bg-emerald-100 text-emerald-700',
        Rejected: 'bg-red-100 text-red-700',
        Cancelled: 'bg-gray-200 text-gray-700',
        Valid: 'bg-emerald-100 text-emerald-700',
        'Expiring Soon': 'bg-amber-100 text-amber-700',
        Expired: 'bg-red-100 text-red-700',
    };

    return classes[status] ??
        'bg-gray-100 text-gray-700';
};

const openLeaveModal = () => {
    leaveForm.reset();
    leaveForm.clearErrors();
    leaveForm.with_pay = true;
    leaveModalOpen.value = true;
};

const closeLeaveModal = () => {
    leaveModalOpen.value = false;
    leaveForm.reset();
    leaveForm.clearErrors();
};

const submitLeave = () => {
    leaveForm.post(
        route('employee-portal.leaves.store'),
        {
            preserveScroll: true,
            onSuccess: closeLeaveModal,
        },
    );
};

const cancelLeave = (leave) => {
    if (
        ! window.confirm(
            'Cancel this pending leave application?',
        )
    ) {
        return;
    }

    router.patch(
        route(
            'employee-portal.leaves.cancel',
            leave.id,
        ),
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="Employee Self-Service" />

    <div class="min-h-screen bg-slate-100">
        <header class="border-b bg-white shadow-sm">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4"
            >
                <div class="flex items-center gap-4">
                    <img
                        src="/images/baliangao-logo.png"
                        alt="Baliangao Logo"
                        class="h-14 w-14 rounded-full bg-white p-1 shadow"
                    />

                    <div>
                        <h1 class="text-xl font-black text-blue-950">
                            Baliangao Employee Management System
                        </h1>

                        <p class="text-sm text-gray-500">
                            Employee Self-Service
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('dashboard')"
                    class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white"
                >
                    Dashboard
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8">
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <div
                v-if="errorMessage"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-700"
            >
                {{ errorMessage }}
            </div>

            <section
                v-if="!employee"
                class="rounded-3xl bg-white p-10 text-center shadow-sm"
            >
                <div class="text-5xl">
                    ⚠️
                </div>

                <h2 class="mt-5 text-2xl font-black text-blue-950">
                    Employee Record Not Linked
                </h2>

                <p class="mt-3 text-gray-600">
                    Contact the administrator to link your user
                    account to your employee record.
                </p>
            </section>

            <template v-else>
                <section
                    class="rounded-3xl bg-gradient-to-r from-blue-950 to-blue-600 p-8 text-white shadow-xl"
                >
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-blue-200"
                    >
                        Employee Self-Service
                    </p>

                    <h2 class="mt-2 text-3xl font-black">
                        {{ employee.full_name }}
                    </h2>

                    <p class="mt-2 text-blue-100">
                        {{ employee.position }}
                    </p>

                    <p class="mt-1 text-sm text-blue-200">
                        {{ employee.department }}
                    </p>
                </section>

                <nav
                    class="mt-7 grid gap-2 rounded-2xl bg-white p-3 shadow-sm sm:grid-cols-3"
                >
                    <Link
                        :href="route('employee-portal.index')"
                        class="rounded-xl px-5 py-3 text-center font-bold"
                        :class="
                            activeTab === 'profile'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                    >
                        My Profile
                    </Link>

                    <Link
                        :href="route('employee-portal.leaves')"
                        class="rounded-xl px-5 py-3 text-center font-bold"
                        :class="
                            activeTab === 'leaves'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                    >
                        My Leave Monitoring
                    </Link>

                    <Link
                        :href="route('employee-portal.documents')"
                        class="rounded-xl px-5 py-3 text-center font-bold"
                        :class="
                            activeTab === 'documents'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                    >
                        My Document Tracker
                    </Link>
                </nav>

                <!-- Profile -->
                <section
                    v-if="activeTab === 'profile'"
                    class="mt-6 rounded-2xl bg-white p-7 shadow-sm"
                >
                    <h3 class="text-xl font-black text-blue-950">
                        My Employee Information
                    </h3>

                    <div
                        class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Employee Number
                            </p>

                            <p class="mt-1 font-bold">
                                {{ employee.employee_number }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Full Name
                            </p>

                            <p class="mt-1 font-bold">
                                {{ employee.full_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Sex
                            </p>

                            <p class="mt-1 font-bold">
                                {{ employee.sex }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Birth Date
                            </p>

                            <p class="mt-1 font-bold">
                                {{ formatDate(employee.birth_date) }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Department
                            </p>

                            <p class="mt-1 font-bold">
                                {{ employee.department }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Position
                            </p>

                            <p class="mt-1 font-bold">
                                {{ employee.position }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Salary Grade
                            </p>

                            <p class="mt-1 font-bold">
                                SG {{ employee.salary_grade || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Step
                            </p>

                            <p class="mt-1 font-bold">
                                {{ employee.step || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Date Hired
                            </p>

                            <p class="mt-1 font-bold">
                                {{ formatDate(employee.date_hired) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400">
                                Status
                            </p>

                            <span
                                class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-bold"
                                :class="statusClass(employee.status)"
                            >
                                {{ employee.status }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Leaves -->
                <section
                    v-if="activeTab === 'leaves'"
                    class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b px-6 py-5"
                    >
                        <div>
                            <h3 class="text-xl font-black text-blue-950">
                                My Leave Applications
                            </h3>

                            <p class="text-sm text-gray-500">
                                Submit and monitor your leave requests.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white"
                            @click="openLeaveModal"
                        >
                            + Apply for Leave
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-blue-950 text-white">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Leave Type
                                    </th>

                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Inclusive Dates
                                    </th>

                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Days
                                    </th>

                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Status
                                    </th>

                                    <th class="px-5 py-4 text-right text-xs uppercase">
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                <tr
                                    v-for="leave in employee.leave_records"
                                    :key="leave.id"
                                >
                                    <td class="px-5 py-4 font-bold">
                                        {{ leave.leave_type }}
                                    </td>

                                    <td class="px-5 py-4 text-sm">
                                        {{ formatDate(leave.date_from) }}
                                        –
                                        {{ formatDate(leave.date_to) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ leave.number_of_days }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-bold"
                                            :class="statusClass(leave.status)"
                                        >
                                            {{ leave.status }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <button
                                            v-if="leave.status === 'Pending'"
                                            type="button"
                                            class="text-sm font-bold text-red-600"
                                            @click="cancelLeave(leave)"
                                        >
                                            Cancel
                                        </button>

                                        <span
                                            v-else
                                            class="text-sm text-gray-400"
                                        >
                                            —
                                        </span>
                                    </td>
                                </tr>

                                <tr
                                    v-if="employee.leave_records.length === 0"
                                >
                                    <td
                                        colspan="5"
                                        class="px-5 py-14 text-center text-gray-500"
                                    >
                                        No leave applications found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Documents -->
                <section
                    v-if="activeTab === 'documents'"
                    class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"
                >
                    <div class="border-b px-6 py-5">
                        <h3 class="text-xl font-black text-blue-950">
                            My Documents
                        </h3>

                        <p class="text-sm text-gray-500">
                            View and download your HRMO documents.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-blue-950 text-white">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Document
                                    </th>

                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Reference
                                    </th>

                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Expiration
                                    </th>

                                    <th class="px-5 py-4 text-left text-xs uppercase">
                                        Status
                                    </th>

                                    <th class="px-5 py-4 text-right text-xs uppercase">
                                        File
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                <tr
                                    v-for="document in employee.documents"
                                    :key="document.id"
                                >
                                    <td class="px-5 py-4">
                                        <p class="font-bold">
                                            {{ document.title }}
                                        </p>

                                        <p class="text-sm text-gray-500">
                                            {{ document.document_type }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        {{
                                            document.reference_number ||
                                            '—'
                                        }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{
                                            formatDate(
                                                document.expiration_date,
                                            )
                                        }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-bold"
                                            :class="statusClass(document.status)"
                                        >
                                            {{ document.status }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <a
                                            v-if="document.file_path"
                                            :href="
                                                route(
                                                    'employee-portal.documents.download',
                                                    document.id,
                                                )
                                            "
                                            class="text-sm font-bold text-blue-600"
                                        >
                                            Download
                                        </a>

                                        <span v-else class="text-gray-400">
                                            No file
                                        </span>
                                    </td>
                                </tr>

                                <tr
                                    v-if="employee.documents.length === 0"
                                >
                                    <td
                                        colspan="5"
                                        class="px-5 py-14 text-center text-gray-500"
                                    >
                                        No documents found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </template>
        </main>

        <!-- Leave application modal -->
        <div
            v-if="leaveModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <form
                class="max-h-[95vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-7 shadow-2xl"
                @submit.prevent="submitLeave"
            >
                <div class="flex justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-blue-950">
                            Apply for Leave
                        </h3>

                        <p class="text-sm text-gray-500">
                            Complete your leave application.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-3xl text-gray-400"
                        @click="closeLeaveModal"
                    >
                        ×
                    </button>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="text-sm font-bold">
                            Leave Type *
                        </span>

                        <select
                            v-model="leaveForm.leave_type"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        >
                            <option value="">
                                Select Leave Type
                            </option>

                            <option
                                v-for="type in leaveTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ type }}
                            </option>
                        </select>

                        <span class="text-xs text-red-600">
                            {{ leaveForm.errors.leave_type }}
                        </span>
                    </label>

                    <label>
                        <span class="text-sm font-bold">
                            Date From *
                        </span>

                        <input
                            v-model="leaveForm.date_from"
                            type="date"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        />

                        <span class="text-xs text-red-600">
                            {{ leaveForm.errors.date_from }}
                        </span>
                    </label>

                    <label>
                        <span class="text-sm font-bold">
                            Date To *
                        </span>

                        <input
                            v-model="leaveForm.date_to"
                            type="date"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        />

                        <span class="text-xs text-red-600">
                            {{ leaveForm.errors.date_to }}
                        </span>
                    </label>

                    <label>
                        <span class="text-sm font-bold">
                            Working Days *
                        </span>

                        <input
                            v-model="leaveForm.number_of_days"
                            type="number"
                            min="0.5"
                            step="0.5"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        />
                    </label>

                    <label>
                        <span class="text-sm font-bold">
                            Payment *
                        </span>

                        <select
                            v-model="leaveForm.with_pay"
                            class="mt-2 w-full rounded-xl border-gray-300"
                        >
                            <option :value="true">
                                With Pay
                            </option>

                            <option :value="false">
                                Without Pay
                            </option>
                        </select>
                    </label>

                    <label class="sm:col-span-2">
                        <span class="text-sm font-bold">
                            Reason/Purpose *
                        </span>

                        <textarea
                            v-model="leaveForm.reason"
                            rows="4"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        ></textarea>

                        <span class="text-xs text-red-600">
                            {{ leaveForm.errors.reason }}
                        </span>
                    </label>
                </div>

                <div class="mt-7 flex justify-end gap-3 border-t pt-5">
                    <button
                        type="button"
                        class="rounded-xl border px-5 py-3 font-bold"
                        @click="closeLeaveModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-700 px-6 py-3 font-bold text-white disabled:opacity-50"
                        :disabled="leaveForm.processing"
                    >
                        {{
                            leaveForm.processing
                                ? 'Submitting...'
                                : 'Submit Application'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>