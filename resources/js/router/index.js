import { createRouter, createWebHistory } from "vue-router";

import Tickets from "../pages/Tickets.vue";
import DasboardHome from '../Dashboard/DashboardHome.vue'


const routes = [
  {
    path: "/tickets",
    name: "tickets",
    component: Tickets,
  },

    {
    path: "/home",
    name: "home",
    component: DasboardHome,
  },

];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
