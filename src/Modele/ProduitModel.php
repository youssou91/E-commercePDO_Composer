<?php
namespace App\Modele;

use PDO;

class ProduitModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAllProduits() {
        $stmt = $this->pdo->prepare("SELECT * FROM produits;");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProduitById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM produits WHERE id_produit = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCategories() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM categorie");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des catégories : " . $e->getMessage());
        }
    }

    public function ajouterProduit($nom, $prix, $quantite, $id_categorie, $model, $courteDescription, $longueDescription, $couleurs, $file) {
        try {
            if (isset($_FILES['chemin_image']) && $_FILES['chemin_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['chemin_image']; 
                $chemin_image = $this->uploadImage($file); 
            }            
            if (empty($chemin_image)) {
                $chemin_image = 'default_image_path.jpg';
            }
            $sql = "INSERT INTO produits (nom, prix_unitaire, quantite, id_categorie, model, courte_description, description, chemin_image";
            $params = [
                ':nom' => $nom,
                ':prix' => $prix,
                ':quantite' => $quantite,
                ':id_categorie' => $id_categorie,
                ':model' => $model,
                ':courteDescription' => $courteDescription,
                ':longueDescription' => $longueDescription,
                ':chemin_image' => $chemin_image,
            ];
            // Si des couleurs sont spécifiées, ajouter à la requête SQL
            if ($couleurs !== null) {
                $sql .= ", couleurs";
                $params[':couleurs'] = is_array($couleurs) ? json_encode($couleurs) : $couleurs;
            }
            $sql .= ") VALUES (:nom, :prix, :quantite, :id_categorie, :model, :courteDescription, :longueDescription, :chemin_image";
            if ($couleurs !== null) {
                $sql .= ", :couleurs";
            }
            $sql .= ")";
    
            // Exécution de la requête SQL
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
    
            return $this->pdo->lastInsertId(); 
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de l'ajout du produit : " . $e->getMessage());
        }
    }
    
    public function uploadImage($file, $uploadDir = 'public/uploads/') {
        $errors = [];
    
        // Vérification de l'existence du répertoire de destination
        if (!is_dir($uploadDir)) {
            // Essayer de créer le répertoire si il n'existe pas
            if (!mkdir($uploadDir, 0777, true)) {
                $errors[] = "Impossible de créer le répertoire 'uploads'.";
            }
        }
    
        // Vérification si le fichier a été téléchargé sans erreur
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors du téléchargement de l'image.";
        } else {
            // Générer un nom unique pour le fichier
            $fileName = time() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
    
            // Récupérer l'extension du fichier et la convertir en minuscule
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
            // Extensions autorisées pour l'image
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
            // Vérification de l'extension du fichier
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Extension non valide. Formats acceptés : jpg, jpeg, png, gif.";
            }
    
            // Vérification de la taille du fichier (limite 5 Mo)
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = "Fichier trop volumineux. Taille max : 5 Mo.";
            }
    
            // Si pas d'erreurs, déplacer le fichier téléchargé
            if (empty($errors)) {
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Retourner le chemin relatif pour l'utilisation en base de données
                    return 'uploads/' . $fileName;
                } else {
                    $errors[] = "Impossible de déplacer le fichier téléchargé. Vérifiez les permissions du répertoire.";
                }
            }
        }
    
        // Si des erreurs existent, afficher les erreurs et retourner null
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo $error . "<br>";
            }
        }
        return null; // Retourne null en cas d'échec
    }
    
    public function getTousLesProduitsAvecPromotions($page = 1, $perPage = 8) {
        $offset = ($page - 1) * $perPage;
        
        // Requête pour compter le nombre total de produits
        $countQuery = "
            SELECT COUNT(DISTINCT p.id_produit) as total
            FROM Produits p
            LEFT JOIN ProduitPromotion pp ON p.id_produit = pp.id_produit
            LEFT JOIN Promotions pr ON pp.id_promotion = pr.id_promotion
            WHERE p.quantite > 0
            AND (pr.id_promotion IS NULL OR (pr.date_debut <= CURDATE() AND pr.date_fin >= CURDATE()))
        ";
        
        // Requête pour récupérer les produits paginés
        $query = "
            SELECT 
                p.id_produit,
                p.nom,
                p.prix_unitaire,
                p.quantite,
                p.chemin_image,
                p.description,
                p.couleurs,
                MAX(pr.valeur) AS promo_valeur,
                MAX(pr.type) AS promo_type
            FROM Produits p
            LEFT JOIN ProduitPromotion pp ON p.id_produit = pp.id_produit
            LEFT JOIN Promotions pr ON pp.id_promotion = pr.id_promotion
            WHERE p.quantite > 0
            AND (pr.id_promotion IS NULL OR (pr.date_debut <= CURDATE() AND pr.date_fin >= CURDATE()))
            GROUP BY p.id_produit, p.nom, p.prix_unitaire, p.quantite, p.chemin_image, p.description, p.couleurs
            LIMIT :offset, :perPage
        ";
        
        // Exécution de la requête de comptage
        $countStmt = $this->pdo->prepare($countQuery);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($total / $perPage);
        
        // Exécution de la requête de sélection
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'produits' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ]
        ];
    }

   
    public function deleteProduitPromotion($id) {
        $sql = "DELETE FROM produitpromotion WHERE id_produit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
    
    public function deleteProduit($id) {
        try {
            $this->pdo->beginTransaction();
            $this->deleteProduitPromotion($id);
            $sql = "DELETE FROM produits WHERE id_produit = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function updateProduit($id, $nom, $prix, $quantite, $model, $courte_description, $description = null) {
        try {
            $sql = "UPDATE produits 
                    SET nom = ?, 
                        prix_unitaire = ?, 
                        quantite = ?, 
                        model = ?, 
                        courte_description = ?, 
                        description = ?
                    WHERE id_produit = ?";
            
            $params = [
                $nom, 
                $prix, 
                $quantite, 
                $model, 
                $courte_description, 
                $description,
                $id
            ];
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de mettre à jour le produit.");
        }
        return true;
    }
    
}