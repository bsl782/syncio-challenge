import { createApp } from "vue";
import App from "./App.vue";
import Vue3Toastify from "vue3-toastify";

import "../css/app.css";
import "vue3-toastify/dist/index.css";

const app = createApp(App);

app.use(Vue3Toastify);

app.mount("#app");
