<?php

namespace Miituu;

class Collection extends Model {

    protected $path = 'collections/';

    public $fields = array('id', 'company_id', 'user_id', 'name', 'created_at', 'updated_at', 'status');

}
