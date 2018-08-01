<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 01/08/2018 - 16:00
 * Description:
 */

namespace App\Traits;

/**
 * Trait Helpers
 * @package App\Traits
 */
trait Helpers
{

    /**
     * verify if string is json.
     * @param $string
     * @return bool
     */
    public function isJson(string $string): bool
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }


}