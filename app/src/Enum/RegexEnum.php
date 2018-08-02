<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 31/07/2018 - 17:59
 * Description: Store the regex expressions
 */

namespace App\Enum;

/**
 * Class RegexEnum
 * @package App
 */
abstract class RegexEnum
{
    // Use in validation
    public const DATE_TIME = '/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/';

    public const LATITUDE = '/^[-+]?(\d*[.])?\d+$/';

    public const LONGITUDE = '/^[-+]?(\d*[.])?\d+$/';
}