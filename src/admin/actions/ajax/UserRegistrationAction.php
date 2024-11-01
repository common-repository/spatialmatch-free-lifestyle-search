<?php

namespace SpatialMatchIdx\admin\actions\ajax;

use SpatialMatchIdx\admin\actions\BaseAjaxAction;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\services\UserService;

class UserRegistrationAction extends BaseAjaxAction
{
    public function execute()
    {
        if (empty($_POST['data'])) {
            $this->error('Missing required argument: data.');
        }

        $user = UserService::getInstance()->getLeadByEmail($_POST['data']['email']);

        if (null === $user) {
            $this->error('User not found.');
        }

        $subject = 'New User Registration';

        $generalSettings = GeneralSettingsModel::getData();

        $to = get_bloginfo('admin_email');

        if (!empty($generalSettings->leads_email)) {
            $to .= ',' . $generalSettings->leads_email;
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $emailBody = '<h3>New User Registration</h3>';
        $emailBody .= "Name: {$user['name']}<br>\n";
        $emailBody .= "Email: {$user['email']}<br>\n";
        $emailBody .= "Phone: {$user['phone']}<br>\n";

        $result = wp_mail($to, $subject, $emailBody, $headers);

        if (false === $result) {
            $this->error('Something went wrong. We were not able to send your message. Please try again later.');
        }

        $this->success(['user' => $user]);
    }
}
