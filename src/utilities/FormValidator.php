<?php
/**
 * FormValidator - Centralized form validation helpers
 * Simplifies and standardizes form validation across the application
 */
class FormValidator
{
    private $errors = [];

    /**
     * Add an error message
     */
    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    /**
     * Check if there are any errors
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get formatted error messages
     */
    public function getErrorMessages()
    {
        $messages = [];
        foreach ($this->errors as $field => $message) {
            $messages[] = $message;
        }
        return $messages;
    }

    /**
     * Validate required field
     */
    public function required($value, $fieldName)
    {
        if (empty(trim($value))) {
            $this->addError($fieldName, "$fieldName is required");
            return false;
        }
        return true;
    }

    /**
     * Validate email format
     */
    public function email($email, $fieldName = 'Email')
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError($fieldName, "Invalid $fieldName format");
            return false;
        }
        return true;
    }

    /**
     * Validate string length
     */
    public function length($value, $min, $max, $fieldName)
    {
        $length = strlen(trim($value));
        if ($length < $min || $length > $max) {
            $this->addError($fieldName, "$fieldName must be between $min and $max characters");
            return false;
        }
        return true;
    }

    /**
     * Validate name (letters and spaces only)
     */
    public function name($name, $fieldName = 'Name')
    {
        if (!preg_match('/^[a-zA-Z\s]+$/', trim($name))) {
            $this->addError($fieldName, "$fieldName must contain only letters and spaces");
            return false;
        }
        return true;
    }

    /**
     * Validate phone number
     */
    public function phone($phone, $fieldName = 'Phone')
    {
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);
        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
            $this->addError($fieldName, "$fieldName must be 10-15 digits");
            return false;
        }
        return true;
    }

    /**
     * Validate postcode
     */
    public function postcode($postcode, $fieldName = 'Postcode')
    {
        if (!preg_match('/^\d{5}$/', trim($postcode))) {
            $this->addError($fieldName, "$fieldName must be exactly 5 digits");
            return false;
        }
        return true;
    }

    /**
     * Validate password strength
     */
    public function password($password, $fieldName = 'Password')
    {
        if (strlen($password) < 6) {
            $this->addError($fieldName, "$fieldName must be at least 6 characters long");
            return false;
        }
        return true;
    }

    /**
     * Validate student registration data
     */
    public function validateStudentRegistration($data)
    {
        $this->required($data['name'], 'Name');
        $this->name($data['name'], 'Name');
        $this->length($data['name'], 2, 100, 'Name');

        $this->required($data['email'], 'Email');
        $this->email($data['email']);

        $this->required($data['password'], 'Password');
        $this->password($data['password']);

        $this->required($data['faculty_code'], 'Faculty');
        $this->required($data['programme_code'], 'Programme');
        $this->required($data['campus'], 'Campus');
        $this->required($data['gender'], 'Gender');
        $this->required($data['level_of_study'], 'Level of Study');
        $this->required($data['mode_of_study'], 'Mode of Study');

        $this->required($data['mailing_address'], 'Mailing Address');
        $this->length($data['mailing_address'], 10, 255, 'Mailing Address');

        $this->required($data['postcode'], 'Postcode');
        $this->postcode($data['postcode']);

        $this->required($data['mobile_phone'], 'Mobile Phone');
        $this->phone($data['mobile_phone'], 'Mobile Phone');

        return !$this->hasErrors();
    }

    /**
     * Validate login data
     */
    public function validateLogin($data)
    {
        $this->required($data['user_id'], 'User ID');
        $this->required($data['password'], 'Password');
        $this->required($data['user_type'], 'User Type');

        return !$this->hasErrors();
    }

    /**
     * Validate course drop request
     */
    public function validateDropRequest($data)
    {
        $this->required($data['course_code'], 'Course');
        $this->required($data['reason'], 'Reason');
        $this->length($data['reason'], 10, 500, 'Reason');
        $this->required($data['lecturer_id'], 'Lecturer');

        return !$this->hasErrors();
    }

    /**
     * Sanitize input data
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean phone number (remove non-digits)
     */
    public static function cleanPhone($phone)
    {
        return preg_replace('/[^\d]/', '', $phone);
    }
}