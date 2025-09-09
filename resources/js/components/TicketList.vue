<template>
  <table class="ticket-list">
    <thead>
      <tr>
        <th>Subject</th>
        <th>Status</th>
        <th>Category</th>
        <th>Confidence</th>
        <th>Note</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <ticket-item
        v-for="t in tickets"
        :key="t.id"
        :ticket="t"
        @classify="$emit('classify', t.id)"
      />
    </tbody>
  </table>
</template>

<script>
import TicketItem from './TicketItem.vue'
export default {
  props: ['tickets'],
  components: { TicketItem }
}
</script>
