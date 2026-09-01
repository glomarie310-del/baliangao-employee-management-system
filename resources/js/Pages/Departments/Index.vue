<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    departments: {
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
const modalOpen = ref(false);
const editingDepartment = ref(null);
const deletingDepartment = ref(null);

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const form = useForm({
    code: '',
    name: '',
    office_head: '',
    contact_number: '',
    email: '',
    location: '',
    description: '',
    status: 'Active',
});

let searchTimer;

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('departments.index'),
            {
                search: search.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 400);
});

const openCreateModal = () => {
    editingDepartment.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'Active';
    modalOpen.value = true;
};

const openEditModal = (department) => {
    editingDepartment.value = department;
    form.clearErrors();

    form.code = department.code ?? '';
    form.name = department.name ?? '';
    form.office_head = department.office_head ?? '';
    form.contact_number = department.contact_number ?? '';
    form.email = department.email ?? '';
    form.location = department.location ?? '';
    form.description = department.description ?? '';
    form.status = department.status ?? 'Active';

    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editingDepartment.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingDepartment.value) {
        form.put(
            route('departments.update', editingDepartment.value.id),
            {
                preserveScroll: true,
                onSuccess: closeModal,
            },
        );

        return;
    }

    form.post(route('departments.store'), {
        preserveScroll: true,
        onSuccess: closeModal,
    });
};

const confirmDelete = (department) => {
    deletingDepartment.value = department;
};

const deleteDepartment = () => {
    if (!deletingDepartment.value) {
        return;
    }

    router.delete(
        route('departments.destroy', deletingDepartment.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                deletingDepartment.value = null;
            },
        },
    );
};

const statusClass = (status) => {
    return status === 'Active'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-gray-200 text-gray-700';
};
</script>

<template>
    <Head title="Departments and Offices" />

    <div class="min-h-screen bg-slate-100">
        <header class="border-b border-gray-200 bg-white shadow-sm">
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
                        class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-50"
                    >
                        Dashboard
                    </Link>

                    <Link
                        :href="route('employees.index')"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800"
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
                        Reference Records
                    </p>

                    <h2 class="mt-1 text-3xl font-black text-blue-950">
                        Departments and Offices
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Manage municipal departments, offices and office heads.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white shadow hover:bg-blue-800"
                    @click="openCreateModal"
                >
                    + Add Department
                </button>
            </section>

            <section
                class="mt-7 grid gap-4 sm:grid-cols-3"
            >
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Total Departments
                    </p>

                    <p class="mt-2 text-3xl font-black text-blue-950">
                        {{ summary.total }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Active
                    </p>

                    <p class="mt-2 text-3xl font-black text-emerald-700">
                        {{ summary.active }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Inactive
                    </p>

                    <p class="mt-2 text-3xl font-black text-gray-700">
                        {{ summary.inactive }}
                    </p>
                </article>
            </section>

            <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm">
                <label class="mb-2 block text-sm font-bold text-gray-700">
                    Search Department
                </label>

                <input
                    v-model="search"
                    type="search"
                    placeholder="Search by code, department or office head"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
            </section>

            <section
                class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-blue-950 text-white">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    Code
                                </th>

                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    Department/Office
                                </th>

                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    Office Head
                                </th>

                                <th class="px-5 py-4 text-left text-xs uppercase">
                                    Employees
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
                                v-for="department in departments"
                                :key="department.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-lg bg-blue-100 px-3 py-2 text-sm font-black text-blue-700"
                                    >
                                        {{ department.code }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800">
                                        {{ department.name }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ department.location || 'No location' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{
                                        department.office_head ||
                                        'Not assigned'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-black text-blue-800">
                                        {{ department.employee_count }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold"
                                        :class="statusClass(department.status)"
                                    >
                                        {{ department.status }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <button
                                        type="button"
                                        class="mr-3 text-sm font-bold text-blue-600"
                                        @click="openEditModal(department)"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="text-sm font-bold text-red-600"
                                        @click="confirmDelete(department)"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="departments.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-16 text-center text-gray-500"
                                >
                                    No departments found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                <div
                    class="flex items-center justify-between border-b px-6 py-5"
                >
                    <div>
                        <h3 class="text-2xl font-black text-blue-950">
                            {{
                                editingDepartment
                                    ? 'Edit Department'
                                    : 'Add Department'
                            }}
                        </h3>

                        <p class="text-sm text-gray-500">
                            Enter the department or office information.
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
                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Office Code *
                            </span>

                            <input
                                v-model="form.code"
                                placeholder="Example: HRMO"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span class="text-xs text-red-600">
                                {{ form.errors.code }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Department/Office Name *
                            </span>

                            <input
                                v-model="form.name"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span class="text-xs text-red-600">
                                {{ form.errors.name }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Office Head
                            </span>

                            <input
                                v-model="form.office_head"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Contact Number
                            </span>

                            <input
                                v-model="form.contact_number"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Email Address
                            </span>

                            <input
                                v-model="form.email"
                                type="email"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />

                            <span class="text-xs text-red-600">
                                {{ form.errors.email }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Office Location
                            </span>

                            <input
                                v-model="form.location"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Status *
                            </span>

                            <select
                                v-model="form.status"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            >
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </label>

                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                Description
                            </span>

                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            ></textarea>
                        </label>
                    </div>

                    <div class="mt-7 flex justify-end gap-3 border-t pt-5">
                        <button
                            type="button"
                            class="rounded-xl border border-gray-300 px-5 py-3 font-bold"
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
                                    : editingDepartment
                                      ? 'Update Department'
                                      : 'Save Department'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete modal -->
        <div
            v-if="deletingDepartment"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl">
                <h3 class="text-xl font-black text-red-700">
                    Delete Department
                </h3>

                <p class="mt-3 text-gray-600">
                    Delete
                    <strong>{{ deletingDepartment.name }}</strong>?
                </p>

                <p
                    v-if="deletingDepartment.employee_count > 0"
                    class="mt-3 rounded-xl bg-amber-50 p-3 text-sm text-amber-700"
                >
                    This office has
                    {{ deletingDepartment.employee_count }} assigned
                    employee(s) and cannot be deleted.
                </p>

                <div class="mt-7 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-300 px-5 py-3 font-bold"
                        @click="deletingDepartment = null"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-red-600 px-5 py-3 font-bold text-white disabled:opacity-40"
                        :disabled="deletingDepartment.employee_count > 0"
                        @click="deleteDepartment"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>