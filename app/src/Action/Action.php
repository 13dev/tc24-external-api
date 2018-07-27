<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 12:20
 * Description:
 */

namespace App\Action;
use Slim\Container;

/**
 * Class Action
 * @package App\Action
 */
class Action
{
    protected $container;

    /**
     * Action constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Magik method to avoid use container->
     * @param $prop
     * @return mixed
     */
    public function __get($prop){
        if($this->container->{$prop}){
            return $this->container->{$prop};
        }
    }
}