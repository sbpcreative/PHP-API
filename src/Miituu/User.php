<?php

namespace Miituu;

class User extends Model {

    protected $path = 'users/';

    public $fields = array('id', 'company_id', 'forename', 'surname', 'email', 'email_verified', 'email_verify_token', 'password', 'status', 'created_at', 'updated_at', 'level_id');

    public $mutable = array('forename', 'surname', 'email', 'password');


}
