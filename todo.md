# CRUD Operations

CRUD operations create, read, update, and delete data. The Model class provides access to methods for inserting, finding, updating, and deleting data in Databases.

This document provides a general introduction to inserting, querying, updating, and deleting data using the Flat PHP Library.

## Insert Data

Model::insert() method is used to insert new records to the database table and returns a bool.

The following examples inserts a new record into users table in the demo database:

```php
$user = new User();

$user = $user->insert([
    'firstname' => 'Asante',
    'lastname' => 'Kuseka'
]);
```

>If we perform an INSERT on a table with an AUTO_INCREMENT field, the insert() method returns the ID of the last inserted record immediately.

```php
$user = new User({
    'firstname' => 'Asante',
    'lastname' => 'Kuseka'
});

$user->save();
```

```php
$user = new User();

$user->firstname = "Asante";
$user->lastname = "Kuseka";

$user->save();
```

## Query data

Find One

The findOne() method returns the first record that matches the query or null if no record matches the query.

The following example searches for the record with id of "1234":

```php
$user = new User();

$user = $user->findOne(['id' => '1234']);
```

Find Many

 ```php
 $phones = new Phones();

$phones = $phones->find([
    'brand' => 'Samsung',
    'color' => 'black'
]);
```

> Flat includes the id field by default unless you explicitly exclude it in a projection array.

The following example finds restaurants based on the cuisine and borough fields and uses a projection to limit the fields that are returned. It also limits the results to 5 documents.

```php
$collection = (new MongoDB\Client)->test->restaurants;

$cursor = $collection->find(
    [
        'cuisine' => 'Italian',
        'borough' => 'Manhattan',
    ],
    [
        'projection' => [
            'name' => 1,
            'borough' => 1,
            'cuisine' => 1,
        ],
        'limit' => 4,
    ]
);
```

## Limit, Sort, and Skip Options

In addition to projection criteria, you can specify options to limit, sort, and skip documents during queries.

The following example uses the limit and sort options to query for the five most populous zip codes in the United States:

```php
$cursor = $collection->find(
    [],
    [
        'limit' => 5,
        'sort' => ['pop' => -1],
    ]
);
```
