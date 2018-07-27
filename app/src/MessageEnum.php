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
    /**
     * Parameter validation fails!
     */
    CONST PARAM_VALIDATION_ERROR = 'Parameter validation failed!';

    CONST FAILED_INSERT = 'Failed insert on database, please try again!';

    CONST CUSTOMER_NO_INFORMATION = 'No information was obtained about this customer.';

    CONST FAILED_REQUEST = 'Error requesting tc24!';

    CONST NO_TOKEN_REQUEST = 'Please provide token and try again!';
}