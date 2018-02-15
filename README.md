# Laravel Insert On Duplicate Key And Insert Ignore

This package provides macros to run INSERT ... ON DUPLICATE KEY UPDATE and INSERT IGNORE queries on models and pivot tables with Laravel's ORM Eloquent using MySql.

## Installation

Install this package with composer.

```sh
composer require guidocella/eloquent-insert-on-duplicate-key
```

If you don't use Package Auto-Discovery yet add the service provider to your Package Service Providers in `config/app.php`.

```php
InsertOnDuplicateKey\InsertOnDuplicateKeyServiceProvider::class,
```

## Usage

### Models

Call `insertOnDuplicateKey` or `insertIgnore` from a model with the array of data to insert in its table.

```php
$data = [
    ['id' => 1, 'name' => 'name1', 'email' => 'user1@email.com'],
    ['id' => 2, 'name' => 'name2', 'email' => 'user2@email.com'],
];

User::insertOnDuplicateKey($data);

User::insertIgnore($data);
```

If you want to update only certain columns with `insertOnDuplicateKey`, pass them as the 2nd argument.

```php
User::insertOnDuplicateKey(
    [
        [
            'id'    => 1,
            'name'  => 'new name',
            'email' => 'foo@gmail.com',
        ],
    ],
    ['name']
);
// The name will be updated but not the email.
```

Based on `panyanyany` modification (thanks dude), if you want to update certain columns with different values to the inserted one, you can pass the update as Raw query.    

For example, for this SQL query (where c is a "counter" with 0 value as default)
```php
INSERT INTO t1 (a,b) VALUES (1,2)
  ON DUPLICATE KEY UPDATE c=c+1,a=VALUES(a);
```
you can pass the update as the 2nd argument  

```php
User::insertOnDuplicateKey(
    [
        [
            'a'  => 1,
            'b'  => 2,
        ],
    ],
    [DB::raw('c=c+1'),'name']
);
// The name will be updated but not the email.
```


### Pivot tables

Call `attachOnDuplicateKey` and `attachIgnore` from a `BelongsToMany` relation to run the inserts in its pivot table. You can pass the data in all of the formats accepted by `attach`.

```php
$pivotData = [
    1 => ['expires_at' => Carbon::today()],
    2 => ['expires_at' => Carbon::tomorrow()],
];

$user->roles()->attachOnDuplicateKey($pivotData);

$user->roles()->attachIgnore($pivotData);
```
