<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    leaveRecords: {
        type: Object,
        required: true,
    },
    employees: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    summary: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const search = ref(props.filters.search ?? '');
const leaveTypeFilter = ref(props.filters.leave_type ?? '');
const statusFilter = ref(props.filters.status ?? '');

const modalOpen = ref(false);
const editingRecord = ref(null);
const deletingRecord = ref(null);
const statusRecord = ref(null);

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

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

const form = useForm({
    employee_id: '',
    leave_type: '',
    date_filed: new Date().toISOString().slice(0, 10),
    date_from: '',
    date_to: '',
    number_of_days: '',
    reason: '',
    with_pay: true,
    status: 'Pending',
    remarks: '',
});

const statusForm = useForm({
    status: '',
    remarks: '',
});

let searchTimer;

watch([search, leaveTypeFilter, statusFilter], () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('leave-records.index'),
            {
                search: search.value || undefined,
                leave_type: leaveTypeFilter.value || undefined,
                status: statusFilter.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 400);
});

watch(
    () => [form.date_from, form.date_to],
    () => {
        if (!form.date_from || !form.date_to) {
            return;
        }

        const start = new Date(`${form.date_from}T00:00:00`);
        const end = new Date(`${form.date_to}T00:00:00`);

        if (end < start) {
            form.number_of_days = '';
            return;
        }

        let workingDays = 0;
        const currentDate = new Date(start);

        while (currentDate <= end) {
            const day = currentDate.getDay();

            if (day !== 0 && day !== 6) {
                workingDays++;
            }

            currentDate.setDate(currentDate.getDate() + 1);
        }

        form.number_of_days = workingDays;
    },
);

const employeeName = (employee) => {
    const middleInitial = employee.middle_name
        ? ` ${employee.middle_name.charAt(0).toUpperCase()}.`
        : '';

    const suffix = employee.suffix ? ` ${employee.suffix}` : '';

    return `${employee.last_name}, ${employee.first_name}${middleInitial}${suffix}`;
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(date));
};

const statusClass = (status) => {
    const classes = {
        Pending: 'bg-amber-100 text-amber-700',
        Approved: 'bg-emerald-100 text-emerald-700',
        Rejected: 'bg-red-100 text-red-700',
        Cancelled: 'bg-gray-200 text-gray-700',
    };

    return classes[status] ?? 'bg-gray-100 text-gray-700';
};

const openCreateModal = () => {
    editingRecord.value = null;
    form.reset();
    form.clearErrors();

    form.date_filed = new Date().toISOString().slice(0, 10);
    form.with_pay = true;
    form.status = 'Pending';

    modalOpen.value = true;
};

const openEditModal = (record) => {
    editingRecord.value = record;
    form.clearErrors();

    form.employee_id = record.employee_id;
    form.leave_type = record.leave_type;
    form.date_filed = record.date_filed ?? '';
    form.date_from = record.date_from ?? '';
    form.date_to = record.date_to ?? '';
    form.number_of_days = record.number_of_days ?? '';
    form.reason = record.reason ?? '';
    form.with_pay = Boolean(record.with_pay);
    form.status = record.status ?? 'Pending';
    form.remarks = record.remarks ?? '';

    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editingRecord.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        with_pay: Boolean(data.with_pay),
    }));

    if (editingRecord.value) {
        form.put(
            route('leave-records.update', editingRecord.value.id),
            {
                preserveScroll: true,
                onSuccess: closeModal,
            },
        );

        return;
    }

    form.post(route('leave-records.store'), {
        preserveScroll: true,
        onSuccess: closeModal,
    });
};

const openStatusModal = (record, status) => {
    statusRecord.value = record;
    statusForm.status = status;
    statusForm.remarks = record.remarks ?? '';
    statusForm.clearErrors();
};

const closeStatusModal = () => {
    statusRecord.value = null;
    statusForm.reset();
    statusForm.clearErrors();
};

const submitStatus = () => {
    statusForm.patch(
        route('leave-records.status', statusRecord.value.id),
        {
            preserveScroll: true,
            onSuccess: closeStatusModal,
        },
    );
};

