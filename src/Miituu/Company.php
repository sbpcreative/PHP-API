<?php

namespace Miituu;

class Company extends Model {

    protected $path = 'companies/';

    public $fields = array('id', 'name', 'status', 'created_at', 'updated_at', 'slug', 'package_id', 'bandwidth_used', 'storage_used', 'over_usage', 'color_scheme', 'use_title', 'users_used', 'billing_status', 'emails_used', 'boltons');

    public $relations = array(
        array(
            'key' => 'images',
            'model' => '\Miituu\Image',
            'multiple' => true
        ),
        array(
            'key' => 'logos',
            'model' => '\Miituu\Image',
            'multiple' => true
        )
    );

    public function collections() {
        return Collection::where('company_id', $this->id);
    }

}
