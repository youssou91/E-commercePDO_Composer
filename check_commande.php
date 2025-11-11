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

    // Afficher la structure de la table commande
    echo "Structure de la table 'commande' :\n";
    $stmt = $pdo->query("DESCRIBE commande");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} : {$column['Type']} ";
        echo $column['Null'] === 'NO' ? 'NOT NULL ' : 'NULL ';
        echo !empty($column['Key']) ? "({$column['Key']}) " : "";
        echo !empty($column['Default']) ? "DEFAULT '{$column['Default']}' " : "";
        echo !empty($column['Extra']) ? $column['Extra'] : "";
        echo "\n";
    }
    
    // Afficher les 5 dernières commandes
    echo "\n5 dernières commandes :\n";
    $stmt = $pdo->query("SELECT * FROM commande ORDER BY id_commande DESC LIMIT 5");
    $commandes = $stmt->fetchAll();
    
    if (count($commandes) > 0) {
        foreach ($commandes as $commande) {
            echo "Commande #{$commande['id_commande']} - ";
            echo "Utilisateur: {$commande['id_utilisateur']}, ";
            echo "Date: {$commande['date_commande']}, ";
            echo "Prix total: {$commande['prix_total']}, ";
            echo "Statut: {$commande['statut']}\n";
            
            // Afficher les produits de la commande
            $stmt2 = $pdo->prepare("SELECT pc.*, p.nom, p.prix_unitaire as prix_actuel FROM produit_commande pc LEFT JOIN produits p ON pc.id_produit = p.id_produit WHERE pc.id_commande = ?");
            $stmt2->execute([$commande['id_commande']]);
            $produits = $stmt2->fetchAll();
            
            if (count($produits) > 0) {
                echo "  Produits :\n";
                foreach ($produits as $produit) {
                    echo "  - {$produit['nom']} (ID: {$produit['id_produit']}): ";
                    echo "Quantité: {$produit['quantite']}, ";
                    echo "Prix unitaire: " . ($produit['prix_unitaire'] ?? 'Non défini') . ", ";
                    echo "Prix actuel: {$produit['prix_actuel']}\n";
                }
            } else {
                echo "  Aucun produit trouvé pour cette commande.\n";
            }
            echo "\n";
        }
    } else {
        echo "Aucune commande trouvée.\n";
    }
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
