<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $errors = [];
    protected $code = 422;

    public function __construct($errors = [], $message = 'Validation failed')
    {
        $this->errors = $errors;
        $this->message = $message;

        parent::__construct($message, $this->code);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function render()
    {
        return response()->json([
            'error' => true,
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->code);
    }
}