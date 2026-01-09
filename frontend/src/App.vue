<template>
  <main style="max-width: 720px; margin: 24px auto; font-family: Arial, sans-serif;">
    <h2>Task Management Application</h2>

    <!-- List + Filter -->
    <section style="margin: 16px 0; padding: 12px; border: 1px solid #ddd;">
      <div style="display:flex; justify-content: space-between; align-items:center;">
        <h3 style="margin: 0;">Tasks List</h3>

        <!-- ✅ Filter applied on already loaded tasks -->
        <select v-model="filterStatus">
          <option value="">All</option>
          <option value="pending">pending</option>
          <option value="in_progress">in_progress</option>
          <option value="completed">completed</option>
        </select>
      </div>

      <div v-if="loading" style="margin-top:10px;">Loading...</div>

      <ul v-else style="list-style: none; padding: 0;">
        <li
          v-for="t in tasks"
          :key="t.id"
          style="display:flex; gap: 10px; align-items:flex-start; padding: 10px 0; border-bottom: 1px solid #eee;"
        >
          <!-- ✅ View Mode -->
          <template v-if="editId !== t.id">
            <div style="flex: 1;">
              <div style="font-weight: 600;">{{ t.title }}</div>
              <div style="font-size: 13px; color: #555;">
                {{ t.description || "—" }} | Status: {{ t.status }} | Due: {{ t.due_date || "—" }}
              </div>
            </div>
            <button @click="deleteTask(t.id)" style="color:#b00020;">Delete</button>
          </template>

          
        </li>
      </ul>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { api } from "./api";

// ✅ store all fetched tasks here (loaded first)
const allTasks = ref([]);

const loading = ref(false);
const error = ref("");

const filterStatus = ref("");

// ✅ filtered tasks shown in UI (after load)
const tasks = computed(() => {
  if (!filterStatus.value) return allTasks.value;
  return allTasks.value.filter((t) => t.status === filterStatus.value);
});

const form = reactive({
  title: "",
  description: "",
  status: "pending",
  due_date: "",
});

async function fetchTasks() {
  loading.value = true;
  error.value = "";

  try {
    // ✅ First load all tasks (no filter)
    const res = await api.get("/api/tasks");
    allTasks.value = res.data?.data || [];
  } catch (e) {
    error.value = "Failed to load tasks.";
  } finally {
    loading.value = false;
  }
}


async function deleteTask(id) {
  try {
    await api.delete(`/api/tasks/${id}`);
    allTasks.value = allTasks.value.filter((t) => t.id !== id);
  } catch (e) {
    error.value = "Failed to delete task.";
  }
}

onMounted(fetchTasks);
</script>
