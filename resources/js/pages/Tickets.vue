<script setup>
import { ref, onMounted } from "vue";
import Layout from "../Layout/Layout.vue";
import axios from "axios";

defineOptions({ layout: Layout });

/* ✅ Tickets */
const tickets = ref([]);

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
});

const filters = ref({
    subject: "",
    status: "",
    category: "",
});

function getTickets(page = 1) {
    axios
        .get("/api/tickets", {
            params: {
                subject: filters.value.subject,
                status: filters.value.status,
                category: filters.value.category,
                page: page,
            },
        })
        .then((response) => {
            tickets.value = response.data.data; // ✅ "data" from Laravel paginate
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total,
            };
        });
}

function changePage(page) {
    if (page > 0 && page <= pagination.value.last_page) {
        getTickets(page);
    }
}

function searchTickets() {
    getTickets(1); // ✅ reset to page 1
}

/* ✅ Modals */
const showModal = ref(false);
const newTicket = ref({
    subject: "",
    status: "open",
    category: "other",
    confidence: "",
    note: "",
});

const DetailsModal = ref(false);
const selectedTicket = ref({});

function openDetailsModal(ticket) {
    selectedTicket.value = { ...ticket };
    DetailsModal.value = true;
}

function closeDetailsModal() {
    DetailsModal.value = false;
}

onMounted(() => {
    getTickets();
});
</script>

<template>
    <div class="ticket-container">
        <!-- Toolbar with filters and new ticket -->
        <div class="ticket-toolbar">
            <div class="filters">
                <input
                    v-model="filters.subject"
                    type="text"
                    placeholder="🔍 tickets subject..."
                    class="filter-input"
                />

                <!-- Status filter -->
                <select v-model="filters.status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="closed">Closed</option>
                </select>

                <!-- Category filter -->
                <select v-model="filters.category" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="account">Account</option>
                    <option value="bug">Bug</option>
                    <option value="feature">Feature</option>
                    <option value="billing">Billing</option>
                    <option value="other">Others</option>
                </select>

                <button class="btn btn--primary" @click="searchTickets">
                    Search
                </button>
            </div>

            <button class="btn btn--primary" @click="showModal = true">
                + New Ticket
            </button>
        </div>

        <!-- Table -->
        <table class="ticket-list">
            <thead class="ticket-list__head">
                <tr>
                    <th class="ticket-list__th">Subject</th>
                    <th class="ticket-list__th">Status</th>
                    <th class="ticket-list__th">Category</th>
                    <th class="ticket-list__th">Confidence</th>
                    <th class="ticket-list__th">Note</th>
                    <th class="ticket-list__th">Action</th>
                </tr>
            </thead>
            <tbody class="ticket-list__body">
                <tr
                    v-for="(ticket, index) in tickets"
                    :key="index"
                    class="ticket-list__row"
                >
                    <td class="ticket-list__cell">{{ ticket.subject }}</td>
                    <td class="ticket-list__cell">
                        <span
                            :class="[
                                'badge',
                                ticket.status === 'open'
                                    ? 'badge--open'
                                    : ticket.status === 'in_progress'
                                    ? 'badge--in_progress'
                                    : 'badge--closed',
                            ]"
                        >
                            {{ ticket.status }}
                        </span>
                    </td>
                    <td class="ticket-list__cell">{{ ticket.category }}</td>
                    <td class="ticket-list__cell">{{ ticket.confidence }}</td>
                    <td class="ticket-list__cell">{{ ticket.note }}</td>
                    <td class="ticket-list__cell">
                        <button
                            class="btn btn--small"
                            @click="openDetailsModal(ticket)"
                        >
                            View
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <button
                :disabled="pagination.current_page === 1"
                @click="changePage(pagination.current_page - 1)"
            >
                Prev
            </button>

            <span>
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>

            <button
                :disabled="pagination.current_page === pagination.last_page"
                @click="changePage(pagination.current_page + 1)"
            >
                Next
            </button>
        </div>

        <!-- New Ticket Modal -->
        <div
            v-if="showModal"
            class="modal-overlay"
            @click.self="showModal = false"
        >
            <div class="modal">
                <h2 class="modal-title">New Ticket</h2>
                <form @submit.prevent="addTicket">
                    <input
                        v-model="newTicket.subject"
                        type="text"
                        placeholder="Subject"
                        class="modal-input"
                        required
                    />
                    <select v-model="newTicket.status" class="modal-input" required>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select v-model="newTicket.category" class="modal-input" required>
                        <option value="account">Account</option>
                        <option value="bug">Bug</option>
                        <option value="feature">Feature</option>
                        <option value="billing">Billing</option>
                        <option value="other">Others</option>
                    </select>
                    <input
                        v-model="newTicket.confidence"
                        type="number"
                        step="0.01"
                        placeholder="Confidence"
                        class="modal-input"
                        required
                    />
                    <textarea
                        v-model="newTicket.note"
                        placeholder="Note"
                        class="modal-input"
                    ></textarea>
                    <div class="modal-actions">
                        <button
                            type="button"
                            class="btn btn--outline"
                            @click="showModal = false"
                        >
                            Cancel
                        </button>
                        <button type="submit" class="btn btn--primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Details Modal -->
        <div
            v-if="DetailsModal"
            class="modal-overlay"
            @click.self="DetailsModal = false"
        >
            <div class="modal">
                <h2 class="modal-title">Ticket Details</h2>
                <form @submit.prevent="updateTicket">
                    <input
                        v-model="selectedTicket.subject"
                        type="text"
                        placeholder="Subject"
                        class="modal-input"
                        required
                    />
                    <select
                        v-model="selectedTicket.status"
                        class="modal-input"
                        required
                    >
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select
                        v-model="selectedTicket.category"
                        class="modal-input"
                        required
                    >
                        <option value="account">Account</option>
                        <option value="bug">Bug</option>
                        <option value="feature">Feature</option>
                        <option value="billing">Billing</option>
                        <option value="other">Others</option>
                    </select>
                    <input
                        v-model="selectedTicket.confidence"
                        type="number"
                        step="0.01"
                        placeholder="Confidence"
                        class="modal-input"
                        required
                    />
                    <textarea
                        v-model="selectedTicket.note"
                        placeholder="Note"
                        class="modal-input"
                    ></textarea>
                    <div class="modal-actions">
                        <button
                            type="button"
                            class="btn btn--outline"
                            @click="DetailsModal = false"
                        >
                            Cancel
                        </button>
                        <button type="submit" class="btn btn--primary">
                            Update
                        </button>
                        <button type="button" class="btn btn--primary">
                            Classify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style>
