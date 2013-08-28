<?php

namespace Miituu;

use \Miituu\Company as Company;
use \Miituu\Collection as Collection;
use \Miituu\Answer as Answer;
use \Miituu\Media as Media;
use \Miituu\Rendition as Rendition;

class Model extends Api implements \Iterator {

    // Params to be used for requests
    private $params          = array();
    // The current position for iterating over multiple items
    private $position        = 0;
    // An array to save these multiple items in
    private $items           = array();

    // All unmodified data for the current item
    private $clean           = array();
    // All modified data for the current item
    private $dirty           = array();
    // Only keys in this array are allowed to be changed
    protected $mutalble      = array();
    // Details of the types of relations the model can have
    protected $relations     = array();

    // Paging information
    private $current_page    = null;
    private $total_items     = null;
    private $total_pages     = null;
    private $per_page        = null;
    private $next_page       = null;
    private $prev_page       = null;

    /*
     *  Make a call to the API, saves the result into the current model and return $this
     */
    public function _call($endpoint = '', $data = array(), $method = 'GET', $auth = true)
    {
        // Initialize Guzzle
        $client = new \Guzzle\Http\Client(self::$base, array(
            'request.options' => array(
                'proxy'   => 'http:://192.168.1.96:8888'
            )
        ));

        // If auth is available and not disabled, create headers with the token
        $headers = ($auth && self::$token) ? array('X-App-Token' => self::$token->token) : array();

        // Add any paging settings to the params
        if ($this->per_page)     $this->params['per_page'] = $this->per_page;
        if ($this->current_page) $this->params['page']     = $this->current_page;

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

            // We've received multiple items, append them, save the paging info, and select the current item
            if (el($body, 'body', false)) {
                $this->items = array_merge($this->items, el($body, 'body'));
                $this->setPaging($body);
                $this->current();

            // If not, just fill the object with the current info
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
        return $this->call();
    }

    /*
     *  Take a string or array of related models to include
     */
    public function _with($withs) {
        // If we got a single item, make sure it's an array
        if (!is_array($withs)) $withs = array($withs);

        foreach ($withs as $with) {
            $this->params['include_'.$with] = 1;
        }

        return $this;
    }

    /*
     *  Specify params for the call, such as limiting answers by collection_id
     */
    public function _where($fields, $value = null) {
        if (!is_array($fields)) $fields = array($fields => $value);

        $this->params = array_merge($this->params, $fields);

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

        $this->next_page     = $this->current_page < $this->total_pages ? $this->current_page += 1 : null;
        $this->prev_page     = $this->current_page > 1 ? $this->current_page -= 1 : null;
    }

    /*
     *  Increment the current_page, make the request, and return the result
     */
    public function nextPage() {
        if ($this->current_page >= $this->total_pages) return false;

        $this->current_page++;

        return $this->call();
    }

    /*
     *  Specify which page to request from the API
     */
    public function _page( $int ) {
        $this->current_page = $int;

        return $this;
    }

    /*
     *  Set the per_page parameter, specify how many items the API should return
     */
    public function _take( $int ) {
        $this->per_page = $int;

        return $this;
    }

    /*
     *  Take an array or object and populate the models fields with it
     */
    public function _fill($data) {
        $this->reset();

        // Look for each field and fill with data
        foreach ($this->fields as $field) {
            $this->clean[$field] = el($data, $field);
        }

        // Look for any relations we should create
        foreach ($this->relations as $details) {
            if ($data = el($data, $details['key'])) {
                // This relation exists in this item

                if ($details['multiple']) {
                    $this->$details['key'] = $details['model']::setItems($data);

                } else {
                    $this->$details['key'] = $details['model']::fill($data);
                }
            }
        }

        return $this;
    }

    /*
     *  Fill a model with multiple items, used when creating included related models
     */
    public function _setItems($data) {
        $this->items = $data;

        return $this;
    }

    /*
     *  Reset all parameters to empty
     */
    public function reset() {
        $this->clean = array();
        $this->dirty = array();
        foreach ($this->relations as $details) {
            $this->{$details['key']} = array();
        }
    }

    /*
     *  Returns BOOL, true if the object is new or has been modified
     */
    public function _dirty() {
        return !$this->exists || count($this->dirty);
    }

    /*
     *  Return BOOL, true if the object exists in the API
     */
    public function _exists() {
        return ($this->id);
    }

    /*
     *  Return an associative array of modified parameters
     */
    public function getDirty() {
        return $this->dirty;
    }

    /*
     *  Return all fields in an associative array, including dirty items
     */
    public function all() {
        return array_merge($this->clean, $this->dirty);
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

    function count() {
        return count($this->items);
    }

    /*
     *  Handle dynamic retrieval of attributes
     */
    public function __get($key) {
        // Simply return the property, if one exists
        if (isset($this->$key)) return $this->$key;

        // If a method exists for it, it'll be retrieving a relationship, so get it
        if (method_exists($this, $key)) {
            return $this->$key()->get();
        }

        // If there is a modified param for this key, return it
        if (el($this->dirty, $key)) return el($this->dirty, $key);

        // If there is a clean param for this key, return it
        if (el($this->clean, $key)) return el($this->clean, $key);

        // If not, see if the dynamic call method can handle it
        return $this->$key();
    }

    /*
     *  Handle dynamic setting of parameters, only items in the mutable array can be updated
     */
    public function __set($key, $value) {
        if (in_array($key, $this->mutable)) {
            return $this->dirty[$key] = $value;
        } else {
            foreach ($this->relations as $rel) {
                if ($rel['key'] == $key) return $this->$key = $value;
            }
            return false;
        }
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
