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
use App\RegexEnum;
use App\Resource\CustomerResource;
use App\Resource\TrackerResource;
use App\Traits\Helpers;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Respect\Validation\Validator as V;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TrackingAction
 * @property \App\FJson fjson
 * @property Logger logger
 * @property \Awurth\SlimValidation\Validator validator
 * @property EntityManager em
 * @package App\Action
 */
class TrackingAction extends Action
{

    use Helpers;
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
     * ARGUMENTS:
     * {uid} - customer identifier
     *
     * POST PARAMETERS:
     * {latitude} - ^[-+]?(\d*[.])?\d+$
     * {longitude} - ^[-+]?(\d*[.])?\d+$
     * {dateTime} - ^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$
     * The body of request should be in JSON example:
     * {
     * "uid": "UID VALUE",
     * "data": [{
     *      "latitude":"32.688656",         // regexp: ^[-+]?(\d*[.])?\d+$
     *      "longitude":"-16.791765"          // regexp: ^[-+]?(\d*[.])?\d+$
     *      "dateTime":"2017-11-08 17:01:39"  // regexp: ^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$
     *      },
     *      {
     *      "latitude":"32.6561411",           // regexp:^[-+]?(\d*[.])?\d+$
     *      "longitude":"-16.9339971"          // regexp: ^[-+]?(\d*[.])?\d+$
     *      "dateTime":"2017-11-08 17:05:00"  // regexp: ^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$
     *      },]
     * }
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function postTracking(Request $request, Response $response, $args): Response
    {
        $this->logger->info('postTraking called!');

        //Get body as string
        $body = $request->getBody()->getContents();

        /** @var array $data store all required params */
        $data = $request->getParams(['longitude', 'latitude', 'dateTime', 'uid']);

        /** @var bool $bodyIsJson is body a json? */
        $bodyIsJson = $this->isJson($body);

        /** @var array $bodyInJson this var store data of body sent with json */
        $bodyInJson = $bodyIsJson ? json_decode($body, true) : null;

        /** @var string $customerUid get customer uid*/
        $customerUid = $bodyIsJson ? $bodyInJson['uid'] : $data['uid'];

        // Get token
        $token = $request->getHeaderLine('token');

        if($bodyIsJson === true){
            /** key data or key uid in json is missing? */
            if(!array_key_exists('data', $bodyInJson) || !array_key_exists('uid', $bodyInJson)) {
                // save to Log
                $this->logger->alert('Mal formed json');

                //Render exception as json
                return $this->fjson->renderException(HTTPCode::HTTP_BAD_REQUEST, MessageEnum::BODY_MALFORMED);
            }

            // verify if data is a array
            if(!\is_array($bodyInJson['data'])){
                // save to Log
                $this->logger->alert('Body is not an array');

                //Render exception as json
                return $this->fjson->renderException(HTTPCode::HTTP_BAD_REQUEST, MessageEnum::BODY_MALFORMED);
            }

            //Validate each one tracker
            foreach ($bodyInJson['data'] as $i => $tracker) {
                $validator = $this->validator->array($tracker, [
                    'latitude' => V::notEmpty()->notBlank()->regex(RegexEnum::LATITUDE),
                    'longitude' => V::notEmpty()->notBlank()->regex(RegexEnum::LONGITUDE),
                    'dateTime' => V::notEmpty()->notBlank()->regex(RegexEnum::DATE_TIME)
                ]);

                if(!$validator->isValid()) {
                    $this->logger->debug('Validation of params fails.. ' . $validator->count() . ' Errors');

                    //Render exception as json
                    return $this->fjson->renderException(HTTPCode::HTTP_BAD_REQUEST, MessageEnum::BODY_MALFORMED);
                }

                //Convert string to DateTime object
                $bodyInJson['data'][$i]['dateTime'] = \DateTime::createFromFormat('Y-m-d H:i:s', $tracker['dateTime']);

            }

        } else {
            //Body is not a json
            //verify params
            $validator = $this->validator->array($data, [
                'latitude' => V::notEmpty()->notBlank()->regex(RegexEnum::LATITUDE),
                'longitude' => V::notEmpty()->notBlank()->regex(RegexEnum::LONGITUDE),
                'dateTime' => V::notEmpty()->notBlank()->regex(RegexEnum::DATE_TIME),
                'uid' => V::notEmpty()->notBlank(),
            ]);

            //Validate params
            if(!$validator->isValid()) {
                $this->logger->debug('Validation of params fails.. ' . $validator->count() . ' Errors');

                //Render exception as json
                return $this->fjson->renderException(HTTPCode::HTTP_BAD_REQUEST, MessageEnum::PARAM_VALIDATION_ERROR);
            }

            //Convert data
            $data['dateTime'] = \DateTime::createFromFormat('Y-m-d H:i:s',$data['dateTime']);

        }

        // User already on DB
        if ($customer = $this->customerResource->findByUid($customerUid)) {

            //Separate logic
            try {
                /**
                 * This function will return (if doesn't throw exception) the updated data.
                 */
                return $this->handleCustomerExists($customer, $bodyIsJson ? $bodyInJson['data'] : [$data], $customerUid, $token);

            } catch (UniqueConstraintViolationException $e) {
                // Save to log
                $this->logger->error(MessageEnum::UNIQUE_VIOLATION . ' -> ' . $e->getMessage());

                //Return generic message
                return $this->fjson->renderException(HTTPCode::HTTP_UNPROCESSABLE_ENTITY, MessageEnum::UNIQUE_VIOLATION );

            } catch (OptimisticLockException $e) {
                // Save to log
                $this->logger->error(MessageEnum::OCCURRED_EXCEPTION . ' -> ' . $e->getMessage());

                //Return generic message
                return $this->fjson->renderException(HTTPCode::HTTP_UNPROCESSABLE_ENTITY, MessageEnum::OCCURRED_EXCEPTION );

            } catch (ORMException $e) {
                // Save to log
                $this->logger->error(MessageEnum::OCCURRED_EXCEPTION . ' -> ' . $e->getMessage());

                //Return generic message
                return $this->fjson->renderException(HTTPCode::HTTP_UNPROCESSABLE_ENTITY, MessageEnum::OCCURRED_EXCEPTION );

            } catch (RequestBodyEmptyException $e) {
                //Save to Log
                $this->logger->error('RequestBodyEmptyException -> ' . $e->getMessage());

                //Return message
                return $this->fjson->renderException(HTTPCode::HTTP_OK, MessageEnum::CUSTOMER_NO_INFORMATION);

            } catch (GuzzleException $e) {
                // Save to log information
                $this->logger->error('GuzzleException -> ' . $e->getCode() . '  ' . $e->getMessage());

                //Invalid token ?!
                if($e->getCode() === HTTPCode::HTTP_UNAUTHORIZED) {
                    // The token is invalid or doesn't exists.
                    return $this->fjson->renderException(HTTPCode::HTTP_UNAUTHORIZED, MessageEnum::INVALID_TOKEN);
                }

                // Fail to make a request to TC24.
                return $this->fjson->renderException(HTTPCode::HTTP_NOT_FOUND, MessageEnum::FAILED_REQUEST);
            }
        }

        // Make a request to get current user
        try {
            // GET User information {/current}
            $customerInformation = $this->makeCurrentRequest($token);

            // Store in DB
            // Create new Customer
            $customer = new Customer();
            $customer->setUid($customerUid);
            $customer->setEmail($customerInformation['email']);
            $customer->setToken($token);

            // Store customer
            $this->customerResource->store($customer);

            if($bodyIsJson) {
                // Iteract all key's sent on body as json
                foreach ($bodyInJson['data'] as $sentTracker) {

                    //Create new tracker
                    $tracker = new Tracker();
                    $tracker->setCustomer($customer);
                    $tracker->setLatitude($sentTracker['latitude']);
                    $tracker->setLongitude($sentTracker['longitude']);
                    $tracker->setCreated($sentTracker['dateTime']);

                    //$this->em->persist($tracker);
                    $this->trackerResource->store($tracker);
                    // flush everything to the database every 5 inserts
                    //if (($i % 5) === 0) {
                    //    $this->em->flush();
                    //    $this->em->clear();
                    //}

                }
            } else {
                //Body is not json insert one only
                $tracker = new Tracker();
                $tracker->setCustomer($customer);
                $tracker->setLatitude($data['latitude']);
                $tracker->setLongitude($data['longitude']);
                $tracker->setCreated($data['dateTime']);

                $this->trackerResource->store($tracker);
            }


        } catch (RequestBodyEmptyException $e) {
            //Save to log
            $this->logger->error('RequestBodyEmptyException -> ' . $e->getMessage());

            //Return a message
            return $this->fjson->renderException(HTTPCode::HTTP_OK, $e->getMessage());

        } catch (GuzzleException $e) {
            $this->logger->error('GuzzleException -> ' . $e->getCode() . '  ' . $e->getMessage());

            //Invalid token ?!
            if($e->getCode() === HTTPCode::HTTP_UNAUTHORIZED) {
                 // The token is invalid or doesn't exists.
                return $this->fjson->renderException(HTTPCode::HTTP_UNAUTHORIZED, MessageEnum::INVALID_TOKEN);
            }

            // Fail to make a request to TC24.
            return $this->fjson->renderException(HTTPCode::HTTP_NOT_FOUND, MessageEnum::FAILED_REQUEST);

        } catch (ORMException | OptimisticLockException | UniqueConstraintViolationException | NotNullConstraintViolationException $e) {
            //Store on Logger
            $this->logger->error(MessageEnum::FAILED_INSERT . ' ' . $e->getMessage());

            // Show message to user
            return $this->fjson->renderException(HTTPCode::HTTP_OK, MessageEnum::FAILED_INSERT);
        }

        //Tracker, user created
        return $this->fjson->render($customerInformation, HTTPCode::HTTP_OK);

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getTracking(Request $request, Response $response, $args): Response
    {
        // Get Token
        $token = $request->getHeaderLine('token');

        if(!$customer = $this->customerResource->findByToken($token)) {
            //Customer not found

            //Save to log
            $this->logger->alert(MessageEnum::CUSTOMER_NOT_FOUND);

            //Return generic message
            return $this->fjson->notFound(MessageEnum::CUSTOMER_NOT_FOUND);
        }

        // Customer was found.

        //Search tracker
        if(!$cTracker = $this->trackerResource->findByCustomer($customer)) {
            //Tracker not found
            //Save to log
            $this->logger->alert(MessageEnum::CUSTOMER_NO_TRACKER);

            //Return generic message
            return $this->fjson->notFound(MessageEnum::CUSTOMER_NO_TRACKER);
        }

        // Return information about tracking and customer.
        return $this->fjson->render([$cTracker]);
    }

    /**
     * Handle When customer exists on DB
     * USED ON postTracking function
     *
     * @param Customer $customer
     * @param array $body
     * @param string $customerUid
     * @param string $customerToken
     * @return Response
     * @throws GuzzleException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RequestBodyEmptyException
     * @throws UniqueConstraintViolationException
     */
    private function handleCustomerExists(Customer $customer, array $body, string $customerUid, string $customerToken): Response
    {
        // Verify if customer have the token
        if($customer->getToken() === null) {
            // Customer don't have token
            //Get session from YGT
            // GET User information {/current}
            $customerInformation = $this->makeCurrentRequest($customerToken);

            //update customer token
            $customer->setToken($customerToken);
            $customer->setUid($customerUid);
            $customer->setEmail($customerInformation['email']);

            //Flush customer
            $this->em->flush($customer);

        }

        // Iteract all key's sent on body as json
        foreach ($body as $sentTracker) {
            // Create a tracker
            // create tracker from entity
            $tracker = new Tracker();
            $tracker->setCustomer($customer);
            $tracker->setLatitude((double) $sentTracker['latitude']);
            $tracker->setLongitude((double) $sentTracker['longitude']);
            $tracker->setCreated($sentTracker['dateTime']);

            //$this->em->persist($tracker);
            $this->trackerResource->store($tracker);
            // flush everything to the database every 5 inserts
            //if (($i % 5) === 0) {
            //    $this->em->flush();
            //    $this->em->clear();
            //}
        }

        return $this->fjson->render($body, HTTPCode::HTTP_OK, MessageEnum::SUCCESSFULLY_INSERTED);

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
    private function makeCurrentRequest(string $token) {

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

}