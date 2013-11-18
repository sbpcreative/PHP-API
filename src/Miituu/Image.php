<?php

namespace Miituu;

class Image extends Model {

    protected $path = 'images/';

    public $fields = array('id', 'company_id', 'user_id', 'questionnaire_id', 'question_id', 'answer_id', 'status', 'mime', 'width', 'height', 'filesize', 'url', 'orig_filename', 'filename', 'progress', 'title', 'created_at', 'updated_at', 'type', 'expires', 'url_original', 'slug');

    public $mutable = array('title', 'status');

    public $relations = array(
        array(
            'key' => 'company',
            'model' => '\Miituu\Company',
            'multiple' => false
        ),
        // TODO: The relations need added to these models still
        array(
            'key' => 'user',
            'model' => '\Miituu\User',
            'multiple' => false
        ),
        array(
            'key' => 'questionnaire',
            'model' => '\Miituu\Questionnaire',
            'multiple' => false
        ),
        array(
            'key' => 'question',
            'model' => '\Miituu\Question',
            'multiple' => false
        ),
        array(
            'key' => 'answer',
            'model' => '\Miituu\Answer',
            'multiple' => false
        )
    );

}
