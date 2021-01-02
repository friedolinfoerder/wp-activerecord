<?php

namespace wp_activerecord;

/**
 * The Query Builder Class
 *
 * @author Friedolin Förder
 */
class Query {

    // join variants
    const JOIN_INNER = 'inner';
    const JOIN_LEFT = 'left';
    const JOIN_RIGHT = 'right';

    /**
     * The type of query
     *
     * @var string The type of query
     */
    protected $type;

    /**
     * Flag for checking if there is a model
     *
     * @var boolean Flag for checking if there is a model
     */
    protected $hasModel;

    /**
     * The current model or table name
     *
     * @var string|null The current model or table name
     */
    protected $model;

    /**
     * SELECT properties
     *
     * @var array SELECT properties
     */
    protected $select = [];

    /**
     * SET columns
     *
     * @var array SET columns
     */
    protected $set = [];

    /**
     * INSERT rows
     *
     * @var array INSERT rows
     */
    protected $insert = [];

    /**
     * WHERE conditions
     *
     * @var array WHERE conditions
     */
    protected $where = [];

    /**
     * GROUP BY conditions
     *
     * @var array GROUP BY conditions
     */
    protected $group_by = [];

    /**
     * HAVING conditions
     *
     * @var array HAVING conditions
     */
    protected $having = [];

    /**
     * ORDER BY conditions
     *
     * @var array ORDER BY conditions
     */
    protected $order_by = [];

    /**
     * LIMIT property
     *
     * @var int|array LIMIT property
     */
    protected $limit;

    /**
     * OFFSET property
     *
     * @var int|array OFFSET property
     */
    protected $offset;

    /**
     * JOIN command
     *
     * @var array JOIN commands
     */
    protected $join = [];

    /**
     * Constructor
     *
     * @param string|ActiveRecord  $model   (optional) A class name of a \wp_activerecord\ActiveRecord class or a table name
     * @param boolean $isModel (optional) Flag for checking if $model is a class name
     */
    public function __construct($model=null, $isModel=false) {
        $this->model = $model;
        $this->hasModel = $isModel;
    }

    /**
     * Select rows
     *
     * @param string $property,... Properties to select
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function select() {
        $this->type('SELECT');
        $this->select = array_merge($this->select, func_get_args());
        return $this;
    }

    /**
     * Delete rows
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function delete() {
        return $this->type('DELETE');
    }

    /**
     * Update rows (Alias for \wp_activerecord\Query::set)
     *
     * @see \wp_activerecord\Query::set
     *
     * @param string|array $column (optional) A column name or a data object
     * @param string       $value  (optional) A value of a column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function update($column=null, $value=null) {
        if(func_num_args() === 0) {
            return $this->type('UPDATE');
        } else {
            return call_user_func_array(array($this, 'set'), func_get_args());
        }
    }

    /**
     * Set columns
     *
     * @param string|array $column A column name or a data object
     * @param string       $value  (optional) A value of a column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function set($column, $value=null) {
        $this->type('UPDATE');
        if(func_num_args() === 1) {
            foreach($column as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }
        if(is_null($value)) {
            // encode null value
            $value = ['NULL'];
        }
        $this->set[$column] = $value;
        return $this;
    }

    /**
     * Insert rows
     *
     * @param array $data One data object or multiple rows
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function insert(array $data) {
        $this->type('INSERT');
        if(!array_key_exists(0, $data)) {
            $data = [$data];
        }
        foreach($data as &$row) {
            foreach($row as &$value) {
                if(is_null($value)) {
                    // encode null value
                    $value = ['NULL'];
                }
            }
        }
        $this->insert = array_merge($this->insert, $data);
        return $this;
    }

    /**
     * Add a where condition
     *
     * @param string|array $column        A column name, a raw condition wrapped in an array or multiple
     *                                    where conditions in an array
     * @param mixed        $type_or_value (optional) The type of the query (e.g. = or >) or the value of the column
     * @param mixed        $value         (optional) The value of the column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function where($column, $type_or_value=null, $value=null) {
        return $this->where_condition('where', func_num_args(), $column, $type_or_value, $value);
    }

    /**
     * Alias for Query::where
     *
     * @see \wp_activerecord\Query::where
     *
     * @param string|array $column        A column name, a raw condition wrapped in an array or multiple
     *                                    where conditions in an array
     * @param mixed        $type_or_value (optional) The type of the query (e.g. = or >) or the value of the column
     * @param mixed        $value         (optional) The value of the column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function and_where($key, $type_or_value=null, $value=null) {
        // call where function
        return call_user_func_array([$this, 'where'], func_get_args());
    }

    /**
     * Create a where condition and adds it with the keyword OR
     *
     * @param string|array $column        A column name, a raw condition wrapped in an array or multiple
     *                                    where conditions in an array
     * @param mixed        $type_or_value (optional) The type of the query (e.g. = or >) or the value of the column
     * @param mixed        $value         (optional) The value of the column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function or_where($key, $type_or_value=null, $value=null) {
        // create new group
        $this->where[] = [];
        // call where function
        return call_user_func_array([$this, 'where'], func_get_args());
    }

    /**
     * Add a group by section
     *
     * @param string|array   $column The column name or the raw string wrapped in an array
     * @param string|boolean $order  (optional) The order of the grouping, ASC/true or DESC/false
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function group_by($column, $order="ASC") {
        return $this->order_condition('group_by', func_num_args(), $column, $order);
    }

    /**
     * Add a having condition
     *
     * @param string|array $column        A column name, a raw condition wrapped in an array or multiple
     *                                    where conditions in an array
     * @param mixed        $type_or_value (optional) The type of the query (e.g. = or >) or the value of the column
     * @param mixed        $value         (optional) The value of the column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function having($column, $type_or_value=null, $value=null) {
        return $this->where_condition('having', func_num_args(), $column, $type_or_value, $value);
    }

    /**
     * Alias for Query::having
     *
     * @see \wp_activerecord\Query::having
     *
     * @param string|array $column        A column name, a raw condition wrapped in an array or multiple
     *                                    where conditions in an array
     * @param mixed        $type_or_value (optional) The type of the query (e.g. = or >) or the value of the column
     * @param mixed        $value         (optional) The value of the column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function and_having($column, $type_or_value=null, $value=null) {
        // call where function
        return call_user_func_array([$this, 'having'], func_get_args());
    }

    /**
     * Create a where condition and adds it with the keyword OR
     *
     * @param string|array $column        A column name, a raw condition wrapped in an array or multiple
     *                                    where conditions in an array
     * @param mixed        $type_or_value (optional) The type of the query (e.g. = or >) or the value of the column
     * @param mixed        $value         (optional) The value of the column
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function or_having($column, $type_or_value=null, $value=null) {
        // create new group
        $this->having[] = [];
        // call where function
        return call_user_func_array([$this, 'having'], func_get_args());
    }

    /**
     * Add a order by section
     *
     * @param string|array   $column The column name or the raw string wrapped in an array
     * @param string|boolean $order  (optional) The order of the grouping, ASC/true or DESC/false
     *
     * @return \wp_activerecord\Query The current query object
     */
    public function order_by($column, $order="ASC") {
        return $this->order_condition('order_by', func_num_args(), $column, $order);
    }

