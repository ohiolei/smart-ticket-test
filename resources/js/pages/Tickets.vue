<script setup>
import { ref, onMounted, computed } from "vue";
import Layout from "../Layout/Layout.vue";
import axios from "axios";

defineOptions({ layout: Layout });

/* ✅ Tickets */
const allTickets = ref([]); // Store all tickets
const loading = ref(true);

// Filters
const filters = ref({
    subject: "",
    status: "",
    category: "",
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Computed property for filtered tickets
const filteredTickets = computed(() => {
    let result = [...allTickets.value];
    
    // Apply subject filter
    if (filters.value.subject) {
        const searchTerm = filters.value.subject.toLowerCase();
        result = result.filter(ticket => 
            ticket.subject.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply status filter
    if (filters.value.status) {
        result = result.filter(ticket => ticket.status === filters.value.status);
    }
    
    // Apply category filter
    if (filters.value.category) {
        result = result.filter(ticket => ticket.category === filters.value.category);
    }
    
    return result;
});

// Computed property for paginated tickets
const paginatedTickets = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredTickets.value.slice(start, end);
});

// Computed property for total pages
const totalPages = computed(() => {
    return Math.ceil(filteredTickets.value.length / itemsPerPage.value);
});

// Fetch all tickets
function getTickets() {
    loading.value = true;
    axios
        .get("/api/tickets?per_page=1000") // Get all tickets with high per_page
        .then((response) => {
            // Handle both paginated and non-paginated responses
            allTickets.value = response.data.data || response.data;
        })
        .catch((error) => {
            console.error("Error fetching tickets:", error);
        })
        .finally(() => {
            loading.value = false;
        });
}

function changePage(page) {
    if (page > 0 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

function addTicket() {
    axios.post("/api/tickets", newTicket.value).then((response) => {
        console.log(response.data);
        showModal.value = false;

        newTicket.value = {
            subject: "",
            status: "open",
            category: "other",
            confidence: "",
            note: "",
        };
        
        // Refresh the tickets list
        getTickets();
    });
}

function updateTicket() {
    axios
        .patch(`/api/tickets/${selectedTicket.value.id}`, selectedTicket.value)
        .then((response) => {
            console.log(response.data);
            DetailsModal.value = false;
            
            // Update the local ticket data
            const index = allTickets.value.findIndex(t => t.id === selectedTicket.value.id);
            if (index !== -1) {
                allTickets.value[index] = { ...allTickets.value[index], ...selectedTicket.value };
            }
        });
}

function classifyTicket() {
    if (!selectedTicket.value.id) return;

    axios
        .post(`/api/tickets/${selectedTicket.value.id}/classify`)
        .then((response) => {
            if (response.status === 409) {
                alert(response.data.message);
            } else {
                alert('Classification started! It may take a few moments.');
                // Refresh after a delay to see the results
                setTimeout(() => {
                    getTickets();
                }, 3000);
            }
        })
        .catch((error) => {
            console.error(error);
            if (error.response?.status === 409) {
                alert(error.response.data.message);
            } else {
                alert('Failed to queue classification.');
            }
        });
}

function searchTickets() {
    // Reset to first page when searching
    currentPage.value = 1;
}

function clearFilters() {
    filters.value = {
        subject: "",
        status: "",
        category: "",
    };
    currentPage.value = 1;
}

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
                    placeholder="🔍 Filter by subject..."
                    class="filter-input"
                    @input="searchTickets"
                />

                <!-- Status filter -->
                <select v-model="filters.status" class="filter-select" @change="searchTickets">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="closed">Closed</option>
                </select>

                <!-- Category filter -->
                <select v-model="filters.category" class="filter-select" @change="searchTickets">
                    <option value="">All Categories</option>
                    <option value="account">Account</option>
                    <option value="bug">Bug</option>
                    <option value="feature">Feature</option>
                    <option value="billing">Billing</option>
                    <option value="other">Others</option>
                </select>

                <button class="btn btn--secondary" @click="clearFilters">
                    Clear Filters
                </button>
            </div>

            <button class="btn btn--primary" @click="showModal = true">
                + New Ticket
            </button>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <span>Loading tickets...</span>
        </div>

        <!-- Results info -->
        <div v-else class="results-info">
            Showing {{ paginatedTickets.length }} of {{ filteredTickets.length }} tickets
            ({{ allTickets.length }} total)
            <span v-if="filters.subject || filters.status || filters.category" class="filter-indicator">
                • Filters active
            </span>
        </div>

        <!-- Table -->
        <table class="ticket-list" v-if="!loading">
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
                    v-for="(ticket, index) in paginatedTickets"
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
                    <td class="ticket-list__cell">
                        <span v-if="ticket.category" :class="['badge--category']">
                            {{ ticket.category }}
                        </span>
                        <span v-else>-</span>
                    </td>
                    <td class="ticket-list__cell">
                        <span v-if="ticket.confidence">
                            {{ (ticket.confidence * 100).toFixed(1) }}%
                        </span>
                        <span v-else>-</span>
                    </td>
                    <td class="ticket-list__cell">
                        <span v-if="ticket.note" title="Has note">📝</span>
                        <span v-else>-</span>
                    </td>
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

        <!-- Empty state -->
        <div v-if="!loading && filteredTickets.length === 0" class="empty-state">
            <p>No tickets found matching your filters.</p>
            <button class="btn btn--primary" @click="clearFilters">
                Clear Filters
            </button>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredTickets.length > 0" class="pagination">
            <button
                :disabled="currentPage === 1"
                @click="changePage(currentPage - 1)"
                class="btn btn--outline"
            >
                Prev
            </button>

            <span class="pagination-info">
                Page {{ currentPage }} of {{ totalPages }}
            </span>

            <button
                :disabled="currentPage === totalPages"
                @click="changePage(currentPage + 1)"
                class="btn btn--outline"
            >
                Next
            </button>

            <select v-model="itemsPerPage" class="page-size-select">
                <option value="5">5 per page</option>
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
            </select>
        </div>

        <!-- New Ticket Modal -->
        <div
            v-if="showModal"
            class="modal-overlay"
            @click.self="showModal = false"
        >
            <div class="modal">
                <h2 class="modal-title">New Ticket</h2>
                <form>
                    <input
                        v-model="newTicket.subject"
                        type="text"
                        placeholder="Subject"
                        class="modal-input"
                        required
                    />
                    <select
                        v-model="newTicket.status"
                        class="modal-input"
                        required
                    >
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select
                        v-model="newTicket.category"
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
                        v-model="newTicket.confidence"
                        type="number"
                        step="0.01"
                        placeholder="Confidence"
                        class="modal-input"
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
                            @click.prevent="showModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn--primary"
                            @click.prevent="addTicket"
                        >
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
                            @click.prevent="DetailsModal = false"
                        >
                            Cancel
                        </button>

                        <button type="submit" class="btn btn--primary">
                            Update
                        </button>

                        <button
                            ref="classifyButton"
                            type="button"
                            class="btn btn--primary"
                            @click.prevent="classifyTicket"
                        >
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

/* ================== */
/* SPINNER STYLES (NEW) */
/* ================== */
.spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 0.5rem;
    vertical-align: middle;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Loading state for classification */
.classifying {
    opacity: 0.7;
    pointer-events: none;
}

.classifying .spinner {
    display: inline-block;
}
</style>