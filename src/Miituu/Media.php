<?php

namespace Miituu;

class Media extends Model {

    protected $path = 'media/';

    public $fields = array('id', 'company_id', 'type', 'duration', 'start_time', 'end_time', 'status', 'created_at', 'updated_at', 'slug', 'name', 'panda_id');

    public $mutable = array('start_time', 'end_time', 'status', 'name');

    public static $video_formats = array('h264.hi', 'webm.hi', 'ogg.hi', 'thumbnail');
    public static $audio_formats = array('m4a', 'mp3', 'oga');

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
    public function url($name, $dynamic = false, $download = false) {
        // If an invalid media name has been submitted, reject it outright
        if (!in_array($name, self::$video_formats) && !in_array($name, self::$audio_formats)) return false;

        // If a dynamic URL has been requested, we build it from the id etc
        if ($dynamic) {
            return Api::$base . $this->path . 'rendition/' . $this->id . '/' . $name . ($download ? '/1' : '');

        // But non-dynamic URLs go straight to the return S3 URL
        } else {
            foreach ($this->renditions as $rendition) {
                if ($rendition->name == $name) return $rendition->url;
            }
        }

        // We couldn't find anything good to return...
        return false;
    }

}
