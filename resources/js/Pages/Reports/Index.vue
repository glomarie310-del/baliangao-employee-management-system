<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    employees: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
    departmentSummary: {
        type: Array,
        default: () => [],
    },
    employeeSummary: {
        type: Object,
        required: true,
    },
    leaveSummary: {
        type: Object,
        required: true,
    },
    documentSummary: {
        type: Object,
        required: true,
    },
    expiringDocuments: {
        type: Array,
        default: () => [],
    },
    recentLeaves: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const activeReport = ref('employees');
const departmentFilter = ref(props.filters.department ?? '');
const statusFilter = ref(props.filters.status ?? '');

const reportTitle = computed(() => {
    const titles = {
        employees: 'Employee Masterlist Report',
        departments: 'Employees by Department',
        leaves: 'Leave Monitoring Report',
        documents: 'Document Expiration Report',
    };

    return titles[activeReport.value];
});

watch([departmentFilter, statusFilter], () => {
    router.get(
        route('reports.index'),
        {
            department: departmentFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
});

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
        Active: 'bg-emerald-100 text-emerald-700',
        Inactive: 'bg-gray-200 text-gray-700',
        Retired: 'bg-amber-100 text-amber-700',
        Separated: 'bg-red-100 text-red-700',
        Pending: 'bg-amber-100 text-amber-700',
        Approved: 'bg-emerald-100 text-emerald-700',
        Rejected: 'bg-red-100 text-red-700',
        Cancelled: 'bg-gray-200 text-gray-700',
        Valid: 'bg-emerald-100 text-emerald-700',
        'Expiring Soon': 'bg-amber-100 text-amber-700',
        Expired: 'bg-red-100 text-red-700',
    };

    return classes[status] ?? 'bg-gray-100 text-gray-700';
};

const clearFilters = () => {
    departmentFilter.value = '';
    statusFilter.value = '';
};

const printReport = () => {
    window.print();
};

const exportUrl = computed(() => {
    const parameters = new URLSearchParams();

    if (departmentFilter.value) {
        parameters.set('department', departmentFilter.value);
    }

    if (statusFilter.value) {
        parameters.set('status', statusFilter.value);
    }

    const query = parameters.toString();

    return `${route('reports.employees.export')}${query ? `?${query}` : ''}`;
});

const totalDepartmentEmployees = computed(() => {
    return props.departmentSummary.reduce(
        (total, department) => total + Number(department.employee_count),
        0,
    );
});
</script>

