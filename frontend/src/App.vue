<template>
  <main class="page">
    <header class="header">
      <h1 class="title">Task Management</h1>
      <p class="subtitle">Create, update, filter and manage tasks</p>
    </header>

    <!-- Create -->
    <section class="card">
      <div class="card-head">
        <h2 class="card-title">Create Task</h2>
      </div>

      <form class="form" @submit.prevent="createTask">
        <div class="grid">
          <div class="field">
            <label class="label">Title <span class="req">*</span></label>
            <input class="input" v-model="form.title" placeholder="e.g. Fix pagination UI" />
          </div>

          <div class="field">
            <label class="label">Status</label>
            <select class="select" v-model="form.status">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <div class="field full">
            <label class="label">Description</label>
            <textarea
              class="textarea"
              v-model="form.description"
              placeholder="Short description (optional)"
              rows="3"
            />
          </div>

          <div class="field">
            <label class="label">Due Date</label>
            <input class="input" v-model="form.due_date" type="date" />
          </div>

          <div class="actions">
            <button class="btn primary" type="submit">Add Task</button>
          </div>

          <p v-if="error" class="error">{{ error }}</p>
        </div>
      </form>
    </section>

    <!-- List + Filter -->
    <section class="card">
      <div class="card-head card-head-row">
        <div>
          <h2 class="card-title">Tasks List</h2>
          <p class="muted" style="margin: 4px 0 0;">Filter and paginate your tasks</p>
        </div>

        <div class="toolbar">
          <label class="label" style="margin:0;">Status</label>
          <select class="select" v-model="filterStatus" @change="onFilterChange">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="muted" style="padding: 12px;">Loading...</div>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 70px;">ID</th>
              <th>Title</th>
              <th>Description</th>
              <th style="width: 150px;">Status</th>
              <th style="width: 150px;">Due Date</th>
              <th style="width: 220px; text-align:right;">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!tasks.length">
              <td colspan="6" class="muted" style="padding: 14px; text-align:center;">
                No tasks found.
              </td>
            </tr>

            <tr v-for="t in tasks" :key="t.id">
              <!-- View Mode -->
              <template v-if="editId !== t.id">
                <td class="mono">{{ t.id }}</td>
                <td class="strong">{{ t.title }}</td>
                <td class="muted">{{ t.description || "—" }}</td>

                <td>
                  <span class="badge" :class="badgeClass(t.status)">
                    {{ labelStatus(t.status) }}
                  </span>
                </td>

                <td class="mono">{{ t.due_date || "—" }}</td>

                <td style="text-align:right;">
                  <button class="btn" @click="startEdit(t)">Edit</button>
                  <button class="btn danger" @click="deleteTask(t.id)">Delete</button>
                </td>
              </template>

              <!-- Edit Mode -->
              <template v-else>
                <td class="mono">{{ t.id }}</td>

                <td>
                  <input class="input" v-model="editForm.title" placeholder="Title *" />
                </td>

                <td>
                  <input class="input" v-model="editForm.description" placeholder="Description" />
                </td>

                <td>
                  <select class="select" v-model="editForm.status">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                  </select>
                </td>

                <td>
                  <input class="input" v-model="editForm.due_date" type="date" />
                </td>

                <td style="text-align:right;">
                  <button class="btn primary" @click="saveEdit(t.id)">Save</button>
                  <button class="btn" @click="cancelEdit">Cancel</button>
                </td>
              </template>
            </tr>
          </tbody>
        </table>

        <p v-if="editError" class="error" style="padding: 10px 12px 0;">{{ editError }}</p>

        <!-- Pagination -->
        <div v-if="meta" class="pager">
          <div class="muted">
            Showing <span class="mono">{{ meta.from || 0 }}</span> -
            <span class="mono">{{ meta.to || 0 }}</span> of
            <span class="mono">{{ meta.total || 0 }}</span>
          </div>

          <div class="pager-actions">
            <button class="btn" :disabled="!links?.prev" @click="cancelEdit(); fetchTasks(links.prev)">
              Prev
            </button>
            <button class="btn" :disabled="!links?.next" @click="cancelEdit(); fetchTasks(links.next)">
              Next
            </button>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { api } from "./api";

const tasks = ref([]);
const links = ref(null);
const meta = ref(null);

const loading = ref(false);
const error = ref("");

const filterStatus = ref("");

const form = reactive({
  title: "",
  description: "",
  status: "pending",
  due_date: "",
});

// edit state
const editId = ref(null);
const editForm = reactive({
  title: "",
  description: "",
  status: "pending",
  due_date: "",
});
const editError = ref("");

// helpers for UI
function labelStatus(s) {
  if (s === "in_progress") return "In Progress";
  if (s === "completed") return "Completed";
  return "Pending";
}
function badgeClass(s) {
  return s === "completed" ? "ok" : s === "in_progress" ? "warn" : "info";
}

// fetch tasks data (supports pagination)
async function fetchTasks(url = null) {
  loading.value = true;
  error.value = "";

  try {
    const requestUrl = url || "/api/tasks";

    const res = await api.get(requestUrl, {
      params: url ? {} : filterStatus.value ? { status: filterStatus.value } : {},
    });

    tasks.value = res.data?.data || [];
    links.value = res.data?.links || null;
    meta.value = res.data?.meta || null;
  } catch (e) {
    error.value = "Failed to load tasks.";
  } finally {
    loading.value = false;
  }
}

