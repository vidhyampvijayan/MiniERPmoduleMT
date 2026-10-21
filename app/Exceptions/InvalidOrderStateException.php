<?php

namespace App\Exceptions;

use Exception;

class InvalidOrderStateException extends Exception
{
    public function __construct($message = 'Invalid purchase order status transition.')
    {
        parent::__construct($message);
    }

    public function report()
    {
        return false;
    }

    public function render($request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'INVALID_ORDER_STATE_TRANSITION',
            ], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
