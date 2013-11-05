<?php

use Guzzle\Http\Client;

namespace Miituu;

class Api
{
    protected static $base      = 'https://api.miituu.com/';

    const LEVEL_PUBLIC          = 4;
    const LEVEL_ADMIN           = 3;
    const LEVEL_OWNER           = 2;
    const LEVEL_MIITUU          = 1;

    const STATUS_PUBLISHED      = 1;

    // These will be filled with model details when authentication happens
    public static $token        = false;
    public static $company      = false;
    public static $user         = false;
    public static $permissions  = false;
    public static $boltons      = false;

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
    public static function authStr()
    {
        return self::$token->token;
    }

    /*
     *  Restore auth details from a token, optionally accepts a public string which it will re-auth with if necessary
     */
    public static function restore($token, $slug = false, $include = false)
    {
        self::$token = Token::fill(array('token' => $token));

        // We can't method chain this check, as self::$token won't exist by the time the call is made
        self::$token->check($include);

        // If restore failed and we have a slug, try publicAuth
        if (!self::$token->success && $slug) {
            return self::publicAuth($slug);
        }

        return self::$token;
    }

    /*
     *  Calls the API to check the auth is valid and refresh all user/company/auth data
     *  This is not usually necessary, as the auth and restore methods all do this
     */
    public static function checkAuth($include = false) {
        return Token::check($include);
    }

    /*
     *  Returns true if there are current auth details, but does not validate them with the API
     */
    public static function hasAuth() {
        return self::$token;
    }

    /*
     *  Login an existing user using email address and password
     */
    public static function login($email, $password)
    {
        self::$token = Token::login($email, $password);

        return self::$token;
    }

    /*
     *  Returns the current company, usually without calling the API
     */
    public static function company() {
        if (self::$company) {
            return self::$company;

        } else {
            return Company::get();
        }
    }

    /*
     *  If a level is provided, return true if the current auth level is at or higher
     *  See top for file level constants
     *  PLEASE NOTE: Lower numbers indicate higher auth level
     *  If a level is not provided, return the current auth level, or false
     */
    public static function level( $level = null )
    {
        if ($level) {
            return (self::$token && self::$token->level_id && self::$token->level_id <= $level);

        } else {
            return (self::$token && self::$token->level_id) ? self::$token->level_id : false;
        }
    }

    /*
     *  Return true if the current auth allows the provided permission
     *  See the docs for a list of all possible permissions
     */
    public static function can( $permission )
    {
        // If no permissions are loaded, the answer is no
        if (!self::$permissions || !is_array(self::$permissions)) return false;

        foreach (self::$permissions as $perm) {
            if (el($perm, 'slug') == $permission) return true;
        }

        return false;
    }


    /*
     *  Return a list of all calls made so far by the API, for debugging purposes
     *  Optionally [slightly] formatted, default
     */
    public static function calls( $format = true )
    {
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
function el($data, $field, $default = null)
{
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
