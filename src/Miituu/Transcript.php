<?php

namespace Miituu;

class Transcript extends Model {

    protected $path = null;

    public $fields = array( 'id', 'answer_id', 'text', 'order', 'created_at', 'updated_at', 'status' );

    public $mutable = array( 'text', 'order' );

    public $relations = array(
        array(
            'key'       => 'anwer',
            'model'     => '\Miituu\Answer',
            'multiple'  => false
        )
    );

}
