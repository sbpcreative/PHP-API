<?php

namespace Miituu;

class Collection extends Model {

    protected $path = 'collections/';

    public $fields = array('id', 'company_id', 'user_id', 'name', 'created_at', 'updated_at', 'status');

    public $mutable = array('name');

    public $relations = array(
        array(
            'key' => 'answers',
            'model' => '\Miituu\Answer',
            'multiple' => true
        )
    );

    public function answers() {
        return Answer::where('collection_id', $this->id);
    }
}
