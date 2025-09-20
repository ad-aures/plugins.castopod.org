import { defineConfig, loadEnv } from "vite";
import codeigniter from "vite-plugin-codeigniter";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd());

  return {
    server: {
      host: true,
      port: env.VITE_PORT || 5173,
      strictPort: true,
    },
    plugins: [codeigniter(), tailwindcss()],
  };
});
