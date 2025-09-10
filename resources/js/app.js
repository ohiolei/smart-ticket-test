import { createApp } from "vue";
import DashboardLayout from "./Layout/Layout.vue";
import router from "./router/index.js"; 
import "../css/dashboard.css";

const app = createApp(DashboardLayout);
app.use(router);
app.mount("#app");