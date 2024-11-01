<?php

namespace SpatialMatchIdx\models;

use SpatialMatchIdx\core\models\Model;

class PaidLicenseFormModel extends Model
{
    public $market;

    public $other_market;

    public $website_url = '';

    public $subscription_type = 'annual';

    public $first_name;

    public $last_name;

    public $email;

    public $cell_phone;

    public $office_phone;

    public $agent_mls_id;

    public $license;

    public $company_name;

    public $street_address;

    public $city;

    public $state;

    public $zip_code;

    public $brokerage_name;

    public $broker_full_name;

    public $broker_mls_id;

    public $broker_office_mls_id;

    public $broker_email;

    protected $prefixOptionName = 'spm';

    protected $validateRules = [
        'subscription_type' => ['required'],
        'market' => ['required'],
        'first_name' => ['required'],
        'last_name' => ['required'],
        'email' => ['required', 'email'],
        'cell_phone' => ['required'],
        'website_url' => ['required', 'url'],
        'office_phone' => ['required'],
        'agent_mls_id' => ['required'],
        'license' => ['required'],
        'company_name' => ['required'],
        'street_address' => ['required'],
        'city' => ['required'],
        'state' => ['required'],
        'zip_code' => ['required'],
        'brokerage_name' => ['required'],
        'broker_full_name' => ['required'],
        'broker_mls_id' => ['required'],
        'broker_office_mls_id' => ['required'],
        'broker_email' => ['required', 'email'],
    ];

    public function __construct()
    {
        $this->website_url = site_url();
    }

    public static function getLabels(): array
    {
        return [
            'subscription_type' => 'Subscription Plan',
            'market' => 'MLS/Market',
            'website_url' => 'Website URL',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'cell_phone' => 'Cell Phone',
            'office_phone' => 'Office Phone',
            'agent_mls_id' => 'Agent MLS ID',
            'license' => 'License #',
            'company_name' => 'Company Name',
            'street_address' => 'Street Address',
            'city' => 'City',
            'state' => 'State',
            'zip_code' => 'Zip Code',
            'brokerage_name' => 'Brokerage Name',
            'broker_full_name' => 'Broker in Charge (Full Name)',
            'broker_mls_id' => 'Broker MLS ID',
            'broker_office_mls_id' => 'Broker Office MLS ID',
            'broker_email' => 'Broker Email',
        ];
    }
}