    /**
     * Add a limit
     *
     * @param int|string|array $limit The limit number or a raw string wrapped in an array
     *
     * @return \wp_activerecord\Query
     */
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add a offset
     *
     * @param int|string|array $limit The offset number or a raw string wrapped in an array
     *
     * @return \wp_activerecord\Query
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add a join
     *
     * @param $table
     * @param $attribute
     * @param $join_attribute
     *
     * @return \wp_activerecord\Query
     *
     */
    public function join($table, $attribute, $join_attribute, $type='inner') {
        $this->join[] = [$table, $attribute, $join_attribute, $type];
        return $this;
    }

    /**
     * Prepare the sql string and the variables for the final sql statement
     *
     * @return \stdClass A preparation object
     */
    public function prepare() {
        $model = $this->model;
        $table = $this->hasModel ? $model::get_table_name() : $model;

        $args = [];
        $sql = [];

        // SELECT, UPDATE, INSERT or DELETE
        if($this->type === 'DELETE') {
            $sql[] = sprintf("DELETE FROM `%s`", $table);
        } elseif($this->type === 'UPDATE') {
            $sql[] = sprintf("UPDATE `%s`", $table);
        } elseif($this->type === 'INSERT') {
            $sql[] = sprintf("INSERT INTO `%s`", $table);
        } else {
            $sql[] = sprintf("SELECT %s", $this->select ? join(", ", $this->select) : "*");
            if($table) {
                $sql[] = sprintf("FROM `%s`", $table);
            }
        }

        // SET
        if($this->set) {
            $this->prepare_set_condition($sql, $args);
        }

        // INSERT
        if($this->insert) {
            $this->prepare_insert_condition($sql, $args);
        }

        // JOIN
        if($this->join) {
            $this->prepare_join_condition($sql, $args);
        }

        // WHERE
        if($this->where) {
            $this->prepare_where_conditions('where', $sql, $args);
        }

        // GROUP BY
        if($this->group_by) {
            $this->prepare_order_conditions('group_by', $sql, $args);
        }

        // HAVING
        if($this->having) {
            $this->prepare_where_conditions('having', $sql, $args);
        }

        // ORDER BY
        if($this->order_by) {
            $this->prepare_order_conditions('order_by', $sql, $args);
        }

        // LIMIT
        if($this->limit) {
            $this->prepare_limit_condition('limit', $sql, $args);
        } elseif($this->offset) {
            $sql[] = "LIMIT 18446744073709551615";
        }

        // OFFSET
        if($this->offset) {
            $this->prepare_limit_condition('offset', $sql, $args);
        }

        // create output object
        $preparation = new \stdClass();
        $preparation->sql = join(" \n", $sql);
        $preparation->vars = $args;

        return $preparation;
    }

