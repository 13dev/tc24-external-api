<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 16:11
 * Description: This class will help to consult DB.
 */

namespace App\Resource;
use App\Entity\Tracker;
use App\MessageEnum;
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
     */
    public function store(Tracker $tracker): void
    {
        // Presists data
        try {
            $this->em->persist($tracker);
            $this->em->flush();
        } catch (OptimisticLockException $e) {
            throw new OptimisticLockException(MessageEnum::FAILED_INSERT, $tracker);
        } catch (ORMException $e) {
            throw new ORMException(MessageEnum::FAILED_INSERT, $tracker);
        }

    }
}