<template>
    <Head title="Reports" />

    <div class="min-h-screen bg-slate-100">
        <!-- Header -->
        <header class="border-b bg-white shadow-sm print:hidden">
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

        <main class="mx-auto max-w-7xl px-5 py-8 print:max-w-none print:p-0">
            <!-- Screen heading -->
            <section
                class="flex flex-col justify-between gap-5 print:hidden sm:flex-row sm:items-center"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-blue-600"
                    >
                        HRMO Analytics
                    </p>

                    <h2 class="mt-1 text-3xl font-black text-blue-950">
                        Reports
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Generate, print and export employee reports.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        v-if="activeReport === 'employees'"
                        :href="exportUrl"
                        class="rounded-xl bg-emerald-600 px-5 py-3 font-bold text-white hover:bg-emerald-700"
                    >
                        Export CSV
                    </a>

                    <button
                        type="button"
                        class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white hover:bg-blue-800"
                        @click="printReport"
                    >
                        Print Report
                    </button>
                </div>
            </section>

            <!-- Summary cards -->
            <section
                class="mt-7 grid gap-4 print:hidden sm:grid-cols-2 lg:grid-cols-4"
            >
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Total Employees
                    </p>
                    <p class="mt-2 text-3xl font-black text-blue-950">
                        {{ employeeSummary.total }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Active Employees
                    </p>
                    <p class="mt-2 text-3xl font-black text-emerald-700">
                        {{ employeeSummary.active }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Approved Leaves
                    </p>
                    <p class="mt-2 text-3xl font-black text-violet-700">
                        {{ leaveSummary.approved }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Expired Documents
                    </p>
                    <p class="mt-2 text-3xl font-black text-red-700">
                        {{ documentSummary.expired }}
                    </p>
                </article>
            </section>

            <!-- Report tabs -->
            <section
                class="mt-7 rounded-2xl bg-white p-3 shadow-sm print:hidden"
            >
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <button
                        type="button"
                        class="rounded-xl px-4 py-3 text-sm font-bold"
                        :class="
                            activeReport === 'employees'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        @click="activeReport = 'employees'"
                    >
                        Employee Masterlist
                    </button>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-3 text-sm font-bold"
                        :class="
                            activeReport === 'departments'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        @click="activeReport = 'departments'"
                    >
                        By Department
                    </button>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-3 text-sm font-bold"
                        :class="
                            activeReport === 'leaves'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        @click="activeReport = 'leaves'"
                    >
                        Leave Report
                    </button>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-3 text-sm font-bold"
                        :class="
                            activeReport === 'documents'
                                ? 'bg-blue-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        @click="activeReport = 'documents'"
                    >
                        Document Report
                    </button>
                </div>
            </section>

            <!-- Employee filters -->
            <section
                v-if="activeReport === 'employees'"
                class="mt-5 rounded-2xl bg-white p-5 shadow-sm print:hidden"
            >
                <div class="grid gap-4 md:grid-cols-3">
                    <select
                        v-model="departmentFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">All Departments</option>

                        <option
                            v-for="department in departments"
                            :key="department"
                            :value="department"
                        >
                            {{ department }}
                        </option>
                    </select>

                    <select
                        v-model="statusFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Retired">Retired</option>
                        <option value="Separated">Separated</option>
                    </select>

                    <button
                        type="button"
                        class="rounded-xl border border-gray-300 font-bold text-gray-600 hover:bg-gray-100"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </button>
                </div>
            </section>

            <!-- Printable report -->
            <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm print:mt-0 print:rounded-none print:shadow-none">
                <!-- Print heading -->
                <div class="hidden border-b border-black pb-5 text-center print:block">
                    <div class="flex items-center justify-center gap-4">
                        <img
                            src="/images/baliangao-logo.png"
                            alt="Baliangao Logo"
                            class="h-20 w-20"
                        />

                        <div>
                            <p class="text-sm">
                                Republic of the Philippines
                            </p>
                            <h1 class="text-xl font-black">
                                MUNICIPALITY OF BALIANGAO
                            </h1>
                            <p class="text-sm">
                                Province of Misamis Occidental
                            </p>
                            <p class="font-bold">
                                Human Resource Management Office
                            </p>
                        </div>
                    </div>

                    <h2 class="mt-5 text-xl font-black uppercase">
                        {{ reportTitle }}
                    </h2>

                    <p class="mt-1 text-sm">
                        Generated on {{ formatDate(new Date()) }}
                    </p>
                </div>

                <!-- Employee masterlist -->
                <div v-if="activeReport === 'employees'">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-blue-950 text-white print:bg-gray-200 print:text-black">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        No.
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        Employee Number
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        Employee
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        Department
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        Position
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        SG/Step
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="(employee, index) in employees"
                                    :key="employee.id"
                                >
                                    <td class="px-4 py-3 text-sm">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        {{ employee.employee_number }}
                                    </td>

                                    <td class="px-4 py-3 text-sm font-bold">
                                        {{ employee.full_name }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        {{ employee.department }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        {{ employee.position }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        SG {{ employee.salary_grade || '—' }}
                                        / {{ employee.step || '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-bold print:p-0"
                                            :class="statusClass(employee.status)"
                                        >
                                            {{ employee.status }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="employees.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-5 py-16 text-center text-gray-500"
                                    >
                                        No employee records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="border-t p-5 text-sm font-bold">
                        Total Records: {{ employees.length }}
                    </p>
                </div>

                <!-- Department report -->
                <div v-if="activeReport === 'departments'">
                    <table class="min-w-full">
                        <thead class="bg-blue-950 text-white print:bg-gray-200 print:text-black">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    No.
                                </th>
                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    Department/Office
                                </th>
                                <th class="px-5 py-4 text-right text-xs uppercase">
                                    Number of Employees
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr
                                v-for="(department, index) in departmentSummary"
                                :key="department.department"
                            >
                                <td class="px-5 py-4">
                                    {{ index + 1 }}
                                </td>

                                <td class="px-5 py-4 font-bold">
                                    {{ department.department }}
                                </td>

                                <td class="px-5 py-4 text-right text-xl font-black text-blue-800 print:text-black">
                                    {{ department.employee_count }}
                                </td>
                            </tr>
                        </tbody>

                        <tfoot class="bg-gray-100 font-black">
                            <tr>
                                <td colspan="2" class="px-5 py-4">
                                    Total
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ totalDepartmentEmployees }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Leave report -->
                <div v-if="activeReport === 'leaves'">
                    <table class="min-w-full">
                        <thead class="bg-blue-950 text-white print:bg-gray-200 print:text-black">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Employee
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Leave Type
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Inclusive Dates
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Days
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr
                                v-for="leave in recentLeaves"
                                :key="leave.id"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-bold">
                                        {{ leave.employee.full_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ leave.employee.employee_number }}
                                    </p>
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ leave.leave_type }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(leave.date_from) }} –
                                    {{ formatDate(leave.date_to) }}
                                </td>

                                <td class="px-4 py-3 font-bold">
                                    {{ leave.number_of_days }}
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold print:p-0"
                                        :class="statusClass(leave.status)"
                                    >
                                        {{ leave.status }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="recentLeaves.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-16 text-center text-gray-500"
                                >
                                    No leave records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Document report -->
                <div v-if="activeReport === 'documents'">
                    <table class="min-w-full">
                        <thead class="bg-blue-950 text-white print:bg-gray-200 print:text-black">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Employee
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Document
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Expiration
                                </th>
                                <th class="px-4 py-3 text-left text-xs uppercase">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr
                                v-for="document in expiringDocuments"
                                :key="document.id"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-bold">
                                        {{ document.employee.full_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ document.employee.employee_number }}
                                    </p>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="font-bold">
                                        {{ document.title }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ document.document_type }}
                                    </p>
                                </td>

                                <td class="px-4 py-3">
                                    {{ formatDate(document.expiration_date) }}
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold print:p-0"
                                        :class="statusClass(document.status)"
                                    >
                                        {{ document.status }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="expiringDocuments.length === 0">
                                <td
                                    colspan="4"
                                    class="px-5 py-16 text-center text-gray-500"
                                >
                                    No expired or expiring documents found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Signatures -->
                <div
                    class="hidden grid-cols-2 gap-24 px-12 pb-8 pt-24 text-center print:grid"
                >
                    <div>
                        <div class="border-t border-black pt-2">
                            Prepared by
                        </div>
                    </div>

                    <div>
                        <div class="border-t border-black pt-2">
                            Human Resource Management Officer
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    body {
        background: white !important;
    }

    table {
        font-size: 10px;
    }

    thead {
        display: table-header-group;
    }

    tr {
        break-inside: avoid;
    }
}
</style>