<?php

namespace App;
use Slim\Http\Response;

/**
 * Class JsonResponse
 */
class JsonResponse extends Response {

    private $message;

    public function __construct($message)
    {
        $this->message = $message;
        $this->loadResponse();
        parent::__construct(200, null, $this->message);
    }

    public function __invoke()
    {
        return $this->loadResponse();
    }

    public function loadResponse(): string
    {
        return json_encode([$this->message]);
    }


}