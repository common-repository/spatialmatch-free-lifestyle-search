<?php

namespace SpatialMatchIdx\core\models;

use ReflectionClass;
use SpatialMatchIdx\core\helpers\StringHelper;
use SpatialMatchIdx\core\validator\Validator;

class Model
{
    /**
     * @var string
     */
    protected $parentOptionName = '';

    /**
     * @var string
     */
    protected $prefixOptionName = '';

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @var array
     */
    protected $validateRules = [];

    /**
     * @var array
     */
    protected $validateMessages= [];

    /**
     * @return string
     */
    public static function getOptionName(): string
    {
        return StringHelper::camelToId(StringHelper::classBaseName(StringHelper::rStrTrim(get_called_class(), 'Model')), '_');
    }

    /**
     * @return string
     */
    public static function getModelName()
    {
        return static::getOptionName();
    }

    /**
     * @return array
     */
    public static function getLabels(): array
    {
        /* Ex: return [
            'attribute1' => 'Label 1',
            'attribute2' => 'Label 2',
        ]*/
        return [];
    }

    /**
     * @param string $attributeName
     * @return string
     */
    public function getLabel(string $attributeName): string
    {
        return static::getLabels()[$attributeName] ?? $attributeName;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        $class = new ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[$property->getName()] = $property->getName();
            }
        }

        return $names;
    }

    /**
     * Returns attribute values.
     * @param array $names list of attributes whose value needs to be returned.
     * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
     * If it is an array, only the attributes in the array will be returned.
     * @param array $except list of attributes whose value should NOT be returned.
     * @return array attribute values (name => value).
     */
    public function getAttributes($names = null, $except = []): array
    {
        $values = [];

        if ($names === null) {
            $names = $this->attributes();
        }

        foreach ($names as $name) {
            $fieldResult = $this->getDataFromFieldGetter($name);

            if (null !== $fieldResult) {
                $values[$name] = $fieldResult;
                continue;
            }

            $values[$name] = $this->$name;
        }

        foreach ($except as $name) {
            unset($values[$name]);
        }

        return $values;
    }


    /**
     * Returns attribute values.
     * @param array $names list of attributes whose value needs to be returned.
     * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
     * If it is an array, only the attributes in the array will be returned.
     * @param array $except list of attributes whose value should NOT be returned.
     * @return array attribute values (name => value).
     */
    public function getAttributesWithoutGetters($names = null, $except = []): array
    {
        $values = [];

        if ($names === null) {
            $names = $this->attributes();
        }

        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }

        foreach ($except as $name) {
            unset($values[$name]);
        }

        return $values;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        $fieldResult = $this->getDataFromFieldGetter($name);

        if (null !== $fieldResult) {
            return $fieldResult;
        }

        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    /**
     * @param $values
     */
    public function setAttributes($values)
    {
        if (is_array($values)) {
            $attributes = $this->attributes();
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * @param $data
     * @param null $formName
     * @return bool
     */
    public function setAllAttributesFromArray($data, $formName = null): bool
    {
        $scope = $formName === null ? self::getOptionName() : $formName;

        if ('' === $scope && !empty($data)) {
            $this->setAttributes($data);

            return true;
        }

        if (isset($data[$scope])) {
            $this->setAttributes($data[$scope]);

            return true;
        }

        return false;
    }

    /**
     * @return static
     */
    public static function getData()
    {
        $self = new static();

        $dataOptions = $self->getDataFromWpOptions();

        if ('' !== $self->parentOptionName && isset($dataOptions[self::getOptionName()])) {
            $self->setAttributes($dataOptions[self::getOptionName()]);
        } else {
            $self->setAttributes($dataOptions);
        }

        return $self;
    }

    /**
     * @return bool
     */
    public function reset(): bool
    {
        $modelOptionName = self::getOptionName();
        $wpOptionName = $modelOptionName;

        $options = $this->getDataFromWpOptions();

        if (false === $options) {
            return true;
        }

        if ('' === $this->parentOptionName) {

            if (!empty($this->prefixOptionName)) {
                $wpOptionName = $this->prefixOptionName . '_' . $wpOptionName;
            }

            delete_option($wpOptionName);

            return true;
        }

        unset($options[$modelOptionName]);
        update_option($this->parentOptionName, $options, false);

        return true;
    }

    /**
     * @param bool $validate
     */
    public function save($validate = true)
    {
        $modelOptionName = self::getOptionName();
        $wpOptionName = $modelOptionName;

        $options = $this->getDataFromWpOptions();

        $this->beforeSave();

        $data = $this->getAttributesWithoutGetters();

        if ('' !== $this->parentOptionName) {
            $wpOptionName = $this->parentOptionName;

            $data[$modelOptionName] = $data;
        } else {
            if (!empty($this->prefixOptionName)) {
                $wpOptionName = $this->prefixOptionName . '_' . $wpOptionName;
            }
        }

        if (false === $options) {
            add_option($wpOptionName, $data, false);
        } else {

            if ('' !== $this->parentOptionName) {
                $options[$modelOptionName] = $data[$modelOptionName];
            } else {
                $options = $data;
            }

            update_option($wpOptionName, $options, false);
        }

        $this->afterSave();
    }


    /**
     * @param bool $validate
     */
    public function saveDataWithoutBeforeAndAfter($validate = true)
    {
        $modelOptionName = self::getOptionName();
        $wpOptionName = $modelOptionName;

        $options = $this->getDataFromWpOptions();

        $data = $this->getAttributesWithoutGetters();

        if ('' !== $this->parentOptionName) {
            $wpOptionName = $this->parentOptionName;

            $data[$modelOptionName] = $data;
        } else {
            if (!empty($this->prefixOptionName)) {
                $wpOptionName = $this->prefixOptionName . '_' . $wpOptionName;
            }
        }

        if (false === $options) {
            add_option($wpOptionName, $data, false);
        } else {

            if ('' !== $this->parentOptionName) {
                $options[$modelOptionName] = $data[$modelOptionName];
            } else {
                $options = $data;
            }

            update_option($wpOptionName, $options, false);
        }
    }

    public function beforeSave()
    {

    }

    public function afterSave()
    {

    }

    /**
     * @param string $attribute
     * @param string $errorType
     * @param string $errorMessage
     */
    public function addError(string $attribute, string $errorType, string $errorMessage = '')
    {
        $this->_errors[$attribute][$errorType] = $errorMessage;
    }

    /**
     * @param array $items
     */
    public function addErrors(array $items)
    {
        foreach ($items as $attribute => $errors) {
            foreach ($errors as $errorType => $error) {
                $this->addError($attribute, $errorType, $error);
            }
        }
    }

    /**
     * @param null|string $attribute
     * @return bool
     */
    public function hasErrors($attribute = null): bool
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * @param null|string $attribute
     * @return bool
     */
    public function hasErrorByType(string $attribute, string $errorType): bool
    {
        return isset($this->_errors[$attribute][$errorType]);
    }

    /**
     * @param null $attribute
     * @return array|null
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->_errors === null ? [] : $this->_errors;
        }

        return $this->_errors[$attribute] ?? [];
    }

    /**
     * @return array
     */
    public function getValidationOptions()
    {
        return [
            'rules' => $this->validateRules,
            'messages' => [],
        ];
    }

    /**
     * @param null|string $attributeNames
     * @return bool
     */
    public function validate($attributeNames = null): bool
    {
        if(empty($this->validateRules) || !is_array($this->validateRules)) {
            return true;
        }

        foreach ($this->validateRules as $attribute => $rules) {
            $data = $this->getAttributesWithoutGetters();
            $value = $data[$attribute] ?? null;
            $validator = new Validator($attribute, $value, $rules, $this->getLabel($attribute), $this->validateMessages);

            if (!$validator->isValid()) {
                $errors = $validator->getErrorMessages();

                foreach ($errors as $fieldName => $error) {
                    foreach ($error as $errorType => $errorMessage) {
                        $this->_errors[$fieldName][$errorType] = $errorMessage;
                    }
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @return mixed|void
     */
    private function getDataFromWpOptions()
    {
        $modelOptionName = static::getOptionName();
        $wpOptionName = $modelOptionName;

        if ($this->parentOptionName) {
            $wpOptionName = $this->parentOptionName;
        } else {
            if (!empty($this->prefixOptionName)) {
                $wpOptionName = $this->prefixOptionName . '_' . $wpOptionName;
            }
        }

        return get_option($wpOptionName, []);
    }

    /**
     * @param string $fieldName
     * @return mixed|null
     */
    private function getDataFromFieldGetter(string $fieldName)
    {
        $getterName = 'get' . StringHelper::camelize($fieldName);

        if (method_exists($this, $getterName)) {
            return $this->$getterName();
        }

        return null;
    }

    /**
     * @param string $fieldName
     * @return mixed|null
     */
    public function getValueFromWpOptionsByFieldName(string $fieldName)
    {
        if (empty($fieldName)) {
            return null;
        }

        $options = $this->getDataFromWpOptions();

        return $options[$fieldName] ?? null;
    }
}
