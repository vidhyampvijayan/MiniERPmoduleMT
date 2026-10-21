<?php

namespace App\Exceptions;

use Exception;

class OrderImmutableException extends Exception
{
    public function __construct($message = 'Order is in a final state and cannot be modified or transitioned.')
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
                'error' => 'ORDER_IMMUTABLE',
            ], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
