<?php

namespace App\Controlleur;

use App\Modele\CartModel;

class CartControlleur
{
    private $cartModel;

    public function __construct(CartModel $cartModel)
    {
        $this->cartModel = $cartModel;
    }

    public function ajouter()
    {
        // Récupération des données POST
        $produitId = $_POST['produit_id'] ?? null;
        $quantite = $_POST['quantite'] ?? 1;
        $userId = $_SESSION['user_id'] ?? null; // Assurez-vous que l'utilisateur est connecté

        if ($produitId && $userId) {
            $this->cartModel->ajouterAuPanier($produitId, $quantite, $userId);
            echo "Produit ajouté au panier.";
        } else {
            http_response_code(400);
            echo "Erreur : Produit ou utilisateur manquant.";
        }
    }

    public function afficher()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $panier = $this->cartModel->obtenirPanierParUtilisateur($userId);
            require '../src/vue/vue_panier.php'; 
        } else {
            http_response_code(403);
            echo "Veuillez vous connecter pour accéder à votre panier.";
        }
    }

    public function vider()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->cartModel->viderPanier($userId);
            echo "Panier vidé.";
        } else {
            http_response_code(403);
            echo "Veuillez vous connecter pour vider votre panier.";
        }
    }

    public function mettreAJourQuantite() {
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Configuration CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');
        
        // Gérer la pré-requête OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Vérifier la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit();
        }
        
        try {
            // Journalisation pour le débogage
            error_log("=== Début de la mise à jour du panier ===");
            error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
            error_log("Contenu de la requête: " . file_get_contents('php://input'));
            error_log("Session: " . print_r($_SESSION, true));
            
            // Récupérer les données POST
            $input = [];
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $input = json_decode($json, true) ?? [];
            } else {
                $input = $_POST;
            }
            
            $produitId = $input['id_produit'] ?? null;
            $quantite = isset($input['quantite']) ? (int)$input['quantite'] : null;
            $userId = $_SESSION['user_id'] ?? null;
            
            // Journalisation des données reçues
            error_log("Données traitées - ID Produit: " . $produitId . ", Quantité: " . $quantite . ", User ID: " . $userId);
            error_log("Données brutes reçues: " . print_r($input, true));

            // Validation des entrées
            if (!$produitId || $quantite === null || $quantite < 1) {
                throw new \Exception('Paramètres invalides: ID produit ou quantité manquante');
            }
            
            if (!$userId) {
                throw new \Exception('Utilisateur non connecté');
            }

            // Initialiser le panier s'il n'existe pas
            if (!isset($_SESSION['panier']) || !is_array($_SESSION['panier'])) {
                $_SESSION['panier'] = [];
            }

            // Vérifier si le produit existe dans le panier
            if (!isset($_SESSION['panier'][$produitId])) {
                throw new \Exception('Produit non trouvé dans le panier');
            }

            // Mise à jour de la quantité dans le panier
            $_SESSION['panier'][$produitId]['quantite'] = $quantite;
            $prixTotal = $this->calculerTotalPanier();
            
            // Journalisation du succès
            error_log("Quantité mise à jour avec succès - ID: $produitId, Nouvelle quantité: $quantite, Nouveau total: $prixTotal");
            
            // Réponse de succès
            $response = [
                'success' => true,
                'prix_total' => $prixTotal,
                'quantite' => $quantite,
                'message' => 'Quantité mise à jour avec succès',
                'panier' => $_SESSION['panier'] // Pour le débogage
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            // En cas d'erreur
            http_response_code(400);
            $errorResponse = [
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => (defined('DEBUG_MODE') && DEBUG_MODE) ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'session' => $_SESSION ?? null,
                    'post' => $_POST ?? null,
                    'input' => $input ?? null
                ] : null
            ];
            
            error_log("Erreur lors de la mise à jour du panier: " . print_r($errorResponse, true));
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
        }
    }

    private function calculerTotalPanier() {
        $total = 0;
        if (isset($_SESSION['panier'])) {
            foreach ($_SESSION['panier'] as $item) {
                if (is_array($item) && isset($item['prix_unitaire'], $item['quantite'])) {
                    $total += $item['prix_unitaire'] * $item['quantite'];
                }
            }
        }
        return $total;
    }
}
