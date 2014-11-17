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
        $this->save_pre($isNew);
        if($isNew) {
            $this->attributes['id'] = static::insert($this->attributes);
        } else {
            static::update($this->attributes)->where('id', $this->id)->execute();
        }
        $this->save_post($isNew);
        return $this;
    }
    
    /**
     * Delete the model
     * 
     * @return \wp_activerecord\Model The model instance
     */
    public function delete() {
        if(array_key_exists('id', $this->attributes)) {
            $this->delete_pre();
            static::delete_by_id($this->id);
            $this->id = null;
            $this->delete_post();
        }
        return $this;
    }
    
    // these methods could be implemented in the derived class
    protected function save_pre($isNew) {}
    protected function save_post($isNew) {}
    protected function delete_pre() {}
    protected function delete_post() {}
    
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
    
    /**
     * Get a var, rows, results or model instances
     * 
     * @param string $name      The name of the non existing method
     * @param array  $arguments The arguments of the non existing method
     * 
     * @return mixed The return value of the query
     * @throws Exception
     */
    public function __callStatic($name, $arguments) {
        $type = null;
        $prop_name = null;
        $query = static::query()->select();
        if(substr($name, 0, 7) === 'get_by_') {
            $type = 'get';
            $prop_name = substr($name, 7);
        } elseif(substr($name, 0, 11) === 'get_one_by_') {
            $type = 'get_one';
            $prop_name = substr($name, 11);
        } elseif(substr($name, 0, 11) === 'get_row_by_') {
            $type = 'get_row';
            $prop_name = substr($name, 11);
        } elseif(substr($name, 0, 15) === 'get_results_by_') {
            $type = 'get_results';
            $prop_name = substr($name, 15);
        } elseif(substr($name, 0, 8) === 'get_var_') {
            $type = 'get_var';
            $props = explode('_by_', substr($name, 8));
            $var = array_shift($props);
            if(count($props) === 0) {
                throw new Exception('Method get_var must be called with a WHERE clause, none given');
            }
            $prop_name = implode('_by_', $props);
            $query->select($var);
        } else {
            throw new Exception("No method with name '$name'");
        }
        $counter = 0;
        $andProperties = explode('_and_', $prop_name);
        foreach($andProperties as $property) {
            $orProperties = explode('_or_', $property);
            $query->where($orProperties[0], $arguments[$counter++]);
            for($index = 1; $index < count($orProperties); $index++) {
                $query->or_where($orProperties[$index], $arguments[$counter++]);
            }
        }
        return $query->{$type}();
    }
}
