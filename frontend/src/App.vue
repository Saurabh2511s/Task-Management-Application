<template>
  <main style="max-width: 720px; margin: 24px auto; font-family: Arial, sans-serif;">
    <h2>Task Management Application</h2>

    <!-- Create -->
    <section style="margin: 16px 0; padding: 12px; border: 1px solid #ddd;">
      <h3 style="margin: 0 0 8px;">Create Task</h3>

      <form @submit.prevent="createTask">
        <div style="display: grid; gap: 8px;">
          <input v-model="form.title" placeholder="Title *" />
          <textarea v-model="form.description" placeholder="Description"></textarea>

          <div style="display: flex; gap: 8px;">
            <select v-model="form.status">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>

            <input v-model="form.due_date" type="date" />
          </div>

          <button type="submit">Add</button>

          <p v-if="error" style="color: #b00020; margin: 0;">{{ error }}</p>
        </div>
      </form>
    </section>

    <!-- List + Filter -->
    <section style="margin: 16px 0; padding: 12px; border: 1px solid #ddd;">
      <div style="display:flex; justify-content: space-between; align-items:center;">
        <h3 style="margin: 0;">Tasks List</h3>

        <!-- Filter  API pagination -->
        <select v-model="filterStatus" @change="onFilterChange">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <div v-if="loading" style="margin-top:10px;">Loading...</div>

      <ul v-else style="list-style: none; padding: 0;">
        <li
          v-for="t in tasks"
          :key="t.id"
          style="display:flex; gap: 10px; align-items:flex-start; padding: 10px 0; border-bottom: 1px solid #eee;"
        >
          <div style="flex: 1;">
            <div style="font-weight: 600;">{{ t.title }}</div>
            <div style="font-size: 13px; color: #555;">
              {{ t.description || "—" }} | Status: {{ t.status }} | Due: {{ t.due_date || "—" }}
            </div>
          </div>

          <button @click="deleteTask(t.id)" style="color:#b00020;">Delete</button>
        </li>
      </ul>

      <!--  Pagination -->
      <div
        v-if="meta"
        style="display:flex; justify-content: space-between; align-items:center; margin-top:12px;"
      >
        <div style="font-size: 13px; color:#555;">
          Showing {{ meta.from || 0 }} - {{ meta.to || 0 }} of {{ meta.total || 0 }}
        </div>

        <div style="display:flex; gap:8px;">
          <button
            :disabled="!links?.prev"
            @click="fetchTasks(links.prev)"
          >
            Prev
          </button>

          <button
            :disabled="!links?.next"
            @click="fetchTasks(links.next)"
          >
            Next
          </button>
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

//  fetch tasks data
async function fetchTasks(url = null) {
  loading.value = true;
  error.value = "";

  try {
    const requestUrl = url || "/api/tasks";

    
    const res = await api.get(requestUrl, {
      params: url
        ? {}
        : (filterStatus.value ? { status: filterStatus.value } : {}),
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

// when filter changes => reload page 1
function onFilterChange() {
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

    // Reset form
    form.title = "";
    form.description = "";
    form.status = "pending";
    form.due_date = "";

    //  best with pagination: reload current page data from API
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

    //  After delete, refetch current page.
    await fetchTasks(null);
  } catch (e) {
    error.value = "Failed to delete task.";
  }
}

onMounted(() => fetchTasks(null));
</script>
