<?php

namespace Miituu;

class Token extends Model {

    protected $path = 'auth/';

    public $fields = array('token', 'company_id', 'level_id', 'expires', 'updated_at', 'created_at', 'id', 'permissions', 'company');

    public $relations = array(
        array(
            'key' => 'user',
            'model' => '\Miituu\User',
            'multiple' => false
        ),
        array(
            'key' => 'company',
            'model' => '\Miituu\Company',
            'multiple' => false
        )
    );

    /*
     *  Take a company slug and request a public token
     */
    public static function publicAuth($slug) {
        $token = Token::with(array('company', 'permissions'))->call('public', array('slug' => $slug));

        // If we we're successful, copy these details to the Api::static for easy access
        if ($token->success) {
            if ($token->company)        Api::$company       = $token->company;
            if ($token->permissions)    Api::$permissions   = $token->permissions;
        }

        return $token;
    }

    /*
     *  Take an existing users's email address and password, and log them in
     */
    public static function login($email, $password) {
        $token = Token::with(array('company', 'user', 'permissions'))
                      ->call('login', array('email' => $email, 'password' => $password), 'POST');

        if ($token->success) {
            if ($token->company)        Api::$company       = $token->company;
            if ($token->user)           Api::$user          = $token->user;
            if ($token->permissions)    Api::$permissions   = $token->permissions;
            if ($token->user && el($token->user, 'permissions'))
                                        Api::$permissions   = el($token->user, 'permissions');
        }

        return $token;
    }


    /*
     *  When there is already a token, check with the API that it is still valid
     *  Also refreshes the company, permissions, and (if applicable) the user
     */
    public function check($include = false) {
        $include = is_array($include) ? $include : array('company', 'user', 'permissions', 'boltons');

        $this->with($include)->call();

        // If we we're successful, copy these details to the Api::static for easy access
        if ($this->success) {
            if ($this->company)        Api::$company       = $this->company;
            if ($this->user)           Api::$user          = $this->user;
            if ($this->permissions)    Api::$permissions   = $this->permissions;

            if ($this->company && el($this->company, 'boltons'))
                                       Api::$boltons       = el($this->company, 'boltons');

            if ($this->user && el($this->user, 'permissions'))
                                       Api::$permissions   = el($this->user, 'permissions');
        }

        return $this;
    }

}
