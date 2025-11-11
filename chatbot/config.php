<?php
// Configuration du chatbot
define('OPENAI_API_KEY', 'votre_cle_api_openai'); // Remplacez par votre clé API OpenAI

// Fonction pour appeler l'API OpenAI
function getChatbotResponse($message) {
    $apiKey = OPENAI_API_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Tu es un assistant de vente pour une boutique en ligne. Sois concis et utile.'
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'max_tokens' => 150,
        'temperature' => 0.7
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return 'Désolé, une erreur est survenue. Veuillez réessayer plus tard.';
    }
    
    curl_close($ch);
    
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        return $responseData['choices'][0]['message']['content'];
    } else {
        return 'Je ne peux pas répondre pour le moment. Veuillez réessayer plus tard.';
    }
}

// Gestion des requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json');
    $response = [
        'response' => getChatbotResponse($_POST['message'])
    ];
    echo json_encode($response);
    exit;
}
?>
