<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $message = 'A business logic error occurred';
    protected $code = 400;

    public function __construct($message = null, $code = null, Exception $previous = null)
    {
        if ($message) {
            $this->message = $message;
        }
        if ($code) {
            $this->code = $code;
        }

        parent::__construct($this->message, $this->code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => true,
            'message' => $this->message,
            'code' => $this->code,
        ], $this->code);
    }
}