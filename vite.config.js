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
        apercuEntreprise: resolve(__dirname, 'apercu-entreprise.html'),
        leadership: resolve(__dirname, 'leadership.html'),
        carriere: resolve(__dirname, 'carriere.html'),
        photoshopFondamentaux: resolve(__dirname, 'photoshop-fondamentaux.html'),
        indesignMiseEnPage: resolve(__dirname, 'indesign-mise-en-page.html'),
        montageVideo: resolve(__dirname, 'montage-video.html'),
        identiteVisuelle: resolve(__dirname, 'design-graphique.html'),
        supportsImprimes: resolve(__dirname, 'design-web-ux-ui.html'),
        creationVideo: resolve(__dirname, 'design-reseaux-sociaux.html'),
        motionDesign: resolve(__dirname, 'motion-design.html'),
        illustration: resolve(__dirname, 'illustration.html'),
        designPackaging: resolve(__dirname, 'design-packaging.html'),
        designEspaceInterieur: resolve(__dirname, 'design-espace-interieur.html'),
        applicationsWeb: resolve(__dirname, 'applications-web.html'),
        applicationsMobiles: resolve(__dirname, 'applications-mobiles.html'),
        logicielsMetier: resolve(__dirname, 'logiciels-metier.html'),
        auditSecurite: resolve(__dirname, 'audit-securite.html'),
        testIntrusion: resolve(__dirname, 'test-intrusion.html'),
        conseilStrategie: resolve(__dirname, 'conseil-strategie.html'),
        formationSensibilisation: resolve(__dirname, 'formation-sensibilisation.html'),
        securiteReseau: resolve(__dirname, 'securite-reseau.html'),
        reponseIncident: resolve(__dirname, 'reponse-incident.html'),
        securiteCloud: resolve(__dirname, 'securite-cloud.html'),
        securiteApplications: resolve(__dirname, 'securite-applications.html'),
        modeleEvenementiel: resolve(__dirname, 'modele-evenementiel.html'),
        modelePromotion: resolve(__dirname, 'modele-promotion.html'),
        modeleAnnonce: resolve(__dirname, 'modele-annonce.html'),
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
