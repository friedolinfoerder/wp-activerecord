<?php

/**
 * wpdbMock
 *
 * @author Friedolin Förder
 */
class wpdbMock {
    
    public $sql;
    public $prefix = 'prefix_';
    public $insert_id;
    
    public function prepare() {
        if(func_num_args() < 2) {
            throw new Exception("There must be more than one argument");
        }
        $args = func_get_args();
        for ($index = 1; $index < count($args); $index++) {
            $args[$index] = "'$args[$index]'";
        }
        return call_user_func_array('sprintf', $args);
    }
    
    public function query($sql) {
        $this->insert_id = 12;
        $this->sql = $sql;
    }
    
    public function get_var($sql) {
        $this->query($sql);
        return 'var';
    }
    
    public function get_row($sql) {
        $this->query($sql);
        return [];
    }
}

// create global mock
$wpdb = new wpdbMock();

define('OBJECT_K', 1);