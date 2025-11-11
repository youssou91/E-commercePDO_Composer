<?php
require 'config/db.php';
$db = getConnection();

// Vérifier si la table commande existe
$tableExists = $db->query("SHOW TABLES LIKE 'commande'")->rowCount() > 0;
echo "Table 'commande' existe : " . ($tableExists ? 'Oui' : 'Non') . "\n\n";

if ($tableExists) {
    // Afficher la structure complète de la table
    $stmt = $db->query("SHOW CREATE TABLE commande");
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Structure de la table 'commande' :\n";
    echo $table['Create Table'] . "\n\n";
    
    // Afficher les 2 premières lignes pour vérifier les données
    echo "Données d'exemple (2 premières lignes) :\n";
    $stmt = $db->query("SELECT * FROM commande LIMIT 2");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}
?>
