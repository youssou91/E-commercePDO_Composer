from flask import Flask, request, jsonify
from flask_cors import CORS
import openai
import os

app = Flask(__name__)
CORS(app)  # Active CORS pour toutes les routes

# Configuration d'OpenAI - Remplacez par votre clé API
openai.api_key = 'votre_cle_api_openai'

@app.route('/api/chat', methods=['POST'])
def chat():
    try:
        data = request.json
        user_message = data.get('message', '')
        
        if not user_message:
            return jsonify({'error': 'Le message est vide'}), 400
        
        # Appel à l'API OpenAI
        response = openai.ChatCompletion.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "Tu es un assistant de vente pour une boutique en ligne. Sois concis et utile."},
                {"role": "user", "content": user_message}
            ],
            max_tokens=150,
            temperature=0.7
        )
        
        bot_response = response.choices[0].message['content'].strip()
        return jsonify({'response': bot_response})
        
    except Exception as e:
        print(f"Erreur: {str(e)}")
        return jsonify({'error': 'Une erreur est survenue'}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)
