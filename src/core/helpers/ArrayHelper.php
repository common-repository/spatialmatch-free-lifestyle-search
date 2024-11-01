<?php

namespace SpatialMatchIdx\core\helpers;

class ArrayHelper
{
    /**
     * @param array $array
     * @return int|string|null
     */
    public static function getArrayKeyFirst(array $array)
    {
        if (count($array) === 0) {
            return null;
        }

        $keys = array_keys($array);

        return $keys[0];
    }

    /**
     * @param array $array
     * @return int|string|null
     */
    public static function getArrayKeyLast(array $array)
    {
        if (count($array) === 0) {
            return null;
        }

        $keys = array_keys($array);

        return $keys[count($keys) - 1];
    }
}