// filter => reload page 1
function onFilterChange() {
  cancelEdit();
  fetchTasks(null);
}

async function createTask() {
  error.value = "";

  if (!form.title.trim()) {
    error.value = "Title is required.";
    return;
  }

  try {
    await api.post("/api/tasks", {
      title: form.title.trim(),
      description: form.description?.trim() || null,
      status: form.status,
      due_date: form.due_date || null,
    });

    form.title = "";
    form.description = "";
    form.status = "pending";
    form.due_date = "";

    await fetchTasks(null);
  } catch (e) {
    error.value =
      e?.response?.status === 422
        ? "Validation failed. Please check your input."
        : "Failed to create task.";
  }
}

async function deleteTask(id) {
  try {
    await api.delete(`/api/tasks/${id}`);
    await fetchTasks(null);
  } catch (e) {
    error.value = "Failed to delete task.";
  }
}

function startEdit(task) {
  editId.value = task.id;
  editError.value = "";

  editForm.title = task.title || "";
  editForm.description = task.description || "";
  editForm.status = task.status || "pending";
  editForm.due_date = task.due_date || "";
}

function cancelEdit() {
  editId.value = null;
  editError.value = "";
}

async function saveEdit(id) {
  editError.value = "";

  if (!editForm.title.trim()) {
    editError.value = "Title is required.";
    return;
  }

  try {
    const res = await api.put(`/api/tasks/${id}`, {
      title: editForm.title.trim(),
      description: editForm.description?.trim() || null,
      status: editForm.status,
      due_date: editForm.due_date || null,
    });

    const updated = res.data?.data;

    if (updated) {
      tasks.value = tasks.value.map((t) => (t.id === id ? updated : t));
    }

    editId.value = null;
  } catch (e) {
    editError.value =
      e?.response?.status === 422
        ? "Validation failed. Please check values."
        : "Failed to update task.";
  }
}

onMounted(() => fetchTasks(null));
</script>

<style scoped>
.page {
  max-width: 980px;
  margin: 24px auto;
  padding: 0 14px;
  font-family: Arial, sans-serif;
}

.header {
  margin-bottom: 14px;
}
.title {
  margin: 0;
  font-size: 22px;
}
.subtitle {
  margin: 6px 0 0;
  color: #666;
  font-size: 13px;
}

.card {
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 10px;
  padding: 14px;
  margin: 16px 0;
  box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.card-head {
  margin-bottom: 12px;
}
.card-head-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
.card-title {
  margin: 0;
  font-size: 16px;
}

.form .grid {
  display: grid;
  grid-template-columns: 1fr 220px;
  gap: 12px;
  align-items: end;
}
.field.full {
  grid-column: 1 / -1;
}
.label {
  display: block;
  font-size: 12px;
  color: #555;
  margin-bottom: 6px;
}
.req {
  color: #b00020;
}
.input,
.select,
.textarea {
  width: 100%;
  border: 1px solid #dcdcdc;
  border-radius: 8px;
  padding: 10px 10px;
  font-size: 14px;
  outline: none;
}
.textarea {
  resize: vertical;
}
.actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.btn {
  border: 1px solid #dcdcdc;
  background: #fff;
  border-radius: 8px;
  padding: 9px 12px;
  cursor: pointer;
  font-size: 13px;
}
.btn:hover {
  background: #f7f7f7;
}
.btn.primary {
  border-color: #2e6bff;
  background: #2e6bff;
  color: #fff;
}
.btn.primary:hover {
  background: #2458d6;
}
.btn.danger {
  border-color: #ffdddd;
  color: #b00020;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-wrap {
  border: 1px solid #eee;
  border-radius: 10px;
  overflow: hidden;
}

.table {
  width: 100%;
  border-collapse: collapse;
}
.table thead th {
  background: #fafafa;
  text-align: left;
  font-size: 12px;
  color: #666;
  padding: 12px;
  border-bottom: 1px solid #eee;
}
.table tbody td {
  padding: 12px;
  border-bottom: 1px solid #f1f1f1;
  vertical-align: top;
}
.table tbody tr:hover {
  background: #fcfcff;
}

.strong {
  font-weight: 600;
}
.muted {
  color: #666;
  font-size: 13px;
}
.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  border: 1px solid transparent;
}
.badge.info {
  background: #eef5ff;
  border-color: #d7e7ff;
  color: #2b5bb3;
}
.badge.warn {
  background: #fff6e6;
  border-color: #ffe2b8;
  color: #8a5a00;
}
.badge.ok {
  background: #eaffea;
  border-color: #c8f0c8;
  color: #1f7a1f;
}

.error {
  color: #b00020;
  margin: 0;
  font-size: 13px;
}

.pager {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  gap: 12px;
  background: #fff;
}
.pager-actions {
  display: flex;
  gap: 8px;
}

@media (max-width: 820px) {
  .form .grid {
    grid-template-columns: 1fr;
  }
  .card-head-row {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
