<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalEmployees: 0,
            activeEmployees: 0,
            hrmoStaff: 0,
            inactiveAccounts: 0,
        }),
    },

    recentUsers: {
        type: Array,
        default: () => [],
    },

    departmentName: {
        type: String,
        default: null,
    },

    hasLinkedEmployee: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(
    () => page.props.auth?.user ?? {},
);

const isAdmin = computed(
    () => user.value.role === 'admin',
);

const isHrmoStaff = computed(
    () => user.value.role === 'hrmo_staff',
);

const isDepartmentHead = computed(
    () => user.value.role === 'department_head',
);

const isEmployee = computed(
    () => user.value.role === 'employee',
);

const canManageHr = computed(() =>
    [
        'admin',
        'hrmo_staff',
    ].includes(user.value.role),
);

const canViewEmployeeMasterlist = computed(() =>
    [
        'admin',
        'hrmo_staff',
        'department_head',
    ].includes(user.value.role),
);

const navigation = computed(() => {
    const links = [
        {
            label: 'Dashboard',
            icon: '🏠',
            routeName: 'dashboard',
            roles: [
                'admin',
                'hrmo_staff',
                'department_head',
                'employee',
            ],
        },

        {
            label: 'My Employee Record',
            icon: '🪪',
            routeName: 'employee-portal.index',
            roles: [
                'department_head',
                'employee',
            ],
        },

        {
            label: 'My Leave Monitoring',
            icon: '📅',
            routeName: 'employee-portal.leaves',
            roles: [
                'department_head',
                'employee',
            ],
        },

        {
            label: 'My Document Tracker',
            icon: '📄',
            routeName: 'employee-portal.documents',
            roles: [
                'department_head',
                'employee',
            ],
        },

        {
            label: isDepartmentHead.value
                ? 'Department Employees'
                : 'Employee Masterlist',
            icon: '👥',
            routeName: 'employees.index',
            roles: [
                'admin',
                'hrmo_staff',
                'department_head',
            ],
        },

        {
            label: 'Departments',
            icon: '🏛️',
            routeName: 'departments.index',
            roles: [
                'admin',
                'hrmo_staff',
                'department_head',
            ],
        },

        {
            label: 'Leave Monitoring',
            icon: '🗓️',
            routeName: 'leave-records.index',
            roles: [
                'admin',
                'hrmo_staff',
            ],
        },

        {
            label: 'Document Tracker',
            icon: '🗂️',
            routeName: 'documents.index',
            roles: [
                'admin',
                'hrmo_staff',
            ],
        },

        {
            label: 'Reports',
            icon: '📊',
            routeName: 'reports.index',
            roles: [
                'admin',
                'hrmo_staff',
            ],
        },

        {
            label: 'User Accounts',
            icon: '🔐',
            routeName: 'users.index',
            roles: [
                'admin',
            ],
        },
    ];

    return links.filter((link) =>
        link.roles.includes(user.value.role),
    );
});

