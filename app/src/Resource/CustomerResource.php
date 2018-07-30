<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 18:25
 * Description:
 */

namespace App\Resource;
use App\Entity\Customer;
use App\MessageEnum;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class CustomerResource
 * @package App\Resource
 */
class CustomerResource extends Resource
{
    /**
     * @param Customer $customer
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function store(Customer $customer): void
    {
        // Presists data
        try {
            $this->em->persist($customer);
            $this->em->flush();
        } catch (OptimisticLockException $e) {
            throw new OptimisticLockException(MessageEnum::FAILED_INSERT, $customer);
        } catch (ORMException $e) {
            throw new ORMException(MessageEnum::FAILED_INSERT, $customer);
        }

    }

    /**
     * @param $token
     * @return null | Customer
     */
    public function exists($token) {
        /** @var Customer $value */
       $value = $this->em->getRepository(Customer::class)->findOneBy(['token' => $token]);
       return $value;
    }
}