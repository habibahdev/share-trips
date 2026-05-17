# Documentation

## Sommaire

* [Prérequis](#prérequis)
* [Installation](#installation)
* [Lancement du projet](#lancement-du-projet)
* [Base de données](#base-de-données)
* [Assets front-end](#assets-front-end)
* [Outils de développement](#outils-de-développement)
* [Arrêt du projet](#arrêt-du-projet)
* [Mise à jour de l’environnement Docker](#mise-à-jour-de-lenvironnement-docker)

## Prérequis

* **PHP** >= 8.2
* **Composer**
* **Node.js** et **npm**
* **Docker** et **Docker Compose**

## Installation

### Cloner le dépôt

```bash
git clone https://github.com/habibahdev/share-trips.git
cd share-trips
```

Pour contribuer en fork, créez d’abord un fork sur GitHub puis clonez **votre** dépôt.

### Fichier d’environnement

Avec **`docker-compose.dev.yaml`**, le service `app` reçoit déjà une **`DATABASE_URL`** adaptée au réseau Docker (`postgres` comme hôte).

### Démarrer Docker (environnement de développement)

```bash
docker compose -f docker-compose.dev.yaml up -d --build
```

### Migrations

```bash
docker compose exec app php bin/console doctrine:migrations:migrate -n
docker compose exec app php bin/console doctrine:fixtures:load -n

```

### Dépendances JavaScript (hôte)

```bash
npm install
```

## Lancement du projet

**Application** : [http://localhost:8000](http://localhost:8000)

## Base de données

### Créer une migration

Après modification d'au moins une entité :

```bash
docker compose exec app php bin/console make:migration
```

Puis exécuter les migrations (voir ci-dessous).

### Appliquer les migrations

```bash
docker compose exec app php bin/console doctrine:migrations:migrate -n
```

Raccourci équivalent : `d:m:m -n` au lieu de `doctrine:migrations:migrate -n`.

### Jeu de données (fixtures)

```bash
docker compose exec app php bin/console doctrine:fixtures:load -n
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
| **Port**    | `5433`   |

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
docker compose exec app composer install
docker compose exec app php bin/console d:m:m -n
```

Rechargez les fixtures seulement si vous en avez besoin :

```bash
docker compose exec app php bin/console doctrine:fixtures:load -n
```