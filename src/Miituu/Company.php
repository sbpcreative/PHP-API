<?php

namespace Miituu;

class Company extends Model {

    protected $path = 'companies/';

    public $fields = array('id', 'name', 'token');

    /*
     *  Take a company slug and request a public token
     */
    public static function publicAuth($slug) {
        $company = new Company;
        return $company->call('public_token', array('slug' => $slug));
    }

}
