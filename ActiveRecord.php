<?php

namespace wp_activerecord;

require "Query.php";

/**
 * The Model class
 * 
 * @author Friedolin Förder <friedolinfoerder@gmx.de>
 */
abstract class ActiveRecord {
    
    /**
     * The attributes of the model
     * 
     * @var array The attributes of the model
     */
    protected $attributes;
    
    /**
     * Constructor
     * 
     * @param array $attributes (optional) The attributes of the model
     */
    public function __construct(array $attributes=[]) {
        $this->attributes = $attributes;
    }
    
    /**
     * Save the model
     * 
     * @return \wp_activerecord\Model The model object
     */
    public function save() {
        $isNew = !array_key_exists('id', $this->attributes);
        if($isNew) {
            $this->attributes['createdAt'] = current_time('mysql');
        }
        $this->attributes['updatedAt'] = current_time('mysql');
        
        if($isNew) {
            $this->attributes['id'] = static::insert($this->attributes);
        } else {
            static::update($this->attributes)->where('id', $this->id)->execute();
        }
        return $this;
    }
    
    /**
     * Delete the model
     * 
     * @return \wp_activerecord\Model The model instance
     */
    public function delete() {
        if($this->id) {
            static::delete_by_id($this->id);
            $this->id = null;
        }
        return $this;
    }
    
    /**
     * Set the table name
     * 
     * @param string $name The name of the table without prefix
     * 
     * @return null
     */
    public static function set_table_name($name) {
        static::$table_name = $name;
    }
    
    /**
     * Get the table name
     * 
     * @return string Return the table name
     */
    public static function get_table_name() {
        return static::wpdb()->prefix . static::$table_name;
    }
    
    /**
     * Get the wpdb instance
     * 
     * @global object $wpdb
     * 
     * @return object The wpdb instance
     */
    public static function wpdb() {
        global $wpdb;
        return $wpdb;
    }
    
    /**
     * Get a property or a call a function of the wpdb instance
     * 
     * @param string $name      The property or method name
     * @param array  $arguments The arguments for the method call
     * 
     * @return mixed The return value of the method call or the value of the property
     */
    public static function __callStatic($name, $arguments) {
        $wpdb = static::wpdb();
        if(method_exists($wpdb, $name)) {
            return call_user_func_array([$wpdb, $name], $arguments);
        }
        return $wpdb->{$name};
    }
    
    /**
     * Create a model with an array of attributes
     * 
     * @param array $attributes An array of attributes
     * 
     * @return \wp_activerecord\Model An model instance
     */
    public static function create($attributes) {
        $instance = new static($attributes);
        return $instance->save();
    }
    
    /**
     * Get a query instance
     * 
     * @return \wp_activerecord\Query A query instance
     */
    public static function query() {
        return new Query(get_called_class(), true);
    }
    
    /**
     * Insert a row into the database
     * 
     * @global object $wpdb
     * 
     * @param array $data An array of properties
     * 
     * @return int The last inserted id
     */
    public static function insert(array $data) {
        global $wpdb;
        static::query()
            ->insert($data)
            ->execute();
        return $wpdb->insert_id;
    }
    
    /**
     * Shortcut for creating a query instance and calling update on it
     * 
     * @see \wp_activerecord\Query::update
     * 
     * @param string|array $column (optional) A column name or a data object
     * @param string       $value  (optional) A value of a column
     * 
     * @return \wp_activerecord\Query The current query object
     */
    public static function update($column=null, $value=null) {
        $query = static::query()->update();
        call_user_func_array([$query, 'set'], func_get_args());
        return $query;
    }
    
    /**
     * Delete a row by id
     * 
     * @param int|string $id The id of the row
     * 
     * @return null
     */
    public static function delete_by_id($id) {
        static::query()
            ->delete()
            ->where('id', $id)
            ->execute();
    }
    
    /**
     * Get a model instance by id
     * 
     * @param int|string $id The id of the row
     * 
     * @return \wp_activerecord\Model The model instance
     */
    public static function get($id) {
        $attributes = (array) static::get_row_by_id($id);
        return new static($attributes);
    }
    
    /**
     * Get the row by id
     * 
     * @param int|string $id The id of the row
     * 
     * @return \stdClass The row object
     */
    public static function get_row_by_id($id) {
        return static::get_row('id', $id);
    }
    
    /**
     * Get the row by a column
     * 
     * @param string $column The column name
     * @param mixed  $value  The value of the column
     * 
     * @return array An Object with row data
     */
    public static function get_row($column, $value) {
        return static::query()
            ->where($column, $value)
            ->get_row();
    }
    
    /**
     * Get the row by a column
     * 
     * @param string $column The column name
     * @param mixed  $value  The value of the column
     * 
     * @return array An Object with row data
     */
    public static function get_var($var, $column, $value) {
        return static::query()
            ->select($var)
            ->where($column, $value)
            ->get_var();
    }
    
    /**
     * Set a column value
     * 
     * @param string $column The name of the column
     * @param mixde  $value  The value of the column
     */
    public function __set($column, $value) {
        $this->attributes[$column] = $value;
    }
    
    /**
     * Get a column value
     * 
     * @param string $column The name of the column
     *
     * @return mixed The value of the column
     */
    public function __get($column) {
        return $this->attributes[$column];
    }
}
