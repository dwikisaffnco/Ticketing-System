import { createApp } from "vue";
import { createPinia } from "pinia";
import Cookies from "js-cookie";
import { axiosInstance } from "@/plugins/axios";

import App from "./App.vue";
import router from "./router";

import { MotionPlugin } from "@vueuse/motion";
import "./assets/main.css";

const token = Cookies.get("token");
if (token) {
  axiosInstance.defaults.headers.common.Authorization = `Bearer ${token}`;
}
const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(MotionPlugin);

app.mount("#app");
