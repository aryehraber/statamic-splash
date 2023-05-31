import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue2'

export default defineConfig({
  build: {
    manifest: false,
    outDir: 'resources/dist',
    assetsDir: '',
    rollupOptions: {
      output: {
        entryFileNames: '[name]-fieldtype.js',
        assetFileNames: '[name]-fieldtype.[ext]'
      }
    }
  },
  plugins: [
    laravel({
      input: [
        'resources/js/splash.js',
        'resources/css/splash.css'
      ],
    }),
    vue(),
  ],

})
