WordPress ActiveRecord
======================

WordPress ActiveRecord implements the [active record pattern](http://en.wikipedia.org/wiki/Active_record_pattern) to easily retrieve, update and delete rows of database tables without struggling with raw SQL query strings.
The goal of this library is to provide a small but yet powerful [ORM](http://en.wikipedia.org/wiki/Object-relational_mapping) for the CMS WordPress, which should be easy to implement. Therefore it only consists of two classes: `ActiveRecord` and `Query`:
* The `ActiveRecord` class maps rows to object instances and the columns to object properties.
* The `Query` class provides a [fluent interface](http://en.wikipedia.org/wiki/Fluent_interface) to create sql queries. 

Usage
-----

You can use the library in your plugin or directly in your `functions.php` file. All you have to do is to require the `ActiveRecord` class and define your model classes (e.g. `Slideshow`):

```php
require 'wp-activerecord/ActiveRecord.php';

// create a model class for the table {wp-prefix}slideshows 
class Slideshow extends \wp_activerecord\ActiveRecord {
    protected static $table_name = 'slideshows';
}
```

With this you can create new rows, update and save them like this:

```php
// create
$slideshow = Slideshow::create([
    'title'        => 'Header slideshow',
    'slide_time'   => 3000,
    'slide_effect' => 'fade'
]);

// retrieve by id
$slideshow = Slideshow::get(1);

// update
$slideshow->title = 'New title';
$slideshow->slide_effect = 'slide';
$slideshow->save();
```

API
---

### Class `ActiveRecord`

#### Static Methods
##### Method `create([$attributes])`
###### Example:
```php
$activeRecord = Table::create();
// or
$activeRecord = Table::create([
   'name'  => 'wp-activerecord',
   'title' => 'WordPress ActiveRecord'
]);
```
<br>
##### Method `delete_by_id($id)`
###### Example:
```php
Table::delete_by_id(3);
```
<br>
##### Method `get($id)`
###### Example:
```php
Table::get(3);
```
<br>
##### Method `get_{type}_by_{column}($value [, $...])`
###### Example:
```php
$activeRecord = Table::get_one_by_title('WordPress');
$array = Table::get_by_name_or_title('wp-activerecord', 'WP');
$row = Table::get_row_by_name_and_title('wp', 'WP');
$var = Table::get_var_name_by_id(3);
```
<br>
##### Method `get_table_name()`
###### Example:
```php
$table_name = Table::get_table_name();
```
<br>
##### Method `insert($data)`
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
<br>
##### Method `query()`
###### Example:
```php
$query = Table::query();
```
<br>
##### Method `update($column [, $value])`
###### Example:
```php
$query = Table::update('name', 'wp-activerecord-updated');
// or
$query = Table::update([
   'name'  => 'wp-activerecord-updated',
   'title' => 'Updated WordPress ActiveRecord'
]);
```
<br>
##### Method `wpdb()`
###### Example:
```php
$wpdb = Table::wpdb();
```
<br>
# Instance methods

##### Method `delete()`
###### Example:
```php
$activeRecord->delete();
```
<br>
##### Method `save()`
###### Example:
```php
$activeRecord->save();
```

#### Event methods

##### Method `save_pre($isNew)`
###### Example:
```php
// in your derived class:
protected function save_pre($isNew) {
    $this->new = $isNew ? 1 : 0;
}
```
<br>
##### Method `save_post($isNew)`
###### Example:
```php
// in your derived class:
protected function save_post($isNew) {
    // do something with $this
}
```
<br>
##### Method `delete_pre()`
###### Example:
```php
// in your derived class:
protected function delete_pre() {
    // do something with $this
}
```
<br>
##### Method `delete_post()`
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
###### Example:
```php
$wpdb = Query::wpdb();
```

#### Instance Methods
##### Method `select([$...])`
###### Example:
```php
$activeRecord = Table::query()
  ->select('id', 'name')
  ->get();
```
<br>
##### Method `select([$...])`
###### Example:
```php
Table::query()
  ->delete()
  ->where('name', 'wp')
  ->execute();
```
<br>
##### Method `update([$column [, $value]])`
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
<br>
##### Method `set($column [, $value])`
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
<br>
##### Method `insert($data)`
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
<br>
##### Method `where($column [, $type_or_value [, $value]])`
###### Example:
```php
Table::query()
  ->where('name', 'wp')
  ->where('title', 'LIKE', '%active%')
  ->where([
    'start' => 12,
    'end'   => 37
  ])
  ->where('value', '>', ['RAND()']) // raw value wrapped in array
  ->where('numbers', 'in', [[1, 2, 3]] // a array as raw value will be joined
  ->get();
```
<br>
