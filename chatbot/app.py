from flask import Flask, request, jsonify
from flask_cors import CORS, cross_origin
import random

app = Flask(__name__)
CORS(app, resources={
    r"/api/*": {
        "origins": "*",
        "methods": ["GET", "POST", "OPTIONS"],
        "allow_headers": ["Content-Type"]
    }
})

print("Démarrage du chatbot en mode simple...")

# Réponses prédéfinies en français
RESPONSES = [
    "Bonjour ! Comment puis-je vous aider aujourd'hui ?",
    "Je suis là pour vous aider avec vos questions.",
    "Pourriez-vous préciser votre demande ?",
    "Je peux vous aider avec les informations sur nos produits.",
    "Avez-vous des questions sur nos services ?",
    "Je suis votre assistant commercial virtuel.",
    "Comment puis-je vous assister ?",
    "Bienvenue sur notre site de e-commerce !"
]

# Modèle de conversation étendu
conversation = {
    # Salutations
    "bonjour": "Bonjour ! Comment puis-je vous aider aujourd'hui ?",
    "salut": "Salut ! Comment puis-je vous assister ?",
    "coucou": "Coucou ! En quoi puis-je vous aider ?",
    "hello": "Hello ! Comment puis-je vous être utile ?",
    
    # Remerciements
    "merci": "Je vous en prie ! N'hésitez pas si vous avez d'autres questions.",
    "merci beaucoup": "Avec plaisir ! N'hésitez pas à revenir vers moi pour plus d'informations.",
    
    # Au revoir
    "aurevoir": "Au revoir ! N'hésitez pas à revenir si vous avez d'autres questions.",
    "bye": "À bientôt ! Passez une excellente journée !",
    "à plus": "À plus tard ! N'hésitez pas à revenir nous voir !",
    
    # Produits électroniques
    "produits": "Nous sommes spécialisés dans les produits électroniques haut de gamme. Voici nos principales catégories :\n- Smartphones et tablettes\n- Ordinateurs portables et PC\n- Téléviseurs et home cinéma\n- Audio et casques\n- Accessoires gaming\n- Objets connectés\n\nQuelle catégorie vous intéresse ?",
    "vendez": "Nous sommes fiers de proposer une large sélection de produits électroniques :\n\n🎮 Gaming :\n- PC Gamer sur mesure\n- Consoles de jeux\n- Écrans 144Hz et 240Hz\n- Sièges et accessoires gaming\n\n📱 Mobilité :\n- Derniers smartphones\n- Tablettes tactiles\n- Montres connectées\n- Accessoires mobiles\n\n💻 Informatique :\n- Ordinateurs portables\n- PC de bureau\n- Composants PC\n- Périphériques\n\n📺 TV & Son :\n- TV 4K/8K\n- Barres de son\n- Enceintes connectées\n- Casques audio\n\n🏠 Maison connectée :\n- Enceintes intelligentes\n- Éclairage connecté\n- Sécurité\n- Automatisation\n\nQuel type de produit souhaitez-vous découvrir ?",
    "articles": "Nous proposons plus de 10 000 références en électronique. Voici nos meilleures ventes :\n\n🔥 Top ventes :\n- Smartphone X-Pro Max\n- PC Portable Ultrabook ZX\n- Casque audio sans fil Elite\n- TV QLED 4K 55\"\n- Console de jeu NextGen\n\nSouhaitez-vous des détails sur un produit en particulier ?",
    
    # Marques
    "marques": "Nous travaillons avec les plus grandes marques :\n\n- Samsung, Apple, Google, Sony\n- LG, Asus, Acer, MSI\n- Bose, JBL, Sennheiser\n- Logitech, Razer, Corsair\n- Philips, TP-Link, Netgear\n\nAvez-vous une préférence de marque ?",
    
    # Spécifications techniques
    "caractéristiques": "Pour vous conseiller au mieux, pourriez-vous préciser ce que vous recherchez comme caractéristiques ? Par exemple :\n- Taille d'écran\n- Capacité de stockage\n- Mémoire vive (RAM)\n- Processeur\n- Autonomie\n\nCela m'aidera à vous proposer les produits les plus adaptés à vos besoins.",
    "spécifications": "Voici comment comparer les produits :\n\n💾 Stockage : 128GB à 2TB\n📱 RAM : 4GB à 64GB\n⚡ Processeurs : Intel i3 à i9, AMD Ryzen\n🖥️ Écrans : 13\" à 17\" pour portables, 24\" à 85\" pour TV\n🔋 Autonomie : Jusqu'à 20h pour les appareils mobiles\n\nQuelles spécifications sont importantes pour vous ?",
    
    # Promotions
    "promo": "Nous avons actuellement des promotions sur plusieurs produits. Voulez-vous que je vous montre nos offres du moment ?",
    "solde": "Les soldes sont en cours ! Découvrez nos réductions exceptionnelles sur une sélection d'articles.",
    "réduction": "Nous proposons régulièrement des réductions. Voulez-vous que je vous informe des meilleures offres ?",
    
    # Prix et promotions
    "prix": "Nos prix sont compétitifs et varient selon les gammes :\n\n💰 Économique : 100€ - 300€\n💎 Milieu de gamme : 300€ - 800€\n🚀 Haut de gamme : 800€ et +\n\nNous proposons également des offres spéciales pour les étudiants et la livraison est gratuite à partir de 50€ d'achat !",
    "cher": "Nous avons des produits pour tous les budgets :\n\n- Entrée de gamme : idéal pour un usage occasionnel\n- Milieu de gamme : bon rapport qualité/prix\n- Haut de gamme : performances optimales\n\nQuelle gamme vous conviendrait le mieux ?",
    "coût": "Le coût dépend des fonctionnalités recherchées. Par exemple :\n\n💻 Ordinateurs portables :\n- Basique : 300-600€\n- Polyvalent : 600-1200€\n- Professionnel : 1200€+\n\n📱 Smartphones :\n- Entrée de gamme : 100-300€\n- Milieu de gamme : 300-700€\n- Premium : 700€+\n\nAvez-vous un budget en tête ?",
    "promo": "🔥 PROMOTIONS DU MOMENT 🔥\n\n- Réduction de 20% sur toute la gamme gaming\n- Écouteurs sans fil à -30%\n- Offre spéciale : PC portable + souris + sac à dos\n- Livraison gratuite sur tout le site\n\nVoulez-vous que je vous montre les offres d'une catégorie en particulier ?",
    
    # Localisation
    "local": "Notre entreprise est basée à Paris, mais nous livrons dans le monde entier !",
    "adresse": "Notre siège social est situé au 123 Rue du Commerce, 75001 Paris. Nous sommes ouverts du lundi au samedi de 9h à 19h.",
    "trouver": "Vous pouvez nous trouver au 123 Rue du Commerce, 75001 Paris. Avez-vous besoin d'indications pour venir ?",
    "paris": "Oui, nous sommes basés à Paris. Notre magasin principal se trouve au 123 Rue du Commerce, 75001 Paris.",
    
    # Livraison
    "livraison": "Nous proposons différentes options de livraison : standard (3-5 jours), express (1-2 jours) et point relais. Laquelle vous intéresse ?",
    "livrer": "Nous livrons partout en France métropolitaine. Les délais de livraison varient de 1 à 5 jours ouvrés selon l'option choisie.",
    "frais de port": "Les frais de port dépendent du montant de votre commande et du mode de livraison choisi. La livraison est offerte à partir de 50€ d'achat !",
    
    # Paiement
    "paiement": "Nous acceptons les cartes bancaires, PayPal, virements bancaires et le paiement à la livraison. Quelle méthode préférez-vous ?",
    "payer": "Vous pouvez régler votre commande par carte bancaire, PayPal, virement ou à la livraison. Quelle option vous convient le mieux ?",
    "carte bancaire": "Nous acceptons toutes les cartes bancaires majeures (Visa, Mastercard, etc.) ainsi que les paiements sécurisés via PayPal.",
    
    # Service client
    "aide": "Je peux vous aider à trouver des produits, vérifier les disponibilités, vous informer sur les promotions et répondre à vos questions. Comment puis-je vous aider ?",
    "contact": "Vous pouvez nous contacter par téléphone au 01 23 45 67 89 ou par email à contact@monsite.com. Notre service client est disponible du lundi au samedi de 9h à 19h.",
    "service client": "Notre service client est à votre écoute du lundi au samedi de 9h à 19h au 01 23 45 67 89 ou par email à contact@monsite.com.",
    "problème": "Je suis désolé que vous rencontriez un problème. Pouvez-vous me décrire ce qui ne va pas pour que je puisse vous aider au mieux ?",
    
    # Commandes
    "commande": "Pour suivre votre commande, j'aurai besoin de votre numéro de commande. L'avez-vous sous la main ?",
    "suivi": "Pour suivre votre commande, veuillez me fournir votre numéro de commande. Je vais vérifier son statut pour vous.",
    "retour": "Vous avez 14 jours pour retourner un article non utilisé et dans son emballage d'origine. Souhaitez-vous initier un retour ?"
}