const modules = computed(() => {
    const items = [
        {
            title: 'My Employee Record',
            description:
                'View your personal and employment information.',
            icon: '🪪',
            routeName: 'employee-portal.index',
            action: 'View My Record →',
            roles: [
                'department_head',
                'employee',
            ],
            iconClass: 'bg-blue-100',
            actionClass: 'text-blue-600',
        },

        {
            title: 'My Leave Monitoring',
            description:
                'Apply for leave and monitor your leave applications.',
            icon: '📅',
            routeName: 'employee-portal.leaves',
            action: 'View My Leave Records →',
            roles: [
                'department_head',
                'employee',
            ],
            iconClass: 'bg-emerald-100',
            actionClass: 'text-emerald-600',
        },

        {
            title: 'My Document Tracker',
            description:
                'View and download your personal HRMO documents.',
            icon: '📄',
            routeName: 'employee-portal.documents',
            action: 'View My Documents →',
            roles: [
                'department_head',
                'employee',
            ],
            iconClass: 'bg-amber-100',
            actionClass: 'text-amber-600',
        },

        {
            title: isDepartmentHead.value
                ? 'Department Employees'
                : 'Employee Masterlist',

            description: isDepartmentHead.value
                ? 'View employees assigned to your department.'
                : 'Manage regular municipal employee records.',

            icon: '👥',
            routeName: 'employees.index',

            action: isDepartmentHead.value
                ? 'View Department Employees →'
                : 'Manage Employees →',

            roles: [
                'admin',
                'hrmo_staff',
                'department_head',
            ],

            iconClass: 'bg-blue-100',
            actionClass: 'text-blue-600',
        },

        {
            title: 'Departments',
            description:
                'View municipal departments, offices, and office heads.',
            icon: '🏛️',
            routeName: 'departments.index',
            action: 'View Departments →',
            roles: [
                'admin',
                'hrmo_staff',
                'department_head',
            ],
            iconClass: 'bg-indigo-100',
            actionClass: 'text-indigo-600',
        },

        {
            title: 'Leave Monitoring',
            description:
                'Manage and approve employee leave applications.',
            icon: '🗓️',
            routeName: 'leave-records.index',
            action: 'Manage Leave Records →',
            roles: [
                'admin',
                'hrmo_staff',
            ],
            iconClass: 'bg-emerald-100',
            actionClass: 'text-emerald-600',
        },

        {
            title: 'Document Tracker',
            description:
                'Upload and manage employee document requirements.',
            icon: '🗂️',
            routeName: 'documents.index',
            action: 'Manage Documents →',
            roles: [
                'admin',
                'hrmo_staff',
            ],
            iconClass: 'bg-amber-100',
            actionClass: 'text-amber-600',
        },

        {
            title: 'Reports',
            description:
                'Print and export employee, leave, and document reports.',
            icon: '📊',
            routeName: 'reports.index',
            action: 'Generate Reports →',
            roles: [
                'admin',
                'hrmo_staff',
            ],
            iconClass: 'bg-pink-100',
            actionClass: 'text-pink-600',
        },

        {
            title: 'User Accounts',
            description:
                'Create user accounts, assign roles, and control access.',
            icon: '🔐',
            routeName: 'users.index',
            action: 'Manage Accounts →',
            roles: [
                'admin',
            ],
            iconClass: 'bg-violet-100',
            actionClass: 'text-violet-600',
        },
    ];

    return items.filter((item) =>
        item.roles.includes(user.value.role),
    );
});

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
        admin: 'bg-violet-100 text-violet-700',
        hrmo_staff: 'bg-blue-100 text-blue-700',
        department_head:
            'bg-amber-100 text-amber-700',
        employee:
            'bg-emerald-100 text-emerald-700',
    };

    return classes[role] ??
        'bg-gray-100 text-gray-700';
};

