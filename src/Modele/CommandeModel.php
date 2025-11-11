<?php
namespace App\Modele;
use PDO;
use App\Classes\Commande;  
use PDOException;

class CommandeModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllCommandes() {
        try {
            $stmt = $this->pdo->prepare("SELECT 
                commande.id_commande, 
                commande.id_utilisateur, 
                commande.date_commande, 
                commande.prix_total, 
                utilisateur.nom_utilisateur, 
                utilisateur.prenom,
                commande.statut
            FROM 
                commande
            INNER JOIN utilisateur ON commande.id_utilisateur = utilisateur.id_utilisateur");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des commandes : " . $e->getMessage());
        }
    }
    
    /**
     * Met à jour le statut d'une commande
     * 
     * @param int $id_commande ID de la commande à mettre à jour
     * @param string $statut Nouveau statut de la commande
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateStatus($id_commande, $statut) {
        try {
            $stmt = $this->pdo->prepare("UPDATE commande SET statut = :statut WHERE id_commande = :id_commande");
            $stmt->bindParam(':id_commande', $id_commande, PDO::PARAM_INT);
            $stmt->bindParam(':statut', $statut);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de la commande $id_commande : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * @deprecated Utiliser updateStatus() à la place
     */
    public function updateCommande($id_commande, $statut) {
        return $this->updateStatus($id_commande, $statut);
    }
    
    public function getCommandeById($id_commande) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM commande WHERE id_commande = :id_commande");
            $stmt->bindParam(':id_commande', $id_commande, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la commande avec ID $id_commande : " . $e->getMessage());
        }
    }
    
    /**
     * Compte le nombre de commandes d'un utilisateur avec filtrage optionnel par statut
     * 
     * @param int $user_id ID de l'utilisateur
     * @param string|null $statut Filtre par statut (optionnel)
     * @return int Nombre de commandes correspondant aux critères
     */
    public function countUserOrders($user_id, $statut = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM commande WHERE id_utilisateur = :user_id";
            $params = [':user_id' => $user_id];
            
            if ($statut !== null) {
                $sql .= " AND statut = :statut";
                $params[':statut'] = $statut;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des commandes pour l'utilisateur $user_id : " . $e->getMessage());
            return 0;
        }
    }
    
    public function addCommande($commande) {
        if (!isset($commande['id_utilisateur']) || !isset($commande['prix_total'])) {
            throw new PDOException("Les informations requises pour ajouter une commande sont manquantes.");
        }
    
        $id_utilisateur = $commande['id_utilisateur'];
        $prix_total = $commande['prix_total'];
        $statut = 'En attente';
    
        try {
            // Début de la transaction
            $this->pdo->beginTransaction();
    
            // Insertion dans la table commande
            $sql = "INSERT INTO commande (id_utilisateur, statut, date_commande, prix_total) VALUES (:id_utilisateur, :statut, NOW(), :prix_total)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_utilisateur' => $id_utilisateur,
                ':statut' => $statut,
                ':prix_total' => $prix_total
            ]);
            // Récupération de l'ID de la commande créée
            $id_commande = $this->pdo->lastInsertId();
    
            // Insertion des produits de la commande
            foreach ($commande['produits'] as $produit) {
                if (!$this->addProduitCommande($id_commande, $produit)) {
                    throw new PDOException("Erreur lors de l'ajout d'un produit à la commande.");
                }
            }
    
            // Validation de la transaction
            $this->pdo->commit();
            return $id_commande;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Erreur lors de l'ajout de la commande : " . $e->getMessage());
        }
    }
    
    function addProduitCommande($id_commande, $produit) {
        $id_produit = $produit['id_produit'];
        $quantite = $produit['quantite'];
        $prix_unitaire = $produit['prix_unitaire'] ?? 0; // Récupérer le prix unitaire
    
        try {
            // Vérifiez si le produit existe dans la table produits
            $stmtCheckProduit = $this->pdo->prepare("SELECT id_produit, prix_unitaire FROM produits WHERE id_produit = :id_produit");
            $stmtCheckProduit->execute([':id_produit' => $id_produit]);
            $produitData = $stmtCheckProduit->fetch(PDO::FETCH_ASSOC);
    
            if ($produitData) {
                // Utiliser le prix unitaire du produit s'il n'est pas défini dans le panier
                if (empty($prix_unitaire)) {
                    $prix_unitaire = $produitData['prix_unitaire'];
                }
                
                // Vérifier si le produit est déjà ajouté à cette commande
                $stmtCheckProduitCommande = $this->pdo->prepare("SELECT * FROM produit_commande WHERE id_commande = :id_commande AND id_produit = :id_produit");
                $stmtCheckProduitCommande->execute([
                    ':id_commande' => $id_commande,
                    ':id_produit' => $id_produit
                ]);
    
                if ($stmtCheckProduitCommande->rowCount() > 0) {
                    // Si le produit est déjà présent, mettez à jour la quantité
                    $stmtUpdateQuantite = $this->pdo->prepare(
                        "UPDATE produit_commande SET quantite = quantite + :quantite, prix_unitaire = :prix_unitaire WHERE id_commande = :id_commande AND id_produit = :id_produit"
                    );
                    $stmtUpdateQuantite->execute([
                        ':quantite' => $quantite,
                        ':prix_unitaire' => $prix_unitaire,
                        ':id_commande' => $id_commande,
                        ':id_produit' => $id_produit
                    ]);
                } else {
                    // Insertion dans produit_commande si ce n'est pas déjà ajouté
                    $stmtProduitCommande = $this->pdo->prepare(
                        "INSERT INTO produit_commande (id_commande, id_produit, quantite, prix_unitaire) VALUES (:id_commande, :id_produit, :quantite, :prix_unitaire)"
                    );
                    $stmtProduitCommande->execute([
                        ':id_commande' => $id_commande,
                        ':id_produit' => $id_produit,
                        ':quantite' => $quantite,
                        ':prix_unitaire' => $prix_unitaire
                    ]);
                }
    
                // Mise à jour de la quantité du produit
                if (!$this->miseAJourQuantiteProduit($id_produit, $quantite)) {
                    throw new \PDOException("Erreur lors de la mise à jour de la quantité du produit.");
                }
            } else {
                throw new \PDOException("Le produit avec l'ID $id_produit n'existe pas.");
            }
    
            return true;
        } catch (\PDOException $e) {
            throw new \PDOException("Erreur dans addProduitCommande : " . $e->getMessage());
        }
    }    
    
    
    function miseAJourQuantiteProduit($id_produit, $quantite) {
        try {
            $sql = "UPDATE produits SET quantite = quantite - :quantite WHERE id_produit = :id_produit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':quantite' => $quantite,
                ':id_produit' => $id_produit
            ]);
            return true;
        } catch (PDOException $e) {
            throw new PDOException("Erreur dans miseAJourQuantiteProduit : " . $e->getMessage());
        }
    }
    
    public function getOrderTotal($order_id) {
        try {
            // Préparer la requête SQL
            $stmt = $this->pdo->prepare("
                SELECT SUM(p.prix_unitaire * pc.quantite) AS total 
                FROM commande c
                INNER JOIN produit_commande pc ON c.id_commande = pc.id_commande
                INNER JOIN produits p ON pc.id_produit = p.id_produit
                WHERE c.id_commande = :order_id
            
            ");
    
            // Lier l'ID de la commande avec un paramètre sécurisé
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    
            // Exécuter la requête
            $stmt->execute();
    
            // Récupérer le résultat
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Retourner le total ou 0 par défaut
            return $row['total'] ?? 0;
    
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du total de la commande $order_id : " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère les commandes d'un utilisateur avec pagination et filtrage par statut
     * 
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @param int $offset Position de départ pour la pagination (défaut: 0)
     * @param int $limit Nombre maximum de résultats à retourner (défaut: 5)
     * @param string|null $statut Filtre par statut (optionnel)
     * @return array Les commandes de l'utilisateur
     */
    public function getCommandesByUser($id_utilisateur, $offset = 0, $limit = 5, $statut = null) {
        try {
            error_log("Début de getCommandesByUser pour l'utilisateur ID: $id_utilisateur");
            
            // Vérifier la connexion PDO
            if (!$this->pdo) {
                error_log("Erreur: Pas de connexion PDO");
                throw new PDOException("Pas de connexion à la base de données");
            }
            
            // Construction de la requête de base
            $query = "
                SELECT 
                    c.id_commande,
                    c.id_utilisateur,
                    c.date_commande,
                    c.prix_total,
                    c.statut
                FROM commande c
                WHERE c.id_utilisateur = :id_utilisateur";
            
            // Ajout du filtre par statut si spécifié
            if ($statut !== null) {
                $query .= " AND c.statut = :statut";
            }
            
            // Tri par date de commande décroissante
            $query .= " ORDER BY c.date_commande DESC";
            
            // Ajout de la pagination
            $query .= " LIMIT :offset, :limit";
            
            error_log("Requête SQL: " . $query);
            
            // Préparation de la requête
            $stmt = $this->pdo->prepare($query);
            
            // Liaison des paramètres
            $stmt->bindValue(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            
            // Liaison du paramètre de statut si nécessaire
            if ($statut !== null) {
                $stmt->bindValue(':statut', $statut);
            }
            
            // Exécution de la requête
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new PDOException("Erreur d'exécution de la requête: " . ($errorInfo[2] ?? 'Inconnue'));
            }
            
            // Récupération des résultats
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Nombre de commandes trouvées: " . count($result));
            
            // Convertir les prix en nombres flottants
            foreach ($result as &$row) {
                $row['prix_total'] = (float)$row['prix_total'];
            }
            
            return $result;
            
        } catch (PDOException $e) {
            $errorMsg = "Erreur PDO dans getCommandesByUser: " . $e->getMessage();
            error_log($errorMsg);
            throw $e; // Renvoyer l'exception pour une meilleure gestion en amont
        } catch (Exception $e) {
            $errorMsg = "Erreur inattendue dans getCommandesByUser: " . $e->getMessage();
            error_log($errorMsg);
            throw $e; // Renvoyer l'exception pour une meilleure gestion en amont
        }
    }
}