<?php
require __DIR__ . '/../../vendor/autoload.php';
use App\Controlleur\ProfileControlleur;
// Vérifiez si l'utilisateur est connecté

// Initialisez les dépendances
$dbConnection = getConnection();  
$userController = new ProfileControlleur($dbConnection);
// Récupérez l'ID de l'utilisateur
$userId = $_SESSION['id_utilisateur'];
// Récupérez les informations utilisateur
$userInfo = $userController->getUserInfo($userId);

// Récupérer les paramètres de pagination et de filtre
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 5; // Nombre de commandes par page
$statut = isset($_GET['statut']) ? $_GET['statut'] : null;

// Récupérer les commandes de l'utilisateur avec pagination et filtrage
$ordersData = $userController->getUserOrders($userId, $page, $perPage, $statut);
$userOrders = $ordersData['commandes'];
$pagination = $ordersData['pagination'];
$currentFilters = $ordersData['filters'];

// Liste des statuts disponibles pour le filtre
$statutsDisponibles = [
    '' => 'Tous les statuts',
    'En attente' => 'En attente',
    'En traitement' => 'En traitement',
    'En expédition' => 'En expédition',
    'Livrée' => 'Livrée',
    'Annulée' => 'Annulée',
    'Payée' => 'Payée'
];

$utilisateurEstConnecte = isset($_SESSION['id_utilisateur']) && !empty($_SESSION['id_utilisateur']);

