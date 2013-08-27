<?php

use Guzzle\Http\Client;
namespace miituu;

class miituu
{
    private $base = 'http://api.miituu.com/';

    public function __construct()
    {

    }

    public static function startFromSlug($slug)
    {

    }

    public function call($endpoint, $data = array(), $method = 'GET', $auth = true)
    {
        $client = new \Guzzle\Http\Client(self::$base);

        $headers = ($auth && self::$token) ? array('headers/X-API-Token' => self::$token) : array();

        if ($method == 'GET') {
            $request = $client->get($endpoint, $headers, array(
                'query' => $data
            ));

        } else if ($method == 'POST') {
            $request = $client->get($endpoint, $headers, $data);

        }

        dd($request);
    }
}
