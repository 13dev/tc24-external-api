<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 30/07/2018 - 14:02
 * Description: This is call when a request comes with no information or empty
 */

namespace App\Exceptions;

use Throwable;

class RequestBodyEmptyException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}