    /**
     * Create the final sql statement
     *
     * @return string The final sql statement
     */
    public function sql() {
        $preparation = $this->prepare();

        if($preparation->vars) {
            // add the sql string to the beginning of the args
            array_unshift($preparation->vars, $preparation->sql);
            $sql = call_user_func_array([static::wpdb(), 'prepare'], $preparation->vars);
        } else {
            $sql = $preparation->sql;
        }

        return $sql;
    }

    /**
     * Get the results of the query
     *
     * @return array The results as an array
     */
    public function get_results() {
        $results = $this->get_raw_results();
        return $this->get_casted_rows($results);
    }

    /**
     * Get the row of the query
     *
     * @return \stdClass The row as an object
     */
    public function get_row() {
        $row = $this->get_raw_row();
        return $this->get_casted_row($row);
    }

    /**
     * Get the column of the query
     *
     * @return array The column as an array
     * @throws \Exception
     */
    public function get_col() {
        if(count($this->select) !== 1) {
            throw new \Exception("Query.get_col: You have to provide exactly one select argument");
        }
        $prop = $this->select[0];
        $values = static::wpdb()->get_col($this->sql());

        $castedValues = [];
        foreach($values as $val) {
            $castedValues[] = $this->get_casted_value($prop, $val);
        }
        return $castedValues;
    }

    /**
     * Get the value of the query
     *
     * @return string The value returned by the query
     * @throws \Exception
     */
    public function get_var() {
        if(count($this->select) !== 1) {
            throw new \Exception("Query.get_var: You have to provide exactly one select argument");
        }
        $prop = $this->select[0];
        $var = static::wpdb()->get_var($this->sql());

        return $this->get_casted_value($prop, $var);
    }

    /**
     * Get the results of the query as an array of model instances
     *
     * @return array The results as an array of model instances
     */
    public function get() {
        if(!$this->hasModel) {
            return $this->get_results();
        }

        $modelClass = $this->model;
        $results = $this->get_raw_results();
        $models = [];
        foreach($results as $result) {
            $models[] = new $modelClass($result);
        }
        return $models;
    }

    /**
     * Get the results of the query as a model instances
     *
     * @return array The results as a model instances
     */
    public function get_one() {
        if(!$this->hasModel) {
            return $this->get_row();
        }

        $modelClass = $this->model;
        $result = $this->get_raw_row();
        if(!$result) {
            return null;
        }
        return new $modelClass($result);
    }

