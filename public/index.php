<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Générer un jeton CSRF s'il n'existe pas déjà
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Déclaration des espaces de noms
use App\Modele\UserModel;
use App\Controlleur\UserControlleur;

// Configuration du mode débogage
define('DEBUG_MODE', true); // Mettre à false en production

// Configuration du rapport d'erreurs
error_reporting(E_ALL);

// Vérifier si c'est une action de modification de commande
if (isset($_POST['action']) && $_POST['action'] === 'modifier_commande' && isset($_POST['id_commande']) && isset($_POST['statut'])) {
    try {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id_utilisateur'])) {
            throw new Exception("Vous devez être connecté pour effectuer cette action");
        }

        // Inclure la configuration de la base de données
        require_once __DIR__ . '/../config/db.php';
        
        // Établir la connexion à la base de données
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données: " . $e->getMessage());
        }
        
        // Inclure les modèles nécessaires
        require_once __DIR__ . '/../src/Modele/CommandeModel.php';
        
        // Créer une instance du modèle de commande
        $commandeModel = new App\Modele\CommandeModel($pdo);
        
        // Récupérer la commande
        $commande = $commandeModel->getCommandeById($_POST['id_commande']);
        
        if (!$commande) {
            throw new Exception("Commande introuvable");
        }
        
        // Vérifier les permissions
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        if ($commande['id_utilisateur'] != $_SESSION['id_utilisateur'] && !$isAdmin) {
            throw new Exception("Vous n'êtes pas autorisé à modifier cette commande");
        }
        
        // Mettre à jour le statut
        $result = $commandeModel->updateStatus($_POST['id_commande'], $_POST['statut']);
        
        if ($result) {
            $_SESSION['success'] = "Le statut de la commande a été mis à jour avec succès";
            
            // Rediriger vers la page appropriée
            if ($isAdmin) {
                header('Location: /admin/commandes');
            } else {
                header('Location: /mon_profile');
            }
            exit();
        } else {
            throw new Exception("Erreur lors de la mise à jour du statut de la commande");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        $referer = $_SERVER['HTTP_REFERER'] ?? '/mon_profile';
        header("Location: $referer");
        exit();
    }
}

// Définir le fuseau horaire par défaut
date_default_timezone_set('America/Toronto');

// Inclure les dépendances
error_log("Chemin absolu de db.php: " . realpath(__DIR__ . '/../config/db.php'));
if (!file_exists(__DIR__ . '/../config/db.php')) {
    die("ERREUR: Le fichier de configuration de la base de données est introuvable.");
}
require_once __DIR__ . '/../config/db.php';

// Vérifier si la fonction getConnection existe
if (!function_exists('getConnection')) {
    die("ERREUR: La fonction getConnection n'est pas définie.");
}

require '../vendor/autoload.php';

