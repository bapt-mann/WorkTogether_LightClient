## ⚙️ Documentation Technique Détaillée du Projet WorkTogether

### 1. L'ORM et la Persistance des données
**Package :** `doctrine/orm` & `doctrine/doctrine-bundle`
**Design Pattern principal :** *Data Mapper* & *Unit of Work*

* **Mécanique interne :** Contrairement au pattern *Active Record* (où l'objet PHP sauvegarde lui-même ses données), Doctrine utilise le pattern *Data Mapper*. Tes entités (`Unit`, `Rental`, `Company`) sont de simples classes PHP (POPO - *Plain Old PHP Objects*). Elles ignorent totalement l'existence de la base de données.
* **L'`EntityManagerInterface` (L'orchestrateur) :** C'est le cœur de Doctrine.
    * `persist($objet)` : Ne fait **aucune** requête SQL. Il met simplement l'objet dans une file d'attente gérée par le *Unit of Work* (une zone de mémoire RAM).
    * `flush()` : Calcule le "diff" (les changements) entre la mémoire et la base de données, puis exécute toutes les requêtes `INSERT`, `UPDATE` ou `DELETE` en une seule transaction optimisée.
* **Les Repositories :** Classes dédiées aux requêtes `SELECT`. Exemple dans ton `PurchaseController` : `$unitRepository->findBy(['rental' => null], null, $unitsNeeded)`. Cela génère dynamiquement la requête SQL avec un `LIMIT`.

### 2. Le Système de Fixtures (Génération de données)
**Package :** `doctrine/doctrine-fixtures-bundle`

* **Mécanique interne :** Ce bundle exécute des scripts PHP pour peupler la base de données via l'`EntityManager`.
* **Le ReferenceRepository (`addReference` / `getReference`) :** C'est une fonctionnalité clé que tu as utilisée. Au lieu de faire des requêtes `SELECT` lourdes pour retrouver la baie n°14 afin d'y insérer une unité, Doctrine stocke l'objet `Bay` en mémoire cache via une clé chaîne de caractères (ex: `'BAY_14'`). Quand tu appelles `getReference()`, il te renvoie l'objet PHP directement depuis la RAM, ce qui rend l'insertion de tes 1260 unités extrêmement rapide.
* **Topological Sorting (`DependentFixtureInterface`) :** Quand tu déclares la méthode `getDependencies()`, le composant construit un graphe orienté (un arbre de dépendances) pour calculer l'ordre exact d'exécution des fichiers afin d'éviter les erreurs de clés étrangères (ex: forcer la création de `Company` avant `User`).

### 3. Le Composant de Sécurité et d'Hachage
**Package :** `symfony/security-bundle`

* **Mécanique interne :** Basé sur un système de pare-feu (*Firewall*) interceptant la requête HTTP globale avant même qu'elle n'atteigne tes contrôleurs.
* **Le hachage (`UserPasswordHasherInterface`) :** Dans ton `RegisterController`, tu l'utilises pour crypter le mot de passe. En interne, Symfony utilise l'algorithme de hachage défini dans `security.yaml` (généralement **Argon2i** ou **Bcrypt**). L'algorithme génère un sel aléatoire (*salt*) intégré directement dans le hash final pour empêcher les attaques par table arc-en-ciel (*Rainbow Tables*).
* **Le contrôle d'accès (`#[IsGranted("ROLE_USER")]`) :** Cet attribut déclenche l'`AccessDecisionManager`. Il vérifie le *Token* de sécurité stocké dans la session de l'utilisateur pour valider ses habilitations. Si l'utilisateur n'a pas le rôle requis, le composant lève une `AccessDeniedException` (Erreur 403).

### 4. Vérification d'Email (Cryptographie)
**Package :** `symfonycasts/verify-email-bundle`

* **Mécanique interne :** Il ne stocke **aucun** token en base de données. Il utilise une signature cryptographique **HMAC** (Hash-based Message Authentication Code).
* **La signature (`generateSignature`) :** Le bundle prend l'ID de l'utilisateur, son adresse e-mail, la date d'expiration, et signe le tout avec la variable secrète `APP_SECRET` de ton fichier `.env`.
* **La validation (`validateEmailConfirmation`) :** Au clic du client, le composant recalcule le hash avec le `APP_SECRET` du serveur. Si le hash correspond au lien, cela prouve mathématiquement que l'URL n'a pas été altérée et que le délai n'est pas expiré. C'est une méthode d'authentification *Stateless* (sans état).

### 5. Formulaires et Validation de Données
**Package :** `symfony/form` & `symfony/validator`

* **Le Data Mapping :** Dans ton `RegisterController` (`$form->handleRequest($request)`), le composant lit la superglobale `$_POST`, extrait les valeurs, et utilise les *Setters* (ex: `setEmail()`, `setCompanyName()`) pour hydrater ton objet PHP `$user`.
* **La protection CSRF :** Nativement, le composant génère un token caché dans le formulaire HTML (`_token`) et le vérifie à la soumission pour s'assurer que la requête POST vient bien de ton site web et non d'un attaquant externe.
* **Gestion des erreurs personnalisées :** Ton implémentation de la regex (`preg_match`) avec l'ajout dynamique d'une `new FormError()` démontre ta capacité à surcharger la validation par défaut du framework avec des règles de gestion métier strictes.
