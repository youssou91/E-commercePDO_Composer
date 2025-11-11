<?php
use App\Modele\CommandeModel;
use App\Controlleur\CommandeControlleur;

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getConnection(); 
$commandeModel = new CommandeModel($pdo);
$commandeController = new CommandeControlleur($commandeModel);
$utilisateurEstConnecte = isset($_SESSION['id_utilisateur']) && !empty($_SESSION['id_utilisateur']);

// Récupérer les commandes en fonction du rôle de l'utilisateur
if ($utilisateurEstConnecte) {
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    if ($isAdmin) {
        $orders = $commandeModel->getAllCommandes();
    } else {
        $orders = $commandeModel->getCommandesByUser($_SESSION['id_utilisateur']);
    }
} else {
    $orders = [];
}

$index = 1;
?>

<!DOCTYPE html>
<html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Liste des commandes</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-gray-50 font-sans">
        <?php if ($utilisateurEstConnecte): 
            $utilisateurId = $_SESSION['id_utilisateur']; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="container mx-auto py-8 px-4">
                    <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">Liste des commandes</h1>
                    <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
                        <table id="ordersTable" class="min-w-full">
                            <thead class="bg-blue-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">#</th>
                                    <th class="px-4 py-2 text-left">Utilisateur</th>
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Prix total</th>
                                    <th class="px-4 py-2 text-left">Statut</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $index => $order): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2"><?=  $index + 1; ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($order['prenom'] . ' ' . $order['nom_utilisateur']); ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($order['date_commande']); ?></td>
                                    <td class="px-4 py-2"><?= number_format(htmlspecialchars($order['prix_total']), 2); ?> $</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full 
                                            <?php 
                                                // Application des couleurs en fonction du statut
                                                if ($order['statut'] == 'En attente') {
                                                    echo 'bg-yellow-200 text-yellow-800'; 
                                                } elseif ($order['statut'] == 'En traitement') {
                                                    echo 'bg-orange-200 text-orange-800'; 
                                                } elseif ($order['statut'] == 'En expédition') {
                                                    echo 'bg-green-200 text-green-800'; 
                                                } elseif ($order['statut'] == 'Livrée') {
                                                    echo 'bg-blue-200 text-blue-800'; 
                                                } elseif ($order['statut'] == 'Annulée') {
                                                    echo 'bg-red-200 text-red-800'; 
                                                } elseif ($order['statut'] == 'Payée') {
                                                    echo 'bg-purple-200 text-purple-800'; 
                                                }
                                            ?>">
                                            <?= htmlspecialchars($order['statut']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex space-x-2">
                                            <!-- Bouton pour traiter la commande -->
                                            <a href="?action=update_status&id_commande=<?= $order['id_commande']; ?>&statut=En+traitement" 
                                               class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center justify-center" 
                                               <?= ($order['statut'] !== 'En attente') ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                                                <i class="bi bi-gear-fill"></i>
                                            </a>
                                            <!-- Bouton pour expédier la commande -->
                                            <a href="?action=update_status&id_commande=<?= $order['id_commande']; ?>&statut=En+expédition" 
                                               class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 flex items-center justify-center" 
                                               <?= ($order['statut'] !== 'En traitement') ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                                                <i class="bi bi-truck"></i>
                                            </a>
                                            <!-- Bouton pour livrer la commande -->
                                            <a href="?action=update_status&id_commande=<?= $order['id_commande']; ?>&statut=Livrée" 
                                               class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center justify-center" 
                                               <?= ($order['statut'] !== 'En expédition') ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                                                <i class="bi bi-check-circle-fill"></i>
                                            </a>
                                            <?php if (in_array($order['statut'], ['En attente', 'En traitement'])): ?>
                                            <form action="/commande/annuler/<?= $order['id_commande']; ?>" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?');">
                                                <input type="hidden" name="_method" value="POST">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 flex items-center justify-center">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <div class="modal fade" id="modalAnnulerCommande<?= $order['id_commande']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmer l'annulation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                Êtes-vous sûr de vouloir annuler la commande : <strong>#<?= $order['id_commande']; ?></strong> ?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <a href="?action=update_status&id_commande=<?= $order['id_commande']; ?>&statut=Annulée" class="btn btn-danger">
                                                    Confirmer l'annulation
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
        <script>
            $(document).ready(function () {
            // Initialisation de DataTables
            $('#ordersTable').DataTable({
                responsive: true, // Rend la table responsive
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json"
                }
            });
            });
        </script>
    </body>

</html>


