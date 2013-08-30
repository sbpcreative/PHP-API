<?php

namespace Miituu;

use \Miituu\Company as Company;
use \Miituu\Collection as Collection;
use \Miituu\Answer as Answer;
use \Miituu\Media as Media;
use \Miituu\Rendition as Rendition;

class Model extends Api implements \Iterator {

    // Access this to indicate if the call was successful or not (BOOL)
    public $success             = null;

    // Params to be used for requests
    private $params          = array();
    // The current position for iterating over multiple items
    private $position        = 0;
    // An array to save these multiple items in
    private $items           = array();

    // These are configure in each model
    public $fields           = array();
    public $mutable          = array();
    public $errors           = array();

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

        self::$calls[] = array(
            'path'          => $url,
            'status'        => $response->getStatusCode()
        );

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
    public function _with() {
        // Build an array of everything to include
        $withs = array();

        // Loop through all the arguments
        $_withs = func_get_args();
        foreach ($_withs as $w) {
            // Add everything to the $withs, be it array or string
            if (is_array($w))  $withs   = array_merge($withs, $w);
            if (is_string($w)) $withs[] = $w;
        }

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

        $this->next_page     = $this->current_page < $this->total_pages ? $this->current_page + 1 : null;
        $this->prev_page     = $this->current_page > 1 ? $this->current_page - 1 : null;
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
    public function _per_page( $int ) {
        $this->per_page = $int;

        return $this;
    }

    /*
     *  Wraps the _per_page function
     */
    public function _take( $int ) {
        return $this->_per_page($int);
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
            if ($model_data = el($data, $details['key'])) {
                // This relation exists in this item

                if ($details['multiple']) {
                    $this->$details['key'] = $details['model']::setItems($model_data);

                } else {
                    $this->$details['key'] = $details['model']::fill($model_data);
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
            // If this relation is in existence, unset it
            if (isset($this->{$details['key']}))
                unset($this->{$details['key']});
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
     *  Return true if there are multiple items to loop through
     */
    public function multiple() {
        return count($this->items) > 0;
    }

    /*
     *  Return all fields in an associative array, including dirty items
     *  Optionally, if there are multiple items, return them all as an array
     *  Optionally, return an object instead, probably use the to_object method though
     */
    public function to_array($as_object = false, $all = null) {
        // Unless all was specific, work it out for ourselves
        if ($all === null) $all = $this->multiple();

        // Return all items?
        if ($all) {
            $result = array();
            foreach ($this as $item) {
                // We're getting recursive here, make sure we don't loop over ourself infinitely!
                $result[] = $item->to_array($as_object, false);
            }
            return $result;
        }

        // Get all the relations for this object
        $relations = array();
        foreach ($this->relations as $rel) {
            if (isset($this->{$rel['key']})) {
                // Convert the related model to an array too,
                $relations[ $rel['key'] ] = $this->{$rel['key']}->to_array();
            }
        }

        // Put it all together
        $data = array_merge($this->clean, $this->dirty, $relations);

        // Return it as an abject?
        if ($as_object) {
            return (object)$data;
        } else {
            return $data;
        }
    }


    /*
     *  Wraps the to_array function, but instructs it to return objects
     *  Getting an object from a function called to_array would be pretty confusing!
     */
    public function to_object($all = null) {
        return $this->to_array(true, $all);
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

        // We got nothing
        return null;
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
