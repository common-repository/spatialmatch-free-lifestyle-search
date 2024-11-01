<?php

namespace SpatialMatchIdx\services;


use SpatialMatchIdx\admin\sections\MapSettings;
use SpatialMatchIdx\admin\sections\NavigationContainer;
use SpatialMatchIdx\core\helpers\URLHelper;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\models\LicenseModel;
use SpatialMatchIdx\models\MapSettingsModel;

class LicenseService
{
    const OPERATION_INSERT = 1;

    const OPERATION_FIRST_INSERT = 2;

    const OPERATION_UPDATE = -1;

    const OPERATION_NON = 0;

    const REDIRECT_DOING = true;

    const REDIRECT_NOT_DOING = false;

    /**
     * @var int
     */
    private $operationType;

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var int seconds
     */
    private $timeAfterSaveLicenseToShowMessages = 36000; // 10h = 36000 (1h = 3600)

    /**
     * @var LicenseModel
     */
    private $license;

    /**
     * @var array
     */
    private $licenseNoticesEceptedActions = [
        'license-types',
        'trial-license-registration',
        'agent-license-registration',
        'developer-license-registration',
        'success-license-registration',
    ];

    /**
     * @var string
     */
    private $upgradeLicenseUrl;

    private $showMenuBadge = false;

    /**
     * @var array
     */
    private $messages = [];

    public function __construct()
    {
        $this->license = LicenseModel::getData();

        $this->upgradeLicenseUrl =  home_url() . '/wp-admin/admin.php?page=hji-spatialmatch-idx&action=upgrade-license';

        $this->showLicenseNotices();
    }

    /**
     * @return LicenseService
     */
    public static function getInstance(): LicenseService
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     *
     */
    public function refreshLicenseModel()
    {
        $this->license = LicenseModel::getData();
    }

