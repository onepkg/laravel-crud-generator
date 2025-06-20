# Laravel CRUD Generator

- [Laravel CRUD Generator](#laravel-crud-generator)
    - [Usage](#usage)
        - [Install the package via composer:](#install-the-package-via-composer)
        - [Run the command:](#run-the-command)
        - [Access the API](#access-the-api)
        - [Config](#config)
        - [Customize stubs](#customize-stubs)


Laravel CRUD Generator is a simple command line tool to generate CRUD operations for your models.

```plaintext
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── {Model}Controller.php  # RESTful controller
│   ├── Requests/
│   │       └── {Model}Request.php    # Form request
│   ├── Resources/
|   |       └── {Model}Collection.php   # JSON collection  
│   │       └── {Model}Resource.php   # JSON resource
├── Models/
│   └── {Model}.php                 # Model
└── Routes/
    └── api.php                     # API route
```

### Usage

##### Install the package via composer:

```bash
composer require --dev onepkg/laravel-crud-generator
```

##### Run the command:

- Generate CRUD files for the users table:

```bash
php artisan onepkg:make-crud User
```

- If the table name is not in the plural form

```bash
php artisan onepkg:make-crud User --table=user
```

- Specify the routing file

```bash
php artisan onepkg:make-crud User --route=api
```

##### Access the API

```plaintext
GET /api/users         # get listing
POST /api/users        # create
GET /api/users/{id}    # show detail
PUT /api/users/{id}    # update
DELETE /api/users/{id} # delete
```

##### Config

- Publish the config file

```bash
php artisan vendor:publish --provider="Onepkg\LaravelCrudGenerator\LaravelCrudServiceProvider"
```

- Modify the configuration

You can modify the configuration in /config/crud - generator.php

```php
[
    'namespacedModel' => 'App\\Models',
    'namespacedRequest' => 'App\\Http\\Requests\\Admin',
    'namespacedResource' => 'App\\Http\\Resources\\Admin',
    'namespacedController' => 'App\\Http\\Controllers\\Admin',
]
```

##### Customize stubs

Publish the stubs

```bash
php artisan stub:publish
```

Modify the corresponding stubs in /stubs/*.

```plaintext
controller.crud.stub
model.crud.stub
request.crud.stub
resource-collection.crud.stub
resource.crud.stub
route.crud.stub
```
