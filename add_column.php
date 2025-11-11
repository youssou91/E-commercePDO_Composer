<?php
// Configuration de la connexion à la base de données
$host = '127.0.0.1';
$dbname = 'cours343';
$username = 'root';
$password = '';
$port = 3306;

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM produit_commande LIKE 'prix_unitaire'");
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne prix_unitaire
        $pdo->exec("ALTER TABLE produit_commande ADD COLUMN prix_unitaire DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantite");
        echo "La colonne 'prix_unitaire' a été ajoutée avec succès à la table 'produit_commande'.\n";
    } else {
        echo "La colonne 'prix_unitaire' existe déjà dans la table 'produit_commande'.\n";
    }
    
    // Vérifier et mettre à jour le type de la colonne prix_total dans la table commande
    $stmt = $pdo->query("SHOW COLUMNS FROM commande WHERE Field = 'prix_total'");
    $column = $stmt->fetch();
    
    if ($column && stripos($column['Type'], 'decimal') === false) {
        $pdo->exec("ALTER TABLE commande MODIFY COLUMN prix_total DECIMAL(10,2) NOT NULL");
        echo "Le type de la colonne 'prix_total' a été mis à jour avec succès dans la table 'commande'.\n";
    } else {
        echo "La colonne 'prix_total' a déjà le bon type dans la table 'commande'.\n";
    }
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
