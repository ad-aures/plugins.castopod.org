import multi from "@rollup/plugin-multi-entry";
import path from "path";
import { defineConfig, loadEnv } from "vite";

export default defineConfig(({ mode }) => {
  const env = Object.assign(process.env, loadEnv(mode, process.cwd()));

  return {
    root: path.resolve(__dirname, env.VITE_RESOURCES_DIR ?? "app/Resources"),
    publicDir: "./static",
    build: {
      outDir: path.resolve(
        __dirname,
        `public/${env.VITE_ASSETS_DIR ?? "assets"}`
      ),
      assetsDir: "",
      manifest: env.VITE_MANIFEST ?? ".vite/manifest.json",
      rollupOptions: {
        input: [
          path.resolve(
            __dirname,
            `${env.VITE_RESOURCES_DIR ?? "app/Resources"}/js/**/*.{js,ts}`
          ),
          path.resolve(
            __dirname,
            `./${env.VITE_RESOURCES_DIR ?? "app/Resources"}/styles/**/*.css`
          ),
        ],
        preserveEntrySignatures: true,
        output: [
          {
            dir: path.resolve(
              __dirname,
              `public/${env.VITE_ASSETS_DIR ?? "assets"}`
            ),
            preserveModules: true,
          },
        ],
      },
    },
    server: {
      host: env.VITE_SERVER_HOST ?? "localhost",
      port: env.VITE_SERVER_PORT ?? 5173,
      strictPort: true,
    },
    plugins: [multi({ preserveModules: true })],
  };
});
