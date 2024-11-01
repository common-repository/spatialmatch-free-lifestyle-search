<?php

namespace SpatialMatchIdx\admin\actions\ajax;

use SpatialMatchIdx\admin\actions\BaseAjaxAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\services\UserService;

class ContactFormSubmissionAction extends BaseAjaxAction
{
    public function execute()
    {
        if (isset($_POST['data']) && is_array($_POST['data']) && !empty($_POST['data'])) {
            $formEntry = $_POST['data'];

            // Returns error if email is invalid
            if (false === filter_var($formEntry['email'], FILTER_VALIDATE_EMAIL)) {
               $this->error('The email is invalid. Please provide a valid email address.');
            }

            // unpack listing object
            if (isset($formEntry['listing']) && !empty($formEntry['listing'])) {
                $listing = json_decode(stripslashes($formEntry['listing']));
                $formEntry['listing'] = $listing;
            } else {
                $formEntry['listing'] = null;
            }

            $user = $this->getLead($formEntry['email']);

            if (null === $user) {
                $user = $this->addLead($formEntry);
            }

            if (null !== $user) {
                /** log form submission to the Slipstream */
                $this->logLeadActionFormEntry($user, $formEntry);
            }

            /** send lead to the agent */
            $result = $this->sendLeadForAdminsEmails($formEntry);

            if (false === $result) {
                $this->error('Something went wrong. We were not able to send your message. Please try again later.');
            }

            $this->success(['user' => $user]);
        }

        $this->error('Missing required argument: data.');
    }

    /**
     * @param array $formEntry
     *
     * @return bool
     */
    protected function sendLeadForAdminsEmails(array $formEntry): bool
    {
        // Matching Gravity Forms subject for Property Inquiry form

        $subject    = 'New submission from Property Inquiry';
        $emailBody  = '';

        $generalSettings = GeneralSettingsModel::getData();

        $to = get_bloginfo('admin_email');

        if (!empty($generalSettings->leads_email)) {
            $to .= ',' . $generalSettings->leads_email;
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $messageLines = $this->_toReadableArray($formEntry);

        foreach ($messageLines as $label => $value) {
            $emailBody .= $label . ': ' . $value . '<br>';
        }


        $result = wp_mail($to, $subject, $emailBody, $headers);

        if (!$result) {
            error_log('MapSearch::sendLead() failed. wp_mail() returned false.');
        }

        return $result;
    }

    /**
     * @param array $user
     * @param array $formEntry
     */
    private function logLeadActionFormEntry(array $user, array $formEntry)
    {
        $params = [
            'action'        => 'form_entry',
            'tags'          => null,
            'parameters'    => json_encode($this->normalizeFormEntryForSlipstream($formEntry)),
            'user'          => $user['id'],
        ];

        SlipstreamApiClient::getInstance()->logAction($params);
    }

    /**
     * @param array $formEntry
     *
     * @return array
     */
    protected function _toReadableArray(array $formEntry): array
    {
        $listing = (isset($formEntry['listing'])) ? $formEntry['listing'] : null;
        $emailData = [
            'Form Name' => 'Property Inquiry',
            'Name'      => $formEntry['name'],
            'Phone'     => $formEntry['phone'],
            'Email'     => $formEntry['email'],
            'Listing ID'=> ($listing) ? $listing->id : null,
            'Listing Address' => ($listing) ? @$listing->address->deliveryLine . ', ' . $listing->address->city . ', ' . $listing->address->state . ' ' . $listing->address->zip : null,
            'Open House Inquiry' => '',
            'Message'   => $formEntry['text'],
            'Source'    => $_SERVER["HTTP_REFERER"],
            'IP Address'=> $this->getIP(),
        ];
        // Check if this Open House Form
        if (!empty($formEntry['type']) && !empty($formEntry['date']) && !empty($formEntry['time'])) {
            $emailData['Form Name'] = 'Property Open House Inquiry';
            $emailData['I would like to see this property'] = $formEntry['type'] . ', ' . $formEntry['date'] . ', '  . $formEntry['time'];
        } else {
            unset($emailData['Open House Inquiry']);
        }

        return array_filter($emailData);
    }

    private function getIP()
    {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if ($this->validateIP($ip)) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? false;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    private function validateIP($ip): bool
    {
        return !(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) ;
    }

    /**
     * @param array $formEntry
     *
     * @return array|null
     */
    protected function addLead(array $formEntry)
    {
        unset($formEntry['listing']);

        return UserService::getInstance()->addLead($formEntry);
    }

    /**
     * @param string $email
     *
     * @return array|null
     */
    protected function getLead(string $email)
    {
        return UserService::getInstance()->getLeadByEmail($email);
    }

    /**
    * Return array formatted similar to gravity forms entry array
    *
    * @param array $formEntry
    *
    * @return array
    */
    protected function normalizeFormEntryForSlipstream(array $formEntry): array
    {
        return [
            'entry'     => [
                'form_title' => 'Property Inquiry',
                'source_url' => $_SERVER["HTTP_REFERER"],
                'ip'         => $this->getIP()
            ],
            'fields'    => [
                [
                    'label' => 'Name',
                    'value' => $formEntry['name']
                ],
                [
                    'label' => 'Phone',
                    'value' => $formEntry['phone']
                ],
                [
                    'label' => 'Email',
                    'value' => $formEntry['email']
                ],
                [
                    'label' => 'Message',
                    'value' => $formEntry['text']
                ]
            ]
        ];
    }
}
