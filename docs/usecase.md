# Cas d'utilisation
[Retour à la documentation](index.md)


## Inscription
* **Acteur:** Visiteur
* **Préconditions:** L'utilisateur n'est pas connecté
* **Scénario principal:**
    1. L'utilisateur remplit le formulaire d'inscription avec e-mail et mot de passe
    2. Le système valide les informations
    3. Le système envoie un email de vérification de l'adresse email
    4. L'utilisateur clique sur le lien dans l'email pour activer son compte
    5. Le compte est vérifié
* **Scénarios alternatifs:**
    1. a. Adresse email déjà existante -> message d'erreur
    2. a. Email invalide -> message d'erreur
    2. b. Mot de passe trop court -> message d'erreur
    4. a. L'utilisateur ne clique pas sur le lien de confirmation -> compte non vérifié

## Authentification
* **Acteur:** Visiteur
* **Préconditions:** L'utilisateur possède un compte
* **Scénario principal:**
    1. L'utilisateur saisit son adresse email et mot de passe
    2. Le système vérifie les identifiants
    3. L'utilisateur est connecté et redirigé vers la page de son profil
* **Scénarios alternatifs:**
    1. a. Email et/ou mot de passe incorrects -> message d'erreur
    2. a. Le compte est désactivé -> accès refusé

## Déconnexion
* **Acteur:** Utilisateur
* **Préconditions:** L'utilisateur est connecté
* **Scénario principal:**
    1. L'utilisateur clique dur "Déconnexion"
    2. Le système termine la session et redirige vers la page d'accueil

## Modifier son profil
* **Acteur:** Utilisateur
* **Préconditions:** L'utilisateur est connecté
* **Scénario principal:**
    1. L'utilisateur accède à sa page de profil
    2. L'utilisateur modifie ses informations (nom, prénom, téléphone)
    3. Le système valide et enregistre les modifications
* **Scénarios alternatifs:**

    2. a. Les informations sont invalides -> message d'erreur

## Publier un trajet
* **Acteur:** Utilisateur (conducteur)
* **Préconditions:** L'utilisateur est connecté et possède un véhicule
* **Scénario principal:**
    1. L'utilisateur remplit le formulaire du trajet (départ, arrivée, date, places, prix)
    2. Le système valide les informations
    3. Le trajet est créé et visible par les autres utilisateurs
* **Scénarios alternatifs:**
    1. a. Le trajet est incomplet -> message d'erreur

## Ajouter un véhicule
* **Acteur:** Utilisateur
* **Préconditions:** L'utilisateur est connecté
* **Scénario principal:**
    1. L'utilisateur saisit les informations de son véhicule (marque, modèle, couleur, plaque, nombre de places disponible)
    2. Le système valide les informations
    3. Le véhicule est ajouté au profil de l'utilisateur
* **Scénarios alternatifs:**
    1. a. La plaque est déjà présente en base -> message d'erreur

## Modifier son mot de passe
* **Acteur:** Utilisateur
* **Préconditions:** L'utilisateur est connecté
* **Scénario principal:**
    1. L'utilisateur saisit son mot de passe actuel et le nouveau mot de passe
    2. Le système vérifie le mot de passe actuel
    3. Le mot de passe est mis à jour
* **Scénarios alternatifs:**
    1. a. Le mot de passe actuel incorrect -> message d'erreur
    1. b. Le nouveau mot de passe trop court -> message d'erreur

## Réserver un trajet
* **Acteur:** Utilisateur (passager)
* **Préconditions:** L'utilisateur est connecté
* **Scénario principal:**
    1. L'utilisateur sélectionne un trajet disponible
    2. L'utilisateur choisit le nombre de places
    3. Le système vérifie la disponibilité
    4. La réservation est enregistrée
    5. Le passager reçoit un email de confirmation
    6. Le conducteur reçoit un email pour confirmer la réservation
* **Scénarios alternatifs:**

    2. a. Le nombre de places demandé > disponible -> message d'erreur
    2. b. Le trajet est annulé entre-temps -> réservation impossible

## Anuuler un trajet par le conducteur
* **Acteur:** Utilisateur (conducteur)
* **Préconditions:** Le conducteur est connecté et possède un trajet publiè
* **Scénario principal:**
    1. Le conducteur annule le trajet
    2. Le système notifie tous les passagers inscrits par email
    3. Les passagers sont remboursés automatiquement
* **Scénarios alternatifs:**
    1. a. Tentative d'annulation d'un trajet inexistant -> message d'erreur
    1. b. Annultion d'un trjet déjà passé -> message d'erreur

## Gestion des utilisateurs (admin)
* **Acteur:** Utilisateur (administrateur)
* **Préconditions:** L'utilisateur est connecté et possède les droits admin
* **Scénario principal:**
    1. L'administrateur accède à la liste des utilisateurs
    2. L'administrateur peut bannir, suspendre, supprimer un utilisateur
    3. Le système envoie un email de notification aux utilisateurs concernés par les modifications
* **Scénarios alternatifs:**

    2. a. Tentative de suppression d'un utilisateur inexistant -> message d'erreur