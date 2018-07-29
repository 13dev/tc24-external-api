<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 29/07/2018 - 21:43
 * Description: This class will help format the response with helper methods etc.
 */

namespace App;

use Slim\Http\Response;
/**
 * Class FJson
 * @package App
 */
class FJson {

    /**
     * @var Response
     */
    private $_response;

    /**
     * @var array
     */
    private $formattedResponse = [
        'data' => null,
        'message' => MessageEnum::NO_CONTENT,
        'status' => 204
    ];

    /**
     * FJson constructor.
     * @param Response $response
     */
    public function __construct(Response $response) {
        $this->_response = $response;
    }

    /**
     * Return a not found response
     * @param string $message
     * @return Response
     */
    public function notFound($message = '') {
        $this->formattedResponse['status'] = 404;
        if(empty($message))
            $this->formattedResponse['message'] = MessageEnum::NOT_FOUND;

        return $this->_response
            ->withStatus($this->formattedResponse['status'])
            ->withJson($this->formattedResponse);

    }

    /**
     * Customise a format response with given params.
     * @param array $data
     * @param int $status
     * @param string $message
     * @return Response
     */
    public function render($data = [], $status = 200, $message = '') {
        $this->formattedResponse['data'] = $data;
        $this->formattedResponse['status'] = $status;
        $this->formattedResponse['message'] = $message;

        // Remove key with empty message
        if(empty($message) && array_key_exists('message', $this->formattedResponse))
            unset($this->formattedResponse['message']);

        return $this->_response
            ->withStatus($status)
            ->withJson($this->formattedResponse);
    }

    /**
     * Render a basic exception.
     * @param int $status
     * @param string $message
     * @return Response
     */
    public function renderException($status = 200, $message = '') {
        $this->formattedResponse['status'] = $status;

        // Set default message
        if(empty($message))
            $this->formattedResponse['message'] = MessageEnum::OCCURRED_EXCEPTION;

        return $this->_response
            ->withStatus($status)
            ->withJson($this->formattedResponse);
    }


}