// Vérifier si la connexion à la base de données fonctionne
try {
    error_log("Tentative de connexion à la base de données...");
    $pdo = getConnection();
    
    error_log("Type de pdo: " . gettype($pdo));
    if (is_object($pdo)) {
        error_log("Classe de pdo: " . get_class($pdo));
    }
    
    if (!($pdo instanceof PDO)) {
        error_log("ERREUR: getConnection() n'a pas retourné une instance de PDO");
        throw new Exception("La connexion à la base de données a échoué : objet PDO non valide");
    }
    
    // Tester la connexion
    error_log("Test de la connexion PDO...");
    $pdo->query('SELECT 1');
    error_log("Connexion PDO établie avec succès");
    
} catch (Exception $e) {
    error_log("ERREUR de connexion à la base de données: " . $e->getMessage());
    error_log("Fichier: " . $e->getFile() . ", Ligne: " . $e->getLine());
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

use AltoRouter\Router;
use App\Controlleur\HomeControlleur;
use App\Controlleur\ContactControlleur;
use App\Controlleur\CommandeControlleur;
use App\Controlleur\ProduitControlleur;
use App\Controlleur\CartControlleur;
use App\Controlleur\AuthControlleur;
use App\Controlleur\AdminControlleur;
use App\Controlleur\AdminProduitControlleur;
use App\Controlleur\ProfileControlleur;
use App\Controlleur\PromotionControlleur;
use App\Modele\ProduitModel;
use App\Modele\CategorieModel;
use App\Modele\CommandeModel;
use App\Modele\CartModel;
use App\Modele\PanierModele;


$pdo = getConnection();
// Création des instances des modèles
$commandeModel = new CommandeModel($pdo);
$commandeController = new CommandeControlleur($commandeModel);
$produitModel = new ProduitModel($pdo);
$categorieModel = new CategorieModel($pdo);
$produitControlleur = new ProduitControlleur($produitModel, $categorieModel);
$cartModel = new CartModel($pdo);
$cartController = new CartControlleur($cartModel);

// Routeur AltoRouter
$router = new AltoRouter();

// Routes principales
$router->map('GET', '/', 'HomeControlleur::index', 'accueil');
$router->map('GET', '/contact', 'ContactControlleur::index', 'contacter');
// Routes pour les produits
$router->map('GET', '/produits/[i:id]', 'ProduitControlleur::show', 'produit_detail');
$router->map('GET', '/produits', 'ProduitControlleur::index', 'produits');

$router->map('GET', '/utilisateurs', 'UserControlleur::index', 'utilisateurs');

$router->map('GET', '/produits/ajout', 'ProduitControlleur::afficheForm', 'ajout');
$router->map('POST', '/produits/ajouterProduit', 'ProduitControlleur::ajouterProduit', 'ajouterProduit');
//
$router->map('GET', '/produits/modifierProduit=[i:id]', 'ProduitControlleur::recupererProduit', 'modifier');
$router->map('POST', '/produits/editerProduit/[i:id_produit]', 'ProduitControlleur::updateProduits', 'editer');

$router->map('GET', '/produits/supprimer=[i:id]', 'ProduitControlleur::supprimerProduit', 'supprimer');
// Routage pour l'action d'ajout de produit au panier
$router->map('POST', '/produits/panier', 'HomeControlleur::ajouterProduit', 'ajouterProduitPanier');
$router->map('POST', '/produits/supprimer/[i:id]', function($id) {
    $_POST['id_produit'] = $id;
    $_POST['action'] = 'supprimer';
    (new HomeControlleur())->gererPanier();
}, 'supprimerProduitPanier');
$router->map('POST', '/produits/supprimer', function() {
    (new HomeControlleur())->gererPanier();
}, 'viderPanier');

// Routes pour les commandes
$router->map('GET', '/commandes', 'CommandeControlleur::index', 'commandes');
$router->map('GET', '/commande/[i:id]', 'CommandeControlleur::afficherCommande', 'commande_detail');
$router->map('POST', '/commande', 'CommandeControlleur::ajouterCommande', 'creer_commande');
$router->map('POST', '/commande/ajouter', 'CommandeControlleur::ajouterCommande', 'ajouter_commande');
$router->map('POST', '/commande/[i:id_commande]/modifier/[a:action]', 'CommandeControlleur::modifierCommande', 'modifier_commande');
$router->map('GET', '/admin/commandes/utilisateur/[i:user_id]', 'CommandeControlleur::adminCommandesUtilisateur', 'admin_commandes_utilisateur');
$router->map('GET', '/utilisateur/[i:user_id]/commandes', 'CommandeControlleur::utilisateurCommandes', 'utilisateur_commandes');

// Routes pour le panier
$router->map('POST', '/cart/ajouter', 'CartControlleur::ajouter');
$router->map('GET', '/cart', 'CartControlleur::afficher');
$router->map('POST', '/cart/vider', 'CartControlleur::vider');
$router->map('POST', '/panier/mettre-a-jour', 'CartControlleur::mettreAJourQuantite', 'panier_mettre_a_jour');

// Routes d'authentification
$router->map('GET|POST', '/login', 'AuthControlleur::loginForm', 'connexion');
$router->map('POST', '/login', 'AuthControlleur::login', 'traitement_connexion');
$router->map('GET|POST', '/register', 'AuthControlleur::register', 'inscription');

$router->map('GET', '/logout', 'AuthControlleur::logout', 'deconnexion');

// Routes pour l'administration
$router->map('GET', '/admin', 'AdminControlleur::dashboard', 'admin_dashboard');
$router->map('POST', '/admin/produits/add', 'AdminProduitControlleur::add', 'admin_ajouter_produit');
$router->map('POST', '/admin/produits/delete/[i:id]', 'AdminProduitControlleur::delete', 'admin_supprimer_produit');

// Routes pour le profil utilisateur
$router->map('GET', '/mon_profile', 'ProfileControlleur::index', 'profile');
$router->map('GET', '/profile', 'ProfileControlleur::index', 'profile2');

// Routes pour la gestion des commandes admin
$router->map('GET', '/admin/commandes', 'CommandeControlleur::index', 'admin_commandes');
$router->map('POST', '/commande/annuler/[i:id]', 'CommandeControlleur::annulerCommande', 'annuler_commande');
$router->map('GET', '/profile/edit', 'ProfileControlleur::editProfile', 'edit_profile');
$router->map('POST', '/profile/edit', 'ProfileControlleur::updateProfile', 'update_profile');
// Initialisation du contrôleur de profil
require_once __DIR__ . '/../src/Controlleur/ProfileControlleur.php';
$profileController = new App\Controlleur\ProfileControlleur($pdo);

// Route pour le paiement (modal ou page complète)
$router->map('GET, POST', '/profile/paiement/[i:id_commande]', function($id_commande) use ($pdo) {
    // Initialiser le contrôleur avec la connexion PDO
    $profileController = new App\Controlleur\ProfileControlleur($pdo);
    
    // Vérifier si c'est une requête AJAX (modal)
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Appeler la méthode payOrder avec le paramètre modal
    $profileController->payOrder($id_commande, $isAjax);
}, 'paiement');
$router->map('GET', '/profile/annuler/[i:id_commande]', 'ProfileControlleur::changeOrderStatus', 'annuler');
$router->map('POST', '/profile/update-password', 'ProfileControlleur::updatePassword', 'update_password');
//utilisateurs

// User routes - French and English versions
$router->map('GET', '/users', 'UserControlleur::index', 'users');

// Route pour changer le statut d'un utilisateur (actif/inactif)
$router->map('POST', '/api/user/[i:userId]/status/[:status]', function($userId, $status) use ($pdo) {
    try {
        error_log("Tentative de changement de statut pour l'utilisateur $userId vers $status");
        $userModel = new UserModel($pdo);
        $controller = new UserControlleur($userModel);
        $result = $controller->toggleUserStatus($userId, $status);
        error_log("Résultat du changement de statut: " . $result);
        echo $result;
    } catch (Exception $e) {
        error_log("Erreur lors du changement de statut: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur lors du changement de statut: ' . $e->getMessage()
        ]);
    }
    exit;
}, 'api_user_toggle_status');

