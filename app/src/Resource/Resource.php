<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 16:25
 * Description:
 */

namespace App\Resource;


use Doctrine\ORM\EntityManager;
use Monolog\Logger;

/**
 * Class Resource
 * @package App\Resource
 */
class Resource
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Resource constructor.
     * @param EntityManager $em
     * @param Logger $logger
     */
    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }
}