-- Schéma de base de données — Softener Lab
-- À importer via phpMyAdmin dans le panneau InfinityFree

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  verification_token VARCHAR(64) DEFAULT NULL,
  verification_token_expires DATETIME DEFAULT NULL,
  failed_attempts INT NOT NULL DEFAULT 0,
  locked_until DATETIME DEFAULT NULL,
  reset_token VARCHAR(64) DEFAULT NULL,
  reset_token_expires DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Migration pour une base déjà existante (si la table `users` a été créée
-- avant l'ajout de ces colonnes) : ces instructions ne font rien si les
-- colonnes existent déjà.
ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token_expires DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS failed_attempts INT NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL;

CREATE TABLE IF NOT EXISTS trainings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  price_eur DECIMAL(8,2) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  training_id INT NOT NULL,
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE
);

-- Catalogue de départ, reprend les formations de formations.html
INSERT INTO trainings (slug, name, description, price_eur) VALUES
('photoshop-fondamentaux', 'Photoshop — Fondamentaux', 'Retouche, montages, exports web et impression', 0),
('indesign-mise-en-page', 'InDesign — Mise en page', 'Brochures, catalogues, gabarits réutilisables', 0),
('montage-video', 'Montage vidéo', 'Rythme, étalonnage, export multi-plateformes', 0),
('cybersecurite-bases', 'Cybersécurité — Les bases', 'Hygiène numérique, phishing, sauvegardes', 0),
('cybersecurite-audit', 'Cybersécurité — Audit & pentest', 'Méthodologie d''audit, tests d''intrusion encadrés', 0);


-- Commandes de services (design, développement, cybersécurité, flyers…), hors formations.
-- Statuts : pending (reçue) -> in_progress (en cours) -> delivered (livrée), ou cancelled (annulée).
-- Cette table est aussi créée automatiquement par l'espace membre si elle est absente.
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category VARCHAR(30) NOT NULL,
  service VARCHAR(100) NOT NULL,
  title VARCHAR(150) NOT NULL,
  brief TEXT NOT NULL,
  deadline DATE DEFAULT NULL,
  status ENUM('pending','in_progress','delivered','cancelled') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
