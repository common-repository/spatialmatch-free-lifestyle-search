<?php

namespace SpatialMatchIdx\core\helpers;

class DateTimeHelper
{
    public static function getTimeframe($getTitle = false)
    {
        if (empty($_REQUEST['timeframe'])) {
            if ($getTitle) {
                return 'All';
            }

            return null;
        }

        $timeframe = $_REQUEST['timeframe'];

        $result = $title = null;

        if ($timeframe) {
            self::setTimezone();

            $todayMidnight = strtotime(date('n/d/Y'));

            switch($timeframe) {
                case 'all':
                    $result = '>=' . strtotime("01/01/2014");
                    $title = 'All';
                    break;

                case 'today':
                    $result = '>=' . $todayMidnight;
                    $title = 'Today';
                    break;

                case '7days':
                    $result = '>=' . strtotime("-7 days", $todayMidnight);
                    $title = 'Last 7 Days';
                    break;

                case '30days':
                    $result = '>=' . strtotime("-30 days", $todayMidnight);
                    $title = 'Last 30 Days';
                    break;

                case 'thismonth':
                    $month = date('n');
                    $year  = date('Y');
                    $result = '>=' . strtotime("{$month}/01/{$year}");
                    $title = 'This Month';
                    break;

                default:
                    $dates = explode(':', $timeframe);

                    if (count($dates) === 2) {
                        if (!empty($dates[0]) && !empty($dates[1])) {
                            $result = strtotime($dates[0]) .
                                ':' . (strtotime($dates[1]) + 60*60*24);

                            $title = "{$dates[0]} - {$dates[1]}";
                        } elseif (empty($dates[0])) {
                            $result = '<=' . (strtotime($dates[1]) + 60*60*24);
                            $title = "From - {$dates[1]}";
                        } elseif (empty($dates[1])) {
                            $result = '>=' . (strtotime($dates[0]));
                            $title = "{$dates[0]} - Till";
                        }

                    } else if (is_numeric($dates[0])) {
                        // Assuming a single timestamp was given - get timeframe for the day
                        $day = strtotime(date('n/d/Y', $dates[0]));
                        $dayPlus = $day + 60*60*24;
                        $result = "$day:$dayPlus";
                        $title = date('n/d/Y', $dates[0]);
                    }
            }
        // Else fall back to the beginning of the service start date, to make sure we get All records.
        } else {
            $result = '>=' . strtotime("01/01/2014");
            $title = 'All';
        }

        if ($getTitle) {
            return $title;
        }

        return $result;
    }


    public static function getTimeframeFromToFormat($getTitle = false)
    {
        $result = [];

        if (empty($_REQUEST['timeframe'])) {
            if ($getTitle) {
                return 'All';
            }

            return $result;
        }

        $timeframe = $_REQUEST['timeframe'];

        $title = null;

        if ($timeframe) {
            self::setTimezone();

            $todayMidnight = strtotime(date('Y-m-d'));

            switch($timeframe) {
                case 'all':
                    $result['startTime'] =  '>=' . strtotime("01/01/2014");
                    $title = 'All';
                    break;

                case 'today':
                    $result['startTime'] =  '>=' . $todayMidnight;
                    $title = 'Today';
                    break;

                case '7days':
                    $result['startTime'] =  '>=' . strtotime("-7 days", $todayMidnight);
                    $title = 'Last 7 Days';
                    break;

                case '30days':
                    $result['startTime'] =  '>=' . strtotime("-30 days", $todayMidnight);
                    $title = 'Last 30 Days';
                    break;

                case 'thismonth':
                    $month = date('n');
                    $year  = date('Y');
                    $result['startTime'] =  '>=' . strtotime("{$month}/01/{$year}");
                    $title = 'This Month';
                    break;

                default:
                    $dates = explode(':', $timeframe);

                    if (count($dates) === 2) {

                        if (!empty($dates[0]) && !empty($dates[1])) {
                            $result['timeframe'] = strtotime($dates[0]) .
                                ':' . (strtotime($dates[1]) + 60*60*24);

                            $result['startTime'] = '>=' . strtotime($dates[0]);
                            $result['endTime'] = '<=' . (strtotime($dates[1]) + 60*60*24);

                            $title = "{$dates[0]} - {$dates[1]}";
                        } elseif (empty($dates[0])) {
                            $result['endTime'] = '<=' . (strtotime($dates[1]) + 60*60*24);
                            $title = "From - {$dates[1]}";
                        } elseif (empty($dates[1])) {
                            $result['startTime'] = '>=' . strtotime($dates[0]);
                            $title = "{$dates[0]} - Till";
                        }
                    } else if (is_numeric($dates[0])) {
                        // Assuming a single timestamp was given - get timeframe for the day
                        $day = strtotime(date('n/d/Y', $dates[0]));
                        $dayPlus = $day + 60*60*24;
                        $result['timeframe'] = "$day:$dayPlus";
                        $result['startTime'] = ">=$day";
                        $result['endTime'] = "<=$dayPlus";
                        $title = date('n/d/Y', $dates[0]);
                    }
            }
            // Else fall back to the beginning of the service start date, to make sure we get All records.
        } else {
            $result['startTime'] =  '>=' . strtotime("01/01/2014");
            $title = 'All';
        }

        if ($getTitle) {
            return $title;
        }

        return $result;
    }

