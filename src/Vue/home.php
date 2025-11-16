    <?php
    // Vérification de la connexion
    $utilisateurEstConnecte = isset($_SESSION['id_utilisateur']) && !empty($_SESSION['id_utilisateur']);
    $produits = $produits ?? []; 
    $totalPanier = 0; 
    $quantiteTotale = 0;
    // Regroupement des produits par ID et addition des quantités
    $panierRegroupe = $_SESSION['panier'] ?? [];
    
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ma boutique</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body class="bg-gray-200 m-5">
        <div class="container mx-auto">
            <h2 class="text-center text-2xl text-blue-600 font-bold my-4">Ma boutique</h2>
            <!-- panier -->
            <div class="bg-white rounded shadow-md p-4 mb-8">
                <h3 class="text-2xl font-bold text-blue-600 text-center mb-4">Mon Panier</h3>
                <?php if (!empty($_SESSION['panier'])): ?>
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border px-4 py-2 text-left">Nom</th>
                                <th class="border px-4 py-2 text-left">Quantité</th>
                                <th class="border px-4 py-2 text-left">Prix Unitaire</th>
                                <th class="border px-4 py-2 text-left">Prix Total</th>
                                <th class="border px-4 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($_SESSION['panier'])): 
                                foreach ($_SESSION['panier'] as $id => $produit): 
                                    if (is_array($produit) && isset($produit['prix_unitaire'], $produit['quantite'])):
                                        $prixUnitaireProduit = $produit['prix_unitaire'];
                                        $quantite = $produit['quantite'];
                                        $prixTotalProduit = $quantite * $prixUnitaireProduit;
                                        $totalPanier += $prixTotalProduit;
                                        $quantiteTotale += $quantite;
                            ?>
                            <tr class="product-row" data-product-id="<?= $id ?>" data-unit-price="<?= $prixUnitaireProduit ?>">
                                <td class="border px-4 py-2"><?= htmlspecialchars($produit['nom'] ?? 'Produit inconnu') ?></td>
                                <td class="border px-4 py-2">
                                    <input type="number" 
                                           name="quantite" 
                                           class="quantity-input w-20 text-center border rounded" 
                                           value="<?= $quantite ?>" 
                                           min="1" 
                                           max="100"
                                           data-product-id="<?= $id ?>">
                                </td>
                                <td class="border px-4 py-2 unit-price"><?= number_format($prixUnitaireProduit, 2) ?> $</td>
                                <td class="border px-4 py-2 total-price"><?= number_format($prixTotalProduit, 2) ?> $</td>
                                <td class="border px-4 py-2">
                                    <form method="POST" action="/produits/supprimer/<?= $id ?>" class="inline delete-form">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endif; endforeach; endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-200">
                                <th colspan="3" class="border px-4 py-2 text-left">Total</th>
                                <th colspan="2" class="border px-4 py-2 total-cart"><?= number_format($totalPanier, 2) ?> $</th>
                            </tr>
                            <tr class="bg-gray-100">
                                <th colspan="3" class="border px-4 py-2 text-left">Quantité Totale</th>
                                <th colspan="2" class="border px-4 py-2 total-quantity"><?= $quantiteTotale ?> article(s)</th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="flex justify-between space-x-4 mt-4">
                        <form method="POST" action="/produits/supprimer" class="w-1/4">
                            <input type="hidden" name="action" value="vider">
                            <button type="submit" class="py-2 px-4 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg shadow-md w-full h-12 flex items-center justify-center">
                                <i class="fas fa-trash mr-2"></i> 
                            </button>
                        </form>
                        <?php if ($utilisateurEstConnecte): 
                            $utilisateurId = $_SESSION['id_utilisateur']; 
                        ?>
                            <form id="commande-form" method="POST" action="/commande" class="w-1/4">
                                <input type="hidden" name="id_utilisateur" value="<?= $utilisateurId ?? '' ?>">
                                <input type="hidden" id="commande-prix-total" name="prix_total" value="<?= $totalPanier ?>">
                                <div id="commande-produits">
                                    <?php foreach ($_SESSION['panier'] as $id => $produit): ?>
                                        <input type="hidden" name="produits[<?= $id ?>][id_produit]" value="<?= $id ?>">
                                        <input type="hidden" name="produits[<?= $id ?>][quantite]" class="commande-quantite" data-produit-id="<?= $id ?>" value="<?= $produit['quantite'] ?>">
                                        <input type="hidden" name="produits[<?= $id ?>][prix_unitaire]" value="<?= $produit['prix_unitaire'] ?>">
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="py-2 px-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg shadow-md w-full h-12 flex items-center justify-center">
                                    <i class="fas fa-shopping-cart mr-2"></i> 
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="GET" action="/login" class="w-1/4">
                                <button type="submit" class="py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-md w-full h-12 flex items-center justify-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i> 
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-600">Votre panier est vide.</p>
                <?php endif; ?>
            </div>
            <!-- Section des Produits -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-5">
                <?php
                    if (!empty($produits)): ?>
                        <?php foreach ($produits as $produit): ?>
                            <?php
                                $idProduit = $produit['id_produit'];
                                $nom = htmlspecialchars($produit['nom'] ?? 'Nom indisponible');
                                $prix = $produit['prix_unitaire'] ?? 0;
                                $cheminImage = htmlspecialchars($produit['chemin_image'] ?? 'public/uploads/default_image.png');
                                $promoType = $produit['promo_type'] ?? null;
                                $promoValeur = $produit['promo_valeur'] ?? null;
                                $quantiteStock = $produit['quantite'] ?? 0;
                                $descripton = $produit['description'] ?? null;
                                $couleurs = $produit['couleurs'] ?? null;
                                $prixReduit = $prix;
                                if ($promoType === 'pourcentage') {
                                    $prixReduit = $prix - ($prix * $promoValeur / 100);
                                } elseif ($promoType === 'fixe') {
                                    $prixReduit = max(0, $prix - $promoValeur);
                                }
                            ?>
                            <div class="border rounded shadow-lg p-4 bg-white">
                                <img src="/public/<?= $cheminImage ?>" 
                                class="w-full h-48 object-cover mb-4 rounded-[10px] shadow-md transition-transform transform hover:scale-105 hover:shadow-lg cursor-pointer" 
                                onclick="openModal('modal-<?= $idProduit ?>')">
                                <h3 class="text-xl font-bold"><?= $nom ?></h3>
                                <p class="text-gray-600">
                                    <?php if ($promoType): ?>
                                        <span class="line-through text-red-500"><?= number_format($prix, 2) ?> $</span>
                                        <span class="text-green-500"><?= number_format($prixReduit, 2) ?> $</span>
                                    <?php else: ?>
                                        <?= number_format($prix, 2) ?> $
                                    <?php endif; ?>
                                </p>
                                <form method="POST" action="/produits/panier">
                                    <input type="hidden" name="id_produit" value="<?= $idProduit ?>">
                                    <input type="hidden" name="nom" value="<?= $nom ?>">
                                    <input type="hidden" name="prix_unitaire" value="<?= $prix ?>">
                                    <input type="hidden" name="prix_reduit" value="<?= $prixReduit ?>">
                                    <input type="hidden" name="promo_type" value="<?= $promoType ?>">
                                    <input type="hidden" name="promo_valeur" value="<?= $promoValeur ?>">
                                    <input type="hidden" name="action" value="ajouter">
                                    <label for="quantite" class="block text-sm font-medium text-gray-700">Quantité</label>
                                    <input type="number" name="quantite" id="quantite" value="1" min="1" max="<?= $quantiteStock ?>" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <button type="submit" class="mt-4 w-full py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md flex items-center justify-center">
                                        <i class="fas fa-cart-plus mr-2"></i> 
                                    </button>
                                </form>
                            </div>
                            <!-- Modal -->
                            <div id="modal-<?= $idProduit ?>" 
                                class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
                                <div class="bg-white w-full max-w-2xl rounded-lg shadow-lg p-8 relative">
                                    <!-- Bouton de fermeture -->
                                    <button onclick="closeModal('modal-<?= $idProduit ?>')" 
                                            class="absolute top-4 right-4 text-gray-600 hover:text-red-600 text-2xl">
                                        ✕
                                    </button>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <img src="/public/<?= $cheminImage ?>" class="w-full h-auto rounded-lg shadow-md">
                                        <div>
                                            <h2 class="text-2xl font-bold mb-4"><?= $nom ?></h2>
                                            <p class="text-gray-700 mb-2">
                                                <strong>Prix :</strong> <?= number_format($prix, 2) ?> $
                                            </p>
                                            <?php if ($promoType): ?>
                                                <p class="text-green-500 mb-4">
                                                    <strong>Prix réduit :</strong> <?= number_format($prixReduit, 2) ?> $
                                                </p>
                                            <?php endif; ?>
                                            <p class="text-gray-700 mb-2">
                                                <strong>Stock Disponible :</strong> <?= $quantiteStock ?> 
                                            </p>
                                            <div>
                                                <p class="text-gray-600 mb-4"><strong>Couleurs disponibles</strong></p>
                                                <div class="mt-2 grid grid-cols-3 gap-4">
                                                    <?php
                                                    // Tableau des couleurs avec leurs classes Tailwind
                                                    $couleurs = [
                                                        'Rouge' => 'bg-red-500',
                                                        'Bleu' => 'bg-blue-500',
                                                        'Vert' => 'bg-green-500',
                                                        'Noir' => 'bg-black text-white',
                                                        'Blanc' => 'bg-white border-gray-300 text-gray-700',
                                                        'Gris' => 'bg-gray-500 text-white',
                                                        'Jaune' => 'bg-yellow-500',
                                                        'Rose' => 'bg-pink-500',
                                                        'Marron' => 'bg-amber-700'
                                                    ];

                                                    // Vérifier si la chaîne des couleurs est bien définie et la décoder
                                                    if (isset($produit['couleurs']) && !empty($produit['couleurs'])) {
                                                        $couleursProduit = json_decode($produit['couleurs'], true);  // Décode en tableau

                                                        // Vérifier si le résultat du json_decode est bien un tableau
                                                        if (is_array($couleursProduit)) {
                                                            foreach ($couleursProduit as $couleur) {
                                                                // Vérifier que la couleur existe dans le tableau $couleurs
                                                                if (array_key_exists($couleur, $couleurs)) {
                                                                    $classeCouleur = $couleurs[$couleur]; // Récupérer la classe Tailwind correspondante
                                                                    echo "
                                                                    <span class='inline-block px-4 py-2 $classeCouleur text-white rounded-md shadow-md'>
                                                                        $couleur
                                                                    </span>";
                                                                }
                                                            }
                                                        } else {
                                                            // Si json_decode échoue, afficher un message d'erreur ou ignorer
                                                            echo "<p>Erreur de format des couleurs disponibles.</p>";
                                                        }
                                                    } else {
                                                        echo "<p>Aucune couleur disponible.</p>";
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <p class="text-gray-600 mb-4">
                                                <strong>Description :</strong> <?= $descripton ?> 
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-600">Aucun produit disponible.</p>
                    <?php endif;
                ?>
            </div>

            <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="flex justify-center mt-8 mb-8">
                <nav class="flex items-center space-x-1">
                    <!-- Bouton Précédent -->
                    <?php if ($pagination['page'] > 1): ?>
                        <a href="?page=<?= $pagination['page'] - 1 ?>" class="px-3 py-1 rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200">
                            &laquo; Précédent
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1 text-gray-400">&laquo; Précédent</span>
                    <?php endif; ?>

                    <!-- Numéros de page -->
                    <?php 
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    // Afficher le premier numéro de page si nécessaire
                    if ($startPage > 1): ?>
                        <a href="?page=1" class="px-3 py-1 rounded-md hover:bg-gray-200">1</a>
                        <?php if ($startPage > 2) echo '<span class="px-1">...</span>'; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $pagination['page']): ?>
                            <span class="px-3 py-1 rounded-md bg-blue-500 text-white"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>" class="px-3 py-1 rounded-md hover:bg-gray-200"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Afficher le dernier numéro de page si nécessaire -->
                    <?php if ($endPage < $pagination['totalPages']): ?>
                        <?php if ($endPage < $pagination['totalPages'] - 1) echo '<span class="px-1">...</span>'; ?>
                        <a href="?page=<?= $pagination['totalPages'] ?>" class="px-3 py-1 rounded-md hover:bg-gray-200"><?= $pagination['totalPages'] ?></a>
                    <?php endif; ?>

                    <!-- Bouton Suivant -->
                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a href="?page=<?= $pagination['page'] + 1 ?>" class="px-3 py-1 rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200">
                            Suivant &raquo;
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1 text-gray-400">Suivant &raquo;</span>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div> 
        <!-- Intégration du Chatbot -->
        <link rel="stylesheet" href="/chatbot/chatbot.css">
        <script src="/chatbot/chatbot.js" defer></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Intégration du Chatbot -->
        <div id="chatbot-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
            <div id="chatbot-trigger" style="background-color: #3b82f6; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <i class="fas fa-robot"></i>
            </div>
            <div id="chatbot-body" style="display: none; background-color: white; border: 1px solid #e5e7eb; border-radius: 10px; width: 350px; height: 500px; flex-direction: column; position: absolute; bottom: 70px; right: 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div style="background-color: #3b82f6; color: white; padding: 12px 15px; border-top-left-radius: 10px; border-top-right-radius: 10px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                    Assistant de vente
                    <i id="chatbot-close" class="fas fa-times"></i>
                </div>
                <div id="chatbot-messages" style="flex-grow: 1; overflow-y: auto; padding: 15px;">
                    <div class="message bot-message" style="background-color: #f3f4f6; color: #1f2937; margin-right: auto; margin-bottom: 12px; max-width: 80%; padding: 8px 12px; border-radius: 15px; font-size: 14px; line-height: 1.4; border-bottom-left-radius: 5px;">
                        Bonjour ! Je suis votre assistant de vente. Comment puis-je vous aider aujourd'hui ?
                    </div>
                </div>
                <div style="display: flex; padding: 10px; border-top: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <input type="text" id="chatbot-input" placeholder="Tapez votre message..." style="flex-grow: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 20px; outline: none; font-size: 14px;">
                    <button id="chatbot-send" style="background-color: #3b82f6; color: white; border: none; border-radius: 20px; padding: 8px 15px; margin-left: 8px; cursor: pointer; transition: background-color 0.2s;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .typing-indicator {
                display: flex;
                padding: 10px;
            }
            .typing-indicator span {
                height: 8px;
                width: 8px;
                margin: 0 2px;
                background-color: #9ca3af;
                border-radius: 50%;
                display: inline-block;
                animation: bounce 1.3s infinite ease-in-out;
            }
            .typing-indicator span:nth-child(2) { animation-delay: 0.15s; }
            .typing-indicator span:nth-child(3) { animation-delay: 0.3s; }
            @keyframes bounce {
                0%, 60%, 100% { transform: translateY(0); }
                30% { transform: translateY(-5px); }
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatbotTrigger = document.getElementById('chatbot-trigger');
            const chatbotBody = document.getElementById('chatbot-body');
            const chatbotClose = document.getElementById('chatbot-close');
            const chatInput = document.getElementById('chatbot-input');
            const chatSend = document.getElementById('chatbot-send');
            const chatMessages = document.getElementById('chatbot-messages');
            let isOpen = false;

            // Ouvrir/fermer le chat
            function toggleChat() {
                isOpen = !isOpen;
                chatbotBody.style.display = isOpen ? 'flex' : 'none';
                if (isOpen) {
                    chatInput.focus();
                }
            }

            chatbotTrigger.addEventListener('click', toggleChat);
            chatbotClose.addEventListener('click', toggleChat);

            // Envoyer un message
            function sendMessage() {
                const message = chatInput.value.trim();
                if (!message) return;

                // Ajouter le message de l'utilisateur
                addMessage(message, 'user');
                chatInput.value = '';

                // Afficher l'indicateur de frappe
                const typingIndicator = document.createElement('div');
                typingIndicator.className = 'typing-indicator';
                typingIndicator.innerHTML = '<span></span><span></span><span></span>';
                chatMessages.appendChild(typingIndicator);
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Envoyer au serveur Python
                fetch('http://localhost:5000/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(response => response.json())
                .then(data => {
                    // Supprimer l'indicateur de frappe
                    chatMessages.removeChild(typingIndicator);
                    
                    // Afficher la réponse du bot
                    if (data.response) {
                        addMessage(data.response, 'bot');
                    } else {
                        addMessage('Désolé, je n\'ai pas pu traiter votre demande.', 'bot');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    chatMessages.removeChild(typingIndicator);
                    addMessage('Une erreur est survenue. Veuillez réessayer plus tard.', 'bot');
                });
            }

            // Ajouter un message à la conversation
            function addMessage(text, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}-message`;
                messageDiv.style.cssText = `
                    background-color: ${sender === 'user' ? '#3b82f6' : '#f3f4f6'};
                    color: ${sender === 'user' ? 'white' : '#1f2937'};
                    margin-${sender === 'user' ? 'left' : 'right'}: auto;
                    margin-bottom: 12px;
                    max-width: 80%;
                    padding: 8px 12px;
                    border-radius: 15px;
                    font-size: 14px;
                    line-height: 1.4;
                    border-bottom-${sender === 'user' ? 'right' : 'left'}-radius: 5px;
                `;
                messageDiv.textContent = text;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Gestionnaires d'événements
            chatSend.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
        </script>
    </body>
    <script>
        // Fonction pour mettre à jour les totaux et les champs du formulaire de commande
        function updateTotals() {
            var total = 0;
            var totalItems = 0;
            var commandeForm = document.getElementById('commande-form');
            var commandeProduitsDiv = document.getElementById('commande-produits');
            
            // Réinitialiser le contenu du div des produits
            commandeProduitsDiv.innerHTML = '';
            
            // Parcourir toutes les lignes du panier
            var rows = document.querySelectorAll('tr[data-product-id]');
            
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var productId = row.getAttribute('data-product-id');
                
                // Récupérer la quantité et le prix unitaire
                var quantityInput = row.querySelector('input.quantity-input');
                if (!quantityInput) continue;
                
                var quantity = parseInt(quantityInput.value) || 0;
                var unitPrice = parseFloat(row.getAttribute('data-unit-price')) || 0;
                var totalPrice = quantity * unitPrice;
                
                // Mettre à jour le total de la ligne
                var totalCell = row.querySelector('.total-price');
                if (totalCell) {
                    totalCell.textContent = totalPrice.toFixed(2) + ' $';
                }
                
                // Ajouter les champs cachés pour le formulaire de commande
                if (commandeForm && quantity > 0) {
                    // Créer les champs pour le produit
                    var idProduitInput = document.createElement('input');
                    idProduitInput.type = 'hidden';
                    idProduitInput.name = 'produits[' + productId + '][id_produit]';
                    idProduitInput.value = productId;
                    
                    var quantiteInput = document.createElement('input');
                    quantiteInput.type = 'hidden';
                    quantiteInput.name = 'produits[' + productId + '][quantite]';
                    quantiteInput.value = quantity;

                    var prixInput = document.createElement('input');
                    prixInput.type = 'hidden';
                    prixInput.name = 'produits[' + productId + '][prix_unitaire]';
                    prixInput.value = unitPrice;

                    var nomInput = document.createElement('input');
                    nomInput.type = 'hidden';
                    nomInput.name = 'produits[' + productId + '][nom]';
                    nomInput.value = row.querySelector('td:first-child').textContent.trim();
                    
                    // Ajouter les champs au formulaire
                    commandeProduitsDiv.appendChild(idProduitInput);
                    commandeProduitsDiv.appendChild(quantiteInput);
                    commandeProduitsDiv.appendChild(prixInput);
                    commandeProduitsDiv.appendChild(nomInput);
                    
                    total += totalPrice;
                    totalItems += quantity;
                }
            }
            
            // Mettre à jour le total général dans le formulaire
            if (commandeForm) {
                var totalInput = document.getElementById('commande-prix-total');
                if (!totalInput) {
                    totalInput = document.createElement('input');
                    totalInput.type = 'hidden';
                    totalInput.id = 'commande-prix-total';
                    totalInput.name = 'prix_total';
                    commandeForm.insertBefore(totalInput, commandeForm.firstChild);
                }
                totalInput.value = total.toFixed(2);
            }
            
            // Mettre à jour l'affichage du total général
            var totalElement = document.querySelector('.total-cart');
            if (totalElement) {
                totalElement.textContent = total.toFixed(2) + ' $';
            }
            
            // Mettre à jour le nombre total d'articles
            var quantityElement = document.querySelector('.total-quantity');
            if (quantityElement) {
                quantityElement.textContent = totalItems + ' article' + (totalItems !== 1 ? 's' : '');
            }
            
            return total;
        }

        // Gérer la modification des quantités
        function handleQuantityChange(e) {
            var input = e.target;
            var value = parseInt(input.value);
            
            // Validation
            if (isNaN(value) || value < 1) {
                input.value = 1;
            } else if (value > 100) {
                input.value = 100;
            }
            
            // Mettre à jour les totaux
            updateTotals();
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Mettre à jour les totaux initiaux
            updateTotals();
            
            // Ajouter les écouteurs d'événements sur les champs de quantité
            var quantityInputs = document.querySelectorAll('.quantity-input');
            for (var i = 0; i < quantityInputs.length; i++) {
                quantityInputs[i].addEventListener('input', handleQuantityChange);
                quantityInputs[i].addEventListener('change', handleQuantityChange);
            }
            
            // Gestion de la soumission du formulaire de commande
            var commandeForm = document.getElementById('commande-form');
            if (commandeForm) {
                commandeForm.addEventListener('submit', function(e) {
                    // Empêcher la soumission par défaut
                    e.preventDefault();
                    
                    // Mettre à jour les totaux avant la soumission
                    updateTotals();
                    
                    // Créer un objet FormData pour la soumission
                    var formData = new FormData(this);
                    
                    // Envoyer la requête AJAX
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.text();
                    })
                    .then(() => {
                        // Rediriger vers la page de profil après une commande réussie
                        window.location.href = '/mon_profile';
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la commande. Veuillez réessayer.');
                    });
                });
            }
        });

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
    </script>
</html>