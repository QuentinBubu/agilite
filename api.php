<?php
header('Content-Type: application/json');

// 1. VOTRE CLÉ API ICI (sécurisée côté serveur)
$apiKey = 'VOTRE_CLE_API'; 
// Utilisation du modèle standard le plus stable
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=' . $apiKey;

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'chat'; // 'chat' ou 'pipeline'
$history = $input['history'] ?? [];

if (!in_array($action, ['chat', 'pipeline'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Action invalide.']);
    exit;
}

if (!is_array($history) || empty($history)) {
    http_response_code(400);
    echo json_encode(['error' => 'Historique vide.']);
    exit;
}

if ($action === 'chat') {
    // MODE CHAT : Économique, l'Agent PO pose juste des questions.
    $systemInstruction = "Tu es l'Agent PO. Pose une seule question courte et claire à la fois pour comprendre les utilisateurs, le but et les contraintes. Réponds en moins de 100 mots. Ne génère jamais le backlog.";
} else {
    // MODE PIPELINE : les trois agents travaillent dans un seul appel pour éviter trois attentes réseau.
    $systemInstruction = "Tu es un pipeline de trois agents. En lisant l'historique, exécute ces étapes et retourne un compte rendu concis :
    
    [AGENT 1 : PO] 
    - Rédige le cahier des charges fonctionnel et technique basé sur la conversation.
    
    [AGENT 2 : BACKLOG]
    - Transforme les besoins en véritable backlog structuré, pas en simple liste de phrases.
    - Regroupe les stories par épic fonctionnel (par exemple « Gestion du compte »).
    - Pour chaque épic, détaille les User Stories atomiques et indépendantes au format « En tant que..., je veux..., afin de... ».
    - Décompose chaque besoin large en actions concrètes : par exemple, « gérer mon compte » devient consulter, modifier, changer le mot de passe et supprimer le compte.
    - Pour chaque User Story, ajoute : identifiant, priorité (Must/Should/Could), estimation en Story Points et 2 à 4 critères d'acceptation vérifiables.
    - N'oublie pas les règles métier, les cas d'erreur et les droits d'accès importants.
    - Après le détail des épics, ajoute obligatoirement une section « Tableau récapitulatif du backlog » sous forme de tableau Markdown avec une ligne par User Story et les colonnes : Epic, ID, User Story, Priorité, Story Points, Critères d'acceptation résumés, Dépendances.
    - Le tableau doit être lisible et cohérent avec le détail qui le précède. N'y mets pas de ligne vide et ne fusionne pas plusieurs User Stories dans une même ligne.
    
    [AGENT 3 : SPRINT MANAGER]
    - Regroupe ces User Stories dans des sprints réalistes de 1 à 2 semaines.
    
    Il n'y a pas d'Agent 4. N'invente pas d'étape supplémentaire. Sépare les trois sections avec les titres Markdown ## Agent 1, ## Agent 2 et ## Agent 3. Réponds en moins de 3500 mots.";
}

$payload = [
    "system_instruction" => [ "parts" => [ ["text" => $systemInstruction] ] ],
    "contents" => $history,
    "generationConfig" => [
        "temperature" => $action === 'chat' ? 0.4 : 0.2,
        "maxOutputTokens" => $action === 'chat' ? 256 : 6144
    ]
];
$encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
if ($encodedPayload === false) {
    http_response_code(400);
    echo json_encode(['error' => 'La requête contient des données invalides.']);
    exit;
}

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedPayload);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, $action === 'chat' ? 20 : 60);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    $isTimeout = $curlError !== '' && (
        str_contains($curlError, 'timed out') ||
        str_contains($curlError, 'Timeout')
    );
    http_response_code($isTimeout ? 504 : 502);
    echo json_encode([
        'error' => $isTimeout
            ? 'Gemini est trop lent pour le moment. Réessayez dans quelques secondes.'
            : 'Impossible de contacter le service Gemini.',
        'details' => $curlError
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode > 0 ? $httpCode : 502);
    echo $response;
    exit;
}

http_response_code($httpCode);
echo $response;
?>