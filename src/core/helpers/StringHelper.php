<?php
namespace SpatialMatchIdx\core\helpers;

class StringHelper
{
    /**
     * @param $name
     * @param string $separator
     * @param bool $strict
     * @return string
     */
    public static function camelToId($name, $separator = '-', $strict = false): string
    {
        $regex = $strict ? '/[A-Z]/' : '/(?<![A-Z])[A-Z]/';
        if ($separator === '_') {
            return strtolower(trim(preg_replace($regex, '_\0', $name), '_'));
        }

        return strtolower(trim(str_replace('_', $separator, preg_replace($regex, $separator . '\0', $name)), $separator));
    }

    public static function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    /**
     * @param $path
     * @param string $suffix
     * @return string
     */
    public static function classBaseName($path, $suffix = ''): string
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) === $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    /**
     * @param string $str
     * @param null|string $remove
     * @return string
     */
    public static function rStrTrim($str, $remove = null)
    {
        $str    = (string)$str;
        $remove = (string)$remove;

        if(empty($remove))
        {
            return rtrim($str);
        }

        $len = strlen($remove);
        $offset = strlen($str)-$len;
        while($offset > 0 && $offset == strpos($str, $remove, $offset))
        {
            $str = substr($str, 0, $offset);
            $offset = strlen($str)-$len;
        }

        return rtrim($str);

    }
}
