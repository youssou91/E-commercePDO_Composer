<?php
namespace App\Controlleur;

require_once __DIR__ . '/../../config/db.php';

use App\Modele\ProduitModel;
use App\Modele\CategorieModel;
use App\Classes\Produits;

class ProduitControlleur {
    private $produitModel;
    private $categorieModel;

    public function __construct(ProduitModel $produitModel, CategorieModel $categorieModel) {
        $this->produitModel = $produitModel;
        $this->categorieModel = $categorieModel;
    }

    public function index() {
        $produits = $this->produitModel->getAllProduits();
        require __DIR__ . '/../vue/Produits.php';
    }

    public function afficherProduits() {
        return $this->produitModel->getAllProduits();
    }

    public function afficherCategories() {
        return $this->categorieModel->getAllCategories();
    }
    
    public function afficheForm() {
        require __DIR__ . '/../vue/ajout.php';
    }

    /**
     * Affiche les détails d'un produit spécifique
     * 
     * @param int $id L'ID du produit à afficher
     */
    public function show($id) {
        try {
            $produit = $this->produitModel->getProduitById($id);
            if (!$produit) {
                throw new \Exception("Produit non trouvé");
            }
            
            // Récupérer la catégorie du produit
            $categorie = $this->categorieModel->getCategorieById($produit['id_categorie']);
            
            // Afficher la vue des détails du produit
            require __DIR__ . '/../vue/detail_produit.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /produits');
            exit();
        }
    }

    public function ajouterProduit(array $data) {
        // Créez l'objet produit à partir des données
        $produit = new Produits(
            $data['nom'],
            $data['prix_unitaire'],
            $data['quantite'],
            $data['id_categorie'],
            $data['model'],
            $data['courte_description'],
            $data['longue_description'] ?? null,
            $data['couleurs'] ?? null,
            $data['chemin_image'] ?? null
        );

        // Ajoute le produit
        $idProduit = $this->produitModel->ajouterProduit(
            $produit->getNom(),
            $produit->getPrixUnitaire(),
            $produit->getQuantite(),
            $produit->getIdCategorie(),
            $produit->getModel(),
            $produit->getCourteDescription(),
            $produit->getDescription(),
            $produit->getCouleursProd(),
            $produit->getCheminImage()
        );

        // Si une image est envoyée, l'upload
        if (isset($data['chemin_image'])) {
            $this->produitModel->uploadImage($data['chemin_image']);
        }

        // Retour à la vue pour afficher après ajout
        $message = "Produit ajouté avec succès!";
        require __DIR__ . '/../vue/ajout.php'; // Revenir au formulaire avec le message
    }
    
    public function recupererProduit($id_produit) {
       // Récupérer le produit par son ID
        $produit = $this->produitModel->getProduitById($id_produit);
        if (!$produit) {
            // Gérer le cas où le produit n'est pas trouvé
            handleError("Produit non trouvé.", 404);
        }
        // Récupérer toutes les catégories pour l'affichage du formulaire
        $categories = $this->categorieModel->getAllCategories();
        require __DIR__. '/../vue/modifierProduit.php';

    }
    
    
    public function supprimerProduit($id) {
        if (isset($id) && is_numeric($id)) {
            $result = $this->produitModel->deleteProduit($id);
            if ($result) {
                $message = "Produit supprimé avec succès.";
            } else {
                $message = "Erreur lors de la suppression du produit.";
            }
        } else {
            throw new \Exception("ID invalide pour la suppression.");
        }
        // Retour à la liste des produits avec un message
        header('Location: /produits');
    }

    // Dans votre contrôleur ProduitControlleur, méthode updateProduit
    public function updateProduits($id_produit) {
        // Vérifier si les données sont envoyées via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom = $_POST['nom'];
            $prix = $_POST['prix_unitaire'];
            $quantite = $_POST['quantite'];
            $id_categorie = $_POST['id_categorie'];
            $model = $_POST['model'];
            $courte_description = $_POST['courte_description'];
            $longue_description = $_POST['longue_description'] ?? null;
            $couleurs = $_POST['couleurs'] ?? null;
            $chemin_image = $_FILES['chemin_image']['name'] ?? null;

            // Si une image a été téléchargée, gérer l'upload
            if ($chemin_image) {
                // Déplacer l'image téléchargée vers un dossier spécifique, par exemple
                $chemin_image = 'public/uploads/' . basename($_FILES['chemin_image']['name']);
                move_uploaded_file($_FILES['chemin_image']['tmp_name'], $chemin_image);
            }

            // Appeler la méthode du modèle pour mettre à jour le produit
            $this->produitModel->updateProduit($id_produit, $nom, $prix, $quantite, $id_categorie, $model, $courte_description, $longue_description, $couleurs, $chemin_image);

            // Rediriger après la mise à jour
            header("Location: /produits/{$id_produit}");
            exit();
        }
    }

}
