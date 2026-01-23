<template>
  <header class="topbar">
    <div class="container topbar-inner">
      <div class="brand">
        <div class="logo">TM</div>
        <div>
          <div class="brand-title">Task Management</div>
          <div class="brand-sub">Vue + Laravel</div>
        </div>
      </div>

      <nav class="nav">
        <RouterLink class="nav-link" to="/tasks">Tasks</RouterLink>
        <RouterLink class="nav-link" to="/tasks/create">Create</RouterLink>
      </nav>
    </div>
  </header>
</template>
