<template>
  <tr class="ticket-list__item">
    <td>{{ ticket.subject }}</td>
    <td><span class="ticket-list__status">{{ ticket.status }}</span></td>
    <td>
      <select v-model="ticket.category" @change="updateCategory">
        <option>Billing</option>
        <option>Technical</option>
        <option>General</option>
      </select>
    </td>
    <td>
      <span class="ticket-list__confidence">{{ ticket.confidence }}</span>
    </td>
    <td>
      <span v-if="ticket.note" class="ticket-list__note-badge">📝</span>
    </td>
    <td>
      <button @click="$emit('classify')">Classify</button>
    </td>
  </tr>
</template>

<script>
export default {
  props: ['ticket'],
  methods: {
    async updateCategory() {
      await fetch(`/api/tickets/${this.ticket.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category: this.ticket.category })
      })
    }
  }
}
</script>