def generate_response(user_input):
    if not user_input or not user_input.strip():
        return "Je n'ai pas reçu de message. Pouvez-vous répéter ?"
    
    # Nettoyer l'entrée
    user_input = user_input.lower().strip()
    
    # Vérifier les correspondances directes
    for keyword, response in conversation.items():
        if keyword in user_input:
            return response
    
    # Réponse aléatoire si aucune correspondance
    return random.choice(RESPONSES)

@app.route('/api/chat', methods=['POST', 'OPTIONS'])
@cross_origin()
def chat():
    if request.method == 'OPTIONS':
        print("OPTIONS request received")
        response = jsonify({"status": "ok"})
        response.headers.add('Access-Control-Allow-Origin', '*')
        response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
        response.headers.add('Access-Control-Allow-Methods', 'POST, OPTIONS')
        return response

    try:
        print("Headers:", request.headers)
        print("Content-Type:", request.content_type)
        print("Raw data:", request.get_data())
        
        data = request.get_json(force=True) if request.content_type == 'application/json' else {}
        print("Parsed JSON data:", data)
        
        user_message = data.get('message', '').strip()
        print(f"Message reçu: {user_message}")
        
        if not user_message:
            return jsonify({
                'response': 'Je ne peux pas répondre à un message vide.',
                'status': 'success'
            })
        
        # Générer une réponse
        bot_response = generate_response(user_message)
        print(f"Réponse générée: {bot_response}")
        
        return jsonify({
            'response': bot_response,
            'status': 'success'
        })
        
    except Exception as e:
        error_msg = f"Erreur dans la route /api/chat: {str(e)}"
        print(error_msg)
        return jsonify({
            'response': 'Désolé, je rencontre une difficulté. Pouvez-vous reformuler votre demande ?',
            'status': 'error',
            'details': error_msg
        })

if __name__ == '__main__':
    try:
        app.run(debug=True, port=5000, host='0.0.0.0')
    except Exception as e:
        print(f"Erreur critique: {e}")
        raise