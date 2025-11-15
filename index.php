<?php
// Définir le chemin racine de l'application
define('APP_ROOT', __DIR__);

// Configuration du mode débogage
define('DEBUG_MODE', true); // Mettre à false en production

// Configuration du rapport d'erreurs
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
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
use App\Modele\UserModel;
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
    (new HomeControlleur())->gererPanier($id);
}, 'supprimerProduitPanier');
$router->map('POST', '/produits/supprimer', function() {
    (new HomeControlleur())->gererPanier();
}, 'viderPanier');

// Routes pour les commandes
$router->map('GET', '/commandes', 'CommandeControlleur::index', 'commandes');
$router->map('POST', '/commande', 'CommandeControlleur::ajouterCommande', 'commande');
$router->map('GET|POST', '/commande/editer/id_commande=[i:id_commande]/action=[a:action]', 'CommandeControlleur::modifierCommande', 'editerCommande');


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
$router->map('GET', '/profile/edit', 'ProfileControlleur::editProfile', 'edit_profile');
$router->map('POST', '/profile/edit', 'ProfileControlleur::updateProfile', 'update_profile');
///
$router->map('GET, POST', '/profile/paiement/[i:id_commande]', 'ProfileControlleur::payOrder', 'paiement');
$router->map('GET', '/profile/details/[i:id_commande]', 'ProfileControlleur::getOrderDetails', 'details');
$router->map('GET', '/profile/annuler/[i:id_commande]', 'ProfileControlleur::changeOrderStatus', 'annuler');
//utilisateurs

$router->map('GET', '/users', 'ProfileControlleur::indexUsers', 'users');
$router->map('POST', '/user/edit/[i:id]', 'ProfileControlleur::updateUser', 'update_user');



// Routes pour la gestion des utilisateurs
$router->map('GET', '/utilisateur/view/[i:id]', 'UserControlleur::view', 'view_user');
$router->map('GET', '/utilisateur/voir/[i:id]', 'UserControlleur::viewUser', 'voir_utilisateur');
$router->map('GET', '/utilisateur/modifier/[i:id]', 'UserControlleur::editUser', 'modifier_utilisateur');
$router->map('POST', '/utilisateur/update/[i:id]', 'UserControlleur::updateUser', 'mettre_a_jour_utilisateur');
$router->map('POST', '/utilisateur/supprimer/[i:id]', 'UserControlleur::deleteUser', 'supprimer_utilisateur');
$router->map('POST', '/utilisateur/delete/[i:id]', 'UserControlleur::deleteUser', 'delete_user');
$router->map('POST', '/utilisateur/toggle-status/[i:id]', 'UserControlleur::toggleStatus', 'toggle_status_utilisateur');

// Routes pour la gestion des informations utilisateur
$router->map('GET', '/api/user/[i:id]', 'UserControlleur::getUserInfo', 'get_user_info');
$router->map('PUT', '/api/user/[i:id]', 'UserControlleur::updateUserInfo', 'update_user_info');

// Routes pour les promotions
$router->map('GET', '/promotions', 'PromotionControlleur::index', 'promotions');
$router->map('GET', '/promotion/[i:id]', 'PromotionControlleur::show', 'promotion_detail');
$router->map('POST', '/promotion/add', 'PromotionControlleur::add', 'admin_ajouter_promotion');
$router->map('GET', '/promotion/add', 'PromotionControlleur::addForm', 'admin_form_ajouter_promotion');
$router->map('POST', '/promotion/delete/[i:id]', 'PromotionControlleur::delete', 'admin_supprimer_promotion');
$router->map('POST', '/promotion/edit/[i:id]', 'PromotionControlleur::edit', 'admin_editer_promotion');

// Vérification des routes


// Active l'affichage des erreurs en mode développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration des routes

// Utilisation de la connexion PDO déjà initialisée

// Vérification des routes
$match = $router->match();

// Vérifier si une route correspond
try {
    if ($match) {
        require 'static/header.php';
        if (is_callable($match['target'])) {
            call_user_func_array($match['target'], $match['params']);
        } else {
            list($controlleur, $method) = explode('::', $match['target']);
            $controlleurClass = __DIR__ . "/src/controlleur/{$controlleur}.php";
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
                            // Vérifier que la connexion PDO est valide
                            if (!($pdo instanceof PDO)) {
                                throw new Exception("La connexion PDO n'est pas valide lors de la création du UserModel");
                            }
                            $userModel = new UserModel($pdo);
                            if (!isset($userModel) || !is_object($userModel)) {
                                throw new Exception("Échec de l'instanciation du UserModel");
                            }
                            $controlleurInstance = new $controlleur($userModel);
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

                        // Log des paramètres reçus
                        error_log("Méthode appelée: " . get_class($controlleurInstance) . "::$method");
                        error_log("Paramètres reçus: " . print_r($match['params'], true));
                        error_log("Paramètres attendus: " . count($parameters));

                        if (count($parameters) > count($match['params'])) {
                            handleError("Nombre de paramètres insuffisants pour la méthode : $method", 400);
                        }
                        // Filtrer les arguments nommés
                        $filteredParams = array_values($match['params']); // Récupère uniquement les arguments positionnels
                        
                        // Log des paramètres filtrés
                        error_log("Paramètres filtrés: " . print_r($filteredParams, true));
                        
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
        require 'static/footer.php';
    } else {
        // Rediriger vers la page de connexion si aucune route n'est trouvée
        header('Location: /login');
        exit;
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
