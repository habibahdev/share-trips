# Documentation

# Sommaire
* [Pré-requis](#pré-requis)
* [Installation](#installation)
* [Lancement du projet](#lancement-du-projet)
* [Mise à jour de l'environnement Docker](#mise-à-jour-de-lenvironnement-docker)
* [Cas d'utilisation](usecase.md)

## Pré-requis
* PHP >= 8.3
* Composer
* nodejs & npm
* Docker & docker compose
* Symfony CLI

## Installation
### 1. Clôner le projet
```
git clone https://github.com/habibahdev/share-trips.git
cd share-trips
composer install
npm install
rm -f migrations/*.php
```

### 2. Variables d'environnement
Créer le fichier `.env.local` à la racine du projet et y placer :

```
DATABASE_URL="postgresql://tripsadmin:tripsadmin@127.0.0.1:5433/sharetrips?serverVersion=15&charset=utf8"
MAILER_DSN=smtp://localhost:1025
MAILER_FROM=noreply@sharetrips.fr
MAILER_FROM_NAME=ShareTrips
```

## Lancement du projet
```
docker compose -f docker-compose.dev.yaml up -d
symfony serve -d
```

### 1. Base de données et migrations
Lors du lancement du conteneur, la base de données est créée directement. Il suffit ensuite de jouer les migrations.

```
docker compose -f docker-compose.dev.yaml exec app php bin/console make:migration
docker compose -f docker-compose.dev.yaml exec app php bin/console d:m:m -n
```

### 2. Fixtures
```
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:fixtures:load -n
```

### 3. Assets
```
# Pour compiler une seule fois
npm run build

# ou

# Pour recompiler css & js à chaque modification
npm run watch
```

### 4. Outils de développement
#### 4.1. MailDev
```
npm run maildev
```
* MailDev : http://localhost:1080

### 5. Arrêt des services
```
symfony server:stop
docker compose -f docker-compose.dev.yaml stop
```

## Mise à jour de l'environnement Docker
Si en faisant un `git pull` vous voyer que le fichier `docker-compose.dev.yaml` est modifié :
```
docker compose -f docker-compose.dev.yaml down
docker compose -f docker-compose.dev.yaml up -d
docker compose -f docker-compose.dev.yaml exec app php bin/console d:m:m -n
docker compose -f docker-compose.dev.yaml exec app php bin/console doctrine:fixtures:load -n
```
> Permet d'appliquer correctement les modifications et de synchroniser la base de données.
