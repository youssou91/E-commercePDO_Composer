<?php
// Vérification basique des données
if (!isset($commande) || !is_array($commande)) {
    die('Aucune donnée de commande disponible.');
}
?>
<div class="p-4 relative">
    <!-- Bouton de fermeture pour la modale -->
    <button onclick="closeOrderDetailsModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 focus:outline-none z-10">
        <i class="fas fa-times text-2xl"></i>
        <span class="sr-only">Fermer</span>
    </button>
    
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Commande #<?= $commande['id_commande'] ?? 'N/A' ?></h3>
        <div class="text-sm text-gray-600 mb-1">
            <span class="font-medium">Date :</span> 
            <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
        </div>
        <div class="text-sm text-gray-700 mb-4">
            <span class="font-medium">Statut :</span>
            <span class="px-3 py-1.5 text-sm font-medium rounded-full ml-2
                <?= ($commande['statut'] ?? '') === 'Payée' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                <?= htmlspecialchars($commande['statut'] ?? 'Inconnu') ?>
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">Produit</th>
                    <th class="px-4 py-2 text-center">Quantité</th>
                    <th class="px-4 py-2 text-right">Prix unitaire</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                if (!empty($produits)): 
                    foreach ($produits as $produit): 
                        $prix = $produit['prix_vente'] ?? $produit['prix_unitaire'];
                        $sousTotal = $prix * $produit['quantite'];
                        $total += $sousTotal;
                ?>
                    <tr class="border-b">
                        <td class="px-4 py-3"><?= htmlspecialchars($produit['nom'] ?? 'Produit inconnu') ?></td>
                        <td class="px-4 py-3 text-center"><?= $produit['quantite'] ?></td>
                        <td class="px-4 py-3 text-right"><?= number_format($prix, 2) ?> $</td>
                        <td class="px-4 py-3 text-right font-medium"><?= number_format($sousTotal, 2) ?> $</td>
                    </tr>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                            Aucun produit trouvé pour cette commande
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="3" class="px-4 py-3 text-right">Total :</td>
                    <td class="px-4 py-3 text-right text-blue-600">
                        <?= number_format($commande['prix_total'] ?? $total, 2) ?> $
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
