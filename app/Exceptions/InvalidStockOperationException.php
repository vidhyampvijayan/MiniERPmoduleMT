<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidStockOperationException extends InvalidArgumentException
{
    public function __construct($message = 'Invalid stock quantity or operation.')
    {
        parent::__construct($message);
    }

    public function report()
    {
        return false;
    }
}
