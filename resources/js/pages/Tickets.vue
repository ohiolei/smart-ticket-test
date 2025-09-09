<template>
  <div class="tickets">
    <ticket-filters @filter="applyFilter" />

    <ticket-list
      :tickets="tickets"
      @classify="classifyTicket"
    />

    <ticket-form v-if="showForm" @submitted="loadTickets" />
  </div>
</template>

<script>
import TicketList from '../components/TicketList.vue'
import TicketFilters from '../components/TicketFilters.vue'
import TicketForm from '../components/TicketForm.vue'

export default {
  components: { TicketList, TicketFilters, TicketForm },
  data() {
    return {
      tickets: [],
      showForm: false
    }
  },
  methods: {
    async loadTickets() {
      const res = await fetch('/api/tickets')
      this.tickets = await res.json()
    },
    async classifyTicket(id) {
      await fetch(`/api/tickets/${id}/classify`, { method: 'POST' })
      this.loadTickets()
    },
    applyFilter(filters) {
      // re-fetch with filters applied
    }
  },
  mounted() {
    this.loadTickets()
  }
}
</script>
