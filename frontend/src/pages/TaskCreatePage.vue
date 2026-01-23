<template>
  <section class="card">
    <div class="card-head">
      <h2 class="card-title">Create Task</h2>
      <p class="muted" style="margin: 6px 0 0;">Add a new task</p>
    </div>

    <form class="form" @submit.prevent="submit">
      <div class="grid">
        <div class="field">
          <label class="label">Title <span class="req">*</span></label>
          <input class="input" v-model="form.title" placeholder="e.g. Fix API baseURL" />
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
          <textarea class="textarea" v-model="form.description" rows="3" placeholder="Optional"></textarea>
        </div>

        <div class="field">
          <label class="label">Due Date</label>
          <input class="input" v-model="form.due_date" type="date" />
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Save</button>
          <RouterLink class="btn" to="/tasks">Cancel</RouterLink>
        </div>

        <p v-if="error" class="error">{{ error }}</p>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useTasksStore } from "../store/tasks";

const router = useRouter();
const store = useTasksStore();

const error = ref("");

const form = reactive({
  title: "",
  description: "",
  status: "pending",
  due_date: "",
});

async function submit() {
  error.value = "";
  if (!form.title.trim()) {
    error.value = "Title is required.";
    return;
  }

  try {
    await store.create({
      title: form.title.trim(),
      description: form.description?.trim() || null,
      status: form.status,
      due_date: form.due_date || null,
    });

    router.push("/tasks");
  } catch (e) {
    error.value = "Failed to create task.";
  }
}
</script>
