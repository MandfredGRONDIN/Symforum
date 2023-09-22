## Installation

Il faut créer un nouveau projet en exécutant la commande suivante :
- symfony new --webapp symforum

Ensuite, copiez tout le contenu du dossier à l'intérieur du projet nouvellement créé et remplacez les anciens fichiers.

Assurez-vous d'avoir Composer installé.

Exécutez la commande suivante pour installer les dépendances :
- composer install

Ensuite, configurez le fichier .env comme suit :
- DATABASE_URL="mysql://Identifiant:mdp@adresse/symforum"

Ensuite, créez la base de données (BDD) et exécutez les migrations en utilisant les commandes suivantes :
-php bin/console doctrine:database:create
-php bin/console doctrine:migrations:migrate

Ensuite, démarrez le serveur de développement en utilisant l'une des commandes suivantes :
- symfony serve ou symfony server:start --no-tls

N'oubliez pas de lancer votre serveur MAMP/WAMP.

Accédez au site dans votre navigateur à l'adresse http://localhost:8000.

## Authentification

Compte actuellement disponible dans la base de donnée : 

Utilisateur Admin : 
- Pseudo = admin
- Mdp = azerty

Utilisateur Insider : 
- Pseudo = insider
- Mdp = azerty

Utilisateur external : 
- Pseudo = external
- Mdp = azerty

Utilisateur collaborator : 
- Pseudo = collaborator
- Mdp = azerty

## Fonctionnalités

1. Authentification et Autorisation :
- Inscription des utilisateurs avec validation du formulaire.
- Différents niveaux d'autorisation (rôles) : Interne, externe, collaborateur et administrateur.
- Les administrateurs ont des privilèges étendus, tels que la gestion des utilisateurs, des catégories et des rôles.

2. Gestion des Utilisateurs :
- Suppression/modification/affichage de comptes utilisateurs (par l'administrateur uniquement).

3. Gestion des Rôles :
- Suppression/modification/affichage de rôles (par l'administrateur uniquement).
- Si vous souhaitez attribuer un rôle d'administrateur à un utilisateur, vous devez créer un utilisateur et modifier son rôle dans la BDD en lui attribuant le rôle d'administrateur.
- Attribution d'une catégorie à un ou plusieurs rôles (par l'administrateur uniquement).

4. Création et Gestion des Catégories :
- Création de nouvelles catégories (par l'administrateur).
- Attribution de rôles à une ou plusieurs catégories (par l'administrateur uniquement).
- Modification et suppression des catégories existantes (par l'administrateur).
- Affichage des catégories en fonction du rôle de l'utilisateur (sauf l'administrateur qui voit tout).

5. Création et Gestion des Boards :
- Création de nouveaux boards (par l'administrateur et par l'insider).
- Modification et suppression des boards existants (par l'administrateur et par l'insider).
- Affichage des boards en fonction du rôle de l'utilisateur (sauf l'administrateur qui voit tout).
- Si l'utilisateur n'a pas le rôle de la catégorie, il ne pourra pas accéder au board (sauf l'administrateur).

6. Création et Gestion des Topics :
- Création de nouveaux topics (par tout le monde).
- Modification et suppression des topics existants (par tout le monde).
- Affichage des topics et des messages en fonction du rôle de l'utilisateur (sauf l'administrateur qui voit tout).
- Si l'utilisateur n'a pas le rôle de la catégorie, il ne pourra pas accéder au topic (sauf l'administrateur).
- L'utilisateur peut ajouter une image à la création/modification du topic.

7. Création et Gestion des Messages :
- Création de nouveaux messages (par tout le monde).
- Modification et suppression des messages existants (par tout le monde).
- Si l'utilisateur n'a pas le rôle de la catégorie, il ne pourra pas accéder au message (sauf l'administrateur).
- L'utilisateur peut ajouter une image à la création/modification du message.