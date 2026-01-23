<template>
  <div class="card">
    <div class="card-head card-head-row">
      <div>
        <h2 class="card-title">Tasks List</h2>
        <p class="muted">Filter + paginate from API</p>
      </div>

      <div class="toolbar">
        <label class="label" style="margin:0;">Status</label>
        <select class="select" v-model="store.filterStatus" @change="store.fetch(null)">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>
    </div>

    <div v-if="store.loading" class="muted" style="padding:12px;">Loading...</div>

    <div v-else class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:70px;">ID</th>
            <th>Title</th>
            <th>Description</th>
            <th style="width:150px;">Status</th>
            <th style="width:150px;">Due Date</th>
            <th style="width:200px; text-align:right;">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="!store.items.length">
            <td colspan="6" class="muted" style="padding: 14px; text-align:center;">
              No tasks found.
            </td>
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
              <RouterLink class="btn" :to="{ name: 'tasks.edit', params: { id: t.id } }">Edit</RouterLink>
              <button class="btn danger" @click="$emit('delete', t.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <p v-if="store.error" class="error" style="padding: 10px 12px 0;">{{ store.error }}</p>

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
  </div>
</template>

<script setup>
import { RouterLink } from "vue-router";
import { useTaskStore } from "../store/taskStore";

const store = useTaskStore();

function labelStatus(s) {
  if (s === "in_progress") return "In Progress";
  if (s === "completed") return "Completed";
  return "Pending";
}
function badgeClass(s) {
  return s === "completed" ? "ok" : s === "in_progress" ? "warn" : "info";
}
</script>
