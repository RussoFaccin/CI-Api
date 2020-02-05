
# CodeIgniter - Api

---

CodeIgniter Rest Api Example with JWT
*Depends on [CI-boilerplate](https://github.com/RussoFaccin/CI-boilerplate.git)*

  

#### Installation

* Clone the repository

* Copy files to a fresh CodeIgniter installation

  

#### Configuration

```sh

// application/config/routes.php

/*
--------------------------------------------------------------------------------
CUSTOM ROUTES
--------------------------------------------------------------------------------
*/

$route['api/login']['post'] = 'api/auth';

```
Add secret key:
```
// application/controllers/Api.php
define('SECRET_KEY', '<secret>');
```

#### Usage

To retrieve the access token access the following route:

```sh

## POST

<SITE_URL>/api/login

Body:
{
    "login": "<login>",
    "password": "<password>"
}

```

With access token:

```sh

## GET

<SITE_URL>/api/login

Authorization: Bearer <token>
```
