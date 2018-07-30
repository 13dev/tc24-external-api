<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 30/07/2018 - 12:12
 * Description: Throw this exception when token was not found in header.
 */

namespace App\Exceptions;

use Throwable;

class TokenNotFoundException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}