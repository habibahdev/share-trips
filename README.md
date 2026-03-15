# ShareTrips

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-7.4.7-000000?logo=symfony&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)
![CI](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?logo=githubactions&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

Application de covoiturage développée avec Symfony 7, PostgreSQL 15 et Bootstrap 5. Permet à des utilisateurs de proposer ou réserver des trajets en covoiturage.

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
```

### 2. Dépendances
```
composer install
npm install
```

### 3. Variables d'environnement
Créer le fichier `.env.local` à la racine du projet et y placer :

```
DATABASE_URL="postgresql://tripsadmin:tripsadmin@127.0.0.1:5433/sharetrips?serverVersion=15&charset=utf8"
```

## Développement local
Le développement local utilise **PostgreSQL dans docker** et **Symfony en local**.

### 1. Lancer PostgreSQL via Docker
```
docker compose -f docker-compose.dev.yaml up -d
```

### 2. Base de données et migrations
Lors du lancement du conteneur, la base de données est créée directement. 

```
symfony console make:migration
symfony console doctrine:migrations:migrate -n
```

### 3. Compiler les ressources externes
```
npm run build
npm run watch
```

### 4. Lancer le serveur
```
symfony serve -d
```

L'application est accessible sur **http://127.0.0.1:8000**

### Arrêt
```
symfony server:stop
docker compose -f docker-compose.dev.yaml stop
```
