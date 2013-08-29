<?php

use Guzzle\Http\Client;

namespace Miituu;

class Api
{
    protected static $base      = 'http://api.miituu.dev/';

    // These will be filled with model details when authentication happens
    public static $token        = false;
    public static $company      = false;
    public static $user         = false;
    public static $permissions  = false;

    //Build an array of each call that's made, for debugging purposes
    protected static $calls       = array();

    public function __construct()
    {

    }

    /*
     *  Take a company slug and start a session
     */
    public static function publicAuth($slug)
    {
        // Public auth is controlled via the company model
        return self::$token = Token::publicAuth($slug);
    }

    /*
     *  Return a JSON string with the auth data for restoring a session later
     */
    public static function authStr() {
        return self::$token->token;
    }

    /*
     *  Restore auth details from a token, optionally accepts a public string which it will re-auth with if necessary
     */
    public static function restore($token, $slug = false) {
        self::$token = Token::fill(array('token' => $token));
        // We can't method chain this check, as self::$token won't exist by the time the call is made
        self::$token->check();

        // If restore failed and we have a slug, try publicAuth
        if (!self::$token->success && $slug) {
            return self::publicAuth($slug);
        }

        return self::$token;
    }

    /*
     *  Login an existing user using email address and password
     */
    public static function login($email, $password) {
        self::$token = Token::login($email, $password);

        return self::$token;
    }

    /*
     *  Return a list of all calls made so far by the API, for debugging purposes
     *  Optionally [slightly] formatted, default
     */
    public static function calls( $format = true ) {
        if (!$format) return self::$calls;

        $html = '<table>';
        foreach (self::$calls as $call) {
            foreach ($call as $field => $value) {
                $html .= "<tr><th>{$field}:</th><td>{$value}</td></tr>";
            }
            $html .= '<tr><td colspan="2"><hr></td></tr>';
        }
        $html .= '</table>';

        return $html;
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
