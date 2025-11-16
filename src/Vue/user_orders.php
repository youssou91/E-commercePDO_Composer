<?php include __DIR__ . '/../../static/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/utilisateur/<?= $user->id_utilisateur ?>">Profil</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Commandes</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Historique des commandes</h2>
                <a href="/utilisateur/<?= $user->id_utilisateur ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour au profil
                </a>
            </div>
            
            <?php if (!empty($orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id_commande'] ?? $order['id'] ?? 'N/A' ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['date_commande'] ?? 'now')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            ($order['statut'] ?? '') === 'payée' ? 'success' : 
                                            (($order['statut'] ?? '') === 'annulée' ? 'danger' : 'warning') 
                                        ?>">
                                            <?= ucfirst($order['statut'] ?? 'en attente') ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($order['montant_total'] ?? 0, 2, ',', ' ') ?> €</td>
                                    <td>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucune commande trouvée pour cet utilisateur.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../static/footer.php'; ?>
