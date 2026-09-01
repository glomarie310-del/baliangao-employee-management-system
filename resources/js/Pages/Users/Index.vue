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
    users: {
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

const modalOpen = ref(false);
const editingUser = ref(null);

const search = ref(
    props.filters.search ?? '',
);

const roleFilter = ref(
    props.filters.role ?? '',
);

const successMessage = computed(
    () => page.props.flash?.success,
);

const errorMessage = computed(
    () => page.props.flash?.error,
);

const requiresEmployeeLink = computed(() =>
    [
        'department_head',
        'employee',
    ].includes(form.role),
);

const availableEmployees = computed(() => {
    return props.employees.filter(
        (employee) => {
            /*
             * Display unlinked employees and the employee
             * currently linked to the user being edited.
             */
            return (
                ! employee.user_id ||
                employee.user_id ===
                    editingUser.value?.id
            );
        },
    );
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'employee',
    is_active: true,
    employee_id: '',
});

let searchTimer;

watch(
    [
        search,
        roleFilter,
    ],
    () => {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            router.get(
                route('users.index'),
                {
                    search:
                        search.value ||
                        undefined,

                    role:
                        roleFilter.value ||
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

watch(
    () => form.role,
    (role) => {
        if (
            ! [
                'department_head',
                'employee',
            ].includes(role)
        ) {
            form.employee_id = '';
        }
    },
);

const roleName = (role) => {
    const roles = {
        admin: 'Administrator',
        hrmo_staff: 'HRMO Staff',
        department_head: 'Department Head',
        employee: 'Regular Employee',
    };

    return roles[role] ?? role;
};

const roleClass = (role) => {
    const classes = {
        admin:
            'bg-violet-100 text-violet-700',

        hrmo_staff:
            'bg-blue-100 text-blue-700',

        department_head:
            'bg-amber-100 text-amber-700',

        employee:
            'bg-emerald-100 text-emerald-700',
    };

    return classes[role] ??
        'bg-gray-100 text-gray-700';
};

const employeeName = (employee) => {
    const middleInitial =
        employee.middle_name
            ? ` ${employee.middle_name
                .charAt(0)
                .toUpperCase()}.`
            : '';

    const suffix =
        employee.suffix
            ? ` ${employee.suffix}`
            : '';

    return `${employee.last_name}, ${employee.first_name}${middleInitial}${suffix}`;
};

const openCreateModal = () => {
    editingUser.value = null;

    form.reset();
    form.clearErrors();

    form.role = 'employee';
    form.is_active = true;
    form.employee_id = '';

    modalOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;

    form.clearErrors();

    form.name = user.name ?? '';
    form.email = user.email ?? '';
    form.password = '';
    form.password_confirmation = '';
    form.role = user.role ?? 'employee';
    form.is_active =
        Boolean(user.is_active);

    form.employee_id =
        user.employee?.id ?? '';

    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editingUser.value = null;

    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingUser.value) {
        form.put(
            route(
                'users.update',
                editingUser.value.id,
            ),
            {
                preserveScroll: true,
                onSuccess: closeModal,
            },
        );

        return;
    }

    form.post(
        route('users.store'),
        {
            preserveScroll: true,
            onSuccess: closeModal,
        },
    );
};

const toggleAccount = (user) => {
    const action =
        user.is_active
            ? 'deactivate'
            : 'activate';

    if (
        ! window.confirm(
            `Are you sure you want to ${action} this account?`,
        )
    ) {
        return;
    }

    router.patch(
        route(
            'users.toggle',
            user.id,
        ),
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="User Accounts" />

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
                        alt="Baliangao Logo"
                        class="h-14 w-14 rounded-full bg-white p-1 shadow"
                    />

                    <div>
                        <h1
                            class="text-xl font-black text-blue-950"
                        >
                            Baliangao Employee Management System
                        </h1>

                        <p class="text-sm text-gray-500">
                            System Administration
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

        <main
            class="mx-auto max-w-7xl px-5 py-8"
        >
            <!-- Messages -->
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

            <!-- Heading -->
            <section
                class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-blue-600"
                    >
                        System Administration
                    </p>

                    <h2
                        class="mt-1 text-3xl font-black text-blue-950"
                    >
                        User Accounts
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Manage system accounts, roles, status,
                        and employee-record links.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white shadow hover:bg-blue-800"
                    @click="openCreateModal"
                >
                    + Create Account
                </button>
            </section>

            <!-- Summary -->
            <section
                class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
            >
                <article
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-bold text-gray-500"
                    >
                        Total Accounts
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
                        class="mt-2 text-3xl font-black text-red-700"
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
                        Administrators
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-violet-700"
                    >
                        {{ summary.administrators }}
                    </p>
                </article>

                <article
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-bold text-gray-500"
                    >
                        Linked Employees
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-indigo-700"
                    >
                        {{ summary.linked }}
                    </p>
                </article>
            </section>

            <!-- Filters -->
            <section
                class="mt-7 rounded-2xl bg-white p-5 shadow-sm"
            >
                <div
                    class="grid gap-4 sm:grid-cols-2"
                >
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search name, email, employee or department..."
                        class="rounded-xl border-gray-300"
                    />

                    <select
                        v-model="roleFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">
                            All Roles
                        </option>

                        <option value="admin">
                            Administrator
                        </option>

                        <option value="hrmo_staff">
                            HRMO Staff
                        </option>

                        <option value="department_head">
                            Department Head
                        </option>

                        <option value="employee">
                            Regular Employee
                        </option>
                    </select>
                </div>
            </section>

            <!-- Users table -->
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
                                    Account
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Role
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Linked Employee
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs uppercase"
                                >
                                    Department
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

                        <tbody class="divide-y">
                            <tr
                                v-for="account in users.data"
                                :key="account.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-black uppercase text-blue-700"
                                        >
                                            {{
                                                account.name?.charAt(
                                                    0,
                                                )
                                            }}
                                        </div>

                                        <div>
                                            <p class="font-bold">
                                                {{ account.name }}
                                            </p>

                                            <p
                                                class="text-sm text-gray-500"
                                            >
                                                {{ account.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            roleClass(
                                                account.role,
                                            )
                                        "
                                    >
                                        {{
                                            roleName(
                                                account.role,
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <template
                                        v-if="account.employee"
                                    >
                                        <p class="font-bold">
                                            {{
                                                account.employee
                                                    .full_name
                                            }}
                                        </p>

                                        <p
                                            class="text-sm text-gray-500"
                                        >
                                            {{
                                                account.employee
                                                    .employee_number
                                            }}
                                        </p>
                                    </template>

                                    <span
                                        v-else
                                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-500"
                                    >
                                        Not linked
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-gray-600"
                                >
                                    {{
                                        account.employee
                                            ?.department ||
                                        '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            account.is_active
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-red-100 text-red-700'
                                        "
                                    >
                                        {{
                                            account.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right"
                                >
                                    <button
                                        type="button"
                                        class="mr-3 text-sm font-bold text-blue-600"
                                        @click="
                                            openEditModal(
                                                account,
                                            )
                                        "
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="text-sm font-bold"
                                        :class="
                                            account.is_active
                                                ? 'text-red-600'
                                                : 'text-emerald-600'
                                        "
                                        @click="
                                            toggleAccount(
                                                account,
                                            )
                                        "
                                    >
                                        {{
                                            account.is_active
                                                ? 'Deactivate'
                                                : 'Activate'
                                        }}
                                    </button>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    users.data.length === 0
                                "
                            >
                                <td
                                    colspan="6"
                                    class="px-5 py-16 text-center text-gray-500"
                                >
                                    No user accounts found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="users.links.length > 3"
                    class="flex flex-wrap gap-2 border-t p-5"
                >
                    <template
                        v-for="link in users.links"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-3 py-2 text-sm"
                            :class="
                                link.active
                                    ? 'bg-blue-700 text-white'
                                    : 'text-gray-700'
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

        <!-- Create/Edit modal -->
        <div
            v-if="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <form
                class="max-h-[95vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-7 shadow-2xl"
                @submit.prevent="submit"
            >
                <div
                    class="flex items-start justify-between"
                >
                    <div>
                        <h3
                            class="text-2xl font-black text-blue-950"
                        >
                            {{
                                editingUser
                                    ? 'Edit User Account'
                                    : 'Create User Account'
                            }}
                        </h3>

                        <p
                            class="mt-1 text-sm text-gray-500"
                        >
                            Set the role and employee-record
                            connection.
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

                <div
                    class="mt-6 grid gap-5 sm:grid-cols-2"
                >
                    <label class="sm:col-span-2">
                        <span
                            class="text-sm font-bold"
                        >
                            Account Name *
                        </span>

                        <input
                            v-model="form.name"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        />

                        <span
                            class="text-xs text-red-600"
                        >
                            {{ form.errors.name }}
                        </span>
                    </label>

                    <label class="sm:col-span-2">
                        <span
                            class="text-sm font-bold"
                        >
                            Email Address *
                        </span>

                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        />

                        <span
                            class="text-xs text-red-600"
                        >
                            {{ form.errors.email }}
                        </span>
                    </label>

                    <label>
                        <span
                            class="text-sm font-bold"
                        >
                            Password
                            {{
                                editingUser
                                    ? '(leave blank to keep)'
                                    : '*'
                            }}
                        </span>

                        <input
                            v-model="form.password"
                            type="password"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            :required="!editingUser"
                        />

                        <span
                            class="text-xs text-red-600"
                        >
                            {{ form.errors.password }}
                        </span>
                    </label>

                    <label>
                        <span
                            class="text-sm font-bold"
                        >
                            Confirm Password
                        </span>

                        <input
                            v-model="
                                form.password_confirmation
                            "
                            type="password"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            :required="!editingUser"
                        />
                    </label>

                    <label>
                        <span
                            class="text-sm font-bold"
                        >
                            Role *
                        </span>

                        <select
                            v-model="form.role"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        >
                            <option value="admin">
                                Administrator
                            </option>

                            <option value="hrmo_staff">
                                HRMO Staff
                            </option>

                            <option value="department_head">
                                Department Head
                            </option>

                            <option value="employee">
                                Regular Employee
                            </option>
                        </select>

                        <span
                            class="text-xs text-red-600"
                        >
                            {{ form.errors.role }}
                        </span>
                    </label>

                    <label>
                        <span
                            class="text-sm font-bold"
                        >
                            Account Status *
                        </span>

                        <select
                            v-model="form.is_active"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            required
                        >
                            <option :value="true">
                                Active
                            </option>

                            <option :value="false">
                                Inactive
                            </option>
                        </select>
                    </label>

                    <label class="sm:col-span-2">
                        <span
                            class="text-sm font-bold"
                        >
                            Link Employee Record
                            <span
                                v-if="
                                    requiresEmployeeLink
                                "
                                class="text-red-600"
                            >
                                *
                            </span>
                        </span>

                        <select
                            v-model="form.employee_id"
                            class="mt-2 w-full rounded-xl border-gray-300"
                            :required="
                                requiresEmployeeLink
                            "
                        >
                            <option value="">
                                {{
                                    requiresEmployeeLink
                                        ? 'Select Employee'
                                        : 'No employee link required'
                                }}
                            </option>

                            <option
                                v-for="employee in availableEmployees"
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{
                                    employee.employee_number
                                }}
                                —
                                {{
                                    employeeName(
                                        employee,
                                    )
                                }}
                                —
                                {{ employee.department }}
                            </option>
                        </select>

                        <span
                            class="mt-1 block text-xs text-red-600"
                        >
                            {{ form.errors.employee_id }}
                        </span>

                        <p
                            v-if="
                                requiresEmployeeLink
                            "
                            class="mt-2 rounded-xl bg-blue-50 p-3 text-xs leading-5 text-blue-700"
                        >
                            Department-head and regular-employee
                            accounts must be linked. The link
                            determines which department, leave
                            records, and documents they can access.
                        </p>
                    </label>
                </div>

                <div
                    class="mt-7 flex justify-end gap-3 border-t pt-5"
                >
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
                                : editingUser
                                  ? 'Update Account'
                                  : 'Create Account'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>