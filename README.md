# Laravel-Eloquent-Metable

Laravel-Eloquent-Metable is a package for extending laravel Eloquent models without messing up with the database schema.

## Example Usage

Adding metadata to an eloquent model

```php
$user = User::create([
    'name' => 'User Name',
    'email' => 'username@email.com',
    'password' => 'password',
]);
$user->setMeta('gender', 'male');
```

Query scope to fetch model by it's metadata

```php
$user = User::whereMeta('gender', 'male');
```

Get attached metadata

```php
$gender = $user->getMeta('gender');
```

## Installation

Add the package to your Laravel app using composer

```bash
composer require elhareth/laravel-eloquent-metable
```

Run the migrations to add the required table to your database.

```bash
php artisan migrate
```

Add the `Elhareth\LaravelEloquentMetable\IsMetable` trait to any eloquent model class that you want to be able to attach metadata to.


```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Elhareth\LaravelEloquentMetable\IsMetable;

class User extends Model
{
    use IsMetable;
}
```

_READY TO GO_


## License

This package is released under the MIT license (MIT).
