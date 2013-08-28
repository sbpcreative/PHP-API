<?php

namespace Miituu;

class Media extends Model {

    protected $path = 'media/';

    public $fields = array('id', 'company_id', 'type', 'duration', 'start_time', 'end_time', 'status', 'created_at', 'updated_at', 'slug', 'name', 'panda_id');

    public $mutable = array('start_time', 'end_time', 'status', 'name');

    public $relations = array(
        array(
            'key' => 'renditions',
            'model' => '\Miituu\Rendition',
            'multiple' => true
        )
    );

    /*
     *  Return the URL from a rendition specified by it's name
     */
    public function url($name) {
        foreach ($this->renditions as $rendition) {
            if ($rendition->name == $name) return $rendition->url;
        }
        return false;
    }

}
