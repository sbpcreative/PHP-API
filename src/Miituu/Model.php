<?php

namespace Miituu;

class Model extends Api {

    public $withs = array();

    /*
     *  Make a call to the API, saves the result into the current model and return $this
     */
    public function call($endpoint, $data = array(), $method = 'GET', $auth = true)
    {
        // Initialize Guzzle
        $client = new \Guzzle\Http\Client(self::$base, array(
            'request.options' => array(
                'proxy'   => 'http:://192.168.1.96:8888'
            )
        ));

        // If auth is available and not disabled, create headers with the token
        $headers = ($auth && self::$auth) ? array('X-App-Token' => self::$auth->token) : array();

        // If we have an array of things to include, add that to the data
        if ($this->withs) $data = array_merge($data, $this->withs);

        // Append the endpoint to the current model's path
        $url = $this->path . $endpoint;

        // Configure GET request
        if ($method == 'GET') {
            $response = $client->get($url, $headers, array(
                'query' => $data,
                'exceptions' => false
            ))->send();

        // Or POST request
        } else if ($method == 'POST') {
            $response = $client->post($url, $headers, $data)->send();
        }

        // Parse the response body
        $body = $response->json();

        // If failure, save the error messages
        if ($response->getStatusCode() != 200) {
            $this->success = false;
            $this->errors = $body['messages'];

        // If success, save all responses
        } else {
            $this->success = true;
            foreach ($body as $key => $value) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /*
     *  Usually the get function is a simple call to the model's path
     *  This can be overridden by models if required
     */
    public function get() {
        return $this->call('');
    }

    /*
     *  Take a string or array of related models to include
     */
    public function _with($withs) {
        // If we got a single item, make sure it's an array
        if (!is_array($withs)) $withs = array($withs);

        foreach ($withs as $with) {
            $this->withs['include_'.$with] = 1;
        }

        return $this;
    }

    /*
     *  Take dynamic method calls and map them to the correct function
     */
    public function __call($method, $parameters) {
        if ($method == 'with') return call_user_func_array(array($this, '_with'), $parameters);

        return call_user_func_array(array($this, $method), $parameters);
    }

    /*
     *  Take static calls and create an instance of the model
     */
    public static function __callStatic($method, $parameters)
    {
        // Create a new instance of the called class
        $model = get_called_class();

        // Call the requested method on the newly created object
        return call_user_func_array(array(new $model, $method), $parameters);
    }

}
