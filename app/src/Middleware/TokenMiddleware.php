<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 30/07/2018 - 11:51
 * Description:
 */

namespace App\Middleware;

use App\Exceptions\TokenNotFoundException;
use App\MessageEnum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TokenMiddleware
 * @package App\Middleware
 */
class TokenMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    /**
     * Check if request has token header.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param $next
     * @return ResponseInterface
     * @throws TokenNotFoundException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if (!$next)
            return $response;

        if(!$request->hasHeader('token'))
            throw new TokenNotFoundException(MessageEnum::NO_TOKEN_REQUEST, 0);

        return $response = $next($request, $response);
    }
}