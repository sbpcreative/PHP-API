<?php

use Guzzle\Http\Client;

namespace Miituu;

class Api
{
    protected static $base  = 'http://api.miituu.dev/';
    public static $token = false;

    public $success = null;

    public function __construct()
    {

    }

    /*
     *  Take a company slug and start a session
     */
    public static function authFromSlug($slug)
    {
        // Public auth is controlled via the company model
        $token = Token::publicAuth($slug);

        // If success, save the auth details
        if ($token->success) {
            self::$token = $token;
        }

        // Return the details
        return $token;
    }

    /*
     *  Return a JSON string with the auth data for restoring a session later
     */
    public static function authStr() {
        return json_encode(self::$token->all());
    }

    /*
     *  Restore auth detail from a JSON string
     */
    public static function restore($str) {
        self::$token = Token::fill(json_decode($str));
    }
}

/*
 *  A simple function that returns an item from an object/array, or the optional default value
 */
function el($data, $field, $default = null) {
    // Array and has field
    if (is_array($data) && array_key_exists($field, $data)) {
        return $data[$field];

    // Object and has field
    } else if (is_object($data) && property_exists($data, $field)) {
        return $data->$field;

    // Return the default
    } else {
        return $default;
    }
}
