<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 16:11
 * Description: This class will help to consult DB.
 */

namespace App\Resource;

use App\Entity\Customer;
use App\Entity\Tracker;
use App\MessageEnum;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
/**
 * Class TrackerResource
 * @package App\Resource
 */
class TrackerResource extends Resource
{
    /**
     * @param Tracker $tracker
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws UniqueConstraintViolationException
     */
    public function store(Tracker $tracker): void
    {
        // Presists data
        try {
            $this->em->persist($tracker);
            $this->em->flush();
        } catch (OptimisticLockException $e) {
            throw new OptimisticLockException(MessageEnum::FAILED_INSERT, $tracker);
        } catch (UniqueConstraintViolationException | ORMException $e) {
            throw new ORMException(MessageEnum::FAILED_INSERT, $tracker);
        }

    }

    /**
     * @param Customer $customer
     * @return null | Tracker
     */
    public function findByCustomer(Customer $customer) {
      $tracker = $this->em
            ->getRepository(Tracker::class)
            ->findOneBy(['customer' => $customer]);

        /** @var Tracker $tracker */
        return $tracker;
    }

    /**
     * @param $customerId
     * @return null | Tracker
     */
    public function exists($customerId) {
        /** @var Tracker $value */
        $value = $this->em->getRepository(Tracker::class)->findOneBy(['customer' => $customerId]);
        return $value;
    }

}