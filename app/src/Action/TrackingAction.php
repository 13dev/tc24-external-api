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
use App\JsonResponse;
use App\MessageEnum;
use App\Resource\CustomerResource;
use App\Resource\TrackerResource;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Respect\Validation\Rules\Json;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as V;

/**
 * Class TrackingAction
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


    public function __construct(Container $container, TrackerResource $trackerResource, CustomerResource $customerResource)
    {
        parent::__construct($container);
        $this->trackerResource = $trackerResource;
        $this->customerResource = $customerResource;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return JsonResponse
     * @throws GuzzleException
     * @throws ORMException
     */
    public function postTracking(Request $request, Response $response, $args) {

        return new JsonResponse();
        $this->logger->info('postTraking called!');

        // Verify Token
        if(!$request->hasHeader('token')){
            $this->logger->info('No token on request!');
            return new ErrorJsonResponse(null, MessageEnum::NO_TOKEN_REQUEST, null, 400);
        }
        $this->logger->info('Token exists!');

        // Get token
        $token = $request->getHeaderLine('token');

        // User already on DB
        if($customer = $this->customerResource->exists($token)) {

            // Validation Params
            $validator = $this->validateParams($request);
            // Verify validation
            if(!$validator->isValid()) {
                // Verification fails
                return (new JsonResponse(MessageEnum::PARAM_VALIDATION_ERROR, 6, 400, $validator->getErrors()))->loadReturn();
            }

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
                return (new JsonResponse(MessageEnum::FAILED_INSERT, 6, 400))->loadReturn();
            } catch (ORMException $e) {
            }

            return $response->withJson([
                'status' => 200,
                'code' => 4,
                'data' => [
                    json_encode($customer, true)
                ],
            ]);

        }
        // Search by token
        // Verify if exists on DB

        // Make a request to get current user
        $client = new \GuzzleHttp\Client();
        $url = $this->settings['tc24']['buildUrl'];

        try {
            // make request to get information about the token
            $responseTC = $client->request('GET',"{$url}/customers/current", [
                'headers' => [
                    'token' => $token
                ]
            ]);

            // Build response array
            $responseData = [
                'status' => $responseTC->getStatusCode(),
                'code' => 4,
            ];

            // Include data if has body
            if($responseTC->getBody())
                $responseData['data'] = json_decode($responseTC->getBody()->getContents(), true);

            // no information about the customer.
            if($responseData['data'] === null)
                return (new JsonResponse(MessageEnum::CUSTOMER_NO_INFORMATION, 3, 204))->loadReturn();


        } catch (ClientException $e) {
            $this->logger->error('Guzzle client Exception ' . $e->getCode() . '  ' . $e->getMessage());

            // Has no body the response
            if(!$e->hasResponse()) (new JsonResponse(MessageEnum::FAILED_REQUEST, 3, 204))->loadReturn();

            // return the json of TC24 API
            return $response->withJson(json_decode($e->getResponse()->getBody()->getContents()));
        }

        $validator = $this->validateParams($request);
        // Verify validation
        if(!$validator->isValid()) {
            // Verification fails
            return (new JsonResponse(MessageEnum::PARAM_VALIDATION_ERROR, 6, 400, $validator->getErrors()))->loadReturn();
        }

        // Store in DB
        // Create new Customer
        $customer = new Customer();
        //$customer->setUid($responseData['data']['code']);
        $customer->setUid(time());
        $customer->setEmail($responseData['data']['email']);
        $customer->setToken($token);

        $tracker = new Tracker();
        $tracker->setAddress($request->getParam('address'));
        $tracker->setCustomer($customer);
        $tracker->setLatitude($request->getParam('latitude'));
        $tracker->setLongitude($request->getParam('longitude'));

        try {
            // Store customer
            $this->customerResource->store($customer);
            // Store Tracker
            $this->trackerResource->store($tracker);
        } catch (OptimisticLockException $e) {
            //Store on Logger
            $this->logger->error(MessageEnum::FAILED_INSERT . ' ' . $e->getMessage());

            // Show message to user
            return (new JsonResponse(MessageEnum::FAILED_INSERT, 6, 400))->loadReturn();
        }


        return $response->withJson($responseData);


        /**
         * Get Header -token
         *  eXISTS ?
         * Is valid ?
         * Get DATA
         * VERIFY DB EXISTS
         * GET GPS DATA
         * INSERT ON DB
         */
    }

    /**
     * @return \Awurth\SlimValidation\Validator
     */
    private function validateParams(Request $request) {

        return $this->validator->validate($request, [
            'address' => V::notEmpty(),
            'latitude' => V::notBlank()->numeric(),
            'longitude' => V::notBlank(),
            // ...
        ]);
    }
}