<template>
  <div class="dashboard" :class="{ 'dark-mode': darkMode }">
    <!-- Top Navbar -->
    <header class="dashboard__topbar">
      <div class="dashboard__logo">smart ticket</div>
      <nav class="dashboard__nav">
        <router-link to="/profile" class="dashboard__nav-item">Profile</router-link>
        <router-link to="/settings" class="dashboard__nav-item">Settings</router-link>
      </nav>

      <!-- Dark/Light Toggle Button -->
      <button class="dashboard__theme-toggle" @click="toggleTheme">
        {{ darkMode ? "🌙 Dark" : "☀️ Light" }}
      </button>
    </header>

    <div class="dashboard__body">
      <!-- Sidebar -->
      <aside
        class="dashboard__sidebar"
        :class="{ 'dashboard__sidebar--collapsed': collapsed }"
      >
        <button class="dashboard__toggle" @click="toggleSidebar">
          ☰
        </button>
        <ul class="dashboard__menu">
          <li class="dashboard__menu-item">
            <router-link to="/home">🏠 Home</router-link>
          </li>
          <li class="dashboard__menu-item">
            <router-link to="/tickets">🎫 Tickets</router-link>
          </li>
          <li class="dashboard__menu-item">
            <router-link to="/settings">⚙ Settings</router-link>
          </li>
        </ul>
      </aside>

      <!-- Main Content -->
      <main class="dashboard__content">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  name: "Layout",
  data() {
    return {
      collapsed: false,
      darkMode: false, // Theme state
    };
  },
  methods: {
    toggleSidebar() {
      this.collapsed = !this.collapsed;
    },
    toggleTheme() {
      this.darkMode = !this.darkMode;
    },
  },
};
</script>