// Single user routes
$router->map('GET', '/utilisateur/[i:id]', 'ProfileControlleur::showUser', 'show_user');
$router->map('GET', '/utilisateur/[i:id]/commandes', 'ProfileControlleur::userOrders', 'user_orders');

// User edit routes
$router->map('POST', '/user/edit/[i:id]', 'ProfileControlleur::updateUser', 'update_user');
$router->map('GET', '/api/commande/[i:id_commande]', 'ProfileControlleur::getOrderDetailsJson', 'api_commande_details');
$router->map('GET', '/utilisateur/[i:id]/modifier', 'ProfileControlleur::editUserForm', 'edit_user_form');

// API Routes
$router->map('GET', '/api/utilisateur/[i:id]/commandes', function($id) use ($pdo) {
    // Désactiver l'affichage des erreurs pour éviter toute sortie non désirée
    ini_set('display_errors', 0);
    error_reporting(0);
    
    // Fonction pour envoyer une réponse JSON
    $sendJsonResponse = function($data, $statusCode = 200, $error = null) {
        $response = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'data' => $data
        ];
        
        if ($error !== null) {
            $response['error'] = $error;
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // S'assurer que les données sont encodées en UTF-8
        if (is_array($response['data'])) {
            array_walk_recursive($response['data'], function(&$item) {
                if (is_string($item)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            });
        }
        
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        // Vérifier les erreurs d'encodage JSON
        if ($json === false) {
            $json = json_encode([
                'success' => false,
                'error' => 'Erreur lors de l\'encodage JSON: ' . json_last_error_msg()
            ]);
            http_response_code(500);
        }
        
        echo $json;
        exit;
    };
    
    // Vérifier que l'ID est valide
    if (!is_numeric($id) || $id <= 0) {
        $sendJsonResponse([], 400, 'ID utilisateur invalide');
    }
    
    try {
        // Vérifier la connexion à la base de données
        if (!($pdo instanceof PDO)) {
            throw new Exception("La connexion à la base de données a échoué");
        }
        
        // Initialiser le modèle de commande
        $commandeModel = new App\Modele\CommandeModel($pdo);
        
        // Récupérer les commandes
        $commandes = $commandeModel->getCommandesByUser($id);
        
        // Formater les données de réponse
        $formattedCommandes = [];
        foreach ($commandes as $commande) {
            $formattedCommandes[] = [
                'id_commande' => (int)$commande['id_commande'],
                'id_utilisateur' => (int)$commande['id_utilisateur'],
                'date_commande' => $commande['date_commande'],
                'prix_total' => (float)$commande['prix_total'],
                'statut' => $commande['statut']
            ];
        }
        
        // Envoyer la réponse
        $sendJsonResponse($formattedCommandes);
        
    } catch (PDOException $e) {
        error_log("Erreur PDO dans l'API commandes: " . $e->getMessage());
        $sendJsonResponse([], 500, 'Erreur de base de données');
    } catch (Exception $e) {
        error_log("Erreur dans l'API commandes: " . $e->getMessage());
        $sendJsonResponse([], 500, 'Erreur interne du serveur');
    }
    
    // Si on arrive ici, c'est qu'aucune réponse n'a été envoyée
    $sendJsonResponse([], 500, 'Erreur inattendue');
});



// Routes pour les promotions
$router->map('GET', '/admin/promotions', 'PromotionControlleur::index', 'admin_promotions');
$router->map('GET', '/admin/promotion/[i:id]', 'PromotionControlleur::show', 'admin_promotion_detail');
$router->map('POST', '/admin/promotion/add', 'PromotionControlleur::add', 'admin_ajouter_promotion');
$router->map('GET', '/admin/promotion/add', 'PromotionControlleur::addForm', 'admin_form_ajouter_promotion');
$router->map('POST', '/admin/promotion/delete/[i:id]', 'PromotionControlleur::delete', 'admin_supprimer_promotion');
$router->map('POST', '/admin/promotion/edit/[i:id]', 'PromotionControlleur::edit', 'admin_editer_promotion');

// Vérification des routes


// Active l'affichage des erreurs en mode développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration des routes

// Utilisation de la connexion PDO déjà initialisée

// Vérification des routes
$match = $router->match();

// Vérifier si une route correspond
// Rendre le routeur disponible globalement avant d'inclure le header
$GLOBALS['router'] = $router;

try {
    if ($match) {
        // Par défaut, on affiche le header et le footer
        $noHeaderFooter = false;
        
        // Détecter si c'est une route API
        $isApiRoute = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;

        // Inclure le header avant d'exécuter le contrôleur, sauf pour les routes API
        if (!$isApiRoute && !(isset($GLOBALS['noHeaderFooter']) && $GLOBALS['noHeaderFooter'] === true)) {
            require '../static/header.php';
        }
        
        if (is_callable($match['target'])) {
            ob_start();
            $result = call_user_func_array($match['target'], $match['params']);
            $output = ob_get_clean();
            
            // Si c'est une requête AJAX ou si $noHeaderFooter est vrai, on n'affiche que le contenu
            if ((isset($GLOBALS['noHeaderFooter']) && $GLOBALS['noHeaderFooter'] === true) || 
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                echo $output;
                exit();
            }
            
            // Sinon, on affiche le contenu normalement (le header est déjà inclus)
            echo $output;
        } else {
            list($controlleur, $method) = explode('::', $match['target']);
            $controlleurClass = "../src/controlleur/{$controlleur}.php";
            if (file_exists($controlleurClass)) {
                require_once $controlleurClass;
                $controlleur = "App\\Controlleur\\" . $controlleur;
                if (class_exists($controlleur)) {
                    // Gestion des dépendances des contrôleurs
                    switch ($controlleur) {
                        case "App\\Controlleur\\ProduitControlleur":
                            $produitModel = new ProduitModel($pdo);
                            $categorieModel = new CategorieModel($pdo);
                            $controlleurInstance = new $controlleur($produitModel, $categorieModel);
                            break;
                    
                        case "App\\Controlleur\\CommandeControlleur":
                            $commandeModel = new CommandeModel($pdo);
                            $controlleurInstance = new $controlleur($commandeModel);
                            break;
                    
                        case "App\\Controlleur\\CartControlleur":
                            $cartModel = new CartModel($pdo);
                            $controlleurInstance = new $controlleur($cartModel);
                            break;
                    
                        case "App\\Controlleur\\UserControlleur": 
                            error_log("=== Création du UserControlleur ===");
                            // Vérifier que la connexion PDO est valide
                            if (!($pdo instanceof PDO)) {
                                $errorMsg = "La connexion PDO n'est pas valide lors de la création du UserModel";
                                error_log($errorMsg);
                                error_log("Type de pdo: " . gettype($pdo));
                                if (is_object($pdo)) {
                                    error_log("Classe de pdo: " . get_class($pdo));
                                }
                                throw new Exception($errorMsg);
                            }
                            error_log("Création du UserModel...");
                            $userModel = new UserModel($pdo);
                            if (!isset($userModel) || !is_object($userModel)) {
                                $errorMsg = "Échec de l'instanciation du UserModel";
                                error_log($errorMsg);
                                throw new Exception($errorMsg);
                            }
                            error_log("UserModel créé avec succès, création du UserControlleur...");
                            $controlleurInstance = new $controlleur($userModel);
                            error_log("UserControlleur créé avec succès");
                            break;
                    
                        case "App\\Controlleur\\AuthControlleur":
                            // Vérifier que la connexion PDO est valide
                            if (!($pdo instanceof PDO)) {
                                throw new Exception("La connexion PDO n'est pas valide lors de la création du UserModel pour AuthControlleur");
                            }
                            $userModel = new UserModel($pdo);
                            if (!isset($userModel) || !is_object($userModel)) {
                                throw new Exception("Échec de l'instanciation du UserModel pour AuthControlleur");
                            }
                            $controlleurInstance = new $controlleur($userModel);
                            break;
                    
                        case "App\\Controlleur\\ProfileControlleur":
                            $controlleurInstance = new $controlleur($pdo);
                            break;
                            
                        default:
                            $controlleurInstance = new $controlleur();
                            break;
                    }
                    
                    if (method_exists($controlleurInstance, $method)) {
                        // Gestion des paramètres pour POST
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $match['params'] = array_merge($match['params'], [$_POST]);
                        }
                        
                        // Gestion des paramètres GET
                        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                            $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
                            if ($queryString) {
                                parse_str($queryString, $queryParams);
                                $match['params'] = array_merge($match['params'], $queryParams);
                            }
                        }
                        
                        // Validation et appel de la méthode
                        $reflection = new ReflectionMethod($controlleurInstance, $method);
                        $parameters = $reflection->getParameters();

                        if (count($parameters) > count($match['params'])) {
                            handleError("Nombre de paramètres insuffisants pour la méthode : $method", 400);
                        }
                        // Filtrer les arguments nommés
                        $filteredParams = array_values($match['params']); // Récupère uniquement les arguments positionnels
                        // Appel de la méthode
                        call_user_func_array([$controlleurInstance, $method], $filteredParams);
                    } else {
                        throw new Exception("Méthode non trouvée : $method dans le contrôleur $controlleur");
                    }
                } else {
                    throw new Exception("Classe non trouvée : $controlleur");
                }
            } else {
                throw new Exception("Fichier du contrôleur introuvable : $controlleurClass");
            }
        }
        
        // Inclure le footer sauf si c'est une route API ou si le flag noHeaderFooter est positionné
        if (!$isApiRoute && !(isset($GLOBALS['noHeaderFooter']) && $GLOBALS['noHeaderFooter'] === true)) {
            require '../static/footer.php';
        }
    } else {
        throw new Exception("Aucune route correspondante trouvée pour " . $_SERVER['REQUEST_URI']);
    }
} catch (Exception $e) {
    handleError($e->getMessage(), 500, $e->getFile(), $e->getLine(), $e->getTraceAsString());
}

