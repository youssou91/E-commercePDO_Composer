<?php
namespace App\Modele;
use PDO;
use App\Classes\Categorie;

class CategorieModel {
    private $pdo;

    public function __construct( PDO $pdo) {
        $this->pdo = $pdo;
    }
    // Méthode pour récupérer toutes les catégories
    public function getAllCategories() {
        $sql = "SELECT * FROM categorie";
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute();
            $categories = [];
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = new Categorie($row['id_categorie'], $row['nom_categorie']);
            }
            return $categories;
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    /**
     * Récupère une catégorie par son ID
     * 
     * @param int $id_categorie L'ID de la catégorie à récupérer
     * @return array|null Les données de la catégorie ou null si non trouvée
     */
    public function getCategorieById($id_categorie) {
        $sql = "SELECT * FROM categorie WHERE id_categorie = :id_categorie";
        try {
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
            $query->execute();
            
            if ($query->rowCount() > 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur dans getCategorieById: " . $e->getMessage());
            throw new \Exception("Erreur lors de la récupération de la catégorie");
        }
    }
}
