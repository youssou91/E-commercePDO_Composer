<?php
require 'config/db.php';
$db = getConnection();
$stmt = $db->query('SHOW COLUMNS FROM utilisateur');
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Colonnes de la table utilisateur :\n";
print_r($columns);
?>
