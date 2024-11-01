<?php

namespace SpatialMatchIdx\models;

use GuzzleHttp\Exception\ClientException;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\services\AdminNoticesService;

class LicenseModel extends SpatialMatchSettingsModel
{
    public $license_key;

    private $default_license_key = 'TEST-0BF8-91E6-8F7C';

    public $license_type;

    public $license_expiration;

    public $customer;

    public $markets = [];

    public $added_at = 0;

    private $licenseTypeByKeyMap = [
        'PRVW' => 'trial',
        'TEST' => 'developer',
        'PROD' => 'production',
        'INVALID' => 'invalid',
    ];

    public function beforeSave()
    {
        parent::beforeSave();

        $this->added_at = time();

        $this->setLicenseType();
        $this->validateLicenseKey();
    }

    public function getDefaultLicenseKey()
    {
        return $this->default_license_key;
    }

    private function setLicenseType()
    {
        if (empty($this->license_key)) {

            $this->license_type = null;
            $this->license_expiration = null;
            $this->customer = null;
            $this->markets = [];

            return;
        }

        $licenseParts = explode('-', $this->license_key);

        /* Check license type by sufix (last part of license key) */
        $lastLicensePart = $licenseParts[count($licenseParts) - 1];

        if (isset($this->licenseTypeByKeyMap[$lastLicensePart])) {
            $this->license_type = $this->licenseTypeByKeyMap[$lastLicensePart];

            return;
        }

        /* Check license type by prefix (first part of license key) */
        $lastLicensePart = $licenseParts[0];

        if (isset($this->licenseTypeByKeyMap[$lastLicensePart])) {
            $this->license_type = $this->licenseTypeByKeyMap[$lastLicensePart];

            return;
        }

        $this->license_type = $this->licenseTypeByKeyMap['PROD'];

        return;
    }

    public function setInvalidType()
    {
        $this->customer = null;
        $this->markets = [];
        $this->license_type = $this->licenseTypeByKeyMap['INVALID'];
    }

    private function validateLicenseKey()
    {
        if (empty($this->license_key)) {
            return;
        }

        try {
            $apiClient = SlipstreamApiClient::getInstance();
            $apiClient->clearAuthToken();
            $authData = $apiClient->checkLicense($this->license_key);
            $this->customer = $authData['customer'];
            $this->markets = $authData['markets'];

            $generalSettings = GeneralSettingsModel::getData();
            $mapSearchAppUrl = site_url() . '/' . $generalSettings->slug;
            AdminNoticesService::getInstance()->addMessage('Success! Access your SpatialMatch IDX at <a href="' . $mapSearchAppUrl . '" target="_blank">' . $mapSearchAppUrl . '</a>.', AdminNoticesService::MESSAGE_SUCCESS);
        } catch (ClientException $exception) {
           $this->setInvalidType();
        }
    }
}
