<?php

namespace SpatialMatchIdx\front\pages\virtualPages\app;

use SpatialMatchIdx\admin\sections\NavigationContainer;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\compliances\ComplianceManager;
use SpatialMatchIdx\core\render\View;
use SpatialMatchIdx\core\virtualPages\front\VirtualPageInterface;
use SpatialMatchIdx\models\ColorSettingsModel;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\models\MapSettingsModel;
use SpatialMatchIdx\services\LicenseService;

class IdxAppPage implements VirtualPageInterface
{
    public function getSlug(): string
    {
        $generalSettings = GeneralSettingsModel::getData();

        return $generalSettings->getAttribute('slug');
    }

    public function execute()
    {
        switch (LicenseService::getInstance()->getLicenseType()) {
            case 'trial':
                $this->showTrialApp();
            break;
            case 'developer':
                $this->showDeveloperApp();
            break;
            case 'production':
                $this->showProduction();
            break;
            default:
            break;
        }
    }

    private function showTrialApp()
    {
        if (current_user_can('administrator')) {
            $this->showIdxApp();
        } else {
            return;
        }
    }

    private function showDeveloperApp()
    {
        $this->showIdxApp();
    }

    private function showProduction()
    {
        $this->showIdxApp();
    }

    private function showIdxApp()
    {

        $data = [
            'appScript' => $this->getAppScript(),
            'appStyle' => $this->getAppStyle(),
        ];

        View::renderFront('front/pages/app/idx-app-page.phtml', $data);
        exit;
    }

    /**
     * @return array
     */
    private function getAppOptions()
    {
        $mapSettings = MapSettingsModel::getData();
        $generalSettings = GeneralSettingsModel::getData();
        $colorSettings = ColorSettingsModel::getData();

        return [
            'product' => 'spatialmatch-idx',
            'license' => LicenseService::getInstance()->getLicenseKey(),
            'site' => site_url(),
            'apiBaseURL' =>  SlipstreamApiClient::getInstance()->getBaseUri(),
            'market' =>  $mapSettings->market,
            'cityGeoType' =>  'area/postal-city',
            'versionAPI' =>  'v20180104',
            'container' =>  '#map-search',
            'googleApiKey' =>  $mapSettings->getGoogleApiKeyOrDefault(),
            'search' => $this->getMapSearchOptions($mapSettings),
            'pageDetailsPattern' => '',
            'authForm' => [
                'fields' => [
                    'name' => [
                        'required' => true,
                    ],
                    'phone' => [
                        'required' => $generalSettings->require_user_registration_phone_number,
                    ],
                ],
            ],
            'contactForm' => [
                'fields' => [
                    'name' => [
                        'required' => true,
                    ],
                    'email' => [
                        'required' => true,
                    ],
                    'text' => [
                        'required' => true,
                        'text' => "I'm interested in MLS# {id} at {address.deliveryLine}, {address.city} {address.zip}",
                    ],
                    'phone' => [
                        'required' => $generalSettings->inquiry_require_user_phone_number,
                    ]
                ],
                'agent' => $this->getAgentInfo($generalSettings),
            ],
            'registration' => [
                'required' => !$generalSettings->allow_opt_out,
                'listingViewCount' => (true === $generalSettings->require_user_registration) ? $generalSettings->prompt_register_count : -1,
            ],
            'palette' => [
                'useCssVariables' => true,
                'color' => $this->getColorOptions($colorSettings),
            ],
            'footer' => $this->getFooterOptions(),
            'allowLookupAreas' => [
                'place',
                'township',
                'county',
                'neighborhood',
                'zipcode',
                'school',
            ],
            'menu' => $this->getMenuOptions($generalSettings),
            'timeCorrection' => 2,
        ];
    }

    /**
     * @return string
     */
    private function getAppScript(): string
    {
        $data = [
            'options' => $this->getAppOptions(),
        ];

        return View::renderFrontString('front/pages/app/app-js.phtml', $data);
    }

