<?php

namespace Miituu;

class Token extends Model {

    protected $path = 'companies/';

    public $fields = array('token', 'company_id', 'level_id', 'expires', 'updated_at', 'created_at', 'id');

    /*
     *  Take a company slug and request a public token
     */
    public static function publicAuth($slug) {
        return Token::call('public_token', array('slug' => $slug));
    }

}
