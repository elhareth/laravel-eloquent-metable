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
use Illuminate\Database\Eloquent\Model;
use Elhareth\LaravelEloquentMetable\IsMetable;

class User extends Model
{
    use IsMetable;
}
```

## Documentation

### Handling Meta

The `IsMetable` trait will add ``metalist()`` method to your model, which meant to handle the realtion between your model and the meta data model `Metable`.

#### Attaching MetaData

Attach meta to a model with the ``setMeta()`` method. The method accepts three arguments: a string to use as a meta name, a value and additionally a string for group. The value argument will accept a number of different inputs, which will be converted to a string for storage in the database.

```php
 $user->setMeta('key', 'value');
 $user->setMeta('key', 'value', null);
 $user->setMeta('key', 'value', 'group');
```

If you want to add many meta records you may pass an array as first parameter

```php
 $user->setMeta([
    'full_name' => 'Full Name',
    'gender' => 'male',
    'bio' => 'Lorem absum...',
 ]);
```

Also you pass a second param as meta data group

```php
 $user->setMeta([
    'full_name' => 'Full Name',
    'gender' => 'male',
    'bio' => 'Lorem absum...',
 ], 'profile');
```

#### Be Carefull !

if you adding a meta record with value of type array if it has value/group key it will be treated as ``$value`` / ``$group`` params, so when you adding an array that may have these keys consider using `setMeta()` method where the value param is treated as value no matter what it does contain.

```php
 $user->setMeta('key', [
    'value' => 'random-content',
 ]); // 'a:1:{s:5:"value";s:14:"random-content";};'
 $value = $user->getMeta('key'); // ['value' => 'random-content']
```

#### Retrieving MetaData

You can retrieve the value of the meta at a given key with the ``getMeta()`` method.

```php
 $gender = $user->getMeta('gender');
```

You may pass a second parameter to the ``getMeta()`` method in order to specify a default value to return if no meta has been set at that key.

```php
 $user->getMeta('gender', 'male'); // will return 'male' if not set
```

You may retrieve a collection of meta depending on specifec group

```php
 $profile = $user->getMetaGroup('profile'); // return collection
```

#### Checking For Presence of MetaData

You can check if a value has been assigned to a given key with the ``hasMeta()`` method.
This method will return true if a record is found on database.


```php
 if ($user->hasMeta('gender')) {
    // ...
 }
```

#### Queued Metables

You may mass assign metables and they will be attached once the model is created

```php
 $user = User::create([
    'name' => 'UserName',
    'email' => 'username@email.com',
    'password' => 'password',
    'metables' => [
        'gender' => 'male', // OR
        'gender' => [
            'value' => 'male', // OR
        ],
        'gender' => [
            'value' => 'male',
            'group' => 'profile', // If not specified group is null
        ],
    ],
 ]);

 $user->getMeta('gender') // male
```

### Pre Metables

You may define default metables for each model and they'll be added once the model is created.

To do so you should implement `Elhareth\LaravelEloquentMetable\IsMetableInterface`> And define a method `defaultMetables()` which returns an array of metables you want to automatically attach to your model.

```php
 use Elhareth\LaravelEloquentMetable\IsMetableInterface;
 use Illuminate\Database\Eloquent\Model;
 
 class User extends Model implements IsMetableInterface
 {
    /**
     * Default Metables
     * 
     * @return array
     */
    protected function defaultMetables(): array
    {
        return [
            'gender' => [
                'value' => null,
                'group' => 'profile',
            ],
            'bio' => [
                'value' => null,
                'group' => 'profile',
            ],
            'locale' => [
                'value' => 'en',
                'group' => null,
            ],
            'avatar' => [
                'value' => 'avatar.png',
            ],
            'theme' => 'dark',
        ];
    }
 }
```

You can get list of model default metables by `getDefaultMetables()`.

#### Deleting MetaData

To remove the meta stored at a given key, use ``removeMeta()``.

```php
 $user->removeMeta('bio');
```

To delete all meta records from a model, use ``deleteMetaRecords()``.

```php
 $user->deleteMetaRecords();
```

Attached meta is automatically purged from the database when a `IsMetable` model is manually deleted. Metaable will `not` be cascaded if the model is deleted by the query builder.

```php
 $user->delete(); // will delete attached meta
 User::where(...)->delete() // will NOT delete attached meta
```

#### Eager Loading MetaData

When working with collections of `IsMetable` models, be sure to eager load the meta relation for all instances together to avoid repeated database queries (i.e. N+1 problem).

Eager load from the query builder:

```php
 $users = User::with('metalist')->where(...)->get();
```

Lazy eager load from an Eloquent collection:

```php
 $users->load('metalist');
```

You can also instruct your model class to `always` eager load the meta relationship by adding ``'metalist'`` to your model's ``$with`` property.

```php
 class User extends Model {
     use IsMetable;
 
     protected $with = ['metalist'];
 }
```

### Querying Meta

The `IsMetable` trait provides a number of query scopes to facilitate modifying queries based on the meta attached to your models

#### Checking for Presence of a key

To only return records that have a value assigned to a particular key, you can use ``whereHasMeta()``. You can also pass an array to this method, which will cause the query to return any models attached to one or more of the provided keys.

```php
 $users = User::whereHasMeta('gender')->get();
 $users = User::whereHasMeta(['gender', 'bio'])->get();
```

You can also query for records that does not contain a meta key using the ``whereDoesntHaveMeta()``. Its signature is identical to that of ``whereHasMeta()``.

```php
 $users = User::whereDoesntHaveMeta('gender')->get();
 $users = User::whereDoesntHaveMeta(['gender', 'bio'])->get();
```

You can restrict your query based on the value stored at a meta key. The ``whereMeta()`` method can be used to compare the value using any of the operators accepted by the Laravel query builder's ``where()`` method.

```php
 $users = User::whereMeta('letters', ['a', 'b', 'c'])->get();
 ```

The ``whereMetaIn()`` method is also available to find records where the value is matches one of a predefined set of options.

```php
 $users = User::whereMetaIn('country', ['CAN', 'USA', 'MEX']);
```

## License

This package is released under the MIT license (MIT).
