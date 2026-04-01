# Documentation

# Sommaire
* [Pré-requis](#pré-requis)
* [Installation](#installation)
* [Lancement du projet](#lancement-du-projet)
* [Mise à jour de l'environnement Docker](#mise-à-jour-de-lenvironnement-docker)
* [Cas d'utilisation](usecase.md)
* [User story](userstory.md)

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
symfony console make:migration
symfony console d:m:m -n
```

### 2. Fixtures
```
symfony console doctrine:fixtures:load -n
```
> Insère dans la base un compte administrateur.

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

#### 4.2. Base de données
```
http://localhost:8081
```
> Les données de connexions sont définies dans le fichier `.env.local`

### 5. Arrêt des services
```
symfony server:stop
docker compose -f docker-compose.dev.yaml stop
```

## Mise à jour de l'environnement Docker
Si en faisant un `git pull` vous voyer que le fichier `docker-compose.dev.yaml` est modifié :
```
docker compose -f docker-compose.dev.yaml down -v
docker compose -f docker-compose.dev.yaml up -d
rm -f migrations/*.php
symfony console make:migration
symfony console d:m:m -n
symfony console doctrine:fixtures:load -n
```
> Permet d'appliquer correctement les modifications et de synchroniser la base de données.