    /**
     * Execute the query
     *
     * @return null
     */
    public function execute() {
        return static::wpdb()->query($this->sql());
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

    protected function type($type) {
        if($this->type && $this->type !== $type) {
            throw new \Exception("The type of query is already '{$this->type}'");
        }
        $this->type = $type;
        return $this;
    }

    protected function where_condition($array, $num_args, $column, $type_or_value, $value) {
        $type = $type_or_value;
        $obj = null;
        if($num_args === 1) {
            if(is_string($column)) {
                $obj = [$column];
            } elseif(!is_array($column)) {
                throw new \Exception('Only one argument provided for function where, but this is not an string or array.');
            } elseif(array_key_exists(0, $column)) {
                $obj = $column;
            } else {
                foreach($column as $k => $v) {
                    if(is_array($v) && count($v) === 2) {
                        $this->{$array}($k, $v[0], $v[1]);
                    } else {
                        $this->{$array}($k, $v);
                    }
                }
                return $this;
            }
        }
        if($num_args === 2) {
            $value = $type_or_value;
            $type = is_null($value) ? 'IS' : '=';
        }
        if(is_null($value)) {
            // encode null value
            $value = ['NULL'];
        }
        if(!$obj) {
            $obj = new \stdClass();
            $obj->column = $column;
            $obj->type = strtoupper($type);
            $obj->value = $value;
        }
        if(!$this->{$array}) {
            $this->{$array}[] = [];
        }
        $this->{$array}[count($this->{$array})-1][] = $obj;
        return $this;
    }

    protected function order_condition($array, $num_args, $column, $order) {
        $obj = null;
        if(is_array($column)) {
            if(array_key_exists(0, $column)) {
                $obj = $column;
            } else {
                foreach($column as $k => $v) {
                    $this->{$array}($k, $v);
                }
                return $this;
            }
        } else {
            if($order === false || is_string($order) && strtoupper($order) !== "ASC") {
                $order = "DESC";
            }
            $obj = new \stdClass();
            $obj->column = $column;
            $obj->order = $order;
        }
        $this->{$array}[] = $obj;
        return $this;
    }

    protected function prepare_where_conditions($array, &$sql, &$args) {
        $where = [];
        foreach($this->{$array} as $group) {
            $items = [];
            foreach($group as $item) {
                if(is_array($item)) {
                    $items[] = array_shift($item);
                    $args = array_merge($args, $item);
                } else {
                    if(is_array($item->column)) {
                        $column = $item->column[0];
                    } else {
                        $column = "`{$item->column}`";
                    }

                    if(is_array($item->value)) {
                        $value = $item->value[0];
                        if(is_array($value)) {
                            $values = [];
                            foreach($value as $v) {
                                $values[] = '%s';
                                $args[] = $v;
                            }
                            $value = sprintf('(%s)', join(', ', $values));
                        }
                    } else {
                        $value = '%s';
                        $args[] = $item->value;
                    }
                    $items[] = sprintf("%s %s %s", $column, $item->type, $value);
                }
            }
            $where[] = sprintf("( %s )", join(" AND ", $items));
        }
        $sql[] = sprintf("%s %s", strtoupper($array), join(" OR ", $where));
    }

    protected function prepare_order_conditions($array, &$sql, &$args) {
        $group_by = [];
        foreach($this->{$array} as $item) {
            if(is_array($item)) {
                $group_by[] = array_shift($item);
                $args = array_merge($args, $item);
            } else {
                $group_by[] = sprintf("`%s` %s", $item->column, $item->order);
            }
        }
        $sql[] = sprintf("%s %s", strtoupper(str_replace('_', ' ', $array)), join(", ", $group_by));
    }

    protected function prepare_limit_condition($array, &$sql, &$args) {
        $limit = $this->{$array};
        if(is_array($limit)) {
            $sql[] = strtoupper($array) . " " . array_shift($limit);
            $args = array_merge($args, $limit);
        } else {
            $args[] = (int)$limit;
            $sql[] = strtoupper($array) . " %d";
        }
    }

    protected function prepare_set_condition(&$sql, &$args) {
        $set = [];
        foreach($this->set as $key => $value) {
            if(is_array($value)) {
                // include decoded value directly
                $set[] = sprintf("`%s` = %s", $key, $value[0]);
            } else {
                $set[] = sprintf("`%s` = %%s", $key);
                $args[] = $value;
            }
        }
        $sql[] = sprintf("SET %s", join(", ", $set));
    }

    protected function prepare_insert_condition(&$sql, &$args) {
        $insert = [];
        $columns = array_keys($this->insert[0]);
        $escapedColumns = [];
        foreach($columns as $column) {
            $escapedColumns[] = "`".$column."`";
        }

        $values = [];
        foreach($this->insert as $row) {
            $rowValues = [];
            foreach($columns as $column) {
                if(is_array($row[$column])) {
                    // decode raw value
                    $rowValues[] = $row[$column][0];
                } else {
                    $rowValues[] = '%s';
                    $args[] = $row[$column];
                }
            }
            $values[] = sprintf("(%s)", join(', ', $rowValues));
        }
        $sql[] = sprintf("(%s) VALUES %s", join(", ", $escapedColumns), join(", ", $values));
    }

    protected function prepare_join_condition(&$sql, &$args) {
        $model = $this->model;
        $table = $this->hasModel ? $model::get_table_name() : $model;

        foreach($this->join as $row) {
            $type = strtoupper($row[3]);
            $sql[] = "{$type} JOIN `{$row[0]}` ON `{$table}`.`{$row[1]}` = `{$row[0]}`.`{$row[2]}`";
        }
    }

    protected function get_casted_rows(array $rows) {
        $castedRows = [];
        foreach($rows as $row) {
            $castedRows[] = $this->get_casted_row($row);
        }
        return $castedRows;
    }

    protected function get_casted_row(array $row) {
        $castedRow = [];
        foreach($row as $key => $value) {
            $castedRow[$key] = $this->get_casted_value($key, $value);
        }
        return $castedRow;
    }

    protected function get_casted_value($prop, $val) {
        if($this->hasModel) {
            $model = $this->model;
            return $model::get_casted_value($prop, $val);
        }
        return $val;
    }

    protected function get_raw_results() {
        return static::wpdb()->get_results($this->sql(), 'ARRAY_A');
    }

    protected function get_raw_row() {
        return static::wpdb()->get_row($this->sql(), 'ARRAY_A');
    }
}
