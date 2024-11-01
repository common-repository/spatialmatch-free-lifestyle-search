<?php
namespace SpatialMatchIdx\front\pages\virtualPages\compliances;

use SpatialMatchIdx\core\render\View;
use SpatialMatchIdx\core\virtualPages\front\VirtualPageInterface;
use SpatialMatchIdx\services\LicenseService;

class NwmlsComplianceVirtualPage implements VirtualPageInterface
{
    public function getSlug(): string
    {
        return 'nwmls-compliance';
    }

    public function execute()
    {
        $data = [
            'name' => '',
            'officeName' => '',
            'address' => [
                'state' => '',
                'city' => '',
                'zip' => '',
            ],
            'deliveryLine' => '',
        ];

        $customerInfo = LicenseService::getInstance()->getCustomerInfo();
        if (!empty($customerInfo['address'])) {
            $data['address'] = array_merge($data['address'], $customerInfo['address']);
        }

        if (!empty($customerInfo['email'])) {
            $data['email'] = $customerInfo['email'];
        }

        if (!empty($customerInfo['name'])) {
            $data['name'] = $customerInfo['name'];
        }

        if (!empty($customerInfo['officeName'])) {
            $data['officeName'] = $customerInfo['officeName'];
        }

        View::render('front/pages/compliances/nwmls-dmca.phtml', ['context' => $data]);
        exit;
    }
}
