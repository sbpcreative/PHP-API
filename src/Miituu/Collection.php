<?php

namespace Miituu;

class Collection extends Model {

    protected $path = 'collections/';

    public $fields = array('id', 'company_id', 'user_id', 'name', 'created_at', 'updated_at', 'status', 'slug');

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

    /*
     *  Take an answer object or ID, and change it's position in the current collection
     */
    public function moveAnswer($answer, $position) {
        // Get the ID from an answer object, or assume $answer is the ID
        $answer_id = $answer instanceof \Miituu\Answer ? $answer->id : $answer;

        return $this->call('order', array('answer_id' => $answer_id, 'to' => $position), 'POST');
    }
}
