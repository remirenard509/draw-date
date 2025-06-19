### Prérequis

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Composer](https://getcomposer.org/)


### copier le fichier .env.sample et le remplir
```bash
cp .env.sample .env
```

### copier le fichier docker-compose.yml.sample en docker-compose.yml
```bash
cp docker-compose.yml.sample docker-compose.yml
```

### sur la base de données et importer le fichier mysql.sql
### acces à la base de données
http://localhost:8090/

### lancer la compilation du porogramme
```bash
docker-compose build && docker-compose up -d
```

### lancer composer générer les dépendances et avoir les autoloads
```bash
composer install
```

### accès à la page de login
http://localhost/app/src/login.html
