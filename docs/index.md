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
git clone git@github.com:habibahdev/share-trips.git
cd share-trips
```

### Dépendances JavaScript (hôte)

```bash
composer install
npm install
```

### Fichier d’environnement

Avec **`docker-compose.dev.yaml`**, le service construit déjà la base de données.

### Démarrer Docker (environnement de développement)

```bash
docker compose -f docker-compose.dev.yaml up -d --build
```

### Migrations

```bash
docker compose exec app php bin/console make:migration
docker compose exec app php bin/console doctrine:migrations:migrate -n
```

Raccourci : `d:m:m -n` au lieu de `doctrine:migrations:migrate -n`.

## Lancement du projet

**Application** : [http://localhost:8000](http://localhost:8000)

## Base de données

### Appliquer les migrations

```bash
docker compose exec app rm -f migrations/*.php
docker compose exec app php bin/console make:migration
docker compose exec app php bin/console d:m:m -n
```

### Jeu de données (fixtures)

```bash
docker compose exec app php bin/console doctrine:fixtures:load -n
```

## Assets front-end

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

Depuis **votre machine**, connectez-vous au serveur PostgreSQL exposé sur l’hôte :

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

Pour arrêter et supprimer les conteneurs :

```bash
docker compose -f docker-compose.dev.yaml down
```

## Mise à jour de l’environnement Docker

À utiliser après une modification importante des Dockerfiles, si vous voulez repartir sur des volumes neufs :

```bash
docker compose -f docker-compose.dev.yaml down -v
docker compose -f docker-compose.dev.yaml up -d --build
docker compose exec app rm -f migrations/*.php
docker compose exec app php bin/console make:migration
docker compose exec app php bin/console d:m:m -n
```

Rechargez les fixtures seulement si vous en avez besoin :

```bash
docker compose exec app php bin/console doctrine:fixtures:load -n
```