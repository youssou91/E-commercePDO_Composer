<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Afficher le contenu de la session
error_log('Session data: ' . print_r($_SESSION, true));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One-Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <!-- Logo -->
            <a href="<?= isset($router) ? $router->generate('accueil') : '#'; ?>" class="text-2xl font-bold text-blue-600">ONE-SHOP</a>
            
            <!-- Toggle Menu (for mobile) -->
            <button id="menu-toggle" class="md:hidden focus:outline-none">
                <div class="w-6 h-1 bg-gray-700 mb-1"></div>
                <div class="w-6 h-1 bg-gray-700 mb-1"></div>
                <div class="w-6 h-1 bg-gray-700"></div>
            </button>

            <!-- Navigation -->
            <nav id="nav-menu" class="hidden md:flex space-x-6">
                <?php 
                // Récupérer le routeur depuis les variables globales
                $router = $GLOBALS['router'] ?? null;
                ?>
                <a href="<?= $router ? $router->generate('accueil') : '/' ?>" class="text-gray-700 hover:text-blue-600">Accueil</a>
                <a href="<?= $router ? $router->generate('contacter') : '/contact' ?>" class="text-gray-700 hover:text-blue-600">Contact</a>

                <!-- Menu Admin -->
                <?php 
                // Debug: Afficher le rôle de l'utilisateur
                error_log('Rôle dans la session: ' . ($_SESSION['role'] ?? 'non défini'));
                ?>
                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                    <a href="<?= $router ? $router->generate('produits') : '/produits' ?>" class="text-gray-700 hover:text-blue-600">Produits</a>
                    <a href="<?= $router ? $router->generate('utilisateurs') : '/utilisateurs' ?>" class="text-gray-700 hover:text-blue-600">Utilisateurs</a>
                    <a href="<?= $router ? $router->generate('admin_commandes') : '/admin/commandes' ?>" class="text-gray-700 hover:text-blue-600">Commandes</a>
                    <a href="<?= $router ? $router->generate('admin_promotions') : '/admin/promotions' ?>" class="text-gray-700 hover:text-blue-600">Promotions</a>
                <?php endif; ?>
            </nav>

            <!-- User Links -->
            <div class="hidden md:flex space-x-4">
                <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <a href="<?= $router ? $router->generate('profile') : '/profile' ?>" class="text-gray-700 hover:text-blue-600 font-bold">
                        <?= htmlspecialchars(($_SESSION['prenom'] ?? 'Mon Profil') . (isset($_SESSION['role']) ? ' (' . htmlspecialchars($_SESSION['role']) . ')' : '')) ?>
                    </a>
                    <a href="<?= $router ? $router->generate('deconnexion') : '/logout' ?>" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= $router ? $router->generate('connexion') : '/login' ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Connexion</a>
                    <a href="<?= $router ? $router->generate('inscription') : '/register' ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', () => {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
