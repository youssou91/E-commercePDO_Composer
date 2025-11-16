<?php
namespace App\Controlleur;

use App\Modele\UserModel;
use Exception;
use PDOException;

require_once __DIR__ . '/../../config/db.php';

class ProfileControlleur {
    private $db;
    public function __construct($db = null) {
        $this->db = $db ?: getConnection();
        
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Récupère les informations d'un utilisateur par son ID
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Les informations de l'utilisateur avec des valeurs par défaut pour les champs manquants
     */
    public function getUserInfo($userId) {
        $userModel = new UserModel($this->db);
        // Utilisation de la méthode getUserInfo du UserModel qui retourne déjà un tableau
        $userInfo = $userModel->getUserInfo($userId);
        
        // Définition des valeurs par défaut
        $defaultValues = [
            'id_utilisateur' => $userId,
            'nom_utilisateur' => 'Non renseigné',
            'prenom' => 'Non renseigné',
            'courriel' => 'Non renseigné',
            'telephone' => 'Non renseigné',
            'numero' => '',
            'rue' => 'Non renseignée',
            'code_postal' => 'Non renseigné',
            'ville' => 'Non renseignée',
            'province' => 'Non renseignée',
            'pays' => 'Non renseigné',
            'couriel' => 'Non renseigné' // Alias pour courriel pour la rétrocompatibilité
        ];
        
        // Si on a des informations utilisateur, on les fusionne avec les valeurs par défaut
        if (is_array($userInfo)) {
            // S'assurer que courriel est défini même si c'est vide
            if (isset($userInfo['courriel'])) {
                $userInfo['couriel'] = $userInfo['courriel'];
            }
            return array_merge($defaultValues, $userInfo);
        }
        
        // Retourner les valeurs par défaut si l'utilisateur n'est pas trouvé
        return $defaultValues;
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['id_utilisateur'])) {
            header('Location: /login');
            exit();
        }
        
