# 🤖 Pipeline Multi-Agents Agile (Gemini API)

Ce projet est un générateur automatisé de gestion de projet Agile. Il remplace le rôle traditionnel du Product Owner et du Scrum Master par un pipeline d'agents IA propulsé par l'API Google Gemini. 

Le système interagit avec le client via un chat pour affiner le besoin, puis génère en toute autonomie le cahier des charges, le backlog (User Stories) et un découpage en sprints réalistes.

## ✨ Fonctionnalités

*   **Chatbot Product Owner :** Phase de cadrage interactive pour comprendre le besoin du client à moindre coût d'API.
*   **Pipeline Agentique Sérialisé :** Génération complète du projet en un seul appel API ("Cost-Killer") simulant 3 agents distincts :
    1.  *Agent PO* : Rédige le cahier des charges.
    2.  *Agent Backlog* : Crée les User Stories (format INVEST).
    3.  *Agent Sprint* : Planifie et regroupe en itérations de 1 à 2 semaines.
*   **Sécurité Back-End :** Masquage de la clé API Gemini via un proxy PHP.
*   **Anti-Surcharge (Retry) :** Relance automatique des requêtes côté front-end en cas de forte demande sur les serveurs Google.
*   **Rendu Markdown :** Interface propre avec génération de tableaux et formatage structuré via `marked.js`.
*   **Backlog détaillé :** L'Agent Backlog regroupe les besoins par épics et ajoute les stories atomiques, priorités, estimations, critères d'acceptation et un tableau récapitulatif.
*   **Export PDF :** Téléchargement du compte rendu final depuis l'interface avec titres colorés, listes et tableaux mis en page.

## 🛠️ Stack Technique

*   **Front-End :** HTML5, CSS3, Vanilla JavaScript, Marked.js (parsing Markdown), html2canvas et jsPDF (export PDF mis en forme).
*   **Back-End :** PHP 8+ (utilisation de cURL).
*   **IA :** API Google Gemini avec le modèle rapide `gemini-3.5-flash-lite`.

## 🚀 Installation & Lancement

1. **Prérequis :**
   * Un serveur local PHP (WampServer, XAMPP, Laragon...).
   * Une clé API Google Gemini (à générer sur [Google AI Studio](https://aistudio.google.com/)).

2. **Configuration :**
   * Clonez ce dépôt dans le dossier racine de votre serveur web (ex: `www` ou `htdocs`).
   * Ouvrez le fichier `api.php`.
   * Remplacez `'VOTRE_CLE_API_GEMINI_ICI'` par votre véritable clé API.

3. **Utilisation :**
   * Lancez votre serveur web local.
   * Accédez à l'application depuis votre navigateur : `http://localhost/nom-du-dossier/`
   * Discutez avec l'Agent PO, puis cliquez sur le bouton **"🚀 J'ai tout dit, générer le projet complet (Pipeline)"** pour déclencher la création du backlog.

## 📁 Architecture du projet

```text
/
├── index.html     # Interface utilisateur (Chat, Boutons, Rendu visuel)
├── api.php        # Proxy sécurisé pour communiquer avec l'API Gemini
└── README.md      # Documentation du projet