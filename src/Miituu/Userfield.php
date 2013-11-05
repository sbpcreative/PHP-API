<?php

namespace Miituu;

class Userfield extends Model {

    protected $path = null;

    public $fields = array('id', 'questionnaire_id', 'title', 'order', 'created_at', 'updated_at', 'required', 'status');

    public $mutable = array();

    public $relations = array(
        array(
            'key'       => 'questionnaire',
            'model'     => '\Miituu\Questionnaire',
            'multiple'  => false
        ),
        array(
            'key'       => 'userfieldanswer',
            'model'     => '\Miituu\Userfieldanswer',
            'multiple'  => true
        )
    );

}