        require_once __DIR__ . '/../Vue/Profile.php';
    }
    
    /**
     * Récupère les commandes d'un utilisateur avec pagination et filtrage
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $page Numéro de page (par défaut: 1)
     * @param int $perPage Nombre d'éléments par page (par défaut: 5)
     * @param string|null $statut Filtre par statut (optionnel)
     * @return array Tableau contenant les commandes et les informations de pagination
     */
    public function getUserOrders($userId, $page = 1, $perPage = 5, $statut = null) {
        $commandeModel = new \App\Modele\CommandeModel($this->db);
        
        // Récupérer le nombre total de commandes (pour la pagination)
        $totalOrders = $commandeModel->countUserOrders($userId, $statut);
        $totalPages = ceil($totalOrders / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Récupérer les commandes avec pagination et filtrage
        $orders = $commandeModel->getCommandesByUser($userId, $offset, $perPage, $statut);
        
        return [
            'commandes' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalOrders,
                'per_page' => $perPage
            ],
            'filters' => [
                'statut' => $statut
            ]
        ];
    }
    
    /**
     * Met à jour les informations du profil utilisateur
     * 
     * @param array $data Données du formulaire
     * @return void
     */
    /**
     * Met à jour le mot de passe de l'utilisateur
     * 
     * @param array $data Données du formulaire
     * @return void
     */
    public function updatePassword($data) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isset($_SESSION['id_utilisateur'])) {
                throw new Exception("Vous devez être connecté pour modifier votre mot de passe.");
            }
            
            // Vérifier le jeton CSRF
            if (!isset($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new Exception("Jeton de sécurité invalide.");
            }
            
            // Récupérer et valider les données
            $userId = (int)$_SESSION['id_utilisateur'];
            $ancienMotDePasse = $data['ancien_mot_de_passe'] ?? '';
            $nouveauMotDePasse = $data['nouveau_mot_de_passe'] ?? '';
            $confirmationMotDePasse = $data['confirmation_mot_de_passe'] ?? '';
            
            // Validation des champs
            if (empty($ancienMotDePasse) || empty($nouveauMotDePasse) || empty($confirmationMotDePasse)) {
                throw new Exception("Tous les champs sont obligatoires.");
            }
            
            if ($nouveauMotDePasse !== $confirmationMotDePasse) {
                throw new Exception("Les nouveaux mots de passe ne correspondent pas.");
            }
            
            if (strlen($nouveauMotDePasse) < 8) {
                throw new Exception("Le nouveau mot de passe doit contenir au moins 8 caractères.");
            }
            
            // Vérifier l'ancien mot de passe
            $userModel = new UserModel($this->db);
            $user = $userModel->getUserById($userId);
            
            if (!$user) {
                throw new Exception("Utilisateur non trouvé.");
            }
            
            $storedPassword = $user->getPassword();
            
            // Vérifier si le mot de passe stocké est haché
            if (password_verify($ancienMotDePasse, $storedPassword)) {
                // Le mot de passe est correct (format haché)
            } else if ($ancienMotDePasse === $storedPassword) {
                // Pour la rétrocompatibilité avec les mots de passe non hachés
                // Note: À terme, il faudrait migrer ces mots de passe vers un hachage sécurisé
            } else {
                throw new Exception("L'ancien mot de passe est incorrect.");
            }
            
            // Mettre à jour le mot de passe
            $result = $userModel->updatePassword($userId, $nouveauMotDePasse);
            
            if ($result) {
                $_SESSION['success'] = "Votre mot de passe a été mis à jour avec succès.";
            } else {
                throw new Exception("Une erreur est survenue lors de la mise à jour du mot de passe.");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            error_log("Erreur lors de la mise à jour du mot de passe: " . $e->getMessage());
        }
        
        // Redirection
        if (!headers_sent()) {
            header('Location: /mon_profile');
            exit();
        } else {
            echo '<script>window.location.href = "/mon_profile";</script>';
            exit();
        }
    }
    
    /**
     * Met à jour le profil utilisateur
     * 
     * @param array $data Données du formulaire
     * @return void
     */
    public function updateProfile($data) {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialiser le tableau de réponse
        $response = [
            'success' => false,
            'message' => '',
            'redirect' => ''
        ];
        
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isset($_SESSION['id_utilisateur'])) {
                throw new Exception("Utilisateur non connecté");
            }
            
            // Vérifier le jeton CSRF
            if (!isset($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new Exception("Jeton de sécurité invalide");
            }
            
            // Fonction de nettoyage des chaînes (remplacement de FILTER_SANITIZE_STRING)
            $cleanString = function($value) {
                if (!is_string($value)) {
                    return '';
                }
                return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            };
            
            // Nettoyer et valider les données
            $userId = (int)$_SESSION['id_utilisateur'];
            $nom = $cleanString($data['nom_utilisateur'] ?? '');
            $prenom = $cleanString($data['prenom_utilisateur'] ?? '');
            $email = filter_var($data['email_utilisateur'] ?? '', FILTER_SANITIZE_EMAIL);
            $telephone = $cleanString($data['telephone_utilisateur'] ?? '');
            $adresse = $cleanString($data['adresse_utilisateur'] ?? '');
            $ville = $cleanString($data['ville_utilisateur'] ?? '');
            $codePostal = $cleanString($data['code_postal_utilisateur'] ?? '');
            $province = $cleanString($data['province_utilisateur'] ?? '');
            $pays = $cleanString($data['pays_utilisateur'] ?? '');
            
            // Valider les données requises
            if (empty($nom) || empty($prenom) || empty($email) || empty($telephone)) {
                throw new Exception("Tous les champs obligatoires doivent être remplis");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("L'adresse email n'est pas valide");
            }
            
            // Mettre à jour les informations de l'utilisateur
            $userModel = new UserModel($this->db);
            $result = $userModel->updateUserInfo([
                'id_utilisateur' => $userId,
                'nom_utilisateur' => $nom,
                'prenom' => $prenom,
                'courriel' => $email,
                'telephone' => $telephone,
                'rue' => $adresse,
                'ville' => $ville,
                'code_postal' => $codePostal,
                'province' => $province,
                'pays' => $pays
            ]);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = "Vos informations ont été mises à jour avec succès.";
                $_SESSION['success'] = $response['message'];
            } else {
                throw new Exception("Une erreur est survenue lors de la mise à jour de votre profil.");
            }
            
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            $_SESSION['error'] = $response['message'];
            error_log("Erreur lors de la mise à jour du profil: " . $e->getMessage());
        }
        
        // Redirection côté client pour éviter les problèmes d'en-têtes
        if (!headers_sent()) {
            header('Location: /mon_profile');
            exit();
        } else {
            // Si les en-têtes ont déjà été envoyés, utiliser JavaScript pour la redirection
            echo '<script>window.location.href = "/mon_profile";</script>';
            exit();
        }
    }
    
    /**
     * Met à jour les informations de l'utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $telephone Téléphone de l'utilisateur
     * @param string $adresse Adresse de l'utilisateur
     * @param string $ville Ville de l'utilisateur
     * @param string $codePostal Code postal de l'utilisateur
     * @param string $province Province de l'utilisateur
     * @param string $pays Pays de l'utilisateur
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateUserInfo($userId, $nom, $prenom, $email, $telephone, $adresse = '', $ville = '', $codePostal = '', $province = '', $pays = '') {
        try {
            $userModel = new UserModel($this->db);
            
            // Vérifier si l'utilisateur existe
            $user = $userModel->getUserById($userId);
            if (!$user) {
                throw new Exception("Utilisateur non trouvé");
            }
            
            // Mettre à jour les informations de l'utilisateur
            $data = [
                'id_utilisateur' => $userId,
                'nom_utilisateur' => $nom,
                'prenom' => $prenom,
                'courriel' => $email,  // Utiliser 'courriel' au lieu de 'email' pour correspondre à la base de données
                'telephone' => $telephone,
                'rue' => $adresse,     // 'rue' est le champ utilisé pour l'adresse dans la base de données
                'ville' => $ville,
                'code_postal' => $codePostal,
                'province' => $province,
                'pays' => $pays
            ];
            
            // Appeler la méthode de mise à jour du modèle utilisateur
            $result = $userModel->updateUserInfo($data);
            
            if ($result) {
                $_SESSION['success'] = "Vos informations ont été mises à jour avec succès.";
                return true;
            } else {
                throw new Exception("Une erreur est survenue lors de la mise à jour de votre profil.");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }
    
    public function deleteProfile($id) {
        $userModel = new UserModel($this->db);
        $user = $userModel->getUserById($id);
        
        if ($user) {
            $userModel->deleteUser($user);
            session_destroy();
            header('Location: /');
            exit();
        } else {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: /mon_profile');
            exit();
        }
    }
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Affiche le formulaire de paiement (en modale ou page complète)
     * 
     * @param int $id_commande ID de la commande à payer
     * @param bool $modal Si true, le contenu est destiné à une modale
     */
    public function payOrder($id_commande, $modal = false) {
        try {
            // Vérification de l'authentification
            if (!isset($_SESSION['id_utilisateur'])) {
                throw new Exception('Veuillez vous connecter pour effectuer un paiement');
            }

            // Récupération des informations de la commande
            $stmt = $this->db->prepare("SELECT * FROM commande WHERE id_commande = ?");
            $stmt->execute([$id_commande]);
            $commande = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Vérification des droits d'accès
            if (!$commande || $commande['id_utilisateur'] != $_SESSION['id_utilisateur']) {
                throw new Exception('Commande introuvable ou accès refusé');
            }

            // Si c'est une requête AJAX (modale)
            if ($modal || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                // Empêcher l'inclusion du header/footer
                $GLOBALS['noHeaderFooter'] = true;
                require __DIR__ . '/../Vue/Paiement.php';
                exit();
            }

            // Pour une page complète
            require_once __DIR__ . '/../Vue/Paiement.php';
            
        } catch (\Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    /**
     * Récupère les détails d'une commande et de ses produits au format JSON.
     * @param int $id_commande L'ID de la commande.
     */
    public function getOrderDetailsJson($id_commande) {
        header('Content-Type: application/json');
        try {
            if (!isset($_SESSION['id_utilisateur'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
                exit;
            }

            $stmt = $this->db->prepare("SELECT * FROM commande WHERE id_commande = ?");
            $stmt->execute([$id_commande]);
            $commande = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$commande || $commande['id_utilisateur'] != $_SESSION['id_utilisateur']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Commande introuvable ou accès refusé.']);
                exit;
            }

            $stmt = $this->db->prepare(
                "SELECT p.nom, pc.quantite, pc.prix_unitaire 
                 FROM produit_commande pc 
                 JOIN produits p ON pc.id_produit = p.id_produit 
                 WHERE pc.id_commande = ?"
            );
            $stmt->execute([$id_commande]);
            $produits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'commande' => $commande, 'produits' => $produits]);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            error_log("Erreur dans getOrderDetailsJson: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Une erreur interne est survenue.']);
            exit;
        }
    }

    //fonction pour changer le status de la commande
    public function changeOrderStatus($id_commande, $status) {
        $userModel = new UserModel($this->db);
        $userModel->changeOrderStatus($id_commande, $status);
    }
    
    /**
     * Affiche les détails d'un utilisateur
     * @param int $id ID de l'utilisateur
     */
    public function showUser($id) {
        $userModel = new UserModel($this->db);
        $userData = $userModel->getUserInfo($id);
        
        if ($userData) {
            // Convertir le tableau associatif en objet
            $user = (object) $userData;
            require_once __DIR__ . '/../Vue/user_profile.php';
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Utilisateur non trouvé';
        }
    }
    
    /**
     * Affiche les commandes d'un utilisateur
     * @param int $id ID de l'utilisateur
     */
    public function userOrders($id) {
        $userModel = new UserModel($this->db);
        $userData = $userModel->getUserInfo($id);
        
        if ($userData) {
            // Convertir le tableau associatif en objet
            $user = (object) $userData;
            $orders = $userModel->getUserOrders($id);
            require_once __DIR__ . '/../Vue/user_orders.php';
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Utilisateur non trouvé';
        }
    }
    
    /**
     * Affiche le formulaire de modification d'un utilisateur
     * @param int $id ID de l'utilisateur
     */
    public function editUserForm($id) {
        $userModel = new UserModel($this->db);
        $userData = $userModel->getUserInfo($id);
        
        if ($userData) {
            // Convertir le tableau associatif en objet
            $user = (object) $userData;
            require_once __DIR__ . '/../Vue/edit_user.php';
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Utilisateur non trouvé';
        }
    }
}
?>
