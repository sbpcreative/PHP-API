<?php

namespace Miituu;

class Userfieldanswer extends Model {

    protected $path = null;

    public $fields = array('id', 'userfield_id', 'respondent_id', 'created_at', 'updated_at', 'answer', 'status');

    public $mutable = array();

    public $relations = array(
        array(
            'key'       => 'respondent',
            'model'     => '\Miituu\Respondent',
            'multiple'  => false
        ),
        array(
            'key'       => 'userfield',
            'model'     => '\Miituu\Userfield',
            'multiple'  => false
        )
    );

}
