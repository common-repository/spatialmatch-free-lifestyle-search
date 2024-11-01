<?php

namespace SpatialMatchIdx\models;

class GeneralSettingsModel extends SpatialMatchSettingsModel
{
    /**
     * @var string
     */
    protected $parentOptionName = '';

    public $slug_default = 'property-search';

    public $slug = 'property-search';

    public $leads_email;

    public $require_user_registration = true;

    public $agent_photo_id;

    public $agent_name;

    public $agent_phone;

    public $company_name;

    public $prompt_register_count = 3;

    public $require_user_registration_phone_number = true;

    public $inquiry_require_user_phone_number = true;

    public $site_logo_id;

    public $logo_height = 75;

    public $allow_opt_out = true;

    public $menu = '';

    protected $validateRules = [
        'slug' => ['required'],
    ];

    protected $validateMessages = [
        'slug' => [
            'required' => 'IDX page slug is required.',
        ],
    ];
}
