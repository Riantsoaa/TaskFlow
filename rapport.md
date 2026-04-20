# Rapport de Projet : TaskFlow

**Auteur :** ANDRINIAINA Riantsoa  
**Vague :** V24  
**Titre :** Application web de gestion de tâches, de rappels et de notes personnelles

## 1. Objectifs du Projet
TaskFlow est une application web dynamique conçue pour aider les utilisateurs (étudiants, professionnels, etc.) à organiser leur quotidien. Les principaux objectifs atteints sont :
- Gestion complète des tâches (CRUD).
- Système de rappels pour les tâches exceptionnelles avec alertes visuelles.
- Prise de notes personnelles simplifiée.
- Interface moderne, élégante et responsive.
- Sécurisation des données via authentification.

## 2. Architecture Technique
L'application repose sur une architecture client-serveur classique utilisant des technologies web standards :

- **Front-end :**
    - HTML5 / CSS3 (Flexbox & Grid) pour la structure et le design.
    - JavaScript (Vanilla) pour l'interactivité, les appels AJAX et le système de rappels.
- **Back-end :**
    - PHP (Procédural) pour la logique métier et la gestion des sessions.
    - MySQL pour le stockage persistant des données.
- **Sécurité :**
    - Hachage des mots de passe avec `password_hash()`.
    - Protection contre les injections SQL via Prepared Statements (PDO).

## 3. Schéma de la Base de Données
La base de données `taskflow` contient trois tables principales :
- `users` : Stocke les informations des utilisateurs.
- `tasks` : Gère les tâches, leur statut et les rappels.
- `notes` : Gère les notes personnelles.

## 4. Fonctionnalités Implémentées

### A. Authentification
Un système complet d'inscription et de connexion permettant à chaque utilisateur d'avoir un espace privé et sécurisé.

### B. Gestion des Tâches
Les utilisateurs peuvent ajouter des tâches, les marquer comme terminées ou les supprimer. Les tâches exceptionnelles sont mises en évidence visuellement via un dégradé violet et une bordure distinctive.

### C. Système de Rappels
Grâce à un "Timer" JavaScript, l'application vérifie en temps réel (côté client) si une tâche exceptionnelle arrive à échéance et affiche une alerte `alert()` le cas échéant.

### D. Prise de Notes
Un espace dédié pour enregistrer des idées ou des informations importantes, trié par date de création.

## 5. Guide d'Installation (WAMPP)
1. Copier le dossier `TaskFlow` dans `C:\wamp64\www\`.
2. Créer une base de données nommée `taskflow` dans phpMyAdmin.
3. Importer le fichier `gestion.sql` fourni.
4. Lancer l'application via `http://localhost/TaskFlow`.

---
*Ce projet démontre une maîtrise complète du cycle de développement web full-stack sans dépendances externes.*
