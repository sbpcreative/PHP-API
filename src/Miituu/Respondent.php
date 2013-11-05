<?php

namespace Miituu;

class Respondent extends Model {

    protected $path = 'respondents/';

    public $fields = array('id', 'questionnaire_id', 'ip_address', 'created_at', 'updated_at', 'status', 'progress', 'accepted', 'search');

    public $mutable = array();

    public $relations = array(
        array(
            'key'       => 'answers',
            'model'     => '\Miituu\Answer',
            'multiple'  => true
        ),
        array(
            'key'       => 'userfieldanswers',
            'model'     => '\Miituu\UserfieldAnswer',
            'multiple'  => true
        )
    );

}
