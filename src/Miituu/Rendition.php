<?php

namespace Miituu;

class Rendition extends Model {

    protected $path = false;

    public $fields = array('id', 'media_id', 'name', 'url', 'size', 'expires', 'width', 'height', 'created_at', 'updated_at', 'mime', 'video_codec', 'audio_codec', 'filename');

    public $mutable = array();

}
