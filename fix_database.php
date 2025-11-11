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

    // 1. Modifier le type de la colonne prix_total dans la table commande
    $pdo->exec("ALTER TABLE commande MODIFY COLUMN prix_total DECIMAL(10,2) NOT NULL");
    echo "La colonne 'prix_total' a été modifiée avec succès dans la table 'commande'.\n";

    // 2. Vérifier si la colonne prix_unitaire existe déjà dans produit_commande
    $stmt = $pdo->query("SHOW COLUMNS FROM produit_commande LIKE 'prix_unitaire'");
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne prix_unitaire
        $pdo->exec("ALTER TABLE produit_commande ADD COLUMN prix_unitaire DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantite");
        echo "La colonne 'prix_unitaire' a été ajoutée avec succès à la table 'produit_commande'.\n";
        
        // Mettre à jour les prix unitaires existants avec les prix actuels des produits
        $pdo->exec("UPDATE produit_commande pc 
                   INNER JOIN produits p ON pc.id_produit = p.id_produit 
                   SET pc.prix_unitaire = p.prix_unitaire 
                   WHERE pc.prix_unitaire = 0 OR pc.prix_unitaire IS NULL");
        echo "Les prix unitaires existants ont été mis à jour avec les prix actuels des produits.\n";
    } else {
        echo "La colonne 'prix_unitaire' existe déjà dans la table 'produit_commande'.\n";
    }
    
    echo "\nLa base de données a été mise à jour avec succès.\n";
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
