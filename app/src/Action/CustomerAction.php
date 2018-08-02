<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 30/07/2018 - 17:20
 * Description:
 */

namespace App\Action;

use App\FJson;
use App\Enum\HTTPCodeEnum;
use App\Enum\MessageEnum;
use App\Resource\CustomerResource;
use App\Resource\TrackerResource;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Monolog\Logger;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class CustomerAction
 * @property EntityManager em
 * @property Logger logger
 * @property FJson fjson
 * @package App\Action
 */
class CustomerAction extends Action
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
     * CustomerAction constructor.
     * @param Container $container
     * @param TrackerResource $trackerResource
     * @param CustomerResource $customerResource
     */
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
     * @return
     */
    public function deleteSession(Request $request, Response $response, $args) {
        //Search user by token
        $token = $request->getHeaderLine('token');


        if(!$customer = $this->customerResource->findByToken($token)) {
            //Customer not found

            //Save to log
            $this->logger->alert(MessageEnum::CUSTOMER_NOT_FOUND);

            //Return generic message
            return $this->fjson->notFound(MessageEnum::CUSTOMER_NOT_FOUND);
        }

        //remove customer token
        try {
            /** remove token from DB */
            $customer->setToken(null);

            //Flush data
            $this->em->flush();

        } catch (ORMException $e) {
            // Save to log
            $this->logger->error(MessageEnum::OCCURRED_EXCEPTION . ' -> ' . $e->getMessage());

            //Return generic message
            return $this->fjson->renderException(HTTPCodeEnum::HTTP_UNPROCESSABLE_ENTITY, MessageEnum::OCCURRED_EXCEPTION );
        }

        //removed!
        return $this->fjson->renderException(HTTPCodeEnum::HTTP_OK, MessageEnum::REMOVED);

    }
}