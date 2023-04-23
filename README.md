# Challenge Api-Pinapp

## About
Laravel with JWT Authentication and Swagger Documentation

## Requirements
* PHP >= 7.4
* Docker 4.1.0
* [Composer](https://github.com/composer/composer)
* [Laravel](https://github.com/laravel/framework)
* [jwt-auth](https://github.com/tymondesigns/jwt-auth)
* [Swagger](https://github.com/DarkaOnLine/L5-Swagger)

## Installation

- Clone this repo or download it

    ```bash
    $ docker-compose build
    $ docker-compose up -d
    ```
- Configure your .env file for authenticating via database

    ```bash
    $ docker exec -it  sh
    $ composer update
    $ php artisan key:generate
    $ php artisan jwt:secret
    $ php artisan l5-swagger:generate
    $ php artisan migrate
    ```

- Visit `localhost`

> **Note:**
- You can now use ```Postman``` or ```Swagger``` to test the API:

## Swagger
- ```http://localhost/api```

## Authentication
- ```POST /api/register``` –> Create user 
- ```POST /api/login``` –> with email and password, obtain a JWT token
- ```GET /api/user``` –> Get user info

## Clients
- ```GET /api/clients``` –> Get all clients
- ```GET /api/clients/{id}``` –> Get client by id
- ```POST /api/clients``` –> Create client
- ```DELETE /api/clients/{id}``` –> Delete client
- ```GET /api/kpi-clients``` –> Get KPI clients

