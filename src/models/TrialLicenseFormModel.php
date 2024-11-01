<?php

namespace SpatialMatchIdx\models;

use SpatialMatchIdx\core\models\Model;

class TrialLicenseFormModel extends Model
{
    public $subscription_type = 'trial';

    public $market;

    public $other_market;

    public $website_url = '';

    public $first_name;

    public $last_name;

    public $email;

    public $cell_phone;

    public $office_phone;

    public $agent_mls_id;

    public $company_name;

    public $street_address;

    public $city;

    public $state;

    public $zip_code;

    protected $prefixOptionName = 'spm';

    protected $validateRules = [
        'first_name' => ['required'],
        'last_name' => ['required'],
        'email' => ['required', 'email'],
        'cell_phone' => ['required'],
        'market' => ['required'],
        'website_url' => ['required', 'url'],
        'office_phone' => ['required'],
        'agent_mls_id' => ['required'],
        'company_name' => ['required'],
        'street_address' => ['required'],
        'city' => ['required'],
        'state' => ['required'],
        'zip_code' => ['required'],
    ];

    public function __construct()
    {
        $this->website_url = site_url();
    }


    public static function getLabels(): array
    {
        return [
            'market' => 'MLS/Market',
            'website_url' => 'Website URL',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'cell_phone' => 'Cell Phone',
            'office_phone' => 'Office Phone',
            'agent_mls_id' => 'Agent MLS ID',
            'company_name' => 'Company Name',
            'street_address' => 'Street Address',
            'city' => 'City',
            'state' => 'State',
            'zip_code' => 'Zip Code',
        ];
    }
}
