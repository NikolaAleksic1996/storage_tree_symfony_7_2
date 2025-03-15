# Symfony File Storage API

This Symfony application fetches data from an external API, processes and stores it in a database, and exposes three REST API endpoints to retrieve files and directories.

## Requirements
- PHP 8.2
- [Composer](https://getcomposer.org/)
- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Installation guide
- Clone the repository
- Create a `.env` file from `.env.dev`
- Run `composer install`
- Run `docker-compose up -d --build`
- Run `docker-compose restart`

 OPEN NEW TERMINAL and:
- Run `php bin/console doctrine:database:create` if it doesn't exist
- Run `php bin/console doctrine:migrations:migrate`
- Run `php bin/console app:fetch-storage-data`
- Run `php bin/console cache:clear`
- Run `http://127.0.0.1:8000/api/files-and-directories` to get files and directories
- Run `http://127.0.0.1:8000/api/directories` to get paginated directories
- Run `http://127.0.0.1:8000/api/files` to get paginated files
