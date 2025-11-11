<?php

use App\Controlleur\PromotionControlleur;
use App\Controlleur\ProduitControlleur;
use App\Modele\ProduitModel; 
use App\Modele\CategorieModel;
// Inclusion de l'autoloader de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// Connexion à la base de données
$pdo = getConnection(); 
// Initialisation du contrôleur
$produitModel = new ProduitModel($pdo); 
$categorieModel = new CategorieModel($pdo);
$produitsController = new ProduitControlleur($produitModel, $categorieModel);
$utilisateurEstConnecte = isset($_SESSION['id_utilisateur']) && !empty($_SESSION['id_utilisateur']);

// Initialisation du contrôleur
$controller = new PromotionControlleur($pdo);


// Récupérer les données depuis le contrôleur
$promotions = $controller->getPromotions(); // Promotions associées aux produits
$produits = $produitsController->afficherProduits(); // Produits pour le modal
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des promotions</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100">
        <?php if ($utilisateurEstConnecte): 
            $utilisateurId = $_SESSION['id_utilisateur']; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="container mx-auto p-6">
                    <h2 class="text-2xl font-bold text-blue-600 mb-4 text-center"> Gestion des promotion </h2>
                    <button 
                        class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition"
                        onclick="document.getElementById('addPromotionModal').classList.remove('hidden')">
                        Ajouter une promotion
                    </button>
                    <!-- Modal pour ajouter une promotion -->
                    <div id="addPromotionModal" class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
                        <div class="bg-white p-6 rounded-lg w-96 shadow-lg">
                            <!-- <h3 class="text-lg font-semibold mb-4">Ajouter une promotion</h3> -->
                            <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center"> Ajouter une promotion </h3>
                            <form action="/admin/promotion/add" method="POST">
                                <div class="mb-4">
                                    <label for="product" class="block text-sm font-medium text-gray-700">Produit</label>
                                    <select name="id_produit" id="product" class="w-full border-gray-300 rounded mt-1" required>
                                        <?php foreach ($produits as $product): ?>
                                            <option value="<?= htmlspecialchars($product['id_produit']) ?>">
                                                <?= htmlspecialchars($product['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="valeur" class="block text-sm font-medium text-gray-700">Réduction (%)</label>
                                    <input type="number" name="valeur" id="valeur" class="w-full border-gray-300 rounded mt-1" required>
                                </div>
                                <div class="mb-4">
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Date de début</label>
                                    <input type="date" name="date_debut" id="start_date" class="w-full border-gray-300 rounded mt-1" required>
                                </div>
                                <div class="mb-4">
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">Date de fin</label>
                                    <input type="date" name="date_fin" id="end_date" class="w-full border-gray-300 rounded mt-1" required>
                                </div>
                                <div class="flex justify-between">
                                    <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Ajouter</button>
                                    <button type="button" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600" onclick="document.getElementById('addPromotionModal').classList.add('hidden')">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">' . htmlspecialchars($_SESSION['success_message']) . '</span>
                                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <title>Close</title>
                                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                                    </svg>
                                </span>
                            </div>';
                            unset($_SESSION['success_message']); // Supprimer le message après affichage
                        }
                        if (isset($_SESSION['error_message'])) {
                            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">' . htmlspecialchars($_SESSION['error_message']) . '</span>
                                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <title>Close</title>
                                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                                    </svg>
                                </span>
                            </div>';
                            unset($_SESSION['error_message']); // Supprimer le message après affichage
                        }
                    ?>
                    <!-- Tableau des promotions -->
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700">
                                    <th class="py-2 px-4 border">Produit</th>
                                    <th class="py-2 px-4 border">Réduction</th>
                                    <th class="py-2 px-4 border">Date de début</th>
                                    <th class="py-2 px-4 border">Date de fin</th>
                                    <!-- <th class="py-2 px-4 border">Actions</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($promotions)): ?>
                                    <?php foreach ($promotions as $promotion): ?>
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="py-2 px-4"><?= isset($promotion['nom']) ? htmlspecialchars($promotion['nom']) : 'Nom non disponible' ?></td>
                                            <td class="py-2 px-4"><?= htmlspecialchars($promotion['valeur']) ?>%</td>
                                            <td class="py-2 px-4"><?= htmlspecialchars($promotion['date_debut']) ?></td>
                                            <td class="py-2 px-4"><?= htmlspecialchars($promotion['date_fin']) ?></td>
                                            <td class="py-2 px-4 space-x-2">
                                                <form action="/admin/promotion/delete/<?= htmlspecialchars($promotion['id_promotion']) ?>" method="POST" style="display: inline;">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" 
                                                            class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 transition"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')">
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Aucune promotion disponible.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: 
                header('Location: /'); 
            endif; ?>   
        <?php else: 
            header('Location: /login'); 
        endif; ?>
    </body>
</html>
