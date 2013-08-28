<?php

namespace Miituu;

class Model extends Api implements \Iterator {

    public $params = array();
    public $position = 0;
    public $body = 0;

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

        // If we have an array of params, add that to the data
        if ($this->params) $data = array_merge($this->params, $data);

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

            if (isset($body['body'])) {
                $this->items = $body['body'];
                $this->setPaging($body);
                $this->current();
            } else {
                $this->fill($body);
            }

        }

        return $this;
    }

    /*
     *  Request a particular item by ID
     */
    public function _find($id) {
        return $this->call('', array('id' => $id));
    }

    /*
     *  Usually the get function is a simple call to the model's path
     *  This can be overridden by models if required
     */
    public function _get() {
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
     *  Save the current paging information from the API
     */
    public function setPaging($body) {
        $this->current_page  = el($body, 'current_page');
        $this->total_items   = el($body, 'total_items');
        $this->total_pages   = el($body, 'total_pages');
        $this->per_page      = el($body, 'per_page');
    }

    /*
     *  Take an array or object and populate the models fields with it
     */
    public function fill($data) {
        foreach ($this->fields as $field) {
            $this->$field = el($data, $field);
        }
    }

    /*
     *  Iterator methods, these allow us to loop through results
     */
    function rewind() {
        $this->position = 0;
    }

    function current() {
        $this->fill( $this->items[$this->position] );
        return $this;
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->items[$this->position]);
    }

    /*
     *  Take dynamic method calls and map them to the correct function
     */
    public function __call($method, $parameters) {
        // If the requested method is available with an underscore prefix, call it
        if (method_exists($this, '_'.$method))
            return call_user_func_array(array($this, '_'.$method), $parameters);
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
