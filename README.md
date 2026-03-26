# ShareTrips

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-7.4.7-000000?logo=symfony&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)
![CI](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?logo=githubactions&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

Plateforme de covoiturage développée avec Symfony permettant de proposer et réserver des trajets entre particuliers.

## Aperçu
![Homepage](docs/images/homepage.png)

## Objectif du projet
ShareTrips a été conçu pour :
* Simplifier l'organisation de trajets entre particuliers
* Mettre en pratique une architecture backend avec Symfony
* Simuler un projet proche de conditions réelles (Docker, CI/CD)

## Fonctionnalités principales
* Authentification et gestion des utilisateurs
* Création et gestion de trajets
* Recherche de trajets
* Réservation de places
* Système d'envoi d'emails (MailDev en local)

## Compétences mises en oeuvre


## Stack technique
| Domaine | Technologies |
|------------| ------------- |
| Backend | PHP 8.3, Symfony 7.4.7 |
| Frontend | Bootstrap 5 |
| Base de données | PostgreSQL 15 |
| DevOps | Docker, docker compose |
| CI/CD | Github Actions |

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

#### 4.2. Adminer
##### Identifiants de connexion
![Homepage](docs/images/adminer.png)

Service disponible à cette adresse : http://localhost:8081

* Système : PostreSQL
* Serveur : postgres
* Utilisateur : tripsadmin
* Mot de passe : tripsadmin
* Base de données : sharetrips

> Ces informations sont disponibles dans le fichier `docker-compose.dev.yaml`.

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
> Permetd'appliquer correctement les modifications et de synchroniser la base de données.
