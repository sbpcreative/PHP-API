<?php

namespace Miituu;

class Answer extends Model {

    protected $path = 'answers/';

    public $fields = array('id', 'question_id', 'respondent_id', 'media_id', 'text', 'type', 'multichoice', 'status', 'order', 'created_at', 'updated_at', 'rating', 'company_id', 'featured', 'description', 'search');

    public $mutable = array('text', 'multichoice', 'status', 'rating', 'featured', 'description');

    public $has_status = true;

    public $relations = array(
        array(
            'key'       => 'media',
            'model'     => '\Miituu\Media',
            'multiple'  => false
        ),
        array(
            'key'       => 'question',
            'model'     => '\Miituu\Question',
            'multiple'  => false
        ),
        array(
            'key'       => 'respondent',
            'model'     => '\Miituu\Respondent',
            'multiple'  => false
        ),
        array(
            'key'       => 'transcripts',
            'model'     => '\Miituu\Transcript',
            'multiple'  => true
        )
    );

    public function media() {
        return null;
        // return Media::where('answer_id', $this->id);
    }

}
