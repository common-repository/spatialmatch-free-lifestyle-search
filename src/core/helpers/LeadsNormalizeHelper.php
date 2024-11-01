<?php

namespace SpatialMatchIdx\core\helpers;

class LeadsNormalizeHelper
{
    public static function normalizeUsersData(array $users): array
    {
        $defArray = [
            'id' => null,
            'name' => null,
            'email' => null,
            'phone' => null,
            'favorites' => null,
            'offMarket' => null,
            'searches' => null,
            'createdDate' => null,
            'lastActive' => null,
            'attributes' => null,
        ];

        $normalizeUsers = [];
        foreach ($users as $user) {
            $tmpUser = [];
            foreach ($defArray as $key => $value) {
                if ($key === 'attributes' && !empty($user['attributes'])) {
                    $attributes = json_decode($user['attributes'], true);
                    if (!empty($attributes['Profile']['photo'])) {
                        $tmpUser['avatar'] = $attributes['Profile']['photo'];
                    }

                }
                if ($key === 'searches') {
                    $tmpUser[$key] = $user['summary'][$key]['total'];
                } elseif ($key === 'favorites') {
                    $tmpUser[$key] = $user['summary'][$key]['total'];
                } elseif ($key === 'offMarket') {
                    $tmpUser[$key] = $user['summary']['favorites']['offMarket'];
                } else {
                    $tmpUser[$key] = $user[$key] ?? null;
                }
            }
            $normalizeUsers[] = $tmpUser;
        }

        return  $normalizeUsers;
    }
}