.ticket-container {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ============================= */
/* FILTERS */
/* ============================= */
.filters {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-input,
.filter-select {
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}

/* Dark Mode Filters */
.dark .filter-input,
.dark .filter-select {
    background: #1e293b;
    border: 1px solid #475569;
    color: #f8fafc;
}
.dark .filter-input::placeholder {
    color: #94a3b8;
}

/* ============================= */
/* BASE TABLE (Light mode default) */
/* ============================= */
.ticket-list {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    font-family: Arial, sans-serif;
    font-size: 0.95rem;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: background 0.3s, color 0.3s;
}

.ticket-list__head {
    background: #f1f5f9;
}

.ticket-list__th {
    text-align: left;
    padding: 0.75rem 1rem;
    font-weight: bold;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
}

.ticket-list__row:nth-child(even) {
    background: #f9fafb;
}

.ticket-list__row:hover {
    background: #e2e8f0;
    cursor: pointer;
}

.ticket-list__cell {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    color: #334155;
}

/* Badges */
.badge {
    padding: 0.25rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}
.badge--open {
    background: #22c55e;
}
.badge--closed {
    background: #ef4444;
}

.badge--in_progress {
    background: #0b1b5b;
}

/* Buttons */
.btn {
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    border: none;
    cursor: pointer;
    font-weight: 500;
}
.btn--small {
    background: #3b82f6;
    color: white;
}
.btn--small:hover {
    background: #2563eb;
}
.btn--outline {
    background: transparent;
    border: 1px solid #3b82f6;
    color: #3b82f6;
}
.btn--outline:hover {
    background: #3b82f6;
    color: white;
}

/* ============================= */
/* DARK MODE OVERRIDES */
/* ============================= */
.dark .ticket-list {
    background: #1e293b;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}
.dark .ticket-list__head {
    background: #334155;
}
.dark .ticket-list__th {
    color: #f8fafc;
    border-bottom: 2px solid #475569;
}
.dark .ticket-list__row:nth-child(even) {
    background: #273449;
}
.dark .ticket-list__row:hover {
    background: #334155;
}
.dark .ticket-list__cell {
    color: #e2e8f0;
    border-bottom: 1px solid #475569;
}

/* Buttons in dark mode */
.dark .btn--outline {
    border: 1px solid #60a5fa;
    color: #60a5fa;
}
.dark .btn--outline:hover {
    background: #3b82f6;
    color: white;
}

/* ================== */
/* FILTERS + TOOLBAR */
/* ================== */
.ticket-container {
    margin-top: 1rem;
}

.ticket-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-input,
.filter-select {
    padding: 0.45rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px #93c5fd;
}

/* Primary button */
.btn--primary {
    background: #3b82f6;
    color: white;
}

.btn--primary:hover {
    background: #2563eb;
}

/* ================== */
/* MODAL STYLING */
/* ================== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    width: 400px;
    max-width: 95%;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
    animation: fadeIn 0.3s ease-in-out;
}

.modal-title {
    font-size: 1.2rem;
    margin-bottom: 1rem;
    font-weight: bold;
}

.modal-input {
    width: 100%;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 0.9rem;
}

.modal-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px #93c5fd;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Dark mode overrides */
.dark .modal {
    background: #1e293b;
    color: #f8fafc;
}

.dark .modal-input {
    background: #273449;
    color: #e2e8f0;
    border: 1px solid #475569;
}

.dark .filter-input,
.dark .filter-select {
    background: #273449;
    border: 1px solid #475569;
    color: #e2e8f0;
}
</style>