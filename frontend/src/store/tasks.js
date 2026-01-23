import { defineStore } from "pinia";
import { http } from "../api/http";

export const useTasksStore = defineStore("tasks", {
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
        const endpoint = url || "/api/tasks";
        const params = url ? {} : (this.filterStatus ? { status: this.filterStatus } : {});
        const res = await http.get(endpoint, { params });

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
      await http.post("/api/tasks", payload);
      await this.fetch(null);
    },

    async remove(id) {
      await http.delete(`/api/tasks/${id}`);
      await this.fetch(null);
    },

    async update(id, payload) {
      const res = await http.put(`/api/tasks/${id}`, payload);
      const updated = res.data?.data;

      // Update only the current page list (no full reload)
      if (updated) {
        this.items = this.items.map((t) => (t.id === id ? updated : t));
      }
      return updated;
    },

    setFilter(status) {
      this.filterStatus = status;
    },
  },
});
