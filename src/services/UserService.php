<?php

namespace SpatialMatchIdx\services;

use SpatialMatchIdx\core\api\SlipstreamApiClient;


class UserService
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @return UserService
     */
    public static function getInstance(): UserService
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * @param array $userData
     * @return mixed|null
     */
    public function addLead(array $userData)
    {
        // Create new user via API
        $apiClient = SlipstreamApiClient::getInstance();

        return $apiClient->createUser($userData);
    }

    /**
     * @param string $email
     * @return mixed|null
     */
    public function getLeadByEmail(string $email)
    {
        // Get user via API
        $apiClient = SlipstreamApiClient::getInstance();

        return $apiClient->getUserByEmail($email);
    }
}
