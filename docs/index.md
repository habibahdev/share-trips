# Documentation

# Sommaire
* [Pré-requis](#pré-requis)
* [Installation](#installation)
* [Lancement du projet](#lancement-du-projet)
* [Base de données](#base-de-données)
* [Assets](#assets)
* [Outils de développement](#outils-de-développement)
* [Arrêt du projet](#arrêt-du-projet)
* [Mise à jour de l'environnement Docker](#mise-à-jour-de-lenvironnement-docker)

## Pré-requis
* PHP >= 8.3
* Composer
* nodejs & npm
* Docker & docker compose

## Installation
### Clôner le projet
```
git clone https://github.com/habibahdev/share-trips.git
cd share-trips
```
### Lancement de l'environnement Docker
```
docker compose -f docker-compose.dev.yaml up -d --build
```

### Installer les dépendances
```
docker compose exec app composer install
```

## Lancement du projet
Après démarrage de Docker

```
http://localhost:8000
```

## Base de données
### Migrations
Si vous avez apporté une modification au niveau des entités
```
docker compose exec app php bin/console make:migration
```
Sinon, faites directement
```
docker compose exec app php bin/console d:m:m -n
```

### Fixtures
```
docker compose exec app php bin/console doctrine:fixtures:load -n
```

## Assets
### Build porduction
```
npm run build
```
### Mode développement
```
npm run watch
```
> À exécuter sur la machine hôte.

## Outils de développement
### MailDev
```
npm run maildev
```
* MailDev : http://localhost:1080

### Base de données
```
http://localhost:8081
```
> Les données de connexions sont définies dans le fichier `.env`

## Arrêt du projet
```
docker compose -f docker-compose.dev.yaml stop
```

## Mise à jour de l'environnement Docker
À utiliser après modification majeur du Docker :
```
docker compose -f docker-compose.dev.yaml down -v
docker compose -f docker-compose.dev.yaml up -d --build
docker compose exec app composer install
docker compose exec app php bin/console d:m:m -n
docker compose exec app php bin/console doctrine:fixtures:load -n
```
> Permet d'appliquer correctement les modifications et de synchroniser la base de données.
