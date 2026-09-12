import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        home: resolve(__dirname, 'index.html'),
        formations: resolve(__dirname, 'formations.html'),
        design: resolve(__dirname, 'design.html'),
        developpement: resolve(__dirname, 'developpement.html'),
        cybersecurite: resolve(__dirname, 'cybersecurite.html'),
        flyers: resolve(__dirname, 'flyers.html'),
        contact: resolve(__dirname, 'contact.html'),
      },
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: (info) => {
          if (info.name && info.name.endsWith('.css')) return 'assets/main.css';
          return 'assets/[name][extname]';
        },
      },
    },
  },
});
