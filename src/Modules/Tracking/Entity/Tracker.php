<?php

namespace App\Modules\Tracking\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracker
 *
 * @ORM\Table(name="tracker", uniqueConstraints={@ORM\UniqueConstraint(name="created", columns={"created"}), @ORM\UniqueConstraint(name="customer_id", columns={"customer_id"})})
 * @ORM\Entity
 */
class Tracker
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=45, nullable=false)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=45, nullable=false)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=200, nullable=true)
     */
    private $address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;


}

