<?php
class Validator {
    private $errors = [];
    private $data = [];
    private $rules = [];
    private $messages = [];
    private $customMessages = [];

    // Default error messages
    private $defaultMessages = [
        'required' => 'The {field} field is required.',
        'email' => 'The {field} must be a valid email address.',
        'min' => 'The {field} must be at least {param} characters.',
        'max' => 'The {field} may not be greater than {param} characters.',
        'numeric' => 'The {field} must be a number.',
        'alpha' => 'The {field} may only contain letters.',
        'alpha_numeric' => 'The {field} may only contain letters and numbers.',
        'url' => 'The {field} must be a valid URL.',
        'date' => 'The {field} must be a valid date.',
        'matches' => 'The {field} must match {param}.',
        'unique' => 'The {field} has already been taken.',
        'in' => 'The selected {field} is invalid.',
        'phone' => 'The {field} must be a valid phone number.',
        'file' => 'The {field} must be a valid file.',
        'image' => 'The {field} must be an image.',
        'mime' => 'The {field} must be a file of type: {param}.',
        'size' => 'The {field} may not be greater than {param}.',
        'between' => 'The {field} must be between {param}.',
        'boolean' => 'The {field} must be true or false.',
        'array' => 'The {field} must be an array.',
        'regex' => 'The {field} format is invalid.'
    ];

    /**
     * Create a new Validator instance
     */
    public function __construct($data = [], $rules = [], $messages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
    }

    /**
     * Validate the data against the rules
     */
    public function validate() {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($rules as $rule) {
                $parameters = [];

                if (strpos($rule, ':') !== false) {
                    list($rule, $parameter) = explode(':', $rule);
                    $parameters = explode(',', $parameter);
                }

                $value = $this->getValue($field);
                $method = 'validate' . ucfirst($rule);

                if (method_exists($this, $method)) {
                    $result = $this->$method($field, $value, $parameters);
                    if ($result === false) {
                        $this->addError($field, $rule, $parameters);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error message for a field
     */
    public function getFirstError($field) {
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Add an error message
     */
    private function addError($field, $rule, $parameters = []) {
        $message = $this->customMessages[$field . '.' . $rule] ?? 
                  $this->customMessages[$rule] ?? 
                  $this->defaultMessages[$rule] ?? 
                  'The {field} field is invalid.';

        $message = str_replace('{field}', str_replace('_', ' ', $field), $message);
        $message = str_replace('{param}', implode(', ', $parameters), $message);

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    /**
     * Get value from data array
     */
    private function getValue($field) {
        return $this->data[$field] ?? null;
    }

    /**
     * Required field validation
     */
    private function validateRequired($field, $value) {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        }
        return true;
    }

    /**
     * Email validation
     */
    private function validateEmail($field, $value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Minimum length validation
     */
    private function validateMin($field, $value, $parameters) {
        $length = is_string($value) ? mb_strlen($value) : $value;
        return $length >= $parameters[0];
    }

    /**
     * Maximum length validation
     */
    private function validateMax($field, $value, $parameters) {
        $length = is_string($value) ? mb_strlen($value) : $value;
        return $length <= $parameters[0];
    }

    /**
     * Numeric validation
     */
    private function validateNumeric($field, $value) {
        return is_numeric($value);
    }

    /**
     * Alpha validation
     */
    private function validateAlpha($field, $value) {
        return preg_match('/^[\pL\s]+$/u', $value);
    }

    /**
     * Alphanumeric validation
     */
    private function validateAlphaNumeric($field, $value) {
        return preg_match('/^[\pL\pN\s]+$/u', $value);
    }

    /**
     * URL validation
     */
    private function validateUrl($field, $value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Date validation
     */
    private function validateDate($field, $value) {
        return strtotime($value) !== false;
    }

    /**
     * Matches validation
     */
    private function validateMatches($field, $value, $parameters) {
        return $value === $this->getValue($parameters[0]);
    }

    /**
     * Unique validation
     */
    private function validateUnique($field, $value, $parameters) {
        $table = $parameters[0];
        $column = $parameters[1] ?? $field;
        $except = $parameters[2] ?? null;

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        if ($except) {
            $sql .= " AND id != ?";
            $params[] = $except;
        }

        $db->query($sql, $params);
        $result = $db->findOne();
        return $result['count'] === 0;
    }

    /**
     * In array validation
     */
    private function validateIn($field, $value, $parameters) {
        return in_array($value, $parameters);
    }

    /**
     * Phone validation
     */
    private function validatePhone($field, $value) {
        return preg_match('/^[+]?[\d\s-]+$/', $value);
    }

    /**
     * File validation
     */
    private function validateFile($field, $value) {
        if (!isset($_FILES[$field])) {
            return false;
        }
        return $_FILES[$field]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Image validation
     */
    private function validateImage($field, $value) {
        if (!$this->validateFile($field, $value)) {
            return false;
        }

        $type = $_FILES[$field]['type'];
        return in_array($type, ['image/jpeg', 'image/png', 'image/gif']);
    }

    /**
     * MIME type validation
     */
    private function validateMime($field, $value, $parameters) {
        if (!$this->validateFile($field, $value)) {
            return false;
        }

        return in_array($_FILES[$field]['type'], $parameters);
    }

    /**
     * File size validation
     */
    private function validateSize($field, $value, $parameters) {
        if (!$this->validateFile($field, $value)) {
            return false;
        }

        return $_FILES[$field]['size'] <= $parameters[0] * 1024 * 1024; // Convert MB to bytes
    }

    /**
     * Between validation
     */
    private function validateBetween($field, $value, $parameters) {
        $size = is_string($value) ? mb_strlen($value) : $value;
        return $size >= $parameters[0] && $size <= $parameters[1];
    }

    /**
     * Boolean validation
     */
    private function validateBoolean($field, $value) {
        $acceptable = [true, false, 0, 1, '0', '1'];
        return in_array($value, $acceptable, true);
    }

    /**
     * Array validation
     */
    private function validateArray($field, $value) {
        return is_array($value);
    }

    /**
     * Regular expression validation
     */
    private function validateRegex($field, $value, $parameters) {
        return preg_match($parameters[0], $value);
    }
}