<?php
// Démarrer la session pour accéder à $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure l'autoloader de Composer pour la gestion automatique des classes
use App\Modele\UserModel;

// Inclure le fichier de connexion ou utiliser votre méthode pour obtenir la connexion PDO
require_once __DIR__ . '/../../../config/db.php';

// Initialisation des variables
$errorMessage = '';
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']); // Nettoyer le message de succès après l'avoir récupéré

try {
    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-connexion'])) {
        // Récupération des données du formulaire
        $email = filter_input(INPUT_POST, 'couriel', FILTER_SANITIZE_EMAIL);
        $password = $_POST['mot_de_pass'] ?? '';
        
        // Validation des entrées
        if (empty($email) || empty($password)) {
            throw new Exception('Veuillez remplir tous les champs');
        }
        
        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format d\'email invalide');
        }
        
        // Connexion à la base de données
        $db = getConnection();
        $userModel = new UserModel($db);
        
        // Tentative de connexion
        $user = $userModel->checkUser($email, $password);
        
        // Si on arrive ici, la connexion a réussi
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['loggedin'] = true;
        
        // Redirection après connexion réussie
        header('Location: /');
        exit();
    }
} catch (Exception $e) {
    // Gestion des erreurs
    $errorMessage = $e->getMessage();
    
    // Journalisation de l'erreur pour le débogage
    error_log('Erreur de connexion: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 h-screen flex items-center justify-center">
    <div class="container mx-auto max-w-md p-8 bg-white rounded-lg shadow-lg my-8">
        <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">Page de Connexion</h1>

        <!-- Affichage du message d'erreur s'il y en a un -->
        <?php if (!empty($errorMessage)): ?>
            <div class="mb-4 p-4 text-red-700 bg-red-100 rounded">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Adresse email</label>
                <input type="email" name="couriel" id="email" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       value="<?= isset($_POST['couriel']) ? htmlspecialchars($_POST['couriel'], ENT_QUOTES, 'UTF-8') : '' ?>" 
                       required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-2">Mot de passe</label>
                <input type="password" name="mot_de_pass" id="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required>
            </div>
            <div class="mb-4 text-right">
                <a href="/register" class="text-blue-600 hover:underline">S'inscrire</a>
            </div>
            <button type="submit" name="btn-connexion" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Se connecter
            </button>
        </form>
    </div>
</body>
</html>
