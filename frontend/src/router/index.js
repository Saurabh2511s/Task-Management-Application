import { createRouter, createWebHistory } from "vue-router";
import AppLayout from "../layouts/AppLayout.vue";

import TaskListPage from "../pages/TaskListPage.vue";
import TaskCreatePage from "../pages/TaskCreatePage.vue";
import TaskEditPage from "../pages/TaskEditPage.vue";

const routes = [
  {
    path: "/",
    component: AppLayout,
    children: [
      { path: "", redirect: "/tasks" },
      { path: "tasks", name: "tasks.list", component: TaskListPage },
      { path: "tasks/create", name: "tasks.create", component: TaskCreatePage },
      { path: "tasks/:id/edit", name: "tasks.edit", component: TaskEditPage, props: true },
    ],
  },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});
