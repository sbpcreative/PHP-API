<?php
dd('sdgsg');
namespace Miituu;

use Guzzle\Http\Client;

class Api
{
    private static $base  = 'http://api.miituu.dev/';
    private static $token = false;

    public $success = null;

    public function __construct()
    {

    }

    public static function startFromSlug($slug)
    {
        $token = Token::fromSlug($slug);
        if ($token->success) {
            self::$token = $token->token;
        }

        return $token;
    }

    public function call($endpoint, $data = array(), $method = 'GET', $auth = true)
    {
        $client = new \Guzzle\Http\Client(self::$base);

        $headers = ($auth && self::$token) ? array('headers/X-API-Token' => self::$token) : array();

        if ($method == 'GET') {
            $response = $client->get($endpoint, $headers, array(
                'query' => $data,
                'exceptions' => false
            ))->send();

        } else if ($method == 'POST') {
            $response = $client->post($endpoint, $headers, $data)->send();
        }

        if ($response->getStatusCode() !== 200) {
            $this->success = false;
        } else {
            $this->success = true;
        }
    }
}
