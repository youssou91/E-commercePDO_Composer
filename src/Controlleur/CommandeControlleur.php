<?php
namespace App\Controlleur;

require_once __DIR__ . '/../../config/db.php';

use App\Modele\CommandeModel;
use App\Modele\UserModel;
use PDOException;

class CommandeControlleur {
    private $commandeModel; 
    private $pdo;

    public function __construct(CommandeModel $commandeModel) {
        $this->commandeModel = $commandeModel;
        $this->pdo = $GLOBALS['pdo'];
    }
    
    public function index() {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id_utilisateur'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page";
            header('Location: /login');
            exit();
        }
        
        // Gérer la mise à jour du statut si demandé
        if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id_commande']) && isset($_GET['statut'])) {
            try {
                $id_commande = (int)$_GET['id_commande'];
                $statut = $_GET['statut'];
                
                // Vérifier que l'utilisateur a le droit de modifier cette commande
                $commande = $this->commandeModel->getCommandeById($id_commande);
                $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                
                if (!$commande || ($commande['id_utilisateur'] != $_SESSION['id_utilisateur'] && !$isAdmin)) {
                    throw new \Exception("Vous n'êtes pas autorisé à modifier cette commande");
                }
                
                // Mettre à jour le statut
                $result = $this->commandeModel->updateStatus($id_commande, $statut);
                
                if ($result) {
                    $_SESSION['success'] = "Le statut de la commande a été mis à jour avec succès";
                } else {
                    throw new \Exception("Erreur lors de la mise à jour du statut");
                }
                
                // Rediriger vers la même page sans les paramètres
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                exit();
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                exit();
            }
        }
        
        // Vérifier si l'utilisateur est un administrateur
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        
        try {
            // Récupérer les commandes
            if ($isAdmin) {
                $commandes = $this->commandeModel->getAllCommandes();
            } else {
                $commandes = $this->commandeModel->getCommandesByUser($_SESSION['id_utilisateur']);
            }
            
            // Afficher la vue des commandes
            require_once __DIR__ . '/../Vue/Commandes.php';
            
        } catch (\Exception $e) {
            error_log("Erreur dans CommandeControlleur::index: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors de la récupération des commandes";
            header('Location: /');
            exit();
        }
    }
    
    /**
     * Affiche les commandes d'un utilisateur spécifique pour l'administration
     * 
     * @param int $user_id L'ID de l'utilisateur
     */
    /**
     * Annule une commande
     * 
     * @param int $id_commande ID de la commande à annuler
     */
    public function annulerCommande($id_commande) {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id_utilisateur'])) {
            $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action";
            header('Location: /login');
            exit();
        }
        
        // Déterminer si l'utilisateur est admin
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        
        // Utiliser le referer pour déterminer la page de redirection
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        
        // Définir l'URL de redirection par défaut
        $redirectUrl = $isAdmin ? '/admin/commandes' : '/mon_profile';
        
        // Si on a un referer, on vérifie s'il contient une des pages connues
        if ($referer) {
            if (strpos($referer, '/admin/commandes') !== false) {
                $redirectUrl = '/admin/commandes';
            } elseif (strpos($referer, '/mon_profile') !== false) {
                $redirectUrl = '/mon_profile';
            }
        }
        
        try {
            // Récupérer la commande
            $commande = $this->commandeModel->getCommandeById($id_commande);
            
            // Vérifier si la commande existe
            if (!$commande) {
                throw new \Exception("Commande introuvable");
            }
            
            // Vérifier les permissions (l'utilisateur doit être le propriétaire ou un admin)
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            if ($commande['id_utilisateur'] != $_SESSION['id_utilisateur'] && !$isAdmin) {
                throw new \Exception("Vous n'êtes pas autorisé à annuler cette commande");
            }
            
            // Vérifier si la commande peut être annulée (seulement si elle est en attente ou en traitement)
            if (!in_array($commande['statut'], ['En attente', 'En traitement'])) {
                throw new \Exception("Cette commande ne peut plus être annulée car son statut est : " . $commande['statut']);
            }
            
            // Mettre à jour le statut de la commande
            $result = $this->commandeModel->updateStatus($id_commande, 'Annulée');
            
            if ($result) {
                $_SESSION['success'] = "La commande a été annulée avec succès";
            } else {
                throw new \Exception("Une erreur est survenue lors de l'annulation de la commande");
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        // Rediriger vers la page appropriée
        header("Location: $redirectUrl");
        exit();
    }
    
    public function adminCommandesUtilisateur($user_id) {
        // Vérifier si l'utilisateur est connecté et est admin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = "Accès refusé";
            header('Location: /login');
            exit();
        }
        
        try {
            // Récupérer les informations de l'utilisateur
            $userModel = new UserModel($this->pdo);
            $user = $userModel->getUserById($user_id);
            
            if (!$user) {
                throw new \Exception("Utilisateur non trouvé");
            }
            
            // Récupérer les commandes de l'utilisateur
            $commandes = $this->commandeModel->getCommandesByUser($user_id);
            
            // Afficher la vue d'administration des commandes
            require_once __DIR__ . '/../Vue/admin/commandes.php';
            
        } catch (\Exception $e) {
            error_log("Erreur dans CommandeControlleur::adminCommandesUtilisateur: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/utilisateurs');
            exit();
        }
    }
    
    /**
     * Modifie le statut d'une commande
     * 
     * @param int $id_commande ID de la commande à modifier
     * @param string $action Action à effectuer (traiter, annuler, etc.)
     */
    public function modifierCommande($id_commande, $action) {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id_utilisateur'])) {
            $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action";
            header('Location: /login');
            exit();
        }
        
        try {
            // Récupérer la commande
            $commande = $this->commandeModel->getCommandeById($id_commande);
            
            if (!$commande) {
                throw new \Exception("Commande introuvable");
            }
            
            // Vérifier que l'utilisateur est l'auteur de la commande ou un administrateur
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            if ($commande['id_utilisateur'] != $_SESSION['id_utilisateur'] && !$isAdmin) {
                throw new \Exception("Vous n'êtes pas autorisé à modifier cette commande");
            }
            
            // Déterminer le nouveau statut en fonction de l'action
            $nouveauStatut = '';
            switch ($action) {
                case 'traiter':
                    $nouveauStatut = 'En traitement';
                    break;
                case 'expedier':
                    $nouveauStatut = 'En expédition';
                    break;
                case 'annuler':
                    $nouveauStatut = 'Annulée';
                    break;
                case 'livrer':
                    $nouveauStatut = 'Livrée';
                    break;
                default:
                    throw new \Exception("Action non reconnue");
            }
            
            // Mettre à jour le statut de la commande
            $result = $this->commandeModel->updateStatus($id_commande, $nouveauStatut);
            
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
                throw new \Exception("Erreur lors de la mise à jour du statut de la commande");
            }
            
        } catch (\Exception $e) {
            error_log("Erreur dans modifierCommande: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            
            // Rediriger vers la page précédente ou le profil
            $referer = $_SERVER['HTTP_REFERER'] ?? '/mon_profile';
            header("Location: $referer");
            exit();
        }
    }
    
    public function afficherCommande($id_commande) {
        try {
            $commande = $this->commandeModel->getCommandeById($id_commande);
            if ($commande) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode($commande);
                } else {
                    // Afficher la vue de détail de la commande
                    require_once __DIR__ . '/../Vue/commande_detail.php';
                }
            } else {
                throw new \Exception("Commande introuvable.");
            }
        } catch (\Exception $e) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(404);
                echo json_encode(['error' => $e->getMessage()]);
            } else {
                $_SESSION['error'] = $e->getMessage();
                header('Location: /commandes');
                exit();
            }
        }
    }
    
    // Ajouter une nouvelle commande
    public function ajouterCommande() {
        try {
            // Vérifier si la requête est de type POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Méthode non autorisée');
            }
            
            // Vérifier si l'utilisateur est connecté
            if (!isset($_SESSION['id_utilisateur'])) {
                header('Location: /login');
                exit();
            }
            
            // Vérifier si les données du formulaire sont présentes
            if (empty($_POST['produits']) || !is_array($_POST['produits'])) {
                throw new \Exception('Aucun produit spécifié pour la commande');
            }
            
            // Initialiser les données de la commande
            $data = [
                'id_utilisateur' => $_SESSION['id_utilisateur'],
                'prix_total' => (float)($_POST['prix_total'] ?? 0),
                'produits' => []
            ];
            
            // Récupérer les produits du formulaire
            foreach ($_POST['produits'] as $id_produit => $produit) {
                $quantite = (int)($produit['quantite'] ?? 0);
                
                // S'assurer que la quantité est valide
                if ($quantite < 1) {
                    continue; // Ignorer les produits avec quantité invalide
                }
                
                $data['produits'][] = [
                    'id_produit' => $id_produit,
                    'quantite' => $quantite,
                    'prix_unitaire' => $produit['prix_unitaire']
                ];
            }
            
            // Ajouter la commande
            $id_commande = $this->commandeModel->addCommande($data);
            
            if ($id_commande) {
                // Vider le panier après la commande
                unset($_SESSION['panier']);
                
                // Rediriger vers la page de profil avec un message de succès
                $_SESSION['success'] = 'Votre commande a été passée avec succès !';
                header('Location: /mon_profile');
                exit();
            } else {
                throw new \Exception("Erreur lors de l'ajout de la commande.");
            }
        } catch (\Exception $e) {
            // Enregistrer l'erreur dans les logs
            error_log("Erreur lors de l'ajout de la commande : " . $e->getMessage());
            
            // Afficher un message d'erreur à l'utilisateur
            $_SESSION['error'] = "Une erreur est survenue lors de la commande : " . $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }
    
    public function afficherTotalCommande($id_commande) {
        try {
            return $this->commandeModel->getOrderTotal($id_commande);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans afficherTotalCommande: " . $e->getMessage());
            return false;
        }
    }
}
