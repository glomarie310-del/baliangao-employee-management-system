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
    employees: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    departments: {
        type: Array,
        default: () => [],
    },

    summary: {
        type: Object,
        required: true,
    },

    canManage: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const employeeModalOpen = ref(false);
const importModalOpen = ref(false);
const editingEmployee = ref(null);
const deletingEmployee = ref(null);
const importFileInput = ref(null);

const search = ref(props.filters.search ?? '');
const departmentFilter = ref(
    props.filters.department ?? '',
);
const statusFilter = ref(
    props.filters.status ?? '',
);

const successMessage = computed(
    () => page.props.flash?.success,
);

const errorMessage = computed(
    () => page.props.flash?.error,
);

const importErrors = computed(
    () => page.props.flash?.import_errors ?? [],
);

const employeeForm = useForm({
    employee_number: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    sex: '',
    birth_date: '',
    civil_status: '',
    contact_number: '',
    email: '',
    address: '',
    department: '',
    position: '',
    salary_grade: '',
    step: '',
    employment_status: 'Regular',
    date_hired: '',
    gsis_number: '',
    pagibig_number: '',
    philhealth_number: '',
    tin_number: '',
    status: 'Active',
});

const importForm = useForm({
    excel_file: null,
});

let searchTimer;

watch(
    [
        search,
        departmentFilter,
        statusFilter,
    ],
    () => {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            router.get(
                route('employees.index'),
                {
                    search:
                        search.value || undefined,

                    department:
                        departmentFilter.value ||
                        undefined,

                    status:
                        statusFilter.value ||
                        undefined,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 400);
    },
);

const dateOnly = (date) => {
    if (! date) {
        return '';
    }

    return String(date).substring(0, 10);
};

const statusClass = (status) => {
    const classes = {
        Active:
            'bg-emerald-100 text-emerald-700',

        Inactive:
            'bg-gray-200 text-gray-700',

        Retired:
            'bg-amber-100 text-amber-700',

        Separated:
            'bg-red-100 text-red-700',
    };

    return classes[status] ??
        'bg-gray-100 text-gray-700';
};

const clearFilters = () => {
    search.value = '';
    departmentFilter.value = '';
    statusFilter.value = '';
};

const openCreateModal = () => {
    if (! props.canManage) {
        return;
    }

    editingEmployee.value = null;

    employeeForm.reset();
    employeeForm.clearErrors();

    employeeForm.employment_status =
        'Regular';

    employeeForm.status = 'Active';

    employeeModalOpen.value = true;
};

const openEditModal = (employee) => {
    if (! props.canManage) {
        return;
    }

    editingEmployee.value = employee;

    employeeForm.clearErrors();

    employeeForm.employee_number =
        employee.employee_number ?? '';

    employeeForm.first_name =
        employee.first_name ?? '';

    employeeForm.middle_name =
        employee.middle_name ?? '';

    employeeForm.last_name =
        employee.last_name ?? '';

    employeeForm.suffix =
        employee.suffix ?? '';

    employeeForm.sex =
        employee.sex ?? '';

    employeeForm.birth_date =
        dateOnly(employee.birth_date);

    employeeForm.civil_status =
        employee.civil_status ?? '';

    employeeForm.contact_number =
        employee.contact_number ?? '';

    employeeForm.email =
        employee.email ?? '';

    employeeForm.address =
        employee.address ?? '';

    employeeForm.department =
        employee.department ?? '';

    employeeForm.position =
        employee.position ?? '';

    employeeForm.salary_grade =
        employee.salary_grade ?? '';

    employeeForm.step =
        employee.step ?? '';

    employeeForm.employment_status =
        employee.employment_status ??
        'Regular';

    employeeForm.date_hired =
        dateOnly(employee.date_hired);

    employeeForm.gsis_number =
        employee.gsis_number ?? '';

    employeeForm.pagibig_number =
        employee.pagibig_number ?? '';

    employeeForm.philhealth_number =
        employee.philhealth_number ?? '';

    employeeForm.tin_number =
        employee.tin_number ?? '';

    employeeForm.status =
        employee.status ?? 'Active';

    employeeModalOpen.value = true;
};

const closeEmployeeModal = () => {
    employeeModalOpen.value = false;
    editingEmployee.value = null;

    employeeForm.reset();
    employeeForm.clearErrors();
};

const submitEmployee = () => {
    if (! props.canManage) {
        return;
    }

    if (editingEmployee.value) {
        employeeForm.put(
            route(
                'employees.update',
                editingEmployee.value.id,
            ),
            {
                preserveScroll: true,
                onSuccess:
                    closeEmployeeModal,
            },
        );

        return;
    }

    employeeForm.post(
        route('employees.store'),
        {
            preserveScroll: true,
            onSuccess:
                closeEmployeeModal,
        },
    );
};

const confirmDelete = (employee) => {
    if (! props.canManage) {
        return;
    }

    deletingEmployee.value = employee;
};

const deleteEmployee = () => {
    if (
        ! props.canManage ||
        ! deletingEmployee.value
    ) {
        return;
    }

    router.delete(
        route(
            'employees.destroy',
            deletingEmployee.value.id,
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                deletingEmployee.value = null;
            },
        },
    );
};