const deleteRecord = () => {
    router.delete(
        route('leave-records.destroy', deletingRecord.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                deletingRecord.value = null;
            },
        },
    );
};
</script>

<template>
    <Head title="Leave Monitoring" />

    <div class="min-h-screen bg-slate-100">
        <header class="border-b bg-white shadow-sm">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4"
            >
                <div class="flex items-center gap-4">
                    <img
                        src="/images/baliangao-logo.png"
                        class="h-14 w-14 rounded-full bg-white p-1 shadow"
                        alt="Baliangao Logo"
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
                        :href="route('dashboard')"
                        class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-bold text-blue-700"
                    >
                        Dashboard
                    </Link>

                    <Link
                        :href="route('employees.index')"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white"
                    >
                        Employees
                    </Link>
                </div>
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
                class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-blue-600"
                    >
                        Employee Benefits
                    </p>

                    <h2 class="mt-1 text-3xl font-black text-blue-950">
                        Leave Monitoring
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Record and monitor employee leave applications.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white shadow hover:bg-blue-800"
                    @click="openCreateModal"
                >
                    + Add Leave Record
                </button>
            </section>

            <section class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Total Applications
                    </p>
                    <p class="mt-2 text-3xl font-black text-blue-950">
                        {{ summary.total }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">Pending</p>
                    <p class="mt-2 text-3xl font-black text-amber-700">
                        {{ summary.pending }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">Approved</p>
                    <p class="mt-2 text-3xl font-black text-emerald-700">
                        {{ summary.approved }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">Rejected</p>
                    <p class="mt-2 text-3xl font-black text-red-700">
                        {{ summary.rejected }}
                    </p>
                </article>
            </section>

            <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm">
                <div class="grid gap-4 lg:grid-cols-3">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search employee..."
                        class="rounded-xl border-gray-300"
                    />

                    <select
                        v-model="leaveTypeFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">All Leave Types</option>
                        <option
                            v-for="leaveType in leaveTypes"
                            :key="leaveType"
                            :value="leaveType"
                        >
                            {{ leaveType }}
                        </option>
                    </select>

                    <select
                        v-model="statusFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </section>

            <section
                class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-blue-950 text-white">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    Employee
                                </th>
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
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="record in leaveRecords.data"
                                :key="record.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800">
                                        {{ record.employee.full_name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ record.employee.employee_number }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ record.leave_type }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    {{ formatDate(record.date_from) }} –
                                    {{ formatDate(record.date_to) }}
                                </td>

                                <td class="px-5 py-4 font-black text-blue-800">
                                    {{ record.number_of_days }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold"
                                        :class="statusClass(record.status)"
                                    >
                                        {{ record.status }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <button
                                        v-if="record.status === 'Pending'"
                                        class="mr-3 text-sm font-bold text-emerald-600"
                                        @click="openStatusModal(record, 'Approved')"
                                    >
                                        Approve
                                    </button>

                                    <button
                                        v-if="record.status === 'Pending'"
                                        class="mr-3 text-sm font-bold text-red-600"
                                        @click="openStatusModal(record, 'Rejected')"
                                    >
                                        Reject
                                    </button>

                                    <button
                                        class="mr-3 text-sm font-bold text-blue-600"
                                        @click="openEditModal(record)"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        class="text-sm font-bold text-red-600"
                                        @click="deletingRecord = record"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="leaveRecords.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-16 text-center text-gray-500"
                                >
                                    No leave records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="leaveRecords.links.length > 3"
                    class="flex flex-wrap gap-2 border-t p-5"
                >
                    <template
                        v-for="link in leaveRecords.links"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-3 py-2 text-sm"
                            :class="
                                link.active
                                    ? 'border-blue-700 bg-blue-700 text-white'
                                    : 'border-gray-300 text-gray-700'
                            "
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </section>
        </main>

        <!-- Add/Edit modal -->
        <div
            v-if="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div
                class="max-h-[95vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl"
            >
                <div class="flex justify-between border-b px-6 py-5">
                    <div>
                        <h3 class="text-2xl font-black text-blue-950">
                            {{
                                editingRecord
                                    ? 'Edit Leave Record'
                                    : 'Add Leave Record'
                            }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Complete the leave information.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-3xl text-gray-400"
                        @click="closeModal"
                    >
                        ×
                    </button>
                </div>

                <form class="p-6" @submit.prevent="submit">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                Employee *
                            </span>

                            <select
                                v-model="form.employee_id"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            >
                                <option value="">Select Employee</option>

                                <option
                                    v-for="employee in employees"
                                    :key="employee.id"
                                    :value="employee.id"
                                >
                                    {{ employee.employee_number }} —
                                    {{ employeeName(employee) }}
                                </option>
                            </select>

                            <span class="text-xs text-red-600">
                                {{ form.errors.employee_id }}
                            </span>
                        </label>

                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                Leave Type *
                            </span>

                            <select
                                v-model="form.leave_type"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            >
                                <option value="">Select Leave Type</option>

                                <option
                                    v-for="leaveType in leaveTypes"
                                    :key="leaveType"
                                    :value="leaveType"
                                >
                                    {{ leaveType }}
                                </option>
                            </select>

                            <span class="text-xs text-red-600">
                                {{ form.errors.leave_type }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Date Filed *
                            </span>

                            <input
                                v-model="form.date_filed"
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                With Pay? *
                            </span>

                            <select
                                v-model="form.with_pay"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            >
                                <option :value="true">With Pay</option>
                                <option :value="false">Without Pay</option>
                            </select>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Date From *
                            </span>

                            <input
                                v-model="form.date_from"
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Date To *
                            </span>

                            <input
                                v-model="form.date_to"
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Number of Working Days *
                            </span>

                            <input
                                v-model="form.number_of_days"
                                type="number"
                                min="0.5"
                                step="0.5"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Status
                            </span>

                            <select
                                v-model="form.status"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            >
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </label>

                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                Reason/Purpose
                            </span>

                            <textarea
                                v-model="form.reason"
                                rows="3"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            ></textarea>
                        </label>

                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                HRMO Remarks
                            </span>

                            <textarea
                                v-model="form.remarks"
                                rows="2"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            ></textarea>
                        </label>
                    </div>

                    <div class="mt-7 flex justify-end gap-3 border-t pt-5">
                        <button
                            type="button"
                            class="rounded-xl border px-5 py-3 font-bold"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-700 px-6 py-3 font-bold text-white disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : editingRecord
                                      ? 'Update Record'
                                      : 'Save Record'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Approval/rejection modal -->
        <div
            v-if="statusRecord"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <form
                class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl"
                @submit.prevent="submitStatus"
            >
                <h3 class="text-xl font-black text-blue-950">
                    {{ statusForm.status }} Leave
                </h3>

                <p class="mt-2 text-gray-600">
                    {{ statusRecord.employee.full_name }} —
                    {{ statusRecord.leave_type }}
                </p>

                <label class="mt-5 block">
                    <span class="text-sm font-bold text-gray-700">
                        Remarks
                    </span>

                    <textarea
                        v-model="statusForm.remarks"
                        rows="3"
                        class="mt-2 w-full rounded-xl border-gray-300"
                    ></textarea>
                </label>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border px-5 py-3 font-bold"
                        @click="closeStatusModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl px-5 py-3 font-bold text-white"
                        :class="
                            statusForm.status === 'Approved'
                                ? 'bg-emerald-600'
                                : 'bg-red-600'
                        "
                    >
                        Confirm {{ statusForm.status }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete modal -->
        <div
            v-if="deletingRecord"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl">
                <h3 class="text-xl font-black text-red-700">
                    Delete Leave Record
                </h3>

                <p class="mt-3 text-gray-600">
                    Delete the {{ deletingRecord.leave_type }} record of
                    <strong>{{ deletingRecord.employee.full_name }}</strong>?
                </p>

                <div class="mt-7 flex justify-end gap-3">
                    <button
                        class="rounded-xl border px-5 py-3 font-bold"
                        @click="deletingRecord = null"
                    >
                        Cancel
                    </button>

                    <button
                        class="rounded-xl bg-red-600 px-5 py-3 font-bold text-white"
                        @click="deleteRecord"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>