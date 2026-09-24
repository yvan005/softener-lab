<?php
// includes/config.php
// Ce fichier est généré automatiquement à chaque déploiement à partir de
// includes/config.template.php — ne modifie pas directement ce fichier sur
// le serveur, tes changements seraient écrasés au prochain push.

define('SITE_URL', 'http://softenerlab.freepage.cc');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '__SMTP_USER__');
define('SMTP_PASS', '__SMTP_PASS__');
define('SMTP_FROM', '__SMTP_USER__');
define('SMTP_FROM_NAME', 'Softener Lab');

// Administrateurs : ces adresses (comptes créés et confirmés sur le site) accèdent à
// /admin.php. Par défaut, l'adresse d'envoi du site. Pour en ajouter :
// define('ADMIN_EMAILS', [SMTP_FROM, 'toi@exemple.com']);
define('ADMIN_EMAILS', [SMTP_FROM]);
