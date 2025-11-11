<?php
namespace App\Modele;
use App\Classes\Produit; 
use \PDO;

class PromotionModel {
    private $db; 

    public function __construct($pdo) {
        $this->db = $pdo; 
    }

    public function getAllPromotions()
    {
        $query = "
            SELECT pp.id_promotion, p.nom AS nom, pr.code_promotion, pr.valeur, pr.date_debut, pr.date_fin 
          FROM produitpromotion pp 
          JOIN produits p ON pp.id_produit = p.id_produit
          JOIN promotions pr ON pp.id_promotion = pr.id_promotion
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterPromotion($valeur, $date_debut, $date_fin) 
    {
        $query = "INSERT INTO promotions (valeur, date_debut, date_fin) VALUES (:valeur, :date_debut, :date_fin)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':valeur', $valeur, PDO::PARAM_INT);
        $stmt->bindParam(':date_debut', $date_debut, PDO::PARAM_STR);
        $stmt->bindParam(':date_fin', $date_fin, PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function associerProduitPromotion($id_produit, $id_promotion) 
    {
        $query = "INSERT INTO produitpromotion (id_produit, id_promotion) VALUES (:id_produit, :id_promotion)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_produit', $id_produit, PDO::PARAM_INT);
        $stmt->bindParam(':id_promotion', $id_promotion, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getProduits() 
    {
        $query = "SELECT id_produit, nom FROM produits";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime les associations d'une promotion avec les produits
     * 
     * @param int $id_promotion ID de la promotion
     * @return bool Succès de l'opération
     */
    public function supprimerAssociationsProduits($id_promotion)
    {
        $query = "DELETE FROM produitpromotion WHERE id_promotion = :id_promotion";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_promotion', $id_promotion, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Supprime une promotion de la base de données
     * 
     * @param int $id_promotion ID de la promotion à supprimer
     * @return bool Succès de l'opération
     */
    public function supprimerPromotion($id_promotion)
    {
        $query = "DELETE FROM promotions WHERE id_promotion = :id_promotion";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_promotion', $id_promotion, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>