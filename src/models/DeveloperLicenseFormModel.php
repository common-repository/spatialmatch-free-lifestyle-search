<?php

namespace SpatialMatchIdx\models;

use SpatialMatchIdx\core\models\Model;

class DeveloperLicenseFormModel extends Model
{
    public $first_name;

    public $last_name;

    public $email;

    public $phone;

    public $company_name;

    public $company_website;

    protected $prefixOptionName = 'spm';

    protected $validateRules = [
        'first_name' => ['required'],
        'last_name' => ['required'],
        'email' => ['required', 'email'],
        'phone' => ['required'],
        'company_name' => ['required'],
        'company_website' => ['required', 'url'],
    ];

    public static function getLabels(): array
    {
        return [
            'market' => 'MLS/Market',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company_name' => 'Company Name',
            'company_website' => 'Company Website',
        ];
    }
}
