<?php

namespace SpatialMatchIdx\core\helpers;

class LeadsOptionsHelper
{

    public static function getLeadsOptions(array $params): array
    {
        $options = [
            'pageSize' => 25,
            'summary' => true,
        ];

        $optionsSortParams = self::getSortParams($params);
        $optionsFilteringParams = self::getFilterParams($params);
        $optionsSearchParams = self::getSearchParams($params);

        return array_merge($options, $optionsSortParams, $optionsFilteringParams, $optionsSearchParams);
    }

    public static function getSearchParams(array $params): array
    {
        return isset($params['s']) ? ['keyword' => $params['s']] : [];
    }

    public static function getSortParams(array $params): array
    {
        $order = $params['order'] ?? 'desc';
        $orderby = $params['orderby'] ?? 'createdDate';

        $args = [];
        if (!empty($order) && !empty($orderby)) {
            $args['sortField'] = $orderby;
            $args['sortOrder'] = $order;
        }

        return $args;
    }

    public static function getFilterParams(array $params): array
    {
        $filterParam = $params['time_period'] ?? null;

        $args = [];
        if ($filterParam !== null) {
            $timezoneString = DateTimeHelper::getTimezoneString();
            $date = new \DateTime('now', new \DateTimeZone($timezoneString));

            if ($filterParam !== 'all') {
                switch ($filterParam) {
                    case 'today': $createdDate = $date->setTime(0,0)->getTimestamp();
                        return ['createdDate' => '>=' . $createdDate];

                    case '7days': $createdDate = $date->modify('-7 day')->setTime(0,0)->getTimestamp();
                        return ['createdDate' => '>=' . $createdDate];

                    case '30days': $createdDate = $date->modify('-30 day')->setTime(0,0)->getTimestamp();
                        return ['createdDate' => '>=' . $createdDate];

                    case 'thismonth':  $createdDate = $date->setDate($date->format('Y'), $date->format('m'), 1)->setTime(0,0)->getTimestamp();
                        return ['createdDate' => '>=' . $createdDate];
                    case 'custom':
                        return self::getTimePeriodOptionForCustomRange($params);
                }

                return $args;
            }
        }

        return $args;
    }

    private static function getTimePeriodOptionForCustomRange(array $params): array
    {
        if (!empty($params['from']) && !empty($params['to'])) {
            return ['createdDate' => $params['from'] . ':' . $params['to']];
        }

        if (!empty($params['from']) && empty($params['to'])) {
            return ['createdDate' => '>' . $params['from']];
        }

        if (empty($params['from']) && !empty($params['to'])) {
            return ['createdDate' => '<' . $params['to']];
        }

        if (empty($params['from']) && empty($params['to'])) {
            return [];
        }
    }
}
