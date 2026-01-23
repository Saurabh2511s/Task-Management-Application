import { defineStore } from "pinia";
import { api } from "../api";

export const useTaskStore = defineStore("tasks", {
  state: () => ({
    items: [],
    links: null,
    meta: null,
    loading: false,
    error: "",
    filterStatus: "",
  }),

  actions: {
    async fetch(url = null) {
      this.loading = true;
      this.error = "";

      try {
        const requestUrl = url || "/api/tasks";
        const res = await api.get(requestUrl, {
          params: url ? {} : (this.filterStatus ? { status: this.filterStatus } : {}),
        });

        this.items = res.data?.data || [];
        this.links = res.data?.links || null;
        this.meta = res.data?.meta || null;
      } catch (e) {
        this.error = "Failed to load tasks.";
      } finally {
        this.loading = false;
      }
    },

    async create(payload) {
      this.error = "";
      await api.post("/api/tasks", payload);
      await this.fetch(null);
    },

    async remove(id) {
      this.error = "";
      await api.delete(`/api/tasks/${id}`);
      await this.fetch(null);
    },

    async update(id, payload) {
      this.error = "";
      const res = await api.put(`/api/tasks/${id}`, payload);
      const updated = res.data?.data;

      if (updated) {
        this.items = this.items.map((t) => (t.id === id ? updated : t));
      }
      return updated;
    },

    async getById(id) {
      // If you don't have show API, we use the list cache first.
      const existing = this.items.find((t) => String(t.id) === String(id));
      if (existing) return existing;

      // If you have GET /api/tasks/{id}, use it (recommended)
      const res = await api.get(`/api/tasks/${id}`);
      return res.data?.data ?? res.data;
    },
  },
});
