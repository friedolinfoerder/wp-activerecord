WordPress ActiveRecord
======================

WordPress ActiveRecord implements the [active record pattern](http://en.wikipedia.org/wiki/Active_record_pattern) to easily retrieve, update and delete rows of database tables without struggling with raw SQL query strings.
The goal of this library is to provide a small but yet powerful [ORM](http://en.wikipedia.org/wiki/Object-relational_mapping) for the CMS WordPress, which is easy to implement. Therefore it only consists of two classes: `ActiveRecord` and `Query`:
* The `ActiveRecord` class maps rows to object instances and the columns to object properties.
* The `Query` class provides a [fluent interface](http://en.wikipedia.org/wiki/Fluent_interface) to create sql queries. 

Usage
-----

You can use the library in your plugin or directly in your `functions.php` file. All you have to do is to require the `ActiveRecord` class and add your model classes (e.g. `Slideshow`):

```php
require 'wp-activerecord/ActiveRecord.php';

// create a model class for the table {wp-prefix}slideshow 
class Slideshow extends \wp_activerecord\ActiveRecord {
    protected $table_name = 'slideshow';
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
