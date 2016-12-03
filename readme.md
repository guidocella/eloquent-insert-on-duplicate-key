# Eloquent Insert On Duplicate Key And Ignore Functions

This package provides functions to run INSERT ... ON DUPLICATE KEY UPDATE and INSERT IGNORE queries with Laravel's ORM Eloquent using MySql.

It is based on [yadakhov/insert-on-duplicate-key](https://github.com/yadakhov/insert-on-duplicate-key), which provides the same functionality with a trait for models to use. However, I mostly needed to run those queries on pivot tables, which can't be done with that trait.

This package simply converts the trait's methods to functions that take either a Model or a BelongsToMany relation as the first argument, and use it to determine the table.

## Examples

### With model

```php
    $users = [
        ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One'],
        ['id' => 2, 'email' => 'user2@email.com', 'name' => 'User Two'],
    ];
    
    insert_on_duplicate_key(new User, $users);

    insert_ignore(new User, $users);
```

### With pivot table

```php
    $pivotRows = [
        ['user_id' => 1, 'user_role' => 1, 'expires_at' => Carbon::today()],
        ['user_id' => 1, 'user_role' => 2, 'expires_at' => Carbon::tomorrow()],
    ];
    
    insert_on_duplicate_key((new User)->roles(), $pivotRows);

    insert_ignore((new User)->roles(), $pivotRows);
```
