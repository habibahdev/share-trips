# User story
[Retour à la documentation](index.md)

## Inscription
* **Story :**

En tant que visiteur, je veux m'inscrire sur la plateforme afin de pouvoir créer un compte et accèder aux fonctionnalités utilisateurs.
* **Critères d'acceptation :**
    * Le formulaire demande adresse email et mot de passe
    * Le système valide le format de l'email et la robustesse du mot de passe
    * Si l'adresse email est déjà utilisée affichage d'un message d'erreur
    * Un email de confirmation est envoyé
    * L'utilisateur doit cliquer sur le lien pour activer son compte

## Authentification
* **Story :**

En tant que visiteur, je souhaite pouvoir me connecter avec mon email et mon mot de passe afin d'accéder à mon compte.
* **Critères d'acceptation :**
    * Email et mot de passe vérifiés
    * En cas d'erreur, affiche un message d'erreur
    * Si le compte est désactivé, l'accès est refusé

## Déconnexion
* **Story :**

En tant qu'utilisateur, je souhaite pouvoir me déconnecter.
* **Critères d'acceptation :**
    * L'utilisateur peut cliquer sur déconnecter
    * La session est terminée et redirigée vers la page d'accueil

## Modifier son profil
* **Story :**

En tant qu'utilisateur, je souhaite pouvoir modifier mon profil et mon email afin de maintenir mes informations à jour.
* **Critères d'acceptation :**
    * Les informations sont modifiables et validées
    * L'adresse email ne peut pas être modifiée
    * En cas d'erreur, affiche un message d'erreur

## Publier un trajet
* **Story :**

En tant qu'utilisateur (conducteur), je souhaite publier un trajet afin que les passagers puissent le réserver.
* **Critères d'acceptation :**
    * Formulaire de trajet : départ, destination, date, places, prix
    * Informations validées par le système
    * Trajet visible par les passagers

## Ajouter un véhicule
* **Story :**

En tant qu'utilisateur, je souhaite ajouter un véhicule afin de proposer des trajets.
* **Critères d'acceptation :**
    * Formulaire : marque, modèle, couleur, plaque d'immatriculation, nombre de places proposés
    * Validation du véhicule
    * Plaque d'immatriculation doit être unique, sinon message d'erreur

## Modifier son mot de passe
* **Story :**

En tant qu'utilisateur, je souhaite modifier mon mot de passe afin de sécuriser mon compte
* **Critères d'acceptation :**
    * Vérification du mot de passe actuel
    * Nouveau mot de passe conforme aux exigences
    * Si le mot de passe actuel est incorrect, affichage d'un message d'erreur par le système

## Réserver un trajet
* **Story :**

En tant qu'utilisateur (passager), je souhaite réserver un trajet afin de sécuriser ma place et recevoir un email de confirmation, et que le conducteur soit notifié.
* **Critères d'acceptation :**
    * Vérification de la disponibilité des places
    * Email de confirmation envoyé au passager
    * Email de notification envoyé au conducteur
    * Nombre de places demandé > disponible, affichage d'un message d'erreur par le système

## Annuler un trajet
* **Story :**

En tant qu'utilisateur (conducteur), je souhaite annuler un trajet afin de libérer les passagers et déclencher les remboursements, avec notification par email.
* **Critères d'acceptation :**
    * Tous les passagers reçoivent un email d'annulation
    * Les passagers sont remboursés automatiquement
    * Trajet inexistant, le système affiche un message d'erreur

## Gestion des utilisateurs
* **Story :**

En tant qu'administrateur, je souhaite gérer les utilisateurs afin de contrôler l'accès et notifier  les utilisateurs concernés par email.
* **Critères d'acceptation :**
    * Suspendre, bannir, supprimer un utilisateur
    * Email envoyé aux utilisateurs concernés
    * Tentative sur un utilisateur inexistant, le système affiche un message d'erreur