const formatDate = (date) => {
    if (! date) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        'en-PH',
        {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        },
    ).format(new Date(date));
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const isCurrentRoute = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-slate-100">
        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col bg-gradient-to-b from-blue-950 to-blue-900 text-white shadow-2xl transition-transform duration-300 lg:translate-x-0"
            :class="
                sidebarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full'
            "
        >
            <div
                class="flex h-24 items-center gap-3 border-b border-white/10 px-6"
            >
                <img
                    src="/images/baliangao-logo.png"
                    alt="Baliangao Logo"
                    class="h-14 w-14 rounded-full bg-white p-1 shadow"
                />

                <div>
                    <h1 class="text-xl font-black tracking-wide">
                        BEMS
                    </h1>

                    <p class="text-xs leading-4 text-blue-200">
                        Employee Management System
                    </p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <p
                    class="px-3 pb-3 text-xs font-bold uppercase tracking-widest text-blue-300"
                >
                    Main Menu
                </p>

                <div class="space-y-2">
                    <Link
                        v-for="item in navigation"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition"
                        :class="
                            isCurrentRoute(item.routeName)
                                ? 'bg-white text-blue-950 shadow'
                                : 'text-blue-100 hover:bg-white/10 hover:text-white'
                        "
                        @click="closeSidebar"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center text-lg"
                        >
                            {{ item.icon }}
                        </span>

                        <span>
                            {{ item.label }}
                        </span>
                    </Link>
                </div>

                <p
                    class="px-3 pb-3 pt-8 text-xs font-bold uppercase tracking-widest text-blue-300"
                >
                    Account
                </p>

                <div class="space-y-2">
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold text-blue-100 hover:bg-white/10 hover:text-white"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center text-lg"
                        >
                            ⚙️
                        </span>

                        My Account
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left font-semibold text-red-200 hover:bg-red-500/20 hover:text-white"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center text-lg"
                        >
                            🚪
                        </span>

                        Log Out
                    </Link>
                </div>
            </nav>

            <div class="border-t border-white/10 p-5">
                <p class="text-xs font-bold text-blue-200">
                    Municipality of Baliangao
                </p>

                <p class="mt-1 text-xs text-blue-300">
                    Human Resource Management Office
                </p>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-72">
            <header
                class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur"
            >
                <div
                    class="flex h-20 items-center justify-between px-5 sm:px-8"
                >
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            class="rounded-xl border border-gray-200 p-2 text-gray-600 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <div>
                            <h2
                                class="text-xl font-black text-blue-950 sm:text-2xl"
                            >
                                Dashboard
                            </h2>

                            <p
                                class="hidden text-sm text-gray-500 sm:block"
                            >
                                Baliangao Employee Management System
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-bold text-gray-800">
                                {{ user.name }}
                            </p>

                            <p class="text-xs text-gray-500">
                                {{ roleName(user.role) }}
                            </p>
                        </div>

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 font-black uppercase text-blue-700"
                        >
                            {{ user.name?.charAt(0) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-5 sm:p-8">
                <!-- Welcome -->
                <section
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-950 via-blue-800 to-blue-600 p-7 text-white shadow-xl sm:p-10"
                >
                    <div class="relative z-10">
                        <p
                            class="text-sm font-bold uppercase tracking-[0.25em] text-blue-200"
                        >
                            Municipality of Baliangao
                        </p>

                        <h2
                            class="mt-3 text-3xl font-black sm:text-4xl"
                        >
                            Welcome, {{ user.name }}!
                        </h2>

                        <p
                            v-if="isEmployee"
                            class="mt-3 max-w-2xl leading-7 text-blue-100"
                        >
                            Apply for leave and securely access your
                            employee documents and personal record.
                        </p>

                        <div
                            v-else-if="isDepartmentHead"
                            class="mt-3 max-w-2xl"
                        >
                            <p class="leading-7 text-blue-100">
                                View employees under your department,
                                apply for leave, and access your personal
                                HRMO documents.
                            </p>

                            <p
                                v-if="departmentName"
                                class="mt-2 text-sm font-bold text-blue-200"
                            >
                                Department: {{ departmentName }}
                            </p>
                        </div>

                        <p
                            v-else
                            class="mt-3 max-w-2xl leading-7 text-blue-100"
                        >
                            Manage employee records, leave
                            applications, documents, departments, and
                            reports securely.
                        </p>

                        <Link
                            v-if="
                                isEmployee ||
                                isDepartmentHead
                            "
                            :href="
                                route(
                                    'employee-portal.leaves',
                                )
                            "
                            class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-800 shadow"
                        >
                            Apply for Leave →
                        </Link>

                        <Link
                            v-else
                            :href="
                                route(
                                    'employees.index',
                                )
                            "
                            class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-800 shadow"
                        >
                            View Employee Masterlist →
                        </Link>
                    </div>

                    <div
                        class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/10"
                    ></div>
                </section>
                <div
                    v-if="
                        (isEmployee || isDepartmentHead) &&
                        !hasLinkedEmployee
                    "
                    class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-700"
                >
                    <p class="font-black">
                        Employee Record Not Linked
                    </p>

                    <p class="mt-2 text-sm leading-6">
                        Your user account has not been linked to an employee
                        record. Contact the BEMS administrator so your
                        department, leave records, and documents can be accessed.
                    </p>
                </div>

                <!-- Employee notice -->
                <section
                    v-if="
                        isEmployee ||
                        isDepartmentHead
                    "
                    class="mt-8 rounded-2xl border border-blue-200 bg-blue-50 p-6"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-2xl"
                        >
                            🔒
                        </div>

                        <div>
                            <h3 class="font-black text-blue-950">
                                Personal Records Protection
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-blue-700"
                            >
                                Your leave and document sections only
                                contain records linked to your own
                                employee account.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Statistics -->
                <section
                    v-if="
                        canViewEmployeeMasterlist
                    "
                    class="mt-8 grid gap-5 sm:grid-cols-2"
                    :class="
                        canManageHr
                            ? 'xl:grid-cols-4'
                            : 'xl:grid-cols-2'
                    "
                >
                    <article
                        class="rounded-2xl bg-white p-6 shadow-sm"
                    >
                        <p
                            class="text-sm font-bold uppercase text-gray-500"
                        >
                            {{
                                isDepartmentHead
                                    ? 'Department Employees'
                                    : 'Regular Employees'
                            }}
                        </p>

                        <p
                            class="mt-3 text-4xl font-black text-blue-950"
                        >
                            {{ stats.totalEmployees ?? 0 }}
                        </p>
                    </article>

                    <article
                        class="rounded-2xl bg-white p-6 shadow-sm"
                    >
                        <p
                            class="text-sm font-bold uppercase text-gray-500"
                        >
                            Active Employees
                        </p>

                        <p
                            class="mt-3 text-4xl font-black text-emerald-700"
                        >
                            {{ stats.activeEmployees ?? 0 }}
                        </p>
                    </article>

                    <article
                        v-if="canManageHr"
                        class="rounded-2xl bg-white p-6 shadow-sm"
                    >
                        <p
                            class="text-sm font-bold uppercase text-gray-500"
                        >
                            HRMO Accounts
                        </p>

                        <p
                            class="mt-3 text-4xl font-black text-violet-700"
                        >
                            {{ stats.hrmoStaff ?? 0 }}
                        </p>
                    </article>

                    <article
                        v-if="canManageHr"
                        class="rounded-2xl bg-white p-6 shadow-sm"
                    >
                        <p
                            class="text-sm font-bold uppercase text-gray-500"
                        >
                            Inactive Accounts
                        </p>

                        <p
                            class="mt-3 text-4xl font-black text-red-600"
                        >
                            {{ stats.inactiveAccounts ?? 0 }}
                        </p>
                    </article>
                </section>

                <!-- Modules -->
                <section class="mt-8">
                    <h3
                        class="text-xl font-black text-blue-950"
                    >
                        System Modules
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Select a module to continue.
                    </p>

                    <div
                        class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
                    >
                        <Link
                            v-for="item in modules"
                            :key="item.routeName"
                            :href="route(item.routeName)"
                            class="rounded-2xl bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                        >
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl text-2xl"
                                :class="item.iconClass"
                            >
                                {{ item.icon }}
                            </div>

                            <h4
                                class="mt-5 text-lg font-black text-blue-950"
                            >
                                {{ item.title }}
                            </h4>

                            <p
                                class="mt-2 text-sm leading-6 text-gray-500"
                            >
                                {{ item.description }}
                            </p>

                            <p
                                class="mt-4 text-sm font-bold"
                                :class="item.actionClass"
                            >
                                {{ item.action }}
                            </p>
                        </Link>
                    </div>
                </section>

                <!-- Admin recent accounts -->
                <section
                    v-if="isAdmin"
                    class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b px-6 py-5"
                    >
                        <div>
                            <h3
                                class="text-lg font-black text-blue-950"
                            >
                                Recent System Accounts
                            </h3>

                            <p
                                class="mt-1 text-sm text-gray-500"
                            >
                                Recently created BEMS accounts
                            </p>
                        </div>

                        <Link
                            :href="route('users.index')"
                            class="text-sm font-bold text-blue-600"
                        >
                            View All
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase text-gray-500"
                                    >
                                        Account
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase text-gray-500"
                                    >
                                        Role
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase text-gray-500"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs uppercase text-gray-500"
                                    >
                                        Created
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                <tr
                                    v-for="account in recentUsers"
                                    :key="account.id"
                                >
                                    <td class="px-6 py-4">
                                        <p class="font-bold">
                                            {{ account.name }}
                                        </p>

                                        <p
                                            class="text-sm text-gray-500"
                                        >
                                            {{ account.email }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-4">
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

                                    <td class="px-6 py-4">
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
                                        class="px-6 py-4 text-sm text-gray-500"
                                    >
                                        {{
                                            formatDate(
                                                account.created_at,
                                            )
                                        }}
                                    </td>
                                </tr>

                                <tr
                                    v-if="
                                        recentUsers.length === 0
                                    "
                                >
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        No accounts found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>