/**
 * Gère les erreurs de l'application
 * 
 * @param string $errstr Message d'erreur
 * @param int $errno Code d'erreur HTTP
 * @param string $errfile Fichier où l'erreur s'est produite
 * @param int $errline Ligne où l'erreur s'est produite
 * @param string $trace Stack trace de l'erreur
 */
function handleError($errstr, $errno = 500, $errfile = '', $errline = 0, $trace = '') {
    // Générer un identifiant unique pour l'erreur
    $errorId = uniqid('error_', true);
    
    // Format du message d'erreur détaillé
    $errorDetails = [
        'id' => $errorId,
        'code' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errfile ? $errline : null,
        'timestamp' => date('Y-m-d H:i:s'),
        'trace' => $trace
    ];
    
    // Enregistrer l'erreur dans le log avec tous les détails
    $logMessage = sprintf(
        "[%s] Erreur [%s]: %s dans %s à la ligne %d | ID: %s",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline,
        $errorId
    );
    
    error_log($logMessage);
    
    // Si le mode debug est activé, enregistrer la stack trace
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        error_log("Stack trace: " . $trace);
    }
    
    // Définir l'en-tête HTTP approprié
    http_response_code($errno);
    
    // Si c'est une requête AJAX, renvoyer une réponse JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'id' => $errorId,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
                'debug' => (defined('DEBUG_MODE') && DEBUG_MODE === true) ? $errorDetails : null
            ]
        ]);
        exit;
    }
    
    // Sinon, afficher une page d'erreur
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    
    // Afficher une page d'erreur plus détaillée en mode debug
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Erreur ' . htmlspecialchars($errno) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
                .container { max-width: 800px; margin: 0 auto; }
                h1 { color: #d32f2f; }
                pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; }
                .error-id { font-size: 0.9em; color: #666; margin-bottom: 20px; }
                .trace { margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Erreur ' . htmlspecialchars($errno) . '</h1>
                <div class="error-id">ID d\'erreur: ' . htmlspecialchars($errorId) . '</div>
                <p><strong>' . nl2br(htmlspecialchars($errstr)) . '</strong></p>
                ' . ($errfile ? '<p>Fichier: ' . htmlspecialchars($errfile) . ' (ligne ' . $errline . ')</p>' : '') . '
                ' . ($trace ? '
                <div class="trace">
                    <h3>Stack trace:</h3>
                    <pre>' . htmlspecialchars($trace) . '</pre>
                </div>
                ' : '') . '
            </div>
        </body>
        </html>';
    } else {
        // En production, afficher un message générique
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Erreur ' . htmlspecialchars($errno) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 40px 20px; text-align: center; }
                .container { max-width: 600px; margin: 0 auto; }
                h1 { color: #d32f2f; }
                .error-id { font-size: 0.9em; color: #666; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Oups ! Une erreur est survenue</h1>
                <p>Désolé, une erreur est survenue lors du traitement de votre demande.</p>
                <p>Notre équipe technique a été notifiée et travaille à résoudre le problème.</p>
                <div class="error-id">ID d\'erreur: ' . htmlspecialchars($errorId) . '</div>
                <p><a href="/">Retour à la page d\'accueil</a></p>
            </div>
        </body>
        </html>';
    }
    
    // Arrêter l'exécution du script
    exit(1);
}
