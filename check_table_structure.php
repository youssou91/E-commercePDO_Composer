<?php
require 'config/db.php';
$db = getConnection();

// Vérifier si la table utilisateur existe
$tableExists = $db->query("SHOW TABLES LIKE 'utilisateur'")->rowCount() > 0;
echo "Table 'utilisateur' existe : " . ($tableExists ? 'Oui' : 'Non') . "\n\n";

if ($tableExists) {
    // Afficher la structure complète de la table
    $stmt = $db->query("SHOW CREATE TABLE utilisateur");
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Structure de la table 'utilisateur' :\n";
    echo $table['Create Table'] . "\n\n";
    
    // Afficher les 5 premières lignes pour vérifier les données
    echo "Données d'exemple (5 premières lignes) :\n";
    $stmt = $db->query("SELECT * FROM utilisateur LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}
?>
