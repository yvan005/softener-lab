<?php
// includes/config.php

// Adresse complète de ton site une fois en ligne (sans slash final)
define('SITE_URL', 'http://softenerlab.freepage.cc');

// --- Configuration SMTP (Gmail utilisé en exemple) ---
// InfinityFree bloque la fonction mail() de PHP : il faut passer par un SMTP externe.
// Avec Gmail : active la validation en 2 étapes sur le compte, puis crée un
// "mot de passe d'application" (myaccount.google.com/apppasswords) — n'utilise
// jamais le mot de passe normal du compte Gmail ici.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ton-adresse@gmail.com');
define('SMTP_PASS', 'mot-de-passe-application-16-caracteres');
define('SMTP_FROM', 'ton-adresse@gmail.com');
define('SMTP_FROM_NAME', 'Softener Lab');
