<?php

namespace SpatialMatchIdx\core\api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use SpatialMatchIdx\models\LicenseModel;
use SpatialMatchIdx\services\LicenseService;

class SlipstreamApiClient
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var Client
     */
    private $apiClient;

    /**
     * @var string
     */
    private $license;

    /**
     * @var string
     */
    private $product = 'spatialmatch-idx';

    /**
     * @var string
     */
    private $apiVersion = 'v20180104';

    /**
     * @var string
     */
    private $authToken;

    private $cacheTockenKey = 'spm_api_token';

    private $baseUri = 'https://slipstream.homejunction.com/ws/';

    public function __construct()
    {
        $this->apiClient = new Client(['base_uri' => $this->baseUri]);
        $licenseService = LicenseService::getInstance();

        if (($licenseKey = $licenseService->getLicenseKey()) && 'invalid' !== $licenseService->getLicenseType()) {
            $this->license = $licenseKey;
            $this->authToken = $this->getAuthToken();
        }
    }

    public static function getInstance(): SlipstreamApiClient
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @param $action
     * @param array $queryParams
     * @param string $method
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($action, $queryParams = [], $method = 'GET')
    {
        $options = [];

        if ($queryParams) {
            $options = [
                'query' => $queryParams,
                'Accept' => 'application/json',
            ];
        }

        return $this->apiClient->request($method, $action, $options);
    }

    /**
     * @param $action
     * @param array $queryParams
     * @param string $method
     * @return array|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function secureRequest($action, $queryParams = [], $method = 'GET')
    {
        $authToken = $this->getAuthToken();

        if (null === $authToken) {
            return new Response(
                200,
                [],
                json_encode(['success' => false, 'result' => []])
            );
        }

        $options = [
            'Accept' => 'application/json',
            'headers' => [
                'HJI-Slipstream-Token' => $this->getAuthToken(),
            ],
        ];

        if ($queryParams) {
            $options['query'] = $queryParams;
        }

        try {
            return $this->apiClient->request($method, $action, $options);
        } catch (ClientException $exception) {
            error_log($exception->getMessage());

            //throw new SlipstreamApiClientException('SlipstreamApiClient: Something going wrong.');
            return new Response(
                400,
                [],
                json_encode(['success' => false, 'error' => ['message' => 'SlipstreamApiClient: Something going wrong.']])
            );
        }
    }

    /**
     * @param null $licenseKey
     * @return mixed
     */
    public function auth($licenseKey = null)
    {
        $licenseKey = $licenseKey ?? $this->license;
        $options = [
            'licenseKey' => $licenseKey,
            'license' => $licenseKey,
            'product' => $this->product,
            'version' => $this->apiVersion,
            'site' => site_url(),
            'customer' => true,
            'markets' => true,
        ];

        try {
            $response = $this->request('api/authenticate', $options);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['result'];
        } catch (\Exception $exception) {
            $licenseModel = LicenseModel::getData();
            $licenseModel->setInvalidType();
            $licenseModel->saveDataWithoutBeforeAndAfter();
            LicenseService::getInstance()->refreshLicenseModel();

            return [
                'expires' => 0,
                'token' => null,
            ];
        }

    }

    /**
     * @param null $licenseKey
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkLicense($licenseKey = null)
    {
        $licenseKey = $licenseKey ?? $this->license;
        $options = [
            'licenseKey' => $licenseKey,
            'license' => $licenseKey,
            'product' => $this->product,
            'version' => $this->apiVersion,
            'site' => site_url(),
            'customer' => true,
            'markets' => true,
        ];

        $response = $this->request('api/authenticate', $options);
        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'];
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        $apiToken = get_transient($this->cacheTockenKey);

        if (false !== $apiToken) {

            return $apiToken;
        }

        $authData = $this->auth();

        if (null !== $authData['token']) {
            $expTime = $authData['expires'] - time();
            set_transient($this->cacheTockenKey, $authData['token'], $expTime);
        }

        return $authData['token'];
    }

    public function clearAuthToken()
    {
        delete_transient($this->cacheTockenKey);
    }

    /**
     * @param $result
     * @throws SlipstreamApiClientException
     */
    private function checkErrorAndThrowException($result)
    {
        if (isset($result['success']) && false === $result['success']) {
            $errorMessage = $result['error']['message'] ?? 'Something going wrong.';

            throw new SlipstreamApiClientException($errorMessage);
        }
    }

    public function getStatus()
    {
        $response = $this->secureRequest('api/status');

        $data = json_decode($response->getBody()->getContents(), true);

        return $data;
    }

    /**
     * @param array $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLeads(array $options)
    {
        $defOptions = [
            'sortField' => 'createdDate',
            'sortOrder' => 'desc'
        ];

        $options = array_merge($defOptions, $options);

        $response = $this->secureRequest('users/search', $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $options
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(array $options)
    {
        $response = $this->secureRequest('/ws/users/create', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result']['user'] ?? null;
    }

    /**
     * @param $options
     * @return array|null
     */
    public function updateUser($options)
    {
        $response = $this->secureRequest('/ws/users/update', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->checkErrorAndThrowException($data);

        return $data['result'] ?? null;
    }

    /**
     * @param $uid
     * @return array|null
     */
    public function deleteUser($uid)
    {
        $defaults = [
            'id' => $uid,
        ];

        $response = $this->secureRequest('/ws/users/delete', $defaults);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param array $options
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(array $options)
    {
        $response = $this->secureRequest('/ws/users/get', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result']['users'][0] ?? null;
    }

    /**
     * @param string $email
     * @param bool $summary
     * @return array|null
     */
    public function getUserByEmail(string $email, bool $summary = false)
    {
        return $this->getUser([
            'email' => $email,
            'summary' => $summary,
        ]);
    }

    /**
     * @param string $id
     * @param bool $summary
     * @return array|null
     */
    public function getUserById(string $id, bool $summary = false)
    {
        return $this->getUser([
            'id' => $id,
            'summary' => $summary,
        ]);
    }

    /**
     * @param array $options
     * @return mixed|null
     */
    public function logAction(array $options)
    {
        $response = $this->secureRequest('/ws/analytics/log', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $uid
     * @param array $filters
     * @param array $params
     * @return array|null
     */
    public function getUserFavorites($uid, $filters = [], $params = [])
    {
        $defaults = [
            'userId' => $uid,
        ];

        $options = array_merge($defaults, $filters, $params);

        $response = $this->secureRequest('/ws/users/favorites/search', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $uid
     * @param array $filters
     * @param array $params
     * @return array|null
     */
    public function getUserSearches($uid, $filters = [], $params = [])
    {
        $defaults = [
            'userId' => $uid,
        ];

        $options = array_merge($defaults, $filters, $params);

        $response = $this->secureRequest('/ws/users/searches/search', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $uid
     * @param array $filters
     * @param array $params
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserFormEntries($uid, $filters = [], $params = [])
    {
        $defaults = [
            'userId' => $uid,
            'user' => $uid,
            'action' => 'form_entry',
            'userDetails' => true,
        ];


        $options = array_merge($defaults, $filters, $params);

        $response = $this->secureRequest('/ws/analytics/actions/search', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $uid
     * @param array $filters
     * @param array $params
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserAnalyticsSearches($uid, $filters = [], $params = [])
    {
        $defaults = [
            'action' => 'avm,listings.search,listings.get,form_entry',
            'user' => $uid,
            'userDetails' => true,
        ];


        $options = array_merge($defaults, $filters, $params);

        $response = $this->secureRequest('/ws/analytics/actions/search', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $uid
     * @param array $filters
     * @param array $params
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserAnalyticsSessions($uid, $filters = [], $params = [])
    {
        $defaults = [
            'user' => $uid,
        ];


        $options = array_merge($defaults, $filters, $params);

        $response = $this->secureRequest('/ws/analytics/sessions/search', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $id
     * @param array $filters
     * @param array $params
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserAnalyticsSession($id, $filters = [], $params = [])
    {
        $defaults = [
            'id' => $id,
            'actions' => true,
        ];


        $options = array_merge($defaults, $filters, $params);

        $response = $this->secureRequest('/ws/analytics/sessions/get', $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param array $params
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateSearchesAlert($params = [])
    {
        $response = $this->secureRequest('/ws/users/searches/update', $params);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $id
     * @param $uid
     * @return array|null
     */
    public function deleteSearches($id, $uid)
    {

        $params = [
            'searchId' => $id,
            'userId' => $uid,
        ];

        $response = $this->secureRequest('/ws/users/searches/remove', $params);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }

    /**
     * @param $id
     * @param $uid
     * @return array|null
     */
    public function deleteFavorites($id, $uid)
    {

        $params = [
            'favoriteId' => $id,
            'userId' => $uid,
        ];

        $response = $this->secureRequest('/ws/users/favorites/remove', $params);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['result'] ?? null;
    }
}
