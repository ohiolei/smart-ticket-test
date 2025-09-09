<template>
  <div class="dashboard">
    <dashboard-card title="Total Tickets" :value="stats.total" />
    <dashboard-card title="Open" :value="stats.open" />
    <dashboard-card title="Closed" :value="stats.closed" />

    <dashboard-chart :data="stats.byCategory" />
  </div>
</template>

<script>
import DashboardCard from './DashboardCard.vue'
import DashboardChart from './DashboardChart.vue'

export default {
  components: { DashboardCard, DashboardChart },
  data() {
    return { stats: { total: 0, open: 0, closed: 0, byCategory: [] } }
  },
  async mounted() {
    const res = await fetch('/api/stats')
    this.stats = await res.json()
  }
}
</script>
