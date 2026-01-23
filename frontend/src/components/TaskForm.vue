<template>
  <div class="card">
    <div class="card-head">
      <h2 class="card-title">{{ title }}</h2>
    </div>

    <form class="form" @submit.prevent="$emit('submit')">
      <div class="grid">
        <div class="field">
          <label class="label">Title <span class="req">*</span></label>
          <input class="input" v-model="model.title" placeholder="Title" />
        </div>

        <div class="field">
          <label class="label">Status</label>
          <select class="select" v-model="model.status">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>

        <div class="field full">
          <label class="label">Description</label>
          <textarea class="textarea" v-model="model.description" rows="3"></textarea>
        </div>

        <div class="field">
          <label class="label">Due Date</label>
          <input class="input" v-model="model.due_date" type="date" />
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">{{ submitLabel }}</button>
          <RouterLink class="btn" :to="{ name: 'tasks.list' }">Cancel</RouterLink>
        </div>

        <p v-if="error" class="error">{{ error }}</p>
      </div>
    </form>
  </div>
</template>

<script setup>
import { RouterLink } from "vue-router";

defineProps({
  title: { type: String, required: true },
  submitLabel: { type: String, required: true },
  model: { type: Object, required: true },
  error: { type: String, default: "" },
});
</script>
