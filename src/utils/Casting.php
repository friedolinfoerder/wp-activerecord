<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 6/18/18
 * Time: 8:12 AM
 */

namespace wp_activerecord\utils;

use DateTime;

class Casting
{
    public static $alias = [
        'integer' => 'int',
        'number' => 'float',
        'bool' => 'boolean',
    ];

    public static function cast_int($val) {
        return (int)$val;
    }

    public static function cast_float($val) {
        return (float)$val;
    }

    public static function cast_boolean($val) {
        return (boolean)$val;
    }
    public static function decast_boolean($val) {
        return $val ? 1 : 0;
    }

    public static function cast_datetime($val) {
        return DateTime::setTimestamp(strtotime($val));
    }
    public static function decast_datetime(DateTime $val) {
        return $val->format('Y-m-d H:i:s');
    }
}