    /**
     * @return string|null
     */
    public function getLicenseType()
    {
        return $this->license->license_type;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return 'invalid' !== $this->license->license_type;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->license->license_type);
    }

    public function getLicenseKey()
    {
        return $this->license->license_key;
    }

    public function setLicenseKey($value): LicenseService
    {
        $this->license->license_key = $value;

        return $this;
    }

    public function getCustomerInfo()
    {
        return $this->license->customer;
    }

    public function getMarkets()
    {
        return $this->license->markets;
    }

    public function showLicenseNotices()
    {
        if (isset($_GET['action']) && in_array($_GET['action'], $this->licenseNoticesEceptedActions)) {
            return;
        }

        $currentTime = time();

        switch ($this->license->license_type) {
            case 'production':
                break;
            case 'trial':
                if ($currentTime - $this->license->added_at < $this->timeAfterSaveLicenseToShowMessages) {
                    return;
                }
                $this->showMenuBadge = true;
                $this->addLicenseTrialNotice();
                break;
            case 'developer':
                $this->showMenuBadge = true;
                $this->addLicenseDevelopNotice();
                break;
            case 'invalid':
                $this->addLicenseInvalidNotice();
                break;
            default:
                $this->addLicenseEmptyNotice();

        }
    }

    public function addLicenseTrialNotice()
    {
        $this->addMessage('<div class="notice notice-warning is-dismissible">
                <p>You are using Limited Trial License. <a href="' . $this->upgradeLicenseUrl . '">Upgrade to paid subscription </a> to go live with your SpatialMatch IDX.</p>
            </div>');
    }

    public function addLicenseDevelopNotice()
    {
        $url = GeneralSettingsService::getInstance()->getApplicationUrl();
        $message = '<div class="notice notice-warning is-dismissible">
                <p>Access  IDX Search at <a href="' . $url . '" target="_blank">' . $url . '</a><br>You are using Developer License, which provides access to test data only. <a href="' . $this->upgradeLicenseUrl . '">Upgrade license</a> to get live MLS feed.</p>
            </div>';
        $excludePagePathParams = [
            'page' => 'hji-spatialmatch-idx',
            'action' => 'upgrade-license'
        ];
        $this->addMessage($message, $excludePagePathParams);
    }

    public function addLicenseInvalidNotice()
    {
        $this->addMessage(sprintf('<div class="notice notice-error is-dismissible">
                <p>Invalid SpatialMatch IDX license: <b>%s</b>. <a href="%s">Upgrade your license</a> or contact <a href="/wp-admin/admin.php?page=hji-spatialmatch-idx-help">support</a>.</p>
            </div>', $this->license->license_key, $this->upgradeLicenseUrl));
    }

    public function addLicenseEmptyNotice()
    {
        $licensePageUrl = site_url() . '/wp-admin/admin.php?page=hji-spatialmatch-idx&action=license-types';

        if (isset($_GET['page']) && ('hji-spatialmatch-idx' === $_GET['page']) && (!isset($_GET['action'])  || 'license' === $_GET['action'])) {
            $this->addMessage('<div class="notice notice-warning is-dismissible">
                <p>You must enter your SpatialMatch IDX license key to connect to the API. To get a license key click <a href="'. $licensePageUrl . '">here</a>. If you already have a license key, enter it below.</p>
            </div>');
        } else {
            $licenseSettingsPageUrl = site_url() . '/wp-admin/admin.php?page=hji-spatialmatch-idx&action=license';
            $this->addMessage('<div class="notice notice-warning is-dismissible">
                <p>You must enter your SpatialMatch IDX license key to connect to the API. To get a license key click <a href="'. $licensePageUrl . '">here</a>. If you already have a license key, click <a href="' . $licenseSettingsPageUrl . '">here.</a></p>
            </div>');
        }
    }

    public function licenseNotices()
    {
        add_action('admin_notices', [$this, 'showMessages']);
    }

    public function hasMessages()
    {
        return (count($this->messages) > 0);
    }

    public function showMessages()
    {
        foreach ($this->messages as $message) {
            $counter = 0;
            foreach ($message['excludePagePathParams'] as $key =>$value) {
                if (isset($_GET[$key]) && $_GET[$key] === $value) {
                    $counter++;
                }
            }

            if ($counter !== count($message['excludePagePathParams']) || ($counter === 0 && count($message['excludePagePathParams']) === 0)) {
                echo $message['message'];
            }
        }
    }

    /**
     * @return bool
     */
    public function showMenuBadge()
    {
        return $this->showMenuBadge;
    }

    /**
     * @param string $message
     * @param array $excludePagePathParams
     */
    private function addMessage(string $message, $excludePagePathParams = [])
    {
        $this->messages[] = [
            'message' => $message,
            'excludePagePathParams' => $excludePagePathParams
        ];
    }

    /**
     * @param int $operationType
     * @return string|void|null
     */
    public function afterLicenseKeySave(int $operationType)
    {
        if ($this->isValid()) {
            $markets = [];
            if ($operationType !== self::OPERATION_NON) {

                $this->refreshLicenseModel();
                $newMarketsObject = $this->getMarkets();

                if (is_array($newMarketsObject) && count($newMarketsObject) > 0) {
                    $markets = $this->getMarketsId($newMarketsObject);
                }
            }

            if ($operationType === self::OPERATION_UPDATE && count($markets) > 0) {
                $oldData = MapSettingsModel::getData()->getAttributes();

                $redirectFlag = false;
                if (!empty($oldData['market'])) {
                    $redirectFlag = $this->doingTypeOperationOnMapSettingsPage($oldData['market'], $markets);
                }


                if ($redirectFlag === self::REDIRECT_DOING) {
                    return URLHelper::getLink((new MapSettings)->getSlug());
                }
            } elseif (in_array($operationType, [self::OPERATION_INSERT, self::OPERATION_FIRST_INSERT])) {

                $this->addedSiteLogo();
                $this->addPrimaryMenu();

                if (count($markets) > 0 ) {
                    $this->updateMarketOnMapSettings($markets[0]);
                }

                if ($operationType === self::OPERATION_FIRST_INSERT) {
                    $this->addDefaultAgentInfo();
                }
            }
        }

        return null;
    }

    /**
     * @param array $newMarkets
     * @return array
     */
    private function getMarketsId(array $newMarkets):array
    {
        $markets = [];
        foreach ($newMarkets as $market) {
            $markets[] = $market['id'];
        }

        return $markets;
    }

    /**
     * @param array $postData
     * @return int
     */
    public function checkOperationType(array $postData): int
    {
        $savedData = LicenseModel::getData()->getAttributes();
        $savedLicenseKey = $savedData['license_key'];
        $newLicenseKey = $postData['license_key'];

        if($savedData['added_at'] === 0) {
            return self::OPERATION_FIRST_INSERT;
        }

        if (empty($savedLicenseKey) && !empty($newLicenseKey)) {
            return self::OPERATION_INSERT;
        }

        if (!empty($savedLicenseKey) && !empty($newLicenseKey)) {
            return  self::OPERATION_UPDATE;
        }

        return self::OPERATION_NON;
    }

    /**
     * @param string $savedMarket
     * @param array $newLicenseMarkets
     * @return bool
     */
    private function doingTypeOperationOnMapSettingsPage(string $savedMarket, array $newLicenseMarkets): bool
    {
        if (!in_array($savedMarket, $newLicenseMarkets, true)) {
            $this->updateMarketOnMapSettings($newLicenseMarkets[0]);

            $adminNotice = AdminNoticesService::getInstance();
            $adminNotice->addMessage('Review Map Centering settings as they have changed due to the new license key.');

            return self::REDIRECT_DOING;
        }

        return self::REDIRECT_NOT_DOING;
    }

    /**
     * @param string $marketId
     */
    private function updateMarketOnMapSettings(string $marketId)
    {
        $mapSettingsData = MapSettingsModel::getData()->getAttributes();
        $mapSettingsData['market'] = $marketId;
        $mapSettingsData['longitude'] = '';
        $mapSettingsData['latitude'] = '';
        $mapSettingsData['zoom'] = $mapSettingsData['defaultZoom'];

        $mapSettingsModel = new MapSettingsModel();
        $mapSettingsModel->setAttributes($mapSettingsData);
        $mapSettingsModel->save();
    }

    private function addedSiteLogo(): bool
    {
        if (!has_custom_logo()) {
            return false;
        }

        $data = GeneralSettingsModel::getData()->getAttributes();
        if (empty($data['site_logo_id'])) {
            $data['site_logo_id'] = get_theme_mod('custom_logo');
            $generalSettings = new GeneralSettingsModel();
            $generalSettings->setAttributes($data);
            $generalSettings->save();

            return true;
        }

        return false;
    }

    private function addPrimaryMenu()
    {
        $data = GeneralSettingsModel::getData()->getAttributes();

        if (empty($data['menu'])) {
            $primaryMenu = NavigationContainer::getPrimaryMenu();

            if (empty($primaryMenu) || !($primaryMenu instanceof \WP_Term)) {
                return false;
            }

            $data['menu'] = $primaryMenu->slug;
            $generalSettings = new GeneralSettingsModel();
            $generalSettings->setAttributes($data);
            $generalSettings->save();

            return true;
        }

        return false;
    }

    private function addDefaultAgentInfo()
    {
        $licenseData = LicenseModel::getData();

        $data = GeneralSettingsModel::getData()->getAttributes();
        $data['agent_name'] = $licenseData->customer['name'] ?? '';
        $data['company_name'] = $licenseData->customer['officeName'] ?? '';
        $data['agent_phone'] = $licenseData->customer['officePhone'] ?? '';

        if (empty($data['leads_email'])) {
            $data['leads_email'] = $licenseData->customer['email'] ?? '';
        }

        $generalSettings = new GeneralSettingsModel();
        $generalSettings->setAttributes($data);
        $generalSettings->save();
    }

    /**
     * @return bool
     */
    public function getOperationType(): bool
    {
        return $this->operationType;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setOperationType(bool $value)
    {
        $this->operationType = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
