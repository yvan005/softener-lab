<?php
// includes/orders.php
// Commandes de services (design, développement, cybersécurité, flyers…), distinctes
// des formations (table `purchases`). Catalogue, statuts et accès à la table `orders`.

require_once __DIR__ . '/member.php';

const OPEN_ORDER_LIMIT = 10; // commandes ouvertes (reçues + en cours) maximum par membre

/** Catalogue proposé dans le formulaire — même liste que contact.html. */
function order_catalog(): array {
    return [
        'design' => [
            'label' => 'Design',
            'services' => ['Design graphique', 'Design web / UX-UI', 'Design réseaux sociaux', 'Motion design',
                           'Illustration', 'Design de packaging', "Design d'espace / intérieur"],
        ],
        'developpement' => [
            'label' => 'Développement',
            'services' => ['Applications web', 'Applications mobiles', 'Logiciels métier'],
        ],
        'cybersecurite' => [
            'label' => 'Cybersécurité',
            'services' => ['Audit de sécurité', "Test d'intrusion", 'Conseil et stratégie', 'Formation et sensibilisation',
                           'Sécurité réseau', 'Réponse à incident', 'Sécurité cloud', 'Sécurité des applications'],
        ],
        'flyers' => [
            'label' => 'Flyers & templates',
            'services' => ['Modèle Événementiel', 'Modèle Promotion', 'Modèle Annonces', 'Modèle Recrutement',
                           'Modèle Associatif', 'Modèle Restaurant / Menu', 'Modèle Informatif / Éducatif',
                           'Modèle Politique', 'Modèle Invitation', 'Modèle Coupon / Réduction'],
        ],
        'autre' => [
            'label' => 'Autre',
            'services' => ['Autre / plusieurs services'],
        ],
    ];
}

/** Valide une valeur "categorie|service" venant du formulaire. Renvoie [categorie, service] ou null. */
function order_find_service(string $value): ?array {
    $parts = explode('|', $value, 2);
    if (count($parts) !== 2) return null;
    [$cat, $srv] = $parts;
    $catalog = order_catalog();
    if (isset($catalog[$cat]) && in_array($srv, $catalog[$cat]['services'], true)) {
        return [$cat, $srv];
    }
    return null;
}

function order_category_label(string $key): string {
    return order_catalog()[$key]['label'] ?? 'Autre';
}

function order_statuses(): array {
    return [
        'pending'     => ['label' => 'Reçue',    'badge' => 'badge--pending'],
        'in_progress' => ['label' => 'En cours', 'badge' => 'badge--info'],
        'delivered'   => ['label' => 'Livrée',   'badge' => 'badge--ok'],
        'cancelled'   => ['label' => 'Annulée',  'badge' => 'badge--neutral'],
    ];
}

function order_badge(string $status): string {
    $s = order_statuses()[$status] ?? ['label' => $status, 'badge' => 'badge--neutral'];
    return '<span class="badge ' . e($s['badge']) . '">' . e($s['label']) . '</span>';
}

function order_ref($id): string {
    return 'CMD-' . str_pad((string) (int) $id, 4, '0', STR_PAD_LEFT);
}

/**
 * Vrai si la table `orders` est utilisable. Si elle n'existe pas encore (migration
 * pas faite), on tente de la créer ; en cas d'échec l'espace membre continue de
 * fonctionner sans la partie commandes (aucune erreur 500).
 */
function orders_ready(PDO $pdo): bool {
    static $ready = null;
    if ($ready !== null) return $ready;

    $tableOk = false;
    try {
        $pdo->query('SELECT 1 FROM orders LIMIT 1');
        $tableOk = true;
    } catch (Throwable $e) {
        // table absente : on tente la création ci-dessous
    }

    if (!$tableOk) {
        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS orders (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  user_id INT NOT NULL,
                  category VARCHAR(30) NOT NULL,
                  service VARCHAR(100) NOT NULL,
                  title VARCHAR(150) NOT NULL,
                  brief TEXT NOT NULL,
                  deadline DATE DEFAULT NULL,
                  status ENUM('pending','in_progress','delivered','cancelled') NOT NULL DEFAULT 'pending',
                  admin_note TEXT DEFAULT NULL,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )"
            );
            $pdo->query('SELECT 1 FROM orders LIMIT 1');
        } catch (Throwable $e) {
            error_log('Softener Lab : table `orders` introuvable et création impossible — importe sql/schema.sql. ' . $e->getMessage());
            return $ready = false;
        }
    }

    // Colonne `admin_note` (message de l'admin au membre), absente de la toute première version de la table.
    try {
        $pdo->query('SELECT admin_note FROM orders LIMIT 1');
    } catch (Throwable $e) {
        try {
            $pdo->exec('ALTER TABLE orders ADD COLUMN admin_note TEXT NULL');
        } catch (Throwable $e2) {
            error_log('Softener Lab : ajout de la colonne `orders.admin_note` impossible — ' . $e2->getMessage());
            return $ready = false;
        }
    }

    return $ready = true;
}

function orders_unavailable_notice(): string {
    return '<div class="alert alert--info" role="status">Le suivi des commandes est momentanément indisponible. Réessaie un peu plus tard ou <a href="/contact.html" style="text-decoration:underline;">contacte-nous</a>.</div>';
}
