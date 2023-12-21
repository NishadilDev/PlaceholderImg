<?php

namespace PlaceholderImg\Exceptions;

use InvalidArgumentException;

class InvalidDimensionsException extends InvalidArgumentException
{
    public function __construct($message = 'Invalid dimensions.', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
