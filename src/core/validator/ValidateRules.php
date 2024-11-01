<?php

namespace SpatialMatchIdx\core\validator;

use Countable;
use DateTime;
use DateTimeZone;
use Exception;
use Throwable;

trait ValidateRules
{
    /**
     * Validate that a required attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateRequired($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateAlpha($attribute, $value)
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateAlphaDash($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) > 0;
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateAlphaNum($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }

    /**
     * Validate that an attribute is an array.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateArray($attribute, $value)
    {
        return is_array($value);
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateDate($attribute, $value)
    {
        if ($value instanceof DateTime) {
            return true;
        }

        if ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateDateFormat($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'date_format');

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $format = $parameters[0] == 'Y-m' ? '!Y-m' : $parameters[0];

        $date = DateTime::createFromFormat($format, $value);

        return $date && $date->format($parameters[0]) == $value;
    }

    /**
     * Validate that an attribute has a given number of digits.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateDigits($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'digits');

        return ! preg_match('/[^0-9]/', $value)
            && strlen((string) $value) == $parameters[0];
    }

    /**
     * Validate that an attribute is between a given number of digits.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateDigitsBetween($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'digits_between');

        $length = strlen((string) $value);

        return ! preg_match('/[^0-9]/', $value)
            && $length >= $parameters[0] && $length <= $parameters[1];
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateEmail($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate an attribute is contained within a list of values.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateIn($attribute, $value, $parameters)
    {
        if (is_array($value) && $this->hasRule($attribute, 'Array')) {
            foreach ($value as $element) {
                if (is_array($element)) {
                    return false;
                }
            }

            return count(array_diff($value, $parameters)) == 0;
        }

        return ! is_array($value) && in_array((string) $value, $parameters);
    }

    /**
     * Validate that an attribute is an integer.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateInteger($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate an attribute is not contained within a list of values.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateNotIn($attribute, $value, $parameters)
    {
        return ! $this->validateIn($attribute, $value, $parameters);
    }

    /**
     * Validate that an attribute is numeric.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateNumeric($attribute, $value)
    {
        return is_numeric($value);
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateRegex($attribute, $value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'regex');

        return preg_match($parameters[0], $value) > 0;
    }

    /**
     * Validate that an attribute is a string.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateString($attribute, $value)
    {
        return is_string($value);
    }

    /**
     * Validate that an attribute is a valid timezone.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateTimezone($attribute, $value)
    {
        try {
            new DateTimeZone($value);
        } catch (Exception  $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * Validate that an attribute is a valid URL.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateUrl($attribute, $value)
    {
        if (! is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL);
    }
    /**
     * Require a certain number of parameters to be present.
     *
     * @param  int    $count
     * @param  array  $parameters
     * @param  string  $rule
     * @return void
     *
     * @throws Exception
     */
    protected function requireParameterCount($count, $parameters, $rule)
    {
        if (count($parameters) < $count) {
            throw new Exception("Validation rule $rule requires at least $count parameters.");
        }
    }
}
