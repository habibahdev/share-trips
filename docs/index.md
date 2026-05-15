# Documentation ShareTrips

## Sommaire

* [Prérequis](#prérequis)
* [Installation](#installation)
* [Lancement du projet](#lancement-du-projet)
* [Base de données](#base-de-données)
* [Assets front-end](#assets-front-end)
* [Outils de développement](#outils-de-développement)
* [Arrêt du projet](#arrêt-du-projet)
* [Mise à jour de l’environnement Docker](#mise-à-jour-de-lenvironnement-docker)
* [Fichier Compose alternatif](#fichier-compose-alternatif)

## Prérequis

* **PHP** ≥ 8.2 (recommandé : 8.3, comme dans les Dockerfiles du dépôt)
* **Composer**
* **Node.js** et **npm** (pour Webpack Encore ; exécution en général sur la machine hôte)
* **Docker** et **Docker Compose** (plugin V2 : `docker compose`)

## Installation

### Cloner le dépôt

```bash
git clone https://github.com/habibahdev/share-trips.git
cd share-trips
```

Pour contribuer en fork, créez d’abord un fork sur GitHub puis clonez **votre** dépôt.

### Fichier d’environnement

Copiez ou adaptez les fichiers d’environnement Symfony (`.env`, `.env.local`, etc.) selon la [documentation Symfony](https://symfony.com/doc/current/configuration.html).  
Avec **`docker-compose.dev.yaml`**, le service `app` reçoit déjà une **`DATABASE_URL`** adaptée au réseau Docker (`postgres` comme hôte). Les variables sensibles (Stripe, e-mail, etc.) restent à configurer dans `.env` / `.env.local`.

### Démarrer Docker (environnement de développement)

```bash
docker compose -f docker-compose.dev.yaml up -d --build
```

Attendre que PostgreSQL soit prêt (le service `app` démarre après le healthcheck de `postgres`).

### Dépendances PHP dans le conteneur

Le code du projet est monté dans le conteneur `app` ; installez les dépendances **dans ce conteneur** pour utiliser la même version de PHP et les extensions attendues :

```bash
docker compose -f docker-compose.dev.yaml exec app composer install
```

### Migrations

```bash
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Dépendances JavaScript (hôte)

```bash
npm install
```

*(Vous pouvez utiliser `npm ci` si vous partez d’un clone propre avec un `package-lock.json` à jour.)*

## Lancement du projet

* **Application (PHP built-in server dans le conteneur)** : [http://localhost:8000](http://localhost:8000)  
  (port défini dans `docker-compose.dev.yaml`)

Après modification du code PHP ou de la config, un vidage de cache peut être nécessaire :

```bash
docker compose -f docker-compose.dev.yaml exec app php bin/console cache:clear
```

## Base de données

### Créer une migration

Après modification des entités :

```bash
docker compose -f docker-compose.dev.yaml exec app php bin/console make:migration
```

Puis exécuter les migrations (voir ci-dessous).

### Appliquer les migrations

```bash
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Raccourci équivalent : `d:m:m -n` au lieu de `doctrine:migrations:migrate --no-interaction`.

### Jeu de données (fixtures)

```bash
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:fixtures:load --no-interaction
```

> Attention : en général, cette commande **réinitialise** les données de la base ciblée par `DATABASE_URL`.

## Assets front-end

Les commandes Encore s’exécutent en principe **sur la machine hôte** (le conteneur `app` n’embarque pas Node).

### Build de production

```bash
npm run build
```

### Mode développement (recompilation à la volée)

```bash
npm run watch
```

## Outils de développement

### MailDev

```bash
npm run maildev
```

* Interface MailDev : [http://localhost:1080](http://localhost:1080)

### Adminer (interface web PostgreSQL)

* URL : [http://localhost:8081](http://localhost:8081)

Depuis **votre machine** (pas depuis l’intérieur du réseau Docker), connectez-vous au serveur PostgreSQL exposé sur l’hôte :

| Champ        | Valeur        |
|-------------|---------------|
| Système     | PostgreSQL    |
| Serveur     | `localhost`   |
| Utilisateur | `tripsadmin`  |
| Mot de passe | `tripsadmin` |
| Base        | `sharetrips`  |
| **Port**    | **`5433`**    |

Le port **5433** est le port **hôte** mappé sur le conteneur (voir `docker-compose.dev.yaml`). À l’intérieur du réseau Compose, le service `app` utilise l’hôte **`postgres`** et le port **5432**.

## Arrêt du projet

```bash
docker compose -f docker-compose.dev.yaml stop
```

Pour arrêter et supprimer les conteneurs (sans supprimer les volumes par défaut) :

```bash
docker compose -f docker-compose.dev.yaml down
```

## Mise à jour de l’environnement Docker

À utiliser après une modification importante des Dockerfiles ou de `docker-compose.dev.yaml`, si vous voulez repartir sur des volumes neufs :

```bash
docker compose -f docker-compose.dev.yaml down -v
docker compose -f docker-compose.dev.yaml up -d --build
docker compose -f docker-compose.dev.yaml exec app composer install
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Rechargez les fixtures seulement si vous en avez besoin :

```bash
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:fixtures:load --no-interaction
```

## Fichier Compose alternatif

Le dépôt contient aussi **`docker-compose.yaml`** (build avec `Dockerfile`, application sur le port **8001**, PostgreSQL sur **5432**). Les commandes ci-dessus s’adaptent en remplaçant systématiquement :

`docker compose -f docker-compose.dev.yaml`  
par  

`docker compose -f docker-compose.yaml`

Vérifiez les ports et les noms de conteneurs dans ce fichier avant de lancer Adminer ou l’application.
