<?php

namespace SpatialMatchIdx\admin\actions\pages\leads;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\helpers\LeadsNormalizeHelper;
use SpatialMatchIdx\core\helpers\LeadsOptionsHelper;
use SpatialMatchIdx\services\LicenseService;

class LeadsExportAction extends BaseAction
{
    public function execute()
    {
        $slipstreamApiClient = SlipstreamApiClient::getInstance();
        $licenseService = LicenseService::getInstance();

        if ($licenseService->isValid() && !$licenseService->isEmpty()) {
            $urlReferer = $_POST['_wp_http_referer'];
            $urlParsed = parse_url($urlReferer);
            parse_str($urlParsed['query'], $urlQueryArray);

            $options = LeadsOptionsHelper::getLeadsOptions($urlQueryArray);
            $options['pageSize'] = 1000;

            $users = $slipstreamApiClient->getLeads($options);


            $normalizeUsers = LeadsNormalizeHelper::normalizeUsersData($users['result']['users']);
            $pageCount = $users['result']['paging']['count'];
            $pageNumber = $users['result']['paging']['number'] + 1;

            for ($i = $pageNumber; $i < $pageCount; $i++) {
                $options['pageNumber'] = $i;
                $users = $slipstreamApiClient->getLeads($options);
                $tmpNormalizeUsers = LeadsNormalizeHelper::normalizeUsersData($users['result']['users']);

                $normalizeUsers = array_merge($normalizeUsers, $tmpNormalizeUsers);
            }

            $leadsTitle = [
                'id' => 'UID',
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'createdDate' => 'Registered',
                'lastActive' => 'Last Active',
            ];

            $filename = 'export.csv';

            $dateToExport = $this->prepareDataToExport($normalizeUsers, $leadsTitle);

            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");

            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");

            echo $this->outputCsv($dateToExport);
            die();
        }
    }

    /**
     * @param $data
     * @return false|string
     */
    private function outputCsv($data)
    {

        if (!empty($data)):
            ob_start();
            $fp = fopen('php://output', 'w');
            fputcsv($fp, array_keys(reset($data)));

            foreach ($data AS $values):
                fputcsv($fp, $values);
            endforeach;

            fclose($fp);

            return ob_get_clean();
        endif;

        return '';
    }

    private function prepareDataToExport(array $leads, array $leadsTitle): array
    {
        $output = [];

        $i = 1;
        foreach ($leads as $lead) {
            foreach ($lead as $key => $value) {
                if (isset($leadsTitle[$key])) {
                    if ($key === 'lastActive' || $key === 'createdDate') {
                        $date = new \DateTime();
                        $output[$i][$leadsTitle[$key]] = $date->setTimestamp($value)->format('F d, Y g:ia');
                    } else {
                        $output[$i][$leadsTitle[$key]] = $value;
                    }
                }
            }
            $i++;
        }

        return $output;
    }

}
