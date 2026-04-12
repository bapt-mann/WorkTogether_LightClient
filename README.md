# WorkTogether - Portail Client (Datacenter)

## Description

WorkTogether est une application web B2B développée en PHP/Symfony. Elle agit comme l'extranet client d'un prestataire de services d'hébergement en Datacenter. L'application permet aux entreprises clientes de souscrire à des offres de location de serveurs physiques (Baies/Unités), de suivre l'état de leur infrastructure en temps réel, et de gérer la configuration de leur parc de manière autonome.

Ce projet (Client Léger) s'inscrit dans un système d'information global comprenant également un outil d'administration interne (Client Lourd en C\#) et une base de données centralisée.

## Table des matières

- Fonctionnalités Principales
- Stack Technique
- Architecture et Concepts Avancés
- Pré-requis
- Installation et Déploiement
- Utilisation de l'API

## Fonctionnalités Principales

- **Espace Public** : Catalogue dynamique des offres de location d'infrastructures.
- **Sécurité** :
    - Authentification forte avec vérification d'adresse email par signature cryptographique.
    - Protection contre les attaques par force brute (verrouillage temporaire du compte au bout de 3 tentatives avec envoi de jeton de déblocage).
- **Processus de Commande** : Attribution automatisée et transactionnelle des serveurs disponibles en base de données.
- **Espace Client (Dashboard)** :
    - Suivi en temps réel de l'état matériel des serveurs (OK, Incident, Maintenance).
    - Personnalisation du label et de la description des unités.
    - Gestion des contrats (activation/désactivation de la tacite reconduction).

## Stack Technique

- **Backend** : PHP 8.4, Symfony 7.x
- **Base de données** : MySQL 8.0 (ORM Doctrine)
- **Serveur Web** : Nginx / PHP-FPM
- **Outils** : Composer, Docker (pour l'environnement de base de données local), MailDev (tests SMTP)

## Architecture et Concepts Avancés

Ce projet intègre plusieurs mécanismes d'architecture logicielle visant à garantir la performance et la traçabilité des données :

- **Optimisation des performances (Eager Loading)** : Résolution des problématiques de requêtes N+1 via le constructeur de requêtes Doctrine (QueryBuilder) pour le chargement des tableaux de bord clients.
- **Audit Trail (Traçabilité)** : Utilisation d'un `EntityListener` sur l'entité `Unit`. Toute modification de l'état d'un serveur déclenche l'enregistrement transparent d'une archive d'historique au format JSON.
- **Automatisation (Scheduler)** : Exécution de tâches planifiées (Background Tasks) gérant le renouvellement automatique des contrats et la libération des serveurs expirés (Soft Delete/Archivage).

## Pré-requis

Pour installer et exécuter ce projet en local, vous devez disposer des éléments suivants :

- PHP \>= 8.4
- Composer
- Un serveur de base de données MySQL / MariaDB (ou Docker)
- Serveur local (Symfony CLI, Laragon, ou environnement Docker/Nginx complet)

## Installation et Déploiement

1.  **Cloner le dépôt**

<!-- end list -->

```bash
git clone https://github.com/votre-nom-utilisateur/worktogether-web.git
cd worktogether-web
```

2.  **Installer les dépendances**

<!-- end list -->

```bash
composer install
```

3.  **Configurer l'environnement**
    Dupliquez le fichier `.env` en `.env.local` et renseignez les variables d'environnement, notamment la connexion à la base de données et au serveur SMTP :

<!-- end list -->

```env
APP_ENV=dev
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/work_together?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN="smtp://127.0.0.1:1025"
```

4.  **Initialiser la base de données**
    Exécutez les migrations pour générer le schéma de la base de données :

<!-- end list -->

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5.  **Démarrer les services (Environnement de développement)**

<!-- end list -->

```bash
# Lancement du serveur web local
symfony server:start -d

# Lancement du composant Scheduler pour les tâches automatisées
php bin/console messenger:consume scheduler_default
```

## Utilisation de l'API

L'application expose une API REST sécurisée permettant d'interroger l'état des serveurs depuis un système tiers.

**Endpoint** : `GET /api/my-units`

- **Authentification** : Requiert une session utilisateur valide (`ROLE_USER`).
- **Retour** : JSON contenant la liste exhaustive des unités louées par l'entreprise du client connecté et leurs états respectifs.

-----

*Ce projet a été réalisé dans le cadre de la préparation à l'épreuve E6 du BTS SIO (Services Informatiques aux Organisations).*
