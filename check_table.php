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

    // Vérifier si la table produit_commande existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'produit_commande'");
    if ($stmt->rowCount() > 0) {
        echo "La table produit_commande existe.\n";
        
        // Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE produit_commande");
        $columns = $stmt->fetchAll();
        
        echo "Structure de la table produit_commande :\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} : {$column['Type']} ";
            echo $column['Null'] === 'NO' ? 'NOT NULL ' : 'NULL ';
            echo !empty($column['Key']) ? "({$column['Key']}) " : "";
            echo !empty($column['Default']) ? "DEFAULT '{$column['Default']}' " : "";
            echo !empty($column['Extra']) ? $column['Extra'] : "";
            echo "\n";
        }
        
        // Vérifier si la colonne prix_unitaire existe
        $hasPrixUnitaire = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'prix_unitaire') {
                $hasPrixUnitaire = true;
                break;
            }
        }
        
        if (!$hasPrixUnitaire) {
            echo "\nLa colonne 'prix_unitaire' n'existe pas dans la table produit_commande.\n";
            
            // Proposer d'ajouter la colonne
            echo "Voulez-vous ajouter la colonne 'prix_unitaire' à la table ? (o/n) ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            
            if (trim($line) === 'o') {
                $pdo->exec("ALTER TABLE produit_commande ADD COLUMN prix_unitaire DECIMAL(10,2) NOT NULL AFTER quantite");
                echo "La colonne 'prix_unitaire' a été ajoutée avec succès.\n";
            } else {
                echo "Opération annulée.\n";
            }
        } else {
            echo "\nLa colonne 'prix_unitaire' existe déjà dans la table produit_commande.\n";
        }
    } else {
        echo "La table produit_commande n'existe pas.\n";
    }
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérifier la structure de la table commande
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'commande'");
    if ($stmt->rowCount() > 0) {
        echo "\nStructure de la table commande :\n";
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
    } else {
        echo "La table commande n'existe pas.\n";
    }
} catch (PDOException $e) {
    echo "Erreur lors de la vérification de la table commande : " . $e->getMessage() . "\n";
}
?>
