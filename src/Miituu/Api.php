<?php

use Guzzle\Http\Client;

namespace Miituu;

class Api
{
    protected static $base  = 'http://api.miituu.dev/';
    protected static $auth = false;

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
        $company = Company::publicAuth($slug);

        // If success, save the auth details
        if ($company->success) {
            self::$auth = $company;
        }

        // Return the details
        return $company;
    }

    /*
     *  Return a JSON string with the auth data for restoring a session later
     */
    public static function authStr() {
        return json_encode(self::$auth);
    }

    /*
     *  Restore auth detail from a JSON string
     */
    public static function restore($str) {
        self::$auth = json_decode($str);
    }


}
