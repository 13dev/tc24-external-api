<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracker
 *
 * @ORM\Table(name="tracker", uniqueConstraints={@ORM\UniqueConstraint(name="unique_gps", columns={"customer_id", "created"})})
 * @ORM\Entity
 */
class Tracker
{
    public function __construct()
    {
        $this->created = new \DateTime('now');
    }
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
     * @ORM\Column(name="latitude", type="float", length=40, nullable=false)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="float", length=40, nullable=false)
     */
    private $longitude;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \App\Entity\Customer
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return Tracker
     */
    public function setLatitude($latitude): Tracker
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return Tracker
     */
    public function setLongitude($longitude): Tracker
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return Tracker
     */
    public function setCreated($created): Tracker
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \App\Entity\Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param \App\Entity\Customer $customer
     * @return Tracker
     */
    public function setCustomer($customer): Tracker
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


}

