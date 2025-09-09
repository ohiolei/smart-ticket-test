import { createRouter, createWebHistory } from "vue-router";

import Tickets from "../pages/Tickets.vue";


const routes = [
  {
    path: "/tickets",
    name: "tickets",
    component: Tickets,
  },

];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