    /**
     * @return string
     */
    private function getAppStyle(): string
    {
        $colorSettings = ColorSettingsModel::getData();

        return View::renderFrontString('front/pages/app/app-css.phtml', ['colorSettings' => $colorSettings]);
    }

    /**
     * @param MapSettingsModel $mapSettings
     * @return array
     */
    private function getMapSearchOptions(MapSettingsModel $mapSettings): array
    {
        $data = [
            'center' => null,
        ];

        if ($mapSettings->latitude && $mapSettings->longitude) {
            $data['center'] = [
               'lat' => $mapSettings->latitude,
               'lng' => $mapSettings->longitude,
            ];
        } elseif ($mapSettings->market === 'hjimls') {
            $data['center'] = [
                'lat' => '35.134198583266674',
                'lng' => '-117.89352440951737',
            ];
        }

        if (null !== $mapSettings->zoom) {
            $data['zoom'] = (int)$mapSettings->zoom;
        }

        return $data;
    }

    /**
     * @param ColorSettingsModel $colorSettings
     * @return array
     */
    private function getColorOptions(ColorSettingsModel $colorSettings): array
    {
        return [
            'primary' => $colorSettings->primary_color,
            'secondary' => $colorSettings->secondary_color,
            'hover' => $colorSettings->primary_hover,
            'markerActive' => $colorSettings->marker_active,
            'markerOther' => $colorSettings->marker_other,
            'menu' => $colorSettings->menu_font,
            'primaryLink' => $colorSettings->primary_link,
        ];
    }

    /**
     * @param GeneralSettingsModel $generalSettings
     * @return array
     */
    private function getMenuOptions(GeneralSettingsModel $generalSettings): array
    {
        $data = [
            'items' => [],
            'horizontal' => [
                'logo' => null,
                'before' => null,
                'after' => null,
            ],
            'vertical' => [
                'logo' => null,
                'before' => null,
                'after' => null,
            ]
        ];

        $data['items'] = (null === $generalSettings->menu) ? [] : NavigationContainer::getMenuItems($generalSettings->menu);

        if (!empty($generalSettings->site_logo_id)) {
            $logoImg = wp_get_attachment_image($generalSettings->site_logo_id, 'medium', false, ['style' => sprintf('width: auto; height: %spx', $generalSettings->logo_height)]);

            $data['horizontal']['logo'] = sprintf('<a title="Site Logo" href="%s">%s</a>', site_url(), $logoImg);
            $data['vertical']['logo'] = sprintf('<a title="Site Logo" href="%s">%s</a>', site_url(), $logoImg);
        }

        return $data;
    }

    private function getAgentInfo(GeneralSettingsModel $generalSettings): array
    {
        $customerInfo = LicenseService::getInstance()->getCustomerInfo();

        $photoUrl = '';

        if (!empty($generalSettings->agent_photo_id)) {
            $image = wp_get_attachment_image_src( $generalSettings->agent_photo_id, 'thumbnail', false);
            $photoUrl = $image[0];
        }elseif (!empty($generalSettings->site_logo_id)) {
            $image = wp_get_attachment_image_src( $generalSettings->site_logo_id, 'thumbnail', false);
            $photoUrl = $image[0];
        }


        return [
            'name' => $generalSettings->agent_name ? $generalSettings->agent_name : $customerInfo['name'],
            'officeName' => $generalSettings->company_name ? $generalSettings->company_name : $customerInfo['officeName'],
            'officePhone' => $generalSettings->agent_phone ? $generalSettings->agent_phone : $customerInfo['officePhone'] ?? null,
            'photo' => $photoUrl,
            'email' => $customerInfo['email'],
        ];
    }

    private function getFooterOptions(): array
    {
        $complianceManager = ComplianceManager::getInstance();

        $footer['copyright'] = '&copy;' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.';
        $footer['afterDisclaimer'] = $complianceManager->getAfterDisclaimerCopyright();

        return $footer;
    }
}
