import axios from "axios";
import Cookies from "js-cookie";

const token = Cookies.get("token");

axios.defaults.baseURL = "https://ticket-backend.saffnco.app/api"; // for production
// axios.defaults.baseURL = "http://127.0.0.1:8000/api";  // for local development
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.headers.common["Content-Type"] = "application/json";
axios.defaults.headers.common["Accept"] = "application/json";
if (token) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

axios.interceptors.request.use((config) => {
  const token = Cookies.get("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (config.data instanceof FormData) {
    delete config.headers["Content-Type"];
  }

  return config;
});

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response.status === 401) {
      Cookies.remove("token");
      router.push({ name: "login" });
    }
    return Promise.reject(error);
  },
);

export const axiosInstance = axios;
