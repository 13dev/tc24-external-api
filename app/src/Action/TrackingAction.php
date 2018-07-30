<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 12:17
 * Description: This 'controller' will handle all the logic
 */

namespace App\Action;

use App\Entity\Customer;
use App\Entity\Tracker;
use App\Exceptions\RequestBodyEmptyException;
use App\HTTPCode;
use App\MessageEnum;
use App\Resource\CustomerResource;
use App\Resource\TrackerResource;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as V;

/**
 * Class TrackingAction
 * @property \App\FJson fjson
 * @property Logger logger
 * @property \Awurth\SlimValidation\Validator validator
 * @package App\Action
 */
class TrackingAction extends Action
{
    /**
     * @var TrackerResource
     */
    private $trackerResource;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var string
     */
    private $customerCurrentUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;


    public function __construct(Container $container, TrackerResource $trackerResource, CustomerResource $customerResource)
    {
        parent::__construct($container);
        $this->trackerResource = $trackerResource;
        $this->customerResource = $customerResource;
        $this->customerCurrentUrl = $this->settings['tc24']['buildUrl'];
        $this->guzzleClient = new \GuzzleHttp\Client();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function postTracking(Request $request, Response $response, $args)
    {

        $this->logger->info('postTraking called!');

        // Get token
        $token = $request->getHeaderLine('token');

        // Verify validation
        if (!$this->validateParams($request)->isValid()) {

            $this->logger->info('validateParams failed!');

            // Verification fails
            return $this->fjson->renderException(
                HTTPCode::HTTP_BAD_REQUEST,
                MessageEnum::PARAM_VALIDATION_ERROR
            );

        }

        // User already on DB
        if ($customer = $this->customerResource->exists($token)) {
            //Separate logic
            $this->handleCustomerExists($customer, $request, $response, $args);
        }

        // Make a request to get current user
        try {
            $customerInformation = $this->handleCustomerInformation($token);

        } catch (RequestBodyEmptyException $e) {
            $this->logger->error('RequestBodyEmptyException -> ' . $e->getMessage());

            return $this->fjson->renderException(HTTPCode::HTTP_OK, $e->getMessage());

        } catch (GuzzleException $e) {
            $this->logger->error('GuzzleException -> ' . $e->getCode() . '  ' . $e->getMessage());

            return $this->fjson->renderException(HTTPCode::HTTP_NOT_FOUND, MessageEnum::FAILED_REQUEST);
        }

        $validator = $this->validateParams($request);
        // Verify validation
        if (!$validator->isValid()) {
            // Verification fails
            return $this->fjson->render($validator->getErrors(), HTTPCode::HTTP_BAD_REQUEST, MessageEnum::PARAM_VALIDATION_ERROR);
        }

        try {

            // Store in DB
            // Create new Customer
            $customer = new Customer();
            //$customer->setUid($responseData['data']['code']);
            $customer->setUid(time());
            $customer->setEmail($customerInformation['email']);
            $customer->setToken($token);

            $tracker = new Tracker();
            $tracker->setAddress($request->getParam('address'));
            $tracker->setCustomer($customer);
            $tracker->setLatitude($request->getParam('latitude'));
            $tracker->setLongitude($request->getParam('longitude'));

            // Store customer
            $this->customerResource->store($customer);
            // Store Tracker
            $this->trackerResource->store($tracker);
        } catch (ORMException | OptimisticLockException | NotNullConstraintViolationException $e) {
            //Store on Logger
            $this->logger->error(MessageEnum::FAILED_INSERT . ' ' . $e->getMessage());

            // Show message to user
            return $this->fjson->renderException(HTTPCode::HTTP_OK, MessageEnum::FAILED_INSERT);
        }

        return $this->fjson->render($customerInformation, HTTPCode::HTTP_OK);

    }

    /**
     * Handle When customer exists on DB
     * USED ON postTracking function
     * @param Customer $customer
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    private function handleCustomerExists(Customer $customer, Request $request, Response $response, $args): Response
    {

        $tracker = new Tracker();
        $tracker->setAddress($request->getParam('address'));
        /** @var Customer $customer */
        $tracker->setCustomer($customer);
        $tracker->setLatitude($request->getParam('latitude'));
        $tracker->setLongitude($request->getParam('longitude'));

        try {
            $this->trackerResource->store($tracker);
        } catch (OptimisticLockException $e) {
            //Store on Logger
            $this->logger->error(MessageEnum::FAILED_INSERT . ' ' . $e->getMessage());

            // Show message to user
            return $this->fjson->render(null, HTTPCode::HTTP_BAD_REQUEST, MessageEnum::FAILED_INSERT);
        } catch (ORMException $e) {
            //Store on Logger
            $this->logger->error(MessageEnum::FAILED_INSERT . ' ' . $e->getMessage());

            // Show message to user
            return $this->fjson->render(null, HTTPCode::HTTP_OK, MessageEnum::FAILED_INSERT);
        }

        return $this->fjson->render([json_encode($customer, true)], HTTPCode::HTTP_OK);

    }

    /**
     * Make request to current route and get information about user
     * This will perhaps return user information if not, catch exception.
     * @param string $token Token of user
     *
     * @throws ClientException
     * @throws GuzzleException
     *
     * @return mixed|Response
     * @throws RequestBodyEmptyException
     */
    private function handleCustomerInformation(string $token) {

        // make request to get information about the token
        $responseTC = $this->guzzleClient->request(
            'GET',
            "{$this->customerCurrentUrl}/customers/current", [
                'headers' => [
                    'token' => $token
                ]
        ]);

        //retrieve all data from stream
        $stream = (string) $responseTC->getBody();

        //Throw exception
        if(!$stream || empty($stream))
            throw new RequestBodyEmptyException(MessageEnum::CUSTOMER_NO_INFORMATION);

        return json_decode($stream, true);

    }

    /**
     * @return \Awurth\SlimValidation\Validator
     */
    private function validateParams(Request $request): \Awurth\SlimValidation\Validator
    {

        return $this->validator->validate($request, [
            'address' => V::notEmpty(),
            'latitude' => V::notBlank()->numeric(),
            'longitude' => V::notBlank(),
            // ...
        ]);
    }
}