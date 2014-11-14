<?php

/**
 * wpdbMock
 *
 * @author Friedolin Förder
 */
class wpdbMock {
    
    public function prepare() {
        if(func_num_args() < 2) {
            throw new Exception("There must be more than one argument");
        }
        return call_user_func_array('sprintf', func_get_args());
    }
}

// create global mock
$wpdb = new wpdbMock();