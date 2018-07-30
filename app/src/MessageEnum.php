<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 17:03
 * Description: Store messages to be returned
 */

namespace App;


abstract class MessageEnum
{
    // Fail to verify params
    public const PARAM_VALIDATION_ERROR = 'Parameter validation failed!';

    // Occurred error on insert DB
    public const FAILED_INSERT = 'Failed insert on database, please try again!';

    // External api return blank or occurred one error with GUZZLE CLIENT
    public const CUSTOMER_NO_INFORMATION = 'No information was obtained about this customer.';

    // failed to request the external API
    public const FAILED_REQUEST = 'Error requesting tc24!';

    // no token on request message
    public const NO_TOKEN_REQUEST = 'Please provide token and try again!';

    // No content to return message
    public const NO_CONTENT = 'No Content.';

    // Not found message
    public const NOT_FOUND = 'Not Found!';

    // Occurred one exception
    public const OCCURRED_EXCEPTION = 'Unexpected error, please try again!';
}