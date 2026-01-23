<template>
  <section class="card">
    <div class="card-head card-head-row">
      <div>
        <h2 class="card-title">Tasks</h2>
        <p class="muted" style="margin: 6px 0 0;">Filter and paginate your tasks</p>
      </div>

      <div class="toolbar">
        <label class="label" style="margin:0;">Status</label>
        <select class="select" v-model="status" @change="applyFilter">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>

        <RouterLink class="btn primary" to="/tasks/create">+ Create</RouterLink>
      </div>
    </div>

    <div v-if="store.loading" class="muted" style="padding: 12px;">Loading...</div>
    <p v-else-if="store.error" class="error" style="padding: 12px;">{{ store.error }}</p>

    <div v-else class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:70px;">ID</th>
            <th>Title</th>
            <th>Description</th>
            <th style="width:150px;">Status</th>
            <th style="width:150px;">Due</th>
            <th style="width:220px; text-align:right;">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="!store.items.length">
            <td colspan="6" class="muted" style="padding: 14px; text-align:center;">No tasks found.</td>
          </tr>

          <tr v-for="t in store.items" :key="t.id">
            <td class="mono">{{ t.id }}</td>
            <td class="strong">{{ t.title }}</td>
            <td class="muted">{{ t.description || "—" }}</td>
            <td>
              <span class="badge" :class="badgeClass(t.status)">{{ labelStatus(t.status) }}</span>
            </td>
            <td class="mono">{{ t.due_date || "—" }}</td>
            <td style="text-align:right;">
              <RouterLink class="btn" :to="`/tasks/${t.id}/edit`">Edit</RouterLink>
              <button class="btn danger" @click="remove(t.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="store.meta" class="pager">
        <div class="muted">
          Showing <span class="mono">{{ store.meta.from || 0 }}</span> -
          <span class="mono">{{ store.meta.to || 0 }}</span> of
          <span class="mono">{{ store.meta.total || 0 }}</span>
        </div>

        <div class="pager-actions">
          <button class="btn" :disabled="!store.links?.prev" @click="store.fetch(store.links.prev)">Prev</button>
          <button class="btn" :disabled="!store.links?.next" @click="store.fetch(store.links.next)">Next</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useTasksStore } from "../store/tasks";

const store = useTasksStore();
const status = ref(store.filterStatus);

function applyFilter() {
  store.setFilter(status.value);
  store.fetch(null);
}

function remove(id) {
  store.remove(id);
}

function labelStatus(s) {
  if (s === "in_progress") return "In Progress";
  if (s === "completed") return "Completed";
  return "Pending";
}
function badgeClass(s) {
  return s === "completed" ? "ok" : s === "in_progress" ? "warn" : "info";
}

onMounted(() => store.fetch(null));
</script>