const openImportModal = () => {
    if (! props.canManage) {
        return;
    }

    importForm.reset();
    importForm.clearErrors();

    if (importFileInput.value) {
        importFileInput.value.value = '';
    }

    importModalOpen.value = true;
};

const closeImportModal = () => {
    importModalOpen.value = false;

    importForm.reset();
    importForm.clearErrors();

    if (importFileInput.value) {
        importFileInput.value.value = '';
    }
};

const handleImportFile = (event) => {
    importForm.excel_file =
        event.target.files[0] ?? null;
};

const submitImport = () => {
    if (! props.canManage) {
        return;
    }

    importForm.post(
        route('employees.import'),
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess:
                closeImportModal,
        },
    );
};
</script>

<template>
    <Head title="Employee Masterlist" />

    <div class="min-h-screen bg-slate-100">
        <!-- Header -->
        <header
            class="border-b border-gray-200 bg-white shadow-sm"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4"
            >
                <div class="flex items-center gap-4">
                    <img
                        src="/images/baliangao-logo.png"
                        alt="Municipality of Baliangao Logo"
                        class="h-14 w-14 rounded-full bg-white p-1 shadow"
                    />

                    <div>
                        <h1
                            class="text-xl font-black text-blue-950"
                        >
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
                        class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-50"
                    >
                        Dashboard
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </header>

        <main
            class="mx-auto max-w-7xl px-5 py-8"
        >
            <!-- Flash messages -->
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

            <div
                v-if="
                    canManage &&
                    importErrors.length
                "
                class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5"
            >
                <p class="font-bold text-amber-800">
                    Some Excel rows were skipped:
                </p>

                <ul
                    class="mt-3 list-disc space-y-1 pl-5 text-sm text-amber-700"
                >
                    <li
                        v-for="error in importErrors"
                        :key="error"
                    >
                        {{ error }}
                    </li>
                </ul>
            </div>

            <!-- Heading -->
            <section
                class="flex flex-col justify-between gap-5 xl:flex-row xl:items-center"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-blue-600"
                    >
                        HRMO Records
                    </p>

                    <h2
                        class="mt-1 text-3xl font-black text-blue-950"
                    >
                        {{
                            canManage
                                ? 'Regular Employee Masterlist'
                                : 'Department Employee Masterlist'
                        }}
                    </h2>

                    <p class="mt-2 text-gray-500">
                        <span v-if="canManage">
                            Manage and import regular
                            municipal employee records.
                        </span>

                        <span v-else>
                            View employees assigned to your
                            department.
                        </span>
                    </p>
                </div>

                <!-- Admin and HRMO actions only -->
                <div
                    v-if="canManage"
                    class="flex flex-wrap gap-3"
                >
                    <a
                        :href="
                            route(
                                'employees.import.template',
                            )
                        "
                        class="rounded-xl border border-emerald-300 bg-white px-5 py-3 text-sm font-bold text-emerald-700 hover:bg-emerald-50"
                    >
                        Download Excel Template
                    </a>

                    <button
                        type="button"
                        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700"
                        @click="openImportModal"
                    >
                        Import Excel
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-bold text-white hover:bg-blue-800"
                        @click="openCreateModal"
                    >
                        + Add Employee
                    </button>
                </div>
            </section>

            <!-- Summary -->
            <section
                class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <article
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-bold text-gray-500"
                    >
                        Total Employees
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-blue-950"
                    >
                        {{ summary.total }}
                    </p>
                </article>

                <article
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-bold text-gray-500"
                    >
                        Active
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-emerald-700"
                    >
                        {{ summary.active }}
                    </p>
                </article>

                <article
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-bold text-gray-500"
                    >
                        Inactive
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-gray-700"
                    >
                        {{ summary.inactive }}
                    </p>
                </article>

                <article
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-bold text-gray-500"
                    >
                        Retired
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-amber-700"
                    >
                        {{ summary.retired }}
                    </p>
                </article>
            </section>

            <!-- Filters -->
            <section
                class="mt-7 rounded-2xl bg-white p-5 shadow-sm"
            >
                <div
                    class="grid gap-4"
                    :class="
                        canManage
                            ? 'lg:grid-cols-4'
                            : 'lg:grid-cols-3'
                    "
                >
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search employee..."
                        class="rounded-xl border-gray-300 lg:col-span-2"
                    />

                    <select
                        v-if="canManage"
                        v-model="departmentFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">
                            All Departments
                        </option>

                        <option
                            v-for="department in departments"
                            :key="department"
                            :value="department"
                        >
                            {{ department }}
                        </option>
                    </select>

                    <div class="flex gap-2">
                        <select
                            v-model="statusFilter"
                            class="w-full rounded-xl border-gray-300"
                        >
                            <option value="">
                                All Statuses
                            </option>

                            <option value="Active">
                                Active
                            </option>

                            <option value="Inactive">
                                Inactive
                            </option>

                            <option value="Retired">
                                Retired
                            </option>

                            <option value="Separated">
                                Separated
                            </option>
                        </select>

                        <button
                            type="button"
                            class="rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-600"
                            @click="clearFilters"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </section>

            <!-- Table -->
            <section
                class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead
                            class="bg-blue-950 text-white"
                        >
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Employee
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Employee No.
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Department
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Position
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    SG / Step
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-gray-100"
                        >
                            <tr
                                v-for="employee in employees.data"
                                :key="employee.id"
                                class="hover:bg-slate-50"
                            >
                                <td
                                    class="whitespace-nowrap px-5 py-4"
                                >
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 font-black text-blue-700"
                                        >
                                            {{
                                                employee.first_name.charAt(
                                                    0,
                                                )
                                            }}
                                            {{
                                                employee.last_name.charAt(
                                                    0,
                                                )
                                            }}
                                        </div>

                                        <div>
                                            <p
                                                class="font-bold text-gray-800"
                                            >
                                                {{
                                                    employee.full_name
                                                }}
                                            </p>

                                            <p
                                                class="text-sm text-gray-500"
                                            >
                                                {{
                                                    employee.email ||
                                                    'No email'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-sm font-semibold"
                                >
                                    {{
                                        employee.employee_number
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-gray-600"
                                >
                                    {{
                                        employee.department
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-gray-600"
                                >
                                    {{ employee.position }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-sm text-gray-600"
                                >
                                    SG
                                    {{
                                        employee.salary_grade ||
                                        '—'
                                    }}
                                    /
                                    {{ employee.step || '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            statusClass(
                                                employee.status,
                                            )
                                        "
                                    >
                                        {{ employee.status }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'employees.show',
                                                employee.id,
                                            )
                                        "
                                        class="mr-3 text-sm font-bold text-emerald-600"
                                    >
                                        View
                                    </Link>

                                    <template v-if="canManage">
                                        <button
                                            type="button"
                                            class="mr-3 text-sm font-bold text-blue-600"
                                            @click="
                                                openEditModal(
                                                    employee,
                                                )
                                            "
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="text-sm font-bold text-red-600"
                                            @click="
                                                confirmDelete(
                                                    employee,
                                                )
                                            "
                                        >
                                            Delete
                                        </button>
                                    </template>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    employees.data.length ===
                                    0
                                "
                            >
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

                <!-- Pagination -->
                <div
                    v-if="employees.links.length > 3"
                    class="flex flex-wrap gap-2 border-t p-5"
                >
                    <template
                        v-for="link in employees.links"
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
                            preserve-scroll
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg border px-3 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </section>
        </main>

        <!-- Add/Edit Modal -->
        <div
            v-if="
                canManage &&
                employeeModalOpen
            "
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div
                class="max-h-[95vh] w-full max-w-5xl overflow-y-auto rounded-3xl bg-white shadow-2xl"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-6 py-5"
                >
                    <div>
                        <h3
                            class="text-2xl font-black text-blue-950"
                        >
                            {{
                                editingEmployee
                                    ? 'Edit Employee'
                                    : 'Add Regular Employee'
                            }}
                        </h3>

                        <p
                            class="text-sm text-gray-500"
                        >
                            Complete the employee information.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-3xl text-gray-400"
                        @click="closeEmployeeModal"
                    >
                        ×
                    </button>
                </div>

                <form
                    class="p-6"
                    @submit.prevent="submitEmployee"
                >
                    <h4
                        class="mb-4 font-black text-blue-900"
                    >
                        Personal Information
                    </h4>

                    <div
                        class="grid gap-5 md:grid-cols-4"
                    >
                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Employee Number *
                            </span>

                            <input
                                v-model="
                                    employeeForm.employee_number
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors
                                        .employee_number
                                }}
                            </span>
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                First Name *
                            </span>

                            <input
                                v-model="
                                    employeeForm.first_name
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors
                                        .first_name
                                }}
                            </span>
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Middle Name
                            </span>

                            <input
                                v-model="
                                    employeeForm.middle_name
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Last Name *
                            </span>

                            <input
                                v-model="
                                    employeeForm.last_name
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors
                                        .last_name
                                }}
                            </span>
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Suffix
                            </span>

                            <input
                                v-model="
                                    employeeForm.suffix
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                placeholder="Jr., Sr., III"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Sex *
                            </span>

                            <select
                                v-model="
                                    employeeForm.sex
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            >
                                <option value="">
                                    Select Sex
                                </option>

                                <option value="Male">
                                    Male
                                </option>

                                <option value="Female">
                                    Female
                                </option>
                            </select>

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors.sex
                                }}
                            </span>
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Birth Date
                            </span>

                            <input
                                v-model="
                                    employeeForm.birth_date
                                "
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Civil Status
                            </span>

                            <select
                                v-model="
                                    employeeForm.civil_status
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            >
                                <option value="">
                                    Select
                                </option>

                                <option value="Single">
                                    Single
                                </option>

                                <option value="Married">
                                    Married
                                </option>

                                <option value="Widowed">
                                    Widowed
                                </option>

                                <option value="Separated">
                                    Separated
                                </option>

                                <option value="Annulled">
                                    Annulled
                                </option>
                            </select>
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Contact Number
                            </span>

                            <input
                                v-model="
                                    employeeForm.contact_number
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label
                            class="md:col-span-2"
                        >
                            <span
                                class="text-sm font-bold"
                            >
                                Email
                            </span>

                            <input
                                v-model="
                                    employeeForm.email
                                "
                                type="email"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors.email
                                }}
                            </span>
                        </label>

                        <label
                            class="md:col-span-4"
                        >
                            <span
                                class="text-sm font-bold"
                            >
                                Address
                            </span>

                            <textarea
                                v-model="
                                    employeeForm.address
                                "
                                rows="2"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            ></textarea>
                        </label>
                    </div>

                    <h4
                        class="mb-4 mt-8 border-t pt-6 font-black text-blue-900"
                    >
                        Employment Information
                    </h4>

                    <div
                        class="grid gap-5 md:grid-cols-4"
                    >
                        <label
                            class="md:col-span-2"
                        >
                            <span
                                class="text-sm font-bold"
                            >
                                Department/Office *
                            </span>

                            <input
                                v-model="
                                    employeeForm.department
                                "
                                list="department-options"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <datalist
                                id="department-options"
                            >
                                <option
                                    v-for="department in departments"
                                    :key="department"
                                    :value="department"
                                />
                            </datalist>

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors
                                        .department
                                }}
                            </span>
                        </label>

                        <label
                            class="md:col-span-2"
                        >
                            <span
                                class="text-sm font-bold"
                            >
                                Position *
                            </span>

                            <input
                                v-model="
                                    employeeForm.position
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span
                                class="text-xs text-red-600"
                            >
                                {{
                                    employeeForm.errors
                                        .position
                                }}
                            </span>
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Salary Grade
                            </span>

                            <input
                                v-model="
                                    employeeForm.salary_grade
                                "
                                type="number"
                                min="1"
                                max="33"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Step
                            </span>

                            <input
                                v-model="
                                    employeeForm.step
                                "
                                type="number"
                                min="1"
                                max="8"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Employment Status
                            </span>

                            <input
                                v-model="
                                    employeeForm.employment_status
                                "
                                readonly
                                class="mt-2 w-full rounded-xl border-gray-300 bg-gray-100"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Date Hired
                            </span>

                            <input
                                v-model="
                                    employeeForm.date_hired
                                "
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Status *
                            </span>

                            <select
                                v-model="
                                    employeeForm.status
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            >
                                <option value="Active">
                                    Active
                                </option>

                                <option value="Inactive">
                                    Inactive
                                </option>

                                <option value="Retired">
                                    Retired
                                </option>

                                <option value="Separated">
                                    Separated
                                </option>
                            </select>
                        </label>
                    </div>

                    <h4
                        class="mb-4 mt-8 border-t pt-6 font-black text-blue-900"
                    >
                        Government Numbers
                    </h4>

                    <div
                        class="grid gap-5 md:grid-cols-4"
                    >
                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                GSIS Number
                            </span>

                            <input
                                v-model="
                                    employeeForm.gsis_number
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                Pag-IBIG Number
                            </span>

                            <input
                                v-model="
                                    employeeForm.pagibig_number
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                PhilHealth Number
                            </span>

                            <input
                                v-model="
                                    employeeForm.philhealth_number
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span
                                class="text-sm font-bold"
                            >
                                TIN
                            </span>

                            <input
                                v-model="
                                    employeeForm.tin_number
                                "
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>
                    </div>

                    <div
                        class="mt-8 flex justify-end gap-3 border-t pt-6"
                    >
                        <button
                            type="button"
                            class="rounded-xl border px-5 py-3 font-bold"
                            @click="
                                closeEmployeeModal
                            "
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-700 px-6 py-3 font-bold text-white disabled:opacity-50"
                            :disabled="
                                employeeForm.processing
                            "
                        >
                            {{
                                employeeForm.processing
                                    ? 'Saving...'
                                    : editingEmployee
                                      ? 'Update Employee'
                                      : 'Save Employee'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import Modal -->
        <div
            v-if="
                canManage &&
                importModalOpen
            "
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <form
                class="w-full max-w-lg rounded-3xl bg-white p-7 shadow-2xl"
                @submit.prevent="submitImport"
            >
                <div
                    class="flex items-start justify-between"
                >
                    <div>
                        <h3
                            class="text-2xl font-black text-blue-950"
                        >
                            Import Employee Excel File
                        </h3>

                        <p
                            class="mt-1 text-sm text-gray-500"
                        >
                            Upload the completed BEMS
                            Excel template.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-3xl text-gray-400"
                        @click="closeImportModal"
                    >
                        ×
                    </button>
                </div>

                <div
                    class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700"
                >
                    Do not rename or remove the
                    template column headings.
                    Duplicate employee numbers will
                    be skipped.
                </div>

                <label class="mt-6 block">
                    <span
                        class="text-sm font-bold"
                    >
                        Excel File *
                    </span>

                    <input
                        ref="importFileInput"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="mt-2 block w-full rounded-xl border border-gray-300 p-3"
                        required
                        @change="handleImportFile"
                    />

                    <span
                        class="text-xs text-red-600"
                    >
                        {{
                            importForm.errors.excel_file
                        }}
                    </span>
                </label>

                <div
                    class="mt-7 flex justify-end gap-3 border-t pt-5"
                >
                    <button
                        type="button"
                        class="rounded-xl border px-5 py-3 font-bold"
                        @click="closeImportModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 px-6 py-3 font-bold text-white disabled:opacity-50"
                        :disabled="
                            importForm.processing ||
                            !importForm.excel_file
                        "
                    >
                        {{
                            importForm.processing
                                ? 'Importing...'
                                : 'Import Employees'
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete Modal -->
        <div
            v-if="
                canManage &&
                deletingEmployee
            "
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div
                class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl"
            >
                <h3
                    class="text-xl font-black text-red-700"
                >
                    Delete Employee
                </h3>

                <p class="mt-3 text-gray-600">
                    Are you sure you want to delete
                    <strong>
                        {{ deletingEmployee.full_name }}
                    </strong>?
                </p>

                <div
                    class="mt-7 flex justify-end gap-3"
                >
                    <button
                        type="button"
                        class="rounded-xl border px-5 py-3 font-bold"
                        @click="
                            deletingEmployee = null
                        "
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-red-600 px-5 py-3 font-bold text-white"
                        @click="deleteEmployee"
                    >
                        Delete Employee
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>