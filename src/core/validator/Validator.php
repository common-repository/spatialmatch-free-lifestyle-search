<?php

namespace SpatialMatchIdx\core\validator;

class Validator
{
    use ValidateRules;

    /**
     * @var array
     */
    private static $messages = [
//        'required'             => 'The :attribute field is required.',
        'required'             => ':attribute is required.',
        'alpha'                => 'The :attribute may only contain letters.',
        'alpha_dash'           => 'The :attribute may only contain letters, numbers, dashes and underscores.',
        'alpha_num'            => 'The :attribute may only contain letters and numbers.',
        'date'                 => 'The :attribute is not a valid date.',
        'digits'               => 'The :attribute must be :digits digits.',
        'digits_between'       => 'The :attribute must be between :min and :max digits.',
//        'email'                => 'The :attribute must be a valid email address.',
        'email'                => 'Email address must comply with the format xx@xx.xx.',
        'in'                   => 'The selected :attribute is invalid.',
        'integer'              => 'The :attribute must be an integer.',
        'not_in'               => 'The selected :attribute is invalid.',
        'not_regex'            => 'The :attribute format is invalid.',
        'numeric'              => 'The :attribute must be a number.',
        'regex'                => 'The :attribute format is invalid.',
        'string'               => 'The :attribute must be a string.',
        'url'                  => 'The :attribute format is invalid.',
    ];

    /**
     * @var array
     */
    private $_errors = [];

    private $currentFieldLabel;

    public function __construct($attribute, $value, $rule = null, $currentFieldLabel = '', $validateMessages = [])
    {
        if (empty($rule)) {
            return;
        }

        $this->currentFieldLabel = $currentFieldLabel;

        if (is_string($rule)) {
           $this->validateAttributeRule($attribute, $value, $rule, $validateMessages);
        }

        if (is_array($rule)) {
            foreach ($rule as $singleRule) {
                $this->validateAttributeRule($attribute, $value, $singleRule, $validateMessages);
            }
        }
    }


    /**
     * @return bool
     */
    public function isValid()
    {
        return count($this->_errors) < 1;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->_errors;
    }

    /**
     * @param string $attribute
     * @param string $errorType
     * @return string
     */
    public function getErrorMessage(string $attribute, string $errorType):string
    {
        return $this->_errors[$attribute][$errorType] ?? '';
    }

    /**
     * @param $attribute
     * @param $rule
     * @param null $params
     * @param array $validateMessages
     */
    private function addError($attribute, $rule, $params = null, $validateMessages = [])
    {
        $this->_errors[$attribute][$rule] = isset($validateMessages[$attribute][$rule])
            ? $validateMessages[$attribute][$rule]
            : self::createMessage($this->currentFieldLabel, $rule, $params);
    }

    /**
     * @param $attribute
     * @param $value
     * @param null $rule
     * @param array $validateMessages
     */
    private function validateAttributeRule($attribute, $value, $rule = null, $validateMessages = [])
    {
        $methodName = 'validate' . ucfirst($rule);

        if (method_exists($this, $methodName) && ! $this->$methodName($attribute, $value)) {
            $this->addError($attribute, $rule, null, $validateMessages);
        }
    }

    /**
     * @param $attribute
     * @param $rule
     * @param null $params
     * @return array|string|string[]
     */
    public static function createMessage($attribute, $rule, $params = null)
    {
        return str_replace(':attribute', $attribute, self::$messages[$rule]);
    }
}