// Stockez les commandes dans la session pour les rendre accessibles
$_SESSION['orders'] = $userOrders;
// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfile'])) {
    try {
        // Validation des champs requis
        $requiredFields = [
            'nom_utilisateur' => 'Le nom est requis',
            'prenom_utilisateur' => 'Le prénom est requis',
            'email_utilisateur' => 'L\'email est requis',
            'telephone_utilisateur' => 'Le téléphone est requis'
        ];

        $errors = [];
        foreach ($requiredFields as $field => $errorMessage) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $errors[] = $errorMessage;
            }
        }

        // Validation de l'email
        if (!filter_var($_POST['email_utilisateur'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide';
        }

        if (empty($errors)) {
            // Nettoyage des données
            $data = [
                'nom' => htmlspecialchars(trim($_POST['nom_utilisateur'])),
                'prenom' => htmlspecialchars(trim($_POST['prenom_utilisateur'])),
                'email' => filter_var(trim($_POST['email_utilisateur']), FILTER_SANITIZE_EMAIL),
                'telephone' => htmlspecialchars(trim($_POST['telephone_utilisateur'])),
                'adresse' => htmlspecialchars(trim($_POST['adresse_utilisateur'] ?? '')),
                'ville' => htmlspecialchars(trim($_POST['ville_utilisateur'] ?? '')),
                'code_postal' => htmlspecialchars(trim($_POST['code_postal_utilisateur'] ?? '')),
                'province' => htmlspecialchars(trim($_POST['province_utilisateur'] ?? '')),
                'pays' => htmlspecialchars(trim($_POST['pays_utilisateur'] ?? ''))
            ];

            // Mise à jour des informations
            $result = $userController->updateUserInfo(
                $userId,
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['telephone'],
                $data['adresse'],
                $data['ville'],
                $data['code_postal'],
                $data['province'],
                $data['pays']
            );

            if ($result) {
                $_SESSION['success'] = 'Vos informations ont été mises à jour avec succès.';
                // Rafraîchir les données utilisateur
                $userInfo = $userController->getUserInfo($userId);
            } else {
                throw new Exception('Une erreur est survenue lors de la mise à jour de votre profil.');
            }
        } else {
            throw new Exception(implode('<br>', $errors));
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    // Redirection vers la même page pour éviter la soumission multiple du formulaire
    header('Location: /mon_profile');
    exit;
}
// Traitement du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updatePassword'])) {
    $ancienMotDePasse = $_POST['ancien_mot_de_passe'];
    $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'];
    $confirmationMotDePasse = $_POST['confirmation_mot_de_passe'];
    if ($nouveauMotDePasse === $confirmationMotDePasse) {
        if ($userController->updatePassword($userId, $ancienMotDePasse, $nouveauMotDePasse)) {
            echo "Mot de passe mis à jour avec succès!";
        } else {
            echo "L'ancien mot de passe est incorrect.";
        }
    } else {
        echo "Les nouveaux mots de passe ne correspondent pas.";
    }
}
// Gestion des actions sur les commandes
if (isset($_POST['action'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];
    switch ($action) {
        case 'traiter':
            $userController->updateOrderStatus($orderId, 'En traitement');
            break;
        case 'expédier':
            $userController->updateOrderStatus($orderId, 'En expédition');
            break;
        case 'annuler':
            $userController->updateOrderStatus($orderId, 'Annulée');
            break;
    }
    echo '<script>window.location.href = "profile.php";</script>';
    exit;
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php 
    // Afficher les messages de succès/erreur
    if (isset($_SESSION['success'])): 
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">'.$_SESSION['success'].'</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>';
        unset($_SESSION['success']);
    endif;
    
    if (isset($_SESSION['error'])): 
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">'.$_SESSION['error'].'</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>';
        unset($_SESSION['error']);
    endif;
    ?>
    
    <?php if ($utilisateurEstConnecte): 
            $utilisateurId = $_SESSION['id_utilisateur']; ?>
        <div class="container mx-auto p-8">
            <h1 class="text-3xl text-center text-blue-600 font-semibold mb-5">Mon Profil</h1>
            <div class="flex flex-col lg:flex-row gap-5">
                <div class="bg-white p-6 rounded-lg shadow-md lg:w-1/3">
                    <h3 class="text-xl text-center text-blue-600 font-semibold mb-4">Informations personnelles</h3>
                    <p><span class="font-semibold">Nom:</span> <?= htmlspecialchars($userInfo['nom_utilisateur']) ?></p>
                    <p><span class="font-semibold">Prénom:</span> <?= htmlspecialchars($userInfo['prenom']) ?></p>
                    <p><span class="font-semibold">Email:</span> <?= htmlspecialchars($userInfo['couriel']) ?></p>
                    <p><span class="font-semibold">Téléphone:</span> <?= htmlspecialchars($userInfo['telephone']) ?></p>
                    <h4 class="text-lg font-semibold text-blue-600 mt-4">Adresse</h4>
                    <p><span class="font-semibold">Rue:</span> <?= htmlspecialchars($userInfo['numero']).' '.htmlspecialchars($userInfo['rue']) ?></p>
                    <p><span class="font-semibold">Code Postal:</span> <?= htmlspecialchars($userInfo['code_postal']) ?></p>
                    <p><span class="font-semibold">Ville:</span> <?= htmlspecialchars($userInfo['ville']).', '.htmlspecialchars($userInfo['province']) ?></p>
                    <p><span class="font-semibold">Pays:</span> <?= htmlspecialchars($userInfo['pays']) ?></p>
                    <div class="mt-6 flex gap-4">
                        <button class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600" data-modal-target="#modalModifierProfil">
                            <i class="fas fa-user-edit"></i>
                        </button>
                        <button class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600" data-modal-target="#modalModifierMotDePasse">
                            <i class="fas fa-key"></i>
                        </button>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md lg:w-2/3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl text-blue-600 font-semibold">Mes Commandes</h3>
                        
                        <!-- Filtre par statut -->
                        <form method="get" action="" class="flex items-center">
                            <input type="hidden" name="page" value="1">
                            <label for="statut" class="mr-2 text-sm font-medium text-gray-700">Filtrer par statut :</label>
                            <select name="statut" id="statut" onchange="this.form.submit()" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                <?php foreach ($statutsDisponibles as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($statut === $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    
                    <?php if (is_array($userOrders) && count($userOrders) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-center border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b">#</th>
                                        <th class="py-2 px-4 border-b">Date</th>
                                        <th class="py-2 px-4 border-b">Montant</th>
                                        <th class="py-2 px-4 border-b">Statut</th>
                                        <th class="py-2 px-4 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $index = 1; ?>
                                    <?php foreach ($userOrders as $order): ?>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-2 px-4"><?= $index++; ?></td>
                                            <td class="py-2 px-4"><?= htmlspecialchars($order['date_commande']) ?></td>
                                            <td class="px-4 py-2"><?= number_format(htmlspecialchars($order['prix_total']), 2); ?> $</td>
                                            <td class="py-2 px-4">
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
                                            <td>
                                                <div class="flex space-x-2">
                                                    <!-- Détails -->
                                                    <button onclick="openOrderDetailsModal('<?= $order['id_commande'] ?>')" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                    <!-- Paiement -->                                    
                                                    <?php if ($order['statut'] != 'Livrée' && $order['statut'] != 'Annulée' && $order['statut'] != 'En expédition'): ?>
                                                        <form method="post" action="/profile/paiement/<?= $order['id_commande'] ?>">
                                                            <input type="hidden" name="id_commande" value="<?= htmlspecialchars($order['id_commande']) ?>">
                                                            <input type="hidden" name="prix_total" value="<?= htmlspecialchars($order['prix_total']); ?>">
                                                            <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                                                                <i class="fas fa-credit-card"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="#" class="bg-gray-500 text-white py-2 px-4 rounded cursor-not-allowed" disabled>
                                                            <i class="fas fa-credit-card"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <!-- Annulation -->
                                                    <?php if ($order['statut'] == 'En attente' || $order['statut'] == 'En traitement'): ?>
                                                        <button onclick="openModal('<?= $order['id_commande']; ?>')" 
                                                            class="bg-red-500 text-white px-4 py-2 rounded"> 
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="openModal('<?= $order['id_commande']; ?>')" class="bg-gray-500 text-white py-2 px-4 rounded cursor-not-allowed" disabled>
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td> 
                                        </tr>
                                        <!-- Modal unique pour cette commande -->
                                        <div id="modalAnnulerCommande<?= $order['id_commande']; ?>" 
                                            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
                                            <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h5 class="text-lg font-semibold">Confirmer l'annulation</h5>
                                                    <button onclick="closeModal('<?= $order['id_commande']; ?>')" class="text-gray-500 hover:text-gray-800">
                                                        &times;
                                                    </button>
                                                </div>
                                                <div class="mb-4">
                                                    Êtes-vous sûr de vouloir annuler la commande : <strong><?= $order['id_commande']; ?></strong> ? 
                                                    Cette action est irréversible.
                                                </div>
                                                <div class="flex justify-end gap-4">
                                                    <button type="button" onclick="closeModal('<?= $order['id_commande']; ?>')" 
                                                        class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
                                                    <form method="POST" action="/commande/<?= $order['id_commande']; ?>/modifier/annuler" class="inline">
                                                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                                            Confirmer l'annulation
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="mt-6 flex justify-center">
                                <nav class="flex items-center space-x-1">
                                    <!-- Bouton Précédent -->
                                    <?php if ($pagination['current_page'] > 1): ?>
                                        <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $statut ? '&statut=' . urlencode($statut) : '' ?>" 
                                           class="px-3 py-1 border rounded-md text-sm font-medium hover:bg-gray-50">
                                            &laquo; Précédent
                                        </a>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-gray-400">&laquo; Précédent</span>
                                    <?php endif; ?>
                                    
                                    <!-- Numéros de page -->
                                    <?php 
                                    $startPage = max(1, $pagination['current_page'] - 2);
                                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                    
                                    // Afficher le premier numéro de page si nécessaire
                                    if ($startPage > 1): ?>
                                        <a href="?page=1<?= $statut ? '&statut=' . urlencode($statut) : '' ?>" 
                                           class="px-3 py-1 border rounded-md text-sm font-medium hover:bg-gray-50">
                                            1
                                        </a>
                                        <?php if ($startPage > 2) echo '<span class="px-1">...</span>'; ?>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <?php if ($i == $pagination['current_page']): ?>
                                            <span class="px-3 py-1 bg-blue-500 text-white rounded-md text-sm font-medium">
                                                <?= $i ?>
                                            </span>
                                        <?php else: ?>
                                            <a href="?page=<?= $i ?><?= $statut ? '&statut=' . urlencode($statut) : '' ?>" 
                                               class="px-3 py-1 border rounded-md text-sm font-medium hover:bg-gray-50">
                                                <?= $i ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <!-- Afficher le dernier numéro de page si nécessaire -->
                                    <?php if ($endPage < $pagination['total_pages']): ?>
                                        <?php if ($endPage < $pagination['total_pages'] - 1) echo '<span class="px-1">...</span>'; ?>
                                        <a href="?page=<?= $pagination['total_pages'] ?><?= $statut ? '&statut=' . urlencode($statut) : '' ?>" 
                                           class="px-3 py-1 border rounded-md text-sm font-medium hover:bg-gray-50">
                                            <?= $pagination['total_pages'] ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Bouton Suivant -->
                                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                        <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $statut ? '&statut=' . urlencode($statut) : '' ?>" 
                                           class="px-3 py-1 border rounded-md text-sm font-medium hover:bg-gray-50">
                                            Suivant &raquo;
                                        </a>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-gray-400">Suivant &raquo;</span>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-sm text-gray-500 text-center">
                            Affichage de <?= count($userOrders) ?> commande(s) sur <?= $pagination['total_items'] ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">Aucune commande trouvée.</p>
                            <?php if ($statut): ?>
                                <a href="?statut=" class="text-blue-500 hover:underline mt-2 inline-block">
                                    Afficher toutes les commandes
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php else: 
        header('Location: /login'); 
    endif; ?>    
 <!-- Modal Modification Profil -->
<!-- Modal de modification du profil -->
<div id="modalModifierProfil" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg p-6 relative mx-4">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 focus:outline-none" onclick="document.querySelector('#modalModifierProfil').classList.add('hidden');">
            <i class="fas fa-times text-2xl"></i>
            <span class="sr-only">Fermer</span>
        </button>
        <h3 class="text-2xl font-semibold text-center text-blue-600 mb-6">Modifier les informations du profil</h3>
        <form method="POST" action="/profile/edit" class="grid grid-cols-1 md:grid-cols-2 gap-4" id="profileForm" onsubmit="return validateProfileForm()">
            <input type="hidden" name="updateProfile" value="1">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <!-- Colonne 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-sm font-medium" for="nom_utilisateur">Nom <span class="text-red-500">*</span></label>
                    <input type="text" id="nom_utilisateur" name="nom_utilisateur" 
                           value="<?= htmlspecialchars($userInfo['nom_utilisateur']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                           required>
                    <p id="nom_error" class="text-red-500 text-xs mt-1 hidden">Le nom est requis</p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-sm font-medium" for="prenom_utilisateur">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" id="prenom_utilisateur" name="prenom_utilisateur" 
                           value="<?= htmlspecialchars($userInfo['prenom']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                           required>
                    <p id="prenom_error" class="text-red-500 text-xs mt-1 hidden">Le prénom est requis</p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-sm font-medium" for="email_utilisateur">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email_utilisateur" name="email_utilisateur" 
                           value="<?= htmlspecialchars($userInfo['couriel']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                           required>
                    <p id="email_error" class="text-red-500 text-xs mt-1 hidden">Veuillez entrer un email valide</p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-sm font-medium" for="telephone_utilisateur">Téléphone <span class="text-red-500">*</span></label>
                    <input type="tel" id="telephone_utilisateur" name="telephone_utilisateur" 
                           value="<?= htmlspecialchars($userInfo['telephone']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                           pattern="[0-9]{10}" title="Numéro de téléphone à 10 chiffres"
                           required>
                    <p id="telephone_error" class="text-red-500 text-xs mt-1 hidden">Le téléphone est requis (10 chiffres)</p>
                </div>
            </div>
            <!-- Colonne 2 -->
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-sm font-medium" for="adresse_utilisateur">Adresse</label>
                    <input type="text" id="adresse_utilisateur" name="adresse_utilisateur" 
                           value="<?= htmlspecialchars($userInfo['rue']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm font-medium" for="code_postal_utilisateur">Code postal</label>
                        <input type="text" id="code_postal_utilisateur" name="code_postal_utilisateur" 
                               value="<?= htmlspecialchars($userInfo['code_postal'] ?? '') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               pattern="[A-Za-z0-9\s-]+" title="Code postal valide requis">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm font-medium" for="ville_utilisateur">Ville</label>
                        <input type="text" id="ville_utilisateur" name="ville_utilisateur" 
                               value="<?= htmlspecialchars($userInfo['ville'] ?? '') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm font-medium" for="province_utilisateur">Province/État</label>
                        <input type="text" id="province_utilisateur" name="province_utilisateur" 
                               value="<?= htmlspecialchars($userInfo['province'] ?? '') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-sm font-medium" for="pays_utilisateur">Pays</label>
                    <select id="pays_utilisateur" name="pays_utilisateur" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                        <option value="" <?= empty($userInfo['pays']) ? 'selected' : '' ?>>Sélectionner un pays</option>
                        <option value="Canada" <?= ($userInfo['pays'] ?? '') === 'Canada' ? 'selected' : '' ?>>Canada</option>
                        <option value="France" <?= ($userInfo['pays'] ?? '') === 'France' ? 'selected' : '' ?>>France</option>
                        <option value="Belgique" <?= ($userInfo['pays'] ?? '') === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                        <option value="Suisse" <?= ($userInfo['pays'] ?? '') === 'Suisse' ? 'selected' : '' ?>>Suisse</option>
                        <option value="Luxembourg" <?= ($userInfo['pays'] ?? '') === 'Luxembourg' ? 'selected' : '' ?>>Luxembourg</option>
                        <option value="Autre" <?= !empty($userInfo['pays']) && !in_array($userInfo['pays'], ['Canada', 'France', 'Belgique', 'Suisse', 'Luxembourg']) ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
            </div>
            <!-- Bouton de soumission -->
            <div class="col-span-1 md:col-span-2 flex justify-end mt-4">
                <button type="submit" name="updateProfile" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Modification Mot de Passe -->
<div id="modalModifierMotDePasse" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-11/12 md:w-1/2">
        <h2 class="text-2xl text-center text-blue-600 font-semibold mb-6">Modifier mes informations</h2>
        <form method="POST" action="/profile/update-password" class="space-y-4" id="passwordForm" onsubmit="return validatePasswordForm()">
            <input type="hidden" name="change_password" value="1">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="block font-semibold">Ancien Mot de Passe</label>
                <input type="password" name="ancien_mot_de_passe" 
                    class="w-full border border-gray-300 p-2 rounded-lg">
            </div>
            <div>
                <label class="block font-semibold">Nouveau Mot de Passe</label>
                <input type="password" name="nouveau_mot_de_passe" 
                    class="w-full border border-gray-300 p-2 rounded-lg">
            </div>
            <div>
                <label class="block font-semibold">Confirmer le Nouveau Mot de Passe</label>
                <input type="password" name="confirmation_mot_de_passe" 
                    class="w-full border border-gray-300 p-2 rounded-lg">
            </div>
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 mt-6">
               <button type="button" onclick="document.getElementById('modalModifierMotDePasse').classList.add('hidden');" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Enregistrer les modifications
                </button>
            </div>
            
            <div id="formErrors" class="text-red-500 text-sm mt-4 hidden">
                <p>Veuillez corriger les erreurs dans le formulaire avant de soumettre.</p>
            </div>
        </form>
    </div>
</div>


<!-- Modal pour les détails de la commande -->
<div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] overflow-y-auto">
        <div id="orderDetailsContent" class="p-4">
            <!-- Le contenu sera chargé dynamiquement ici -->
        </div>
    </div>
</div>

<!-- Modal pour le paiement -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-[90vh] overflow-y-auto">
        <div id="paymentModalContent" class="p-4">
            <!-- Le contenu sera chargé dynamiquement ici -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-blue-500 text-4xl mb-4"></i>
                <p class="text-gray-600">Chargement du formulaire de paiement...</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour ouvrir la modale des détails de commande
    function openOrderDetailsModal(orderId) {
        const modal = document.getElementById('orderDetailsModal');
        const content = document.getElementById('orderDetailsContent');
        
        modal.classList.remove('hidden');
        content.innerHTML = `<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-4xl"></i><p class="mt-2">Chargement...</p></div>`;

        fetch(`/api/commande/${orderId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const { commande, produits } = data;
                    let produitsHtml = produits.map(p => `
                        <tr class="border-b">
                            <td class="py-2 px-4">${p.nom}</td>
                            <td class="py-2 px-4 text-center">${p.quantite}</td>
                            <td class="py-2 px-4 text-right">${parseFloat(p.prix_unitaire).toFixed(2)} $</td>
                            <td class="py-2 px-4 text-right">${(p.quantite * p.prix_unitaire).toFixed(2)} $</td>
                        </tr>
                    `).join('');

                    content.innerHTML = `
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Détails Commande #${commande.id_commande}</h3>
                            <button onclick="closeOrderDetailsModal()" class="text-gray-500 hover:text-gray-800">&times;</button>
                        </div>
                        <p><strong>Date:</strong> ${new Date(commande.date_commande).toLocaleDateString('fr-FR')}</p>
                        <p><strong>Statut:</strong> ${commande.statut}</p>
                        <table class="min-w-full mt-4">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 text-left">Produit</th>
                                    <th class="py-2 px-4 text-center">Quantité</th>
                                    <th class="py-2 px-4 text-right">Prix Unit.</th>
                                    <th class="py-2 px-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>${produitsHtml}</tbody>
                            <tfoot>
                                <tr class="font-semibold bg-gray-50">
                                    <td colspan="3" class="py-2 px-4 text-right">Total Commande:</td>
                                    <td class="py-2 px-4 text-right">${parseFloat(commande.prix_total).toFixed(2)} $</td>
                                </tr>
                            </tfoot>
                        </table>
                    `;
                } else {
                    content.innerHTML = `<div class="text-red-500 p-4">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                content.innerHTML = `<div class="text-red-500 p-4">Une erreur est survenue.</div>`;
            });
    }

    function closeOrderDetailsModal() {
        document.getElementById('orderDetailsModal').classList.add('hidden');
    }

    // Gestion des modales existantes
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = button.getAttribute('data-modal-target');
            document.querySelector(modalId).classList.toggle('hidden');
        });
    });
    
    function openModal(orderId) {
        const modal = document.getElementById(`modalAnnulerCommande${orderId}`);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('block');
        }
    }
    
    function closeModal(orderId) {
        const modal = document.getElementById(`modalAnnulerCommande${orderId}`);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('block');
        }
    }
    
    
    // Fonction pour fermer la modale de paiement
    function closePaymentModal() {
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.classList.add('hidden');
            // Réinitialiser le contenu pour le prochain chargement
            document.getElementById('paymentModalContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-4xl mb-4"></i>
                    <p class="text-gray-600">Chargement du formulaire de paiement...</p>
                </div>`;
        }
    }
    
    // Fermer la modale en cliquant en dehors du contenu
    document.addEventListener('DOMContentLoaded', function() {
        
        // Fermer la modale avec la touche Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                 closePaymentModal();
            }
        });
        
        // Gestion du clic en dehors de la modale de paiement
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closePaymentModal();
                }
            });
        }
    });

    // Fonction pour ouvrir la modale de paiement
    function openPaymentModal(orderId) {
        // Afficher la modale
        document.getElementById('paymentModal').classList.remove('hidden');
        
        // Charger le formulaire de paiement via AJAX
        fetch(`/profile/paiement/${orderId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Mettre directement le contenu HTML dans la modale
            document.getElementById('paymentModalContent').innerHTML = html;
            
            // Initialiser le bouton PayPal s'il existe
            if (typeof paypal !== 'undefined' && paypal.Buttons) {
                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: document.querySelector('input[name="amount"]')?.value || '0.01'
                                }
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            // Redirection après un paiement réussi
                            window.location.href = `/profile/confirmation?id_commande=${orderId}`;
                        });
                    }
                }).render('#paypal-button-container');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement du formulaire de paiement:', error);
            document.getElementById('paymentModalContent').innerHTML = 
                '<div class="text-red-500 p-4">Une erreur est survenue lors du chargement du formulaire de paiement.</div>';
        });
    }
    

    // Validation du formulaire de profil
    function validateProfileForm() {
        let isValid = true;
        
        // Réinitialiser les erreurs
        document.querySelectorAll('[id$="_error"]').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
            el.classList.add('focus:border-blue-500');
        });
        
        // Validation des champs requis
        const requiredFields = [
            { id: 'nom_utilisateur', message: 'Le nom est requis' },
            { id: 'prenom_utilisateur', message: 'Le prénom est requis' },
            { id: 'email_utilisateur', message: 'L\'email est requis' },
            { id: 'telephone_utilisateur', message: 'Le téléphone est requis' }
        ];
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field.id);
            if (input && !input.value.trim()) {
                showError(field.id, field.message);
                isValid = false;
            }
        });
        
        // Validation de l'email
        const email = document.getElementById('email_utilisateur');
        if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            showError('email_utilisateur', 'Veuillez entrer un email valide');
            isValid = false;
        }
        
        // Validation du téléphone (format français)
        const telephone = document.getElementById('telephone_utilisateur');
        if (telephone && telephone.value && !/^[0-9]{10}$/.test(telephone.value)) {
            showError('telephone_utilisateur', 'Le numéro doit contenir 10 chiffres');
            isValid = false;
        }
        
        // Faire défiler jusqu'à la première erreur si nécessaire
        if (!isValid) {
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        return isValid;
    }
    
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId + '_error');
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            
            // Mise en évidence du champ en erreur
            inputElement.classList.add('border-red-500');
            inputElement.classList.remove('focus:border-blue-500');
            inputElement.classList.add('focus:border-red-500');
            
            // Supprimer la classe d'erreur lors de la saisie
            inputElement.addEventListener('input', function clearError() {
                this.classList.remove('border-red-500');
                this.classList.add('focus:border-blue-500');
                this.classList.remove('focus:border-red-500');
                const errorToRemove = document.getElementById(this.id + '_error');
                if (errorToRemove) errorToRemove.classList.add('hidden');
                this.removeEventListener('input', clearError);
            });
        }
    }
    
    // Gestion de la soumission du formulaire
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            if (!validateProfileForm()) {
                e.preventDefault();
            }
        });
    }
</script>