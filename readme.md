# Eloquent Insert On Duplicate Key And Insert Ignore

This package provides macros to run INSERT ... ON DUPLICATE KEY UPDATE and INSERT IGNORE queries on models and pivot tables with Laravel's ORM Eloquent using MySql.

## Installation

Install this package with composer.

```sh
composer require guidocella/eloquent-insert-on-duplicate-key
```

Then add the service provider to your Package Service Providers in config/app.php.

```php
InsertOnDuplicateKey\InsertOnDuplicateKeyServiceProvider::class,
```

## Usage

### Models

Call insertOnDuplicateKey() or insertIgnore() from a Model with the array of data to insert in its table.

```php
    $data = [
        ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User 1'],
        ['id' => 2, 'email' => 'user2@email.com', 'name' => 'User 2'],
    ];
    
    User::insertOnDuplicateKey($data);
    
    User::insertIgnore($data);
```

### Pivot tables

You can call attachOnDuplicateKey() and attachIgnore() from a BelongsToMany relation to run the inserts in its pivot table. You can pass the data in all of the formats accepted by attach().

```php
    $pivotData = [
        1 => ['expires_at' => Carbon::today()],
        2 => ['expires_at' => Carbon::tomorrow()],
    ];
    
    $user->roles()->attachOnDuplicateKey($pivotData);

    $user->roles()->attachIgnore($pivotData);
```