    /**
     * Converts timestamp to WP Date format set in general settings
     *
     * @since 2.0
     * @param $timestamp
     * @return bool|string
     */
    static function toDate($timestamp)
    {
        $dateFormat = get_option('date_format');

        return self::toDateTimeFormat($timestamp, $dateFormat, null);
    }

    /**
     * Converts timestamp to WP Date and Time format
     * which is set in general settings
     *
     * @since 2.0
     * @param $timestamp
     * @return bool|string
     */
    public static function toDateTime($timestamp)
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        return self::toDateTimeFormat($timestamp, $dateFormat, $timeFormat);
    }

    /**
     * @param $timestamp
     * @param null/string $dateFormat
     * @param null/string $timeFormat
     * @return false|string
     */
    public static function toDateTimeFormat($timestamp, $dateFormat = null, $timeFormat = null)
    {
        $format = $dateFormat . ((empty($dateFormat) || empty($timeFormat)) ? '' : ' ') . $timeFormat;
        if (version_compare(get_bloginfo('version'), 5.3, '>=') && function_exists('wp_date')) {
            return wp_date($format, $timestamp, (new \DateTimeZone(self::getTimezoneString())));
        } else {
            self::setTimezone();

            return date_i18n($format, $timestamp);
        }
    }

    /**
     * Execute to set timezone before using any date related functions.
     *
     * @since 2.0
     */
    static function setTimezone()
    {
        date_default_timezone_set(self::getTimezoneString());
    }

    /**
     * @return mixed|string|void
     */
    static function getTimezoneString()
    {
        $timezone_string = get_option( 'timezone_string' );

        if ( $timezone_string ) {
            return $timezone_string;
        }

        $offset  = (float) get_option( 'gmt_offset' );
        $hours   = (int) $offset;
        $minutes = ( $offset - $hours );

        $sign      = ( $offset < 0 ) ? '-' : '+';
        $abs_hour  = abs( $hours );
        $abs_mins  = abs( $minutes * 60 );
        $timezone = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

        if (empty($timezone) || $timezone === '+00:00') {
            $timezone = 'America/Los_Angeles';
        }

        return $timezone;
    }

    public static function getDateFrom()
    {
        if (empty($_REQUEST['timeframe'])) {
            return '';
        }

        $timeframe = $_REQUEST['timeframe'];

        $datesArray = explode(':', $timeframe);

        if (count($datesArray) < 2) {
            return '';
        }

        return $datesArray[0];
    }

    public static function getDateTo()
    {
        if (empty($_REQUEST['timeframe'])) {
            return '';
        }

        $timeframe = $_REQUEST['timeframe'];

        $datesArray = explode(':', $timeframe);

        if (count($datesArray) < 2) {
            return '';
        }

        return $datesArray[1];
    }
}
