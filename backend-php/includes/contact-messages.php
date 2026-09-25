<?php
// includes/contact-messages.php
// Archive des messages bruts envoyés par les visiteurs non connectés via
// contact.html (contact.php). Indépendant des commandes (table `orders`, réservée
// aux membres) et des notifications internes (cloche) : c'est la trace consultable
// par l'équipe sur /admin-contact-messages.php, en plus de l'email et de la cloche.

require_once __DIR__ . '/member.php';

/** Vrai si la table `contact_messages` est utilisable (créée à la volée sinon). */
function contact_messages_ready(PDO $pdo): bool {
    static $ready = null;
    if ($ready !== null) return $ready;

    try {
        $pdo->query('SELECT 1 FROM contact_messages LIMIT 1');
        return $ready = true;
    } catch (Throwable $e) {
        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS contact_messages (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  name VARCHAR(150) NOT NULL,
                  email VARCHAR(190) NOT NULL,
                  company VARCHAR(150) DEFAULT NULL,
                  service VARCHAR(100) DEFAULT NULL,
                  message TEXT NOT NULL,
                  is_read TINYINT(1) NOT NULL DEFAULT 0,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  INDEX idx_contact_messages_read (is_read, created_at)
                )"
            );
            $pdo->query('SELECT 1 FROM contact_messages LIMIT 1');
            return $ready = true;
        } catch (Throwable $e2) {
            error_log('Softener Lab : table `contact_messages` introuvable et création impossible — importe sql/schema.sql. ' . $e2->getMessage());
            return $ready = false;
        }
    }
}

/** Enregistre un message de contact visiteur. Renvoie son id, ou null si l'archivage échoue. */
function save_contact_message(PDO $pdo, string $name, string $email, string $company, string $service, string $message): ?int {
    if (!contact_messages_ready($pdo)) return null;
    $pdo->prepare(
        'INSERT INTO contact_messages (name, email, company, service, message) VALUES (?, ?, ?, ?, ?)'
    )->execute([
        mb_substr($name, 0, 150),
        mb_substr($email, 0, 190),
        $company !== '' ? mb_substr($company, 0, 150) : null,
        $service !== '' ? mb_substr($service, 0, 100) : null,
        $message,
    ]);
    return (int) $pdo->lastInsertId();
}

function unread_contact_messages_count(PDO $pdo): int {
    if (!contact_messages_ready($pdo)) return 0;
    return (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
}

function mark_contact_message_read(PDO $pdo, int $id): void {
    if (!contact_messages_ready($pdo)) return;
    $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
}

function mark_all_contact_messages_read(PDO $pdo): void {
    if (!contact_messages_ready($pdo)) return;
    $pdo->exec('UPDATE contact_messages SET is_read = 1 WHERE is_read = 0');
}
