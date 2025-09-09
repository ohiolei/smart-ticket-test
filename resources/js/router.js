import { createRouter, createWebHistory } from 'vue-router'
import Tickets from './pages/Tickets.vue'
import Dashboard from './Dashboard/Layout.vue'

const routes = [
  { path: '/tickets', component: Tickets },
  { path: '/dashboard', component: Dashboard },

]

export default createRouter({
  history: createWebHistory(),
  routes
})
