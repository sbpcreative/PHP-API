<?php

namespace Miituu;

class Wall extends Model {

    protected $path = 'walls/';

    public $fields = array( 'id', 'company_id', 'slug', 'status', 'created_at', 'updated_at' );

    public $mutable = array( 'slug', 'status' );

    public $endpoints = array( 'published' );

}
