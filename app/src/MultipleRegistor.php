<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 12:02
 * Description: This class will handle multiple classes to inject on container.
 */

namespace App;

/**
 * Class MultipleRegistor
 * @package App
 */
class MultipleRegistor
{
    /**
     * MultipleRegistor constructor.
     * @param $container
     * @param array $classes
     */
    public function __construct(&$container, array $classes)
    {
        foreach ($classes as $class)
        {
            $nameClass = explode('\\', $class );
            $nameClass = end($nameClass);

            $container[$nameClass] = function ($container) use($class){

                return new $class($container);
            };
        }
    }
}

