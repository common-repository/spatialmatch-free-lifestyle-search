<?php

namespace SpatialMatchIdx\admin\actions\pages\leads;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\PluginAdmin;

class LeadsSearchAction extends BaseAction
{
    public function execute()
    {
        $urlReferer = $_POST['_wp_http_referer'];

        if (empty($urlReferer)) {
            $urlReferer = home_url('wp-admin/admin.php?page=' . PluginAdmin::LEADS_PAGE_SLUG);
        }

        $urlParsed = parse_url($urlReferer);
        parse_str($urlParsed['query'], $urlQueryArray);

        if (isset($_POST['s'])) {
            $postParam['s'] = $_POST['s'];
            unset($_POST['s']);
        } else {
            $postParam['s'] = null;
        }

        if (isset($_POST['time_period'])) {
            $postParam['time_period'] = $_POST['time_period'];
            unset($_POST['time_period']);
        } else {
            $postParam['time_period'] = 'all';
        }

        $urlQueryArray['time_period'] = $urlQueryArray['time_period'] ?? 'all';

        if (isset($_POST['to'])) {
            $postParam['to'] = $_POST['to'];
            unset($_POST['to']);
        } else {
            $postParam['to'] = null;
        }

        if (isset($_POST['from'])) {
            $postParam['from'] = $_POST['from'];
            unset($_POST['from']);
        } else {
            $postParam['from'] = null;
        }

        if ($postParam['time_period'] !== $urlQueryArray['time_period']) {
            if (isset($urlQueryArray['order'])) {
                unset($urlQueryArray['order']);
            }

            if (isset($urlQueryArray['orderby'])) {
                unset($urlQueryArray['orderby']);
            }
        }

        if ($postParam['time_period'] === 'all') {


            if (isset($urlQueryArray['from'])) {
                unset($urlQueryArray['from']);
            }

            if (isset($urlQueryArray['to'])) {
                unset($urlQueryArray['to']);
            }

            if (isset($urlQueryArray['time_period']) && $urlQueryArray['time_period'] !== 'all') {
                if (isset($urlQueryArray['s'])) {
                    unset($urlQueryArray['s']);
                }

                if (!empty($postParam['s'])) {
                    unset($postParam['s']);
                }

            } else {
                $urlQueryArray['s'] = $postParam['s'];
            }

            $urlQueryArray['time_period'] = $postParam['time_period'];
        } else {
            if (
                isset($urlQueryArray['time_period'])
                && $postParam['time_period'] !== $urlQueryArray['time_period']
            ) {
                if (isset($urlQueryArray['s'])) {
                    unset($urlQueryArray['s']);
                }

                if (!empty($postParam['s'])) {
                    unset($postParam['s']);
                }


            } else {
                $urlQueryArray['s'] = $postParam['s'];
            }

            if ($postParam['time_period'] !== null) {
                $urlQueryArray['time_period'] = $postParam['time_period'];

                if ($postParam['time_period'] === 'custom') {
                    if (!empty($postParam['from'])) {
                        $urlQueryArray['from'] = $postParam['from'];
                    } elseif (isset($urlQueryArray['from'])) {
                        unset($urlQueryArray['from']);
                    }

                    if (!empty($postParam['to'])) {
                        $urlQueryArray['to'] = $postParam['to'];
                    } elseif (isset($urlQueryArray['to'])) {
                        unset($urlQueryArray['to']);
                    }
                }
            }
        }

        foreach ($urlQueryArray as $key => $value) {
            $urlQueryArray[$key] = $key . '=' . $value;
        }

        $urlParsed['query'] = implode('&', $urlQueryArray);

        $url = $this->unparseUrl($urlParsed);

        $this->redirect($url);
    }

    private function unparseUrl(array $parsedUrl): string
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = $parsedUrl['host'] ?? '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = $parsedUrl['user'] ?? '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsedUrl['path'] ?? '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
