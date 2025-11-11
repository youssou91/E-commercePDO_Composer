<?php
namespace App\Controlleur;

use App\Modele\PromotionModel;  
class PromotionControlleur {
    private $db;
    private $model;  

    public function __construct() {
        $this->db = getConnection();  
        $this->model = new PromotionModel($this->db);  
    }

    public function index() {
        require_once '../src/vue/Promotions.php';
    }

    public function ajouterPromotion($data) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_produit = $data['id_produit'];
            $valeur = $data['valeur'];
            $date_debut = $data['date_debut'];
            $date_fin = $data['date_fin'];

            try {
                // Ajouter la promotion
                $id_promotion = $this->model->ajouterPromotion($valeur, $date_debut, $date_fin);

                // Associer la promotion au produit
                $this->model->associerProduitPromotion($id_produit, $id_promotion);

                // Redirection en cas de succès
                require_once '../src/vue/Promotions.php';
                exit;
            } catch (Exception $e) {
                // Gestion des erreurs
                echo 'Erreur : ' . $e->getMessage();
            }
        } else {
            // Retourner les produits pour la vue
            return $this->model->getProduits();
        }
    }

    public function getPromotions() {
        return $this->model->getAllPromotions();
    }
    
    // Supprimer une promotion
    public function delete($id) {
        if (!isset($id)) {
            throw new \Exception("ID de promotion manquant");
        }

        try {
            // Supprimer d'abord les associations de produits
            $this->model->supprimerAssociationsProduits($id);
            
            // Puis supprimer la promotion
            $result = $this->model->supprimerPromotion($id);
            
            if ($result) {
                // Rediriger vers la liste des promotions avec un message de succès
                $_SESSION['success_message'] = 'La promotion a été supprimée avec succès.';
                header('Location: /admin/promotions');
                exit;
            } else {
                throw new \Exception("Erreur lors de la suppression de la promotion");
            }
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
            header('Location: /admin/promotions');
            exit;
        }
    }
    
    // Méthode pour afficher le formulaire d'ajout de promotion
    public function addForm() {
        // Vous pouvez récupérer les produits ici pour les envoyer à la vue
        $produits = $this->model->getProduits();  // Utilisez $this->model ici
        require_once '../src/vue/Promotions.php';
    }

    // Méthode pour traiter le formulaire d'ajout de promotion
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données envoyées via POST
            $id_produit = $_POST['id_produit'];
            $valeur = $_POST['valeur'];
            $date_debut = $_POST['date_debut'];
            $date_fin = $_POST['date_fin'];

            // Ajouter la promotion dans la base de données
            $id_promotion = $this->model->ajouterPromotion($valeur, $date_debut, $date_fin);
            
            // Associer le produit à la promotion
            $this->model->associerProduitPromotion($id_produit, $id_promotion);

            // Rediriger vers la page des promotions avec un message de succès
            $_SESSION['success_message'] = 'La promotion a été ajoutée avec succès.';
            header('Location: /admin/promotions');
            exit;
        }
    }
}
?>