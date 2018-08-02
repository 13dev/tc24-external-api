<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 30/07/2018 - 14:02
 * Description: This is call when a request comes with no information or empty
 */

namespace App\Exceptions;

use Throwable;

/**
 * Class RequestBodyEmptyException
 * @package App\Exceptions
 */
class RequestBodyEmptyException extends \Exception
{
    /**
     * RequestBodyEmptyException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}