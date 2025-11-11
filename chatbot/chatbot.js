class Chatbot {
    constructor() {
        this.isOpen = false;
        this.initializeElements();
        this.attachEventListeners();
    }

    initializeElements() {
        this.chatbotContainer = document.createElement('div');
        this.chatbotContainer.className = 'chatbot-container';
        
        this.chatbotTrigger = document.createElement('div');
        this.chatbotTrigger.className = 'chatbot-trigger';
        this.chatbotTrigger.innerHTML = '<i class="fas fa-robot"></i>';
        
        this.chatbotBody = document.createElement('div');
        this.chatbotBody.className = 'chatbot-body';
        
        this.chatbotHeader = document.createElement('div');
        this.chatbotHeader.className = 'chatbot-header';
        this.chatbotHeader.innerHTML = 'Assistant de vente <i class="fas fa-times"></i>';
        
        this.chatbotMessages = document.createElement('div');
        this.chatbotMessages.className = 'chatbot-messages';
        
        this.chatbotInput = document.createElement('div');
        this.chatbotInput.className = 'chatbot-input';
        this.chatbotInput.innerHTML = `
            <input type="text" placeholder="Tapez votre message..." id="chatbot-message-input">
            <button id="chatbot-send"><i class="fas fa-paper-plane"></i></button>
        `;
        
        this.chatbotBody.appendChild(this.chatbotHeader);
        this.chatbotBody.appendChild(this.chatbotMessages);
        this.chatbotBody.appendChild(this.chatbotInput);
        
        this.chatbotContainer.appendChild(this.chatbotTrigger);
        this.chatbotContainer.appendChild(this.chatbotBody);
        
        document.body.appendChild(this.chatbotContainer);
        
        // Récupérer les références des éléments après leur création
        this.messageInput = document.getElementById('chatbot-message-input');
        this.sendButton = document.getElementById('chatbot-send');
        this.closeButton = this.chatbotHeader.querySelector('.fa-times');
    }

    attachEventListeners() {
        // Ouvrir/fermer le chat
        this.chatbotTrigger.addEventListener('click', () => this.toggleChat());
        this.closeButton.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleChat();
        });

        // Envoyer un message avec le bouton
        this.sendButton.addEventListener('click', () => this.sendMessage());
        
        // Envoyer un message avec la touche Entrée
        this.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Message de bienvenue
        this.addBotMessage('Bonjour ! Je suis votre assistant de vente. Comment puis-je vous aider aujourd\'hui ?');
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.chatbotBody.style.display = 'flex';
            this.chatbotTrigger.style.display = 'none';
            this.messageInput.focus();
        } else {
            this.chatbotBody.style.display = 'none';
            this.chatbotTrigger.style.display = 'flex';
        }
    }

    addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.textContent = message;
        this.chatbotMessages.appendChild(messageDiv);
        this.scrollToBottom();
    }

    addBotMessage(message) {
        this.addMessage(message, false);
    }

    addUserMessage(message) {
        this.addMessage(message, true);
    }

    scrollToBottom() {
        this.chatbotMessages.scrollTop = this.chatbotMessages.scrollHeight;
    }

    async sendMessage() {
        const message = this.messageInput.value.trim();
        if (!message) return;

        // Ajouter le message de l'utilisateur
        this.addUserMessage(message);
        this.messageInput.value = '';

        // Afficher un indicateur de frappe
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'message bot-message typing-indicator';
        typingIndicator.innerHTML = '<span></span><span></span><span></span>';
        this.chatbotMessages.appendChild(typingIndicator);
        this.scrollToBottom();

        try {
            const response = await fetch('http://localhost:5000/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();
            
            // Supprimer l'indicateur de frappe
            this.chatbotMessages.removeChild(typingIndicator);
            
            // Afficher la réponse du bot
            if (data && data.response) {
                this.addBotMessage(data.response);
            } else if (data && data.error) {
                console.error('Erreur du serveur:', data.error);
                this.addBotMessage('Désolé, une erreur est survenue : ' + (data.details || 'Erreur inconnue'));
            } else {
                console.error('Réponse inattendue du serveur:', data);
                this.addBotMessage('Désolé, je n\'ai pas pu traiter votre demande.');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.chatbotMessages.removeChild(typingIndicator);
            this.addBotMessage('Une erreur est survenue. Veuillez réessayer plus tard.');
        }
    }
}

// Initialiser le chatbot lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    const chatbot = new Chatbot();
});
