<?php
// Vérifier si l'utilisateur est connecté et a le rôle admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<div class="container mx-auto px-4 py-8">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Image du produit -->
            <div class="md:w-1/2 p-4">
                <?php if (!empty($produit['chemin_image'])): ?>
                    <img src="/<?= htmlspecialchars($produit['chemin_image']) ?>" 
                         alt="<?= htmlspecialchars($produit['nom']) ?>" 
                         class="w-full h-auto rounded-lg">
                <?php else: ?>
                    <div class="bg-gray-200 h-64 flex items-center justify-center text-gray-500">
                        <span>Image non disponible</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Détails du produit -->
            <div class="md:w-1/2 p-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <?= htmlspecialchars($produit['nom']) ?>
                </h1>
                
                <div class="flex items-center mb-4">
                    <span class="text-2xl font-bold text-blue-600">
                        <?= number_format($produit['prix_unitaire'], 2) ?> $
                    </span>
                    <?php if ($produit['quantite'] > 0): ?>
                        <span class="ml-4 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                            En stock (<?= $produit['quantite'] ?>)
                        </span>
                    <?php else: ?>
                        <span class="ml-4 px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">
                            Rupture de stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Catégorie -->
                <?php if (isset($categorie)): ?>
                    <div class="mb-4">
                        <span class="text-gray-600">Catégorie : </span>
                        <span class="font-medium"><?= htmlspecialchars($categorie['nom_categorie'] ?? 'Non spécifiée') ?></span>
                    </div>
                <?php endif; ?>

                <!-- Modèle -->
                <?php if (!empty($produit['model'])): ?>
                    <div class="mb-4">
                        <span class="text-gray-600">Modèle : </span>
                        <span class="font-medium"><?= htmlspecialchars($produit['model']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Couleurs disponibles -->
                <?php if (!empty($produit['couleurs'])): 
                    $couleurs = json_decode($produit['couleurs'], true);
                    if (is_array($couleurs) && !empty($couleurs)): ?>
                        <div class="mb-4">
                            <span class="text-gray-600">Couleurs disponibles : </span>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <?php foreach ($couleurs as $couleur): ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">
                                        <?= htmlspecialchars($couleur) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Description courte -->
                <?php if (!empty($produit['courte_description'])): ?>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($produit['courte_description'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Description longue -->
                <?php if (!empty($produit['description'])): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Détails du produit</h3>
                        <div class="text-gray-600"><?= nl2br(htmlspecialchars($produit['description'])) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-4 mt-6">
                    <?php if ($produit['quantite'] > 0): ?>
                        <form action="/panier/ajouter" method="POST" class="flex-1">
                            <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                            <input type="hidden" name="quantite" value="1">
                            <button type="submit" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                                Ajouter au panier
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <a href="/admin/produits/editer/<?= $produit['id_produit'] ?>" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg text-center transition duration-300">
                            Modifier
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton de retour -->
    <div class="mt-8">
        <a href="/produits" 
           class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste des produits
        </a>
    </div>
</div>
