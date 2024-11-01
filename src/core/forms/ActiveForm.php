<?php

namespace SpatialMatchIdx\core\forms;

use SpatialMatchIdx\core\helpers\ArrayHelper;
use SpatialMatchIdx\core\models\Model;
use SpatialMatchIdx\core\validator\Validator;

class ActiveForm
{
    /**
     * @var Model
     */
    private $model = null;

    /**
     * @var string
     */
    private $modelName;

    private $_errors =[];

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->modelName = $model::getModelName();

        if (isset($_SESSION['ActiveForm'][$this->modelName])) {
            if (isset($_SESSION['ActiveForm'][$this->modelName]['data'])) {
                $this->model->setAttributes($_SESSION['ActiveForm'][$this->modelName]['data']);
            }

            if (isset($_SESSION['ActiveForm'][$this->modelName]['errors'])) {
                $this->_errors = $_SESSION['ActiveForm'][$this->modelName]['errors'];
            }

            unset($_SESSION['ActiveForm'][$this->modelName]);
        }
    }

    /**
     * @param string|array $nameOrData
     * @return bool
     */
    public function load($nameOrData): bool
    {
        if (is_array($nameOrData)) {
            $this->model->setAttributes($nameOrData);

            return true;
        }

        if (!isset($_POST[$nameOrData])) {
            return false;
        }

        $this->model->setAttributes($_POST[$nameOrData]);

        return true;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $isValid = $this->model->validate();

        if (false === $isValid || $this->hasErrors()) {
            foreach ($this->model->getErrors() as $fieldName => $errors) {
                foreach ($errors as $errorType => $errorMessage) {
                    $this->setError($fieldName, $errorType, $errorMessage);
                }
            }

            $_SESSION['ActiveForm'][$this->modelName] = [
                'data' => $this->model->getAttributes(),
                'errors' => $this->_errors,
            ];
        }

        $isValid = !$this->hasErrors();

        return $isValid;
    }

    /**
     * @param string $fieldName
     * @param string $errorType
     * @param string $errorMessage
     */
    public function setError(string $fieldName, string $errorType, string $errorMessage)
    {
        $this->_errors[$this->modelName][$fieldName][$errorType] = $errorMessage;
    }

    /**
     * @param string|null $field
     * @return bool
     */
    public function hasErrors(string $field = null): bool
    {
        if (null === $field) {
            return (isset($this->_errors[$this->modelName]) && count($this->_errors[$this->modelName]) > 0);
        }

        return (isset($this->_errors[$this->modelName][$field]) && count($this->_errors[$this->modelName][$field]) > 0);
    }

    /**
     * @param string|null $field
     * @return array|null
     */
    public function getErrors(string $field = null)
    {
        if (null === $field) {
            return $this->_errors[$this->modelName] ?? null;
        }

        return $this->_errors[$this->modelName][$field] ?? null;
    }

    /**
     * @param string $field
     */
    public function showError(string $field, $fieldId = null)
    {
        if (!($errors = $this->getErrors($field))) {
            return;
        }

        $errorsFirstKey = ArrayHelper::getArrayKeyFirst($errors);

        echo sprintf('<div %s class="active-form-error error-message">%s</div>', ($fieldId) ? "id='$fieldId-error'" : '', $errors[$errorsFirstKey]);
    }

    /**
     * return errorType of first error in errors array
     *
     * @param string $fieldName
     * @return string|null
     */
    public function getErrorType(string $fieldName)
    {
        if (!($errors = $this->getErrors($fieldName))) {
            return null;
        }

        if (!is_array($errors) && count($errors) > 0) {
            return null;
        }

        return ArrayHelper::getArrayKeyFirst($errors);
    }

    /**
     * @param string $field
     */
    public function showClassIfError(string $field)
    {
        if (!$this->getErrors($field)) {
            return;
        }

        echo ' active-form-error error-message';
    }

    /**
     * @param string $field
     * @param string|null $fieldId
     */
    public function showErrorAttributes(string $field, $fieldId = null)
    {
        if (!$this->getErrors($field)) {
            return;
        }

        if ($fieldId) {
            echo ' aria-describedby="' . $fieldId . '-error"';
        }

        echo ' aria-invalid="true"';
    }

    /**
     * @param string $field
     * @return mixed|null
     */
    public function getValue(string $field)
    {
        return $this->model->getAttribute($field);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if ($this->hasErrors()) {
            return false;
        }

        $this->model->save();

        unset($_SESSION['ActiveForm'][$this->modelName]);

        return true;
    }

    public function getData(): array
    {
        return $this->model->getAttributes();
    }

    /**
     * @param null $namePrefix
     */
    public function generateJsValidation($namePrefix = null)
    {
        $modelValidationOptions = $this->model->getValidationOptions();
        $validateRules = [];
        $validateMessages = [];

        foreach ($modelValidationOptions['rules'] as $field => $rules) {

            if (null !== $namePrefix) {
                $fieldName = sprintf('%s[%s]', $namePrefix, $field);
            } else {
                $fieldName = $field;
            }

            foreach ($rules as $rule) {
                $validateRules[$fieldName][$rule] = true;
                $validateMessages[$fieldName][$rule] =  Validator::createMessage($this->model->getLabel($field), $rule);
            }
        }

        $jsValidationOptions = [
            'errorElement' => 'div',
            'errorClass' => 'active-form-error error-message',
            'rules' => $validateRules,
            'messages' => $validateMessages
        ];

        $validationScript = sprintf('
            <script>
                (function($) {
                  if(jQuery().validate) {
                    document.hjiValidator = $("form").validate(%s);
                    const $hjiValidate = $(".hji-validate");
                    
                    $hjiValidate.on("blur", function(){
                      // check current element
                      let self = this;
                      document.hjiValidator.element(self);
                    });
                    
                    $hjiValidate.on("focus", function(){
                      // check current element
                      let self = this;
                     
                      $hjiValidate.each(function() {
                        if (this === self) {
                          return false;
                        }
                        
                        document.hjiValidator.element(this);
                      });
                    });
                  }
                })(jQuery);
            </script>',
            json_encode($jsValidationOptions)
        );

        add_action('admin_print_footer_scripts', function() use ($validationScript) {
            echo $validationScript;
        });
    }

    /**
     * @param string $fieldName
     * @return mixed|null
     */
    public function getDefaultFieldValue(string $fieldName)
    {
        $defaultFieldName = $fieldName . '_default';

        if (property_exists($this->model, $defaultFieldName)) {
            return $this->model->$defaultFieldName;
        }

        return null;
    }
}
