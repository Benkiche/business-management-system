<?php

namespace App\Exceptions;

class InsufficientStockException extends BusinessException
{
    protected $message = 'Insufficient stock available';
    protected $code = 400;

    public function __construct($product = null, $available = null, $requested = null)
    {
        $this->message = "Insufficient stock for {$product}. Available: {$available}, Requested: {$requested}";

        parent::__construct($this->message, $this->code);
    }
}