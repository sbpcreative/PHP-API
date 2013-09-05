<?php

namespace Miituu;

class Question extends Model {

    protected $path = 'questions/';

    public $fields = array('id', 'questionnaire_id', 'media_id', 'name', 'accepts', 'text', 'multichoice', 'required', 'status', 'order', 'created_at', 'updated_at', 'type', 'maximum_selections', 'user_id', 'search');

    public $mutable = array('name', 'accepts', 'text', 'multichoice', 'required', 'order', 'type', 'maximum_selections', 'status');

    public $relations = array(
        array(
            'key' => 'answers',
            'model' => '\Miituu\Answer',
            'multiple' => true
        )
    );

}
