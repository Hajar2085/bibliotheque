# Symfony Library Management System
🔗 Liens Importants
APPLICATION DÉPLOYÉE (Live Demo) : CLIQUEZ ICI POUR VOIR LE SITE;

Repository GitHub : Lien vers ce repo(https://github.com/Hajar2085/bibliotheque)

## Installation

1.  **Prérequis**: PHP 8+, Composer, Symfony CLI.
2.  **Clone**: Clonez ce dépôt.
3.  **Dépendances**: Exécutez `composer install`.
4.  **Base de données**:
    *   Le projet est configuré pour utiliser Mysql par défaut pour faciliter le test (`.env`).
5.  **Mise en place**:
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    php bin/console doctrine:fixtures:load
    ```
6.  **Lancement**:
    ```bash
    symfony server:start
    ```
    Accédez à `http://127.0.0.1:8000`.

## Utilisateurs de Test

| Rôle | Email | Mot de passe |
| :--- | :--- | :--- |
| **Admin** | `hajar@gmail.com` | `000000` |
| **User** | `user@library.com` | `user123` |

## Fonctionnalités

*   **Authentification**: Inscription, Reset password, Connexion, Déconnexion.
*   **Livres**: Catalogue visible par tous. CRUD complet pour l'Admin.
*   **Emprunts**:
    *   Un utilisateur peut emprunter un livre s'il est en stock.
    *   Le stock diminue automatiquement.
    *   L'admin peut marquer un livre comme retourné (le stock augmente).
*   **Réservations**:
    *   Un utilisateur peut réserver un livre s'il n'est plus en stock.
    *   Si le livre est en stock, l'utilisateur est redirigé vers l'emprunt.
