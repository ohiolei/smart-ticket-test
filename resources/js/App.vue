<template>
  <div id="app">
    <header class="app__header">
      <nav>
        <router-link to="/tickets">Tickets</router-link> |
        <router-link to="/dashboard">Dashboard</router-link>
      </nav>
    </header>

    <main class="app__main">
      <router-view />
    </main>
  </div>
</template>

<script>
export default { name: 'App' }
</script>

<style>
.app__header {
  padding: 1rem;
  background: #222;
  color: white;
}
.app__main {
  padding: 2rem;
}
</style>
