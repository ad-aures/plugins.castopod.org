import { defineConfig } from "vite";
import codeigniter from "vite-plugin-codeigniter";

export default defineConfig(() => {
  return {
    server: {
      host: true,
      port: 5173,
      strictPort: true,
    },
    plugins: [codeigniter()],
  };
});
