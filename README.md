WordPress ActiveRecord
======================

WordPress ActiveRecord implements the [active record pattern](http://en.wikipedia.org/wiki/Active_record_pattern) to easily retrieve, update and delete rows of database tables without struggling with raw SQL query strings.
The goal of this library is to provide a small but yet powerful [ORM](http://en.wikipedia.org/wiki/Object-relational_mapping) for the CMS WordPress, which should be easy to implement. Therefore it only consists of two classes: `ActiveRecord` and `Query`:
* The `ActiveRecord` class maps rows to object instances and the columns to object properties.
* The `Query` class provides a [fluent interface](http://en.wikipedia.org/wiki/Fluent_interface) to create sql queries. 

Installation
------------

```sh 
composer require friedolinfoerder/wp-activerecord
```


Usage
-----

You can use the library in your plugin or directly in your `functions.php` file. All you have to do is to require the `ActiveRecord` class and define your model classes (e.g. `Slideshow`):

```php

// create a model class for the table {wp-prefix}slideshows 
class Slideshow extends \wp_activerecord\ActiveRecord {
    protected static $table_name = 'slideshows';
}
```

With this you can create new rows, update and save them like this:

```php
// create new row
$slideshow = Slideshow::create([
    'title'        => 'Header slideshow',
    'slide_time'   => 3000,
    'slide_effect' => 'fade'
]);

// retrieve by id...
$slideshow = Slideshow::get(1);

// ... and update the row
$slideshow->title = 'New title';
$slideshow->slide_effect = 'slide';
$slideshow->save();
```

API
---

* [Class `ActiveRecord`](#class-activerecord)
    * [Static properties](#static-properties)
      * [Property `$casts`](#property-casts)
    * [Static methods](#static-methods)
      * [Method `create([$attributes])`](#method-createattributes)
      * [Method `delete_by_id($id)`](#method-delete_by_idid)
      * [Method `get([$id])`](#method-getid)
      * [Method `get_{type}_by_{column}($value [, $...])`](#method-get_type_by_columnvalue--)
      * [Method `get_table_name()`](#method-get_table_name)
      * [Method `insert($data)`](#method-insertdata)
      * [Method `query()`](#method-query)
      * [Method `update($column [, $value])`](#method-updatecolumn--value)
      * [Method `wpdb()`](#method-wpdb)
    * [Instance methods](#instance-methods)
      * [Method `delete()`](#method-delete)
      * [Method `save()`](#method-save)
    * [Event methods](#event-methods)
      * [Method `save_pre($isNew)`](#method-save_preisnew)
      * [Method `save_post($isNew)`](#method-save_postisnew)
      * [Method `delete_pre()`](#method-delete_pre)
      * [Method `delete_post()`](#method-delete_post)
* [Class `Query`](#class-query)
    * [Static methods](#static-methods-1)
      * [Method `wpdb()`](#method-wpdb-1)
    * [Instance methods](#instance-methods-1)
      * [Method `select([$...])`](#method-select)
      * [Method `delete()`](#method-delete)
      * [Method `update([$column [, $value]])`](#method-updatecolumn--value)
      * [Method `set($column [, $value])`](#method-setcolumn--value)
      * [Method `insert($data)`](#method-insertdata)
      * [Method `where($column [, $type_or_value [, $value]])`](#method-wherecolumn--type_or_value--value)
      * [Method `and_where($column [, $type_or_value [, $value]])`](#method-and_wherecolumn--type_or_value--value)
      * [Method `or_where($column [, $type_or_value [, $value]])`](#method-or_wherecolumn--type_or_value--value)
      * [Method `group_by($column [, $order])`](#method-group_bycolumn--order)
      * [Method `having($column [, $type_or_value [, $value]])`](#method-havingcolumn--type_or_value--value)
      * [Method `and_having($column [, $type_or_value [, $value]])`](#method-and_havingcolumn--type_or_value--value)
      * [Method `or_having($column [, $type_or_value [, $value]])`](#method-or_havingcolumn--type_or_value--value)
      * [Method `order_by($column [, $order])`](#method-order_bycolumn--order)
      * [Method `limit($limit)`](#method-limitlimit)
      * [Method `offset($offset)`](#method-offsetoffset)
      * [Method `join($table, $attribute, $foreign_attribute [, $type])`](#method-jointable-attribute-foreign_attribute--type)
      * [Method `sql()`](#method-sql)
      * [Method `get_results()`](#method-get_results)
      * [Method `get_row()`](#method-get_row)
      * [Method `get_col()`](#method-get_col)
      * [Method `get_var()`](#method-get_var)
      * [Method `get()`](#method-get)
      * [Method `get_one()`](#method-get_one)
      * [Method `execute()`](#method-execute)

### Class `ActiveRecord`

#### Static Properties
##### Property `$casts`
Cast row values to native types.  
###### Example:
```php
class Slideshow extends \wp_activerecord\ActiveRecord {
    protected static $casts = [
        'num_slides' => 'int',
        'duration' => 'float',
        'active' => 'boolean',
        'created_at' => 'datetime',
    ];
}
```

#### Static Methods
##### Method `create([$attributes])`
Create a model with an array of attributes
###### Example:
```php
$activeRecord = Table::create();
// or
$activeRecord = Table::create([
   'name'  => 'wp-activerecord',
   'title' => 'WordPress ActiveRecord'
]);
```

##### Method `delete_by_id($id)`
Delete a row by id
###### Example:
```php
Table::delete_by_id(3);
```

##### Method `get([$id])`
Get all model instances or a model instance by id
###### Example:
```php
$activeRecords = Table::get(); // all records
$activeRecord = Table::get(3); // one record by id
```

##### Method `get_{type}_by_{column}($value [, $...])`
Dynmamic finder method: Get a var, rows, results or model instances
###### Example:
```php
$activeRecord = Table::get_one_by_title('WordPress');
$array = Table::get_by_name_or_title('wp-activerecord', 'WP');
$row = Table::get_row_by_name_and_title('wp', 'WP');
$var = Table::get_var_name_by_id(3);
```

##### Method `get_table_name()`
Get the table name
###### Example:
```php
$table_name = Table::get_table_name();
```

##### Method `insert($data)`
Insert one or multiple rows into the database
###### Example:
```php
$last_insert_id = Table::insert([
   'name'  => 'wp-activerecord',
   'title' => 'WordPress ActiveRecord'
]);
// or
$last_insert_id = Table::insert([[
   'name'  => 'ActiveRecord',
   'title' => 'Class ActiveRecord'
], [
   'name'  => 'Query',
   'title' => 'Class Query'
]]);
```

##### Method `query()`
Get a query instance
###### Example:
```php
$query = Table::query();
```

##### Method `update($column [, $value])`
Shortcut method for creating a query instance and calling update on it
###### Example:
```php
$query = Table::update('name', 'wp-activerecord-updated');
// or
$query = Table::update([
   'name'  => 'wp-activerecord-updated',
   'title' => 'Updated WordPress ActiveRecord'
]);
```

##### Method `wpdb()`
Get the wpdb instance
###### Example:
```php
$wpdb = Table::wpdb();

// use case:
$userInput = '20%';
Table::query()
  ->delete()
  ->where('name', 'like', '%' . Table::wpdb()->esc_like($userInput) . '%')
  ->execute();
```

#### Instance methods

##### Method `delete()`
Delete the model
###### Example:
```php
$activeRecord->delete();
```

##### Method `save()`
Save the model
###### Example:
```php
$activeRecord->save();
```

#### Event methods

##### Method `save_pre($isNew)`
Called before saving the model
###### Example:
```php
// in your derived class:
protected function save_pre($isNew) {
    $this->new = $isNew ? 1 : 0;
}
```

##### Method `save_post($isNew)`
Called after saving the model
###### Example:
```php
// in your derived class:
protected function save_post($isNew) {
    // do something with $this
}
```

##### Method `delete_pre()`
Called before deleting the model
###### Example:
```php
// in your derived class:
protected function delete_pre() {
    // do something with $this
}
```

##### Method `delete_post()`
Called after deleting the model
###### Example:
```php
// in your derived class:
protected function delete_post() {
    // do something with $this
}
```

### Class `Query`

#### Static Methods
##### Method `wpdb()`
Get the wpdb instance
###### Example:
```php
$wpdb = Query::wpdb();
```

#### Instance Methods
Select rows
##### Method `select([$...])`

###### Example:
```php
$activeRecord = Table::query()
  ->select('id', 'name')
  ->get();
```

##### Method `delete()`
Delete rows
###### Example:
```php
Table::query()
  ->delete()
  ->where('name', 'wp')
  ->execute();
```

##### Method `update([$column [, $value]])`
Update rows (Alias for \wp_activerecord\Query::set)
###### Example:
```php
Table::query()
  ->update()
  ->set('name', 'wp')
  ->execute();
// or
Table::query()
  ->update('name', 'wp')
  ->execute();
// or
Table::query()
  ->update([
    'name'  => 'wp',
    'title' => 'WordPress'
  ])
  ->execute();
```

##### Method `set($column [, $value])`
Set columns, which should be updated
###### Example:
```php
Table::query()
  ->set('name', 'wp')
  ->execute();
// or
Table::query()
  ->set([
    'name'  => 'wp',
    'title' => 'WordPress'
  ])
  ->execute();
```

##### Method `insert($data)`
Insert rows
###### Example:
```php
Table::query()
  ->insert([
    'name'  => 'wp',
    'title' => 'WordPress'
  ])
  ->execute();
// or
Table::query
  ->insert([[
    'name'  => 'ActiveRecord',
    'title' => 'Class ActiveRecord'
  ], [
    'name'  => 'Query',
    'title' => 'Class Query'
  ]])
  ->execute();
```

##### Method `where($column [, $type_or_value [, $value]])`
Add a where condition
###### Example:
```php
$activeRecords = Table::query()
  ->where('name', 'wp')
  ->where('title', 'LIKE', '%active%')
  ->where([
    'start' => 12,
    'end'   => 37
  ])
  ->where(['deleted_at', null]) // query for NULL value, produces  `deleted_at` IS NULL
  ->where('value', '>', ['RAND()']) // raw value wrapped in array
  ->where('numbers', 'in', [[1, 2, 3]] // a array as raw value will be joined
  ->get();
```

##### Method `and_where($column [, $type_or_value [, $value]])`
Alias for `where`.

##### Method `or_where($column [, $type_or_value [, $value]])`
Alias for `where`, but adds a new group to the where clause, which will be added with the keyword OR

##### Method `group_by($column [, $order])`
Add a group by section
###### Example:
```php
$activeRecords = Table::query()
  ->group_by('name', 'asc')
  ->get();
```

##### Method `having($column [, $type_or_value [, $value]])`
Add a having condition
###### Example:
```php
$activeRecords = Table::query()
  ->group_by('name')
  ->having(["SUM(price)"], ">", 10) // raw column value wrapped in array
  ->get();
```

##### Method `and_having($column [, $type_or_value [, $value]])`
Alias for `having`.

##### Method `or_having($column [, $type_or_value [, $value]])`
Alias for `having`, but adds a new group to the having clause, which will be added with the keyword OR

##### Method `order_by($column [, $order])`
Add a order by section
###### Example:
```php
$activeRecords = Table::query()
  ->order_by('description')
  ->order_by('name', 'desc')
  ->get();
```

##### Method `limit($limit)`
Add a limit
###### Example:
```php
$activeRecords = Table::query()
  ->limit(5)
  ->get();
```

##### Method `offset($offset)`
Add a offset
###### Example:
```php
$activeRecords = Table::query()
  ->offset(10)
  ->get();
```

##### Method `join($table, $attribute, $foreign_attribute [, $type])`
Add a join condition
###### Example:
```php
$activeRecords = Table::query()
  ->join('OtherTable', 'id', 'table_id')
  ->get();
```

##### Method `sql()`
Create the final sql statement
###### Example:
```php
$sql = Table::query()
  ->select('description')
  ->where('description', 'like', 'Title: %')
  ->sql();
```

##### Method `get_results()`
Get the results of the query
###### Example:
```php
$results = Table::query()
  ->get_results();
```

##### Method `get_row()`
Get the row of the query
###### Example:
```php
$row = Table::query()
  ->where('name', 'this is a unique name')
  ->get_row();
```

##### Method `get_col()`
Get the column of the query
###### Example:
```php
$descriptions = Table::query()
  ->select('description')
  ->get_col();
```

##### Method `get_var()`
Get the value of the query
###### Example:
```php
$description = Table::query()
  ->select('description')
  ->where('name', 'this is a unique name')
  ->get_var();
```

##### Method `get()`
Get the results of the query as an array of model instances
###### Example:
```php
$activeRecords = Table::query()
  ->get();
```

##### Method `get_one()`
Get the results of the query as a model instances
###### Example:
```php
$activeRecord = Table::query()
  ->where('name', 'this is a unique name')
  ->get_one();
```

##### Method `execute()`
Execute the query
###### Example:
```php
Table::query()
  ->delete()
  ->where('name', 'this is a unique name')
  ->execute();
```

License
-------

This code is licensed under the MIT license.
