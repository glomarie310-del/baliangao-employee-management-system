<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    documents: {
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
const editingDocument = ref(null);
const deletingDocument = ref(null);
const fileInput = ref(null);

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.document_type ?? '');
const statusFilter = ref(props.filters.status ?? '');

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const documentTypes = [
    'Personal Data Sheet',
    'Appointment Paper',
    'Oath of Office',
    'Position Description Form',
    'Medical Certificate',
    'NBI Clearance',
    'Police Clearance',
    'Birth Certificate',
    'Marriage Certificate',
    'Diploma',
    'Transcript of Records',
    'Certificate of Eligibility',
    'Training Certificate',
    'Service Record',
    'SALN',
    'IPCR',
    'Other',
];

const form = useForm({
    employee_id: '',
    document_type: '',
    title: '',
    reference_number: '',
    issuing_agency: '',
    date_issued: '',
    expiration_date: '',
    remarks: '',
    document_file: null,
});

let searchTimer;

watch([search, typeFilter, statusFilter], () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('documents.index'),
            {
                search: search.value || undefined,
                document_type: typeFilter.value || undefined,
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

const employeeName = (employee) => {
    const middleInitial = employee.middle_name
        ? ` ${employee.middle_name.charAt(0).toUpperCase()}.`
        : '';

    const suffix = employee.suffix ? ` ${employee.suffix}` : '';

    return `${employee.last_name}, ${employee.first_name}${middleInitial}${suffix}`;
};

const formatDate = (date) => {
    if (!date) {
        return 'No expiration';
    }

    return new Intl.DateTimeFormat('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(date));
};

const dateOnly = (date) => {
    if (!date) {
        return '';
    }

    return String(date).substring(0, 10);
};

const statusClass = (status) => {
    const classes = {
        Valid: 'bg-emerald-100 text-emerald-700',
        'Expiring Soon': 'bg-amber-100 text-amber-700',
        Expired: 'bg-red-100 text-red-700',
    };

    return classes[status] ?? 'bg-gray-100 text-gray-700';
};

const openCreateModal = () => {
    editingDocument.value = null;

    form.reset();
    form.clearErrors();

    if (fileInput.value) {
        fileInput.value.value = '';
    }

    modalOpen.value = true;
};

const openEditModal = (document) => {
    editingDocument.value = document;

    form.clearErrors();

    form.employee_id = document.employee_id;
    form.document_type = document.document_type;
    form.title = document.title;
    form.reference_number = document.reference_number ?? '';
    form.issuing_agency = document.issuing_agency ?? '';
    form.date_issued = dateOnly(document.date_issued);
    form.expiration_date = dateOnly(document.expiration_date);
    form.remarks = document.remarks ?? '';
    form.document_file = null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }

    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editingDocument.value = null;

    form.reset();
    form.clearErrors();

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const handleFile = (event) => {
    form.document_file = event.target.files[0] ?? null;
};

const submit = () => {
    if (editingDocument.value) {
        form.post(
            route('documents.update', editingDocument.value.id),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => closeModal(),
            },
        );

        return;
    }

    form.post(route('documents.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const confirmDelete = (document) => {
    deletingDocument.value = document;
};

const cancelDelete = () => {
    deletingDocument.value = null;
};

const deleteDocument = () => {
    if (!deletingDocument.value) {
        return;
    }

    router.delete(
        route('documents.destroy', deletingDocument.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                deletingDocument.value = null;
            },
        },
    );
};

const clearFilters = () => {
    search.value = '';
    typeFilter.value = '';
    statusFilter.value = '';
};
</script>

<template>
    <Head title="Document Tracker" />

    <div class="min-h-screen bg-slate-100">
        <!-- Header -->
        <header class="border-b border-gray-200 bg-white shadow-sm">
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
                        <h1 class="text-xl font-black text-blue-950">
                            Baliangao Employee Management System
                        </h1>

                        <p class="text-sm text-gray-500">
                            Human Resource Management Office
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
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
            <!-- Messages -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-medium text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <div
                v-if="errorMessage"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 font-medium text-red-700"
            >
                {{ errorMessage }}
            </div>

            <!-- Page heading -->
            <section
                class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-blue-600"
                    >
                        Employee Requirements
                    </p>

                    <h2 class="mt-1 text-3xl font-black text-blue-950">
                        Document Tracker
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Upload and monitor employee documents and expiration
                        dates.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white shadow hover:bg-blue-800"
                    @click="openCreateModal"
                >
                    + Upload Document
                </button>
            </section>

            <!-- Statistics -->
            <section
                class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Total Documents
                    </p>

                    <p class="mt-2 text-3xl font-black text-blue-950">
                        {{ summary.total }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Valid
                    </p>

                    <p class="mt-2 text-3xl font-black text-emerald-700">
                        {{ summary.valid }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Expiring Soon
                    </p>

                    <p class="mt-2 text-3xl font-black text-amber-700">
                        {{ summary.expiring }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">
                        Expired
                    </p>

                    <p class="mt-2 text-3xl font-black text-red-700">
                        {{ summary.expired }}
                    </p>
                </article>
            </section>

            <!-- Filters -->
            <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm">
                <div class="grid gap-4 lg:grid-cols-4">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search employee or document..."
                        class="rounded-xl border-gray-300 lg:col-span-2"
                    />

                    <select
                        v-model="typeFilter"
                        class="rounded-xl border-gray-300"
                    >
                        <option value="">
                            All Document Types
                        </option>

                        <option
                            v-for="type in documentTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ type }}
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
                            <option value="Valid">
                                Valid
                            </option>
                            <option value="Expiring Soon">
                                Expiring Soon
                            </option>
                            <option value="Expired">
                                Expired
                            </option>
                        </select>

                        <button
                            type="button"
                            class="rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-600 hover:bg-gray-100"
                            @click="clearFilters"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </section>

            <!-- Documents table -->
            <section
                class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-blue-950 text-white">
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs font-bold uppercase"
                                >
                                    Employee
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-bold uppercase"
                                >
                                    Document
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-bold uppercase"
                                >
                                    Reference
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-bold uppercase"
                                >
                                    Expiration
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-bold uppercase"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-bold uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="document in documents.data"
                                :key="document.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800">
                                        {{ document.employee.full_name }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        {{ document.employee.employee_number }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800">
                                        {{ document.title }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        {{ document.document_type }}
                                    </p>

                                    <p
                                        v-if="document.original_file_name"
                                        class="mt-1 max-w-xs truncate text-xs text-blue-600"
                                    >
                                        {{ document.original_file_name }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-gray-600"
                                >
                                    {{
                                        document.reference_number ||
                                        'Not provided'
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-sm text-gray-600"
                                >
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

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right"
                                >
                                    <a
                                        v-if="document.file_path"
                                        :href="
                                            route(
                                                'documents.download',
                                                document.id,
                                            )
                                        "
                                        class="mr-3 text-sm font-bold text-emerald-600 hover:text-emerald-900"
                                    >
                                        Download
                                    </a>

                                    <button
                                        type="button"
                                        class="mr-3 text-sm font-bold text-blue-600 hover:text-blue-900"
                                        @click="openEditModal(document)"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="text-sm font-bold text-red-600 hover:text-red-900"
                                        @click="confirmDelete(document)"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="documents.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-16 text-center"
                                >
                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-3xl"
                                    >
                                        📄
                                    </div>

                                    <p class="mt-4 font-bold text-gray-700">
                                        No documents found
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Upload a document or change the filters.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="documents.links.length > 3"
                    class="flex flex-wrap gap-2 border-t border-gray-100 p-5"
                >
                    <template
                        v-for="link in documents.links"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-3 py-2 text-sm font-semibold"
                            :class="
                                link.active
                                    ? 'border-blue-700 bg-blue-700 text-white'
                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50'
                            "
                            preserve-scroll
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

        <!-- Upload/Edit modal -->
        <div
            v-if="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div
                class="max-h-[95vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-6 py-5"
                >
                    <div>
                        <h3 class="text-2xl font-black text-blue-950">
                            {{
                                editingDocument
                                    ? 'Edit Employee Document'
                                    : 'Upload Employee Document'
                            }}
                        </h3>

                        <p class="text-sm text-gray-500">
                            Enter the document information below.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-3xl text-gray-400 hover:text-gray-700"
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
                                <option value="">
                                    Select Employee
                                </option>

                                <option
                                    v-for="employee in employees"
                                    :key="employee.id"
                                    :value="employee.id"
                                >
                                    {{ employee.employee_number }} —
                                    {{ employeeName(employee) }}
                                </option>
                            </select>

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.employee_id }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Document Type *
                            </span>

                            <select
                                v-model="form.document_type"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            >
                                <option value="">
                                    Select Document Type
                                </option>

                                <option
                                    v-for="type in documentTypes"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ type }}
                                </option>
                            </select>

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.document_type }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Document Title *
                            </span>

                            <input
                                v-model="form.title"
                                class="mt-2 w-full rounded-xl border-gray-300"
                                required
                            />

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.title }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Reference Number
                            </span>

                            <input
                                v-model="form.reference_number"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.reference_number }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Issuing Agency
                            </span>

                            <input
                                v-model="form.issuing_agency"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.issuing_agency }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Date Issued
                            </span>

                            <input
                                v-model="form.date_issued"
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.date_issued }}
                            </span>
                        </label>

                        <label>
                            <span class="text-sm font-bold text-gray-700">
                                Expiration Date
                            </span>

                            <input
                                v-model="form.expiration_date"
                                type="date"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            />

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.expiration_date }}
                            </span>
                        </label>

                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                Document File
                            </span>

                            <input
                                ref="fileInput"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="mt-2 block w-full rounded-xl border border-gray-300 p-3"
                                @change="handleFile"
                            />

                            <p class="mt-2 text-xs text-gray-500">
                                Accepted: PDF, JPG, PNG, DOC and DOCX. Maximum
                                file size: 10 MB.
                            </p>

                            <p
                                v-if="
                                    editingDocument?.original_file_name
                                "
                                class="mt-2 text-sm font-semibold text-blue-600"
                            >
                                Current file:
                                {{ editingDocument.original_file_name }}
                            </p>

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.document_file }}
                            </span>
                        </label>

                        <label class="sm:col-span-2">
                            <span class="text-sm font-bold text-gray-700">
                                Remarks
                            </span>

                            <textarea
                                v-model="form.remarks"
                                rows="3"
                                class="mt-2 w-full rounded-xl border-gray-300"
                            ></textarea>

                            <span
                                class="mt-1 block text-xs text-red-600"
                            >
                                {{ form.errors.remarks }}
                            </span>
                        </label>
                    </div>

                    <div
                        class="mt-7 flex justify-end gap-3 border-t border-gray-100 pt-5"
                    >
                        <button
                            type="button"
                            class="rounded-xl border border-gray-300 px-5 py-3 font-bold text-gray-700 hover:bg-gray-100"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-700 px-6 py-3 font-bold text-white hover:bg-blue-800 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">
                                Saving...
                            </span>

                            <span v-else-if="editingDocument">
                                Update Document
                            </span>

                            <span v-else>
                                Upload Document
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete modal -->
        <div
            v-if="deletingDocument"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div
                class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl"
            >
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl"
                >
                    ⚠️
                </div>

                <h3 class="mt-5 text-xl font-black text-red-700">
                    Delete Employee Document
                </h3>

                <p class="mt-3 leading-7 text-gray-600">
                    Are you sure you want to delete
                    <strong>{{ deletingDocument.title }}</strong>
                    belonging to
                    <strong>
                        {{ deletingDocument.employee.full_name }}
                    </strong>?
                </p>

                <p class="mt-2 text-sm text-red-600">
                    The uploaded file will also be permanently deleted.
                </p>

                <div class="mt-7 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-300 px-5 py-3 font-bold"
                        @click="cancelDelete"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-red-600 px-5 py-3 font-bold text-white hover:bg-red-700"
                        @click="deleteDocument"
                    >
                        Delete Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>