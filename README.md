# WorkTogether Light Client

## Commandes utiles du projet (Aide-mémoire Symfony)

Ce projet utilise le framework Symfony. Voici les commandes principales pour manipuler l'application en environnement de développement :

### 1. Démarrage du projet
Pour installer les dépendances et lancer le serveur local :
* `composer install` : Installe toutes les librairies PHP requises.
* `npm install` & `npm run dev` : Compile les assets front-end (si Webpack/Vite est configuré).
* `symfony server:start -d` : Lance le serveur web local en arrière-plan.

### 2. Gestion de la Base de Données (Doctrine)
Ces commandes permettent de manipuler la structure de la base de données :
* `php bin/console doctrine:database:create` : Crée la base de données (si elle n'existe pas).
* `php bin/console doctrine:database:drop --force` : Supprime complètement la base de données (utile pour faire table rase).
* `php bin/console make:migration` : Analyse les entités PHP et génère un fichier SQL pour mettre à jour la BDD.
* `php bin/console doctrine:migrations:migrate` : Exécute les fichiers de migration pour modifier la structure réelle de la base.

### 3. Les Fixtures (Jeu de fausses données)
Élément central du projet pour simuler le Datacenter (Génération des 30 Baies et 1260 Unités) :
* `php bin/console doctrine:fixtures:load` : Vide la base de données et insère le jeu de données de test (inventaire physique, offres commerciales, comptes admin).

### 4. Génération de code (Maker Bundle)
Commandes utilisées pour construire le squelette de l'application :
* `php bin/console make:entity` : Crée ou modifie une entité (ex: Unit, Company) et ses getters/setters.
* `php bin/console make:controller` : Crée un nouveau contrôleur et sa vue Twig associée (ex: PurchaseController).
* `php bin/console make:registration-form` : Génère le système complet d'inscription.
* `php bin/console make:form` : Génère un Formulaire symfony
