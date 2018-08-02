<?php
/**
 * TravelCentral24.
 * User: Leonardo Oliveira
 * Date: 26/07/2018 - 17:03
 * Description: Store messages to be returned
 */

namespace App\Enum;


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

    // Failed to update
    public const FAIL_UPDATE = 'Unable to update records, please try again!';

    // This error occurs when doctrine try update / create data already existent on db
    public const UNIQUE_VIOLATION = 'Unique constraint violation, contact administrator!';

    // Customer was not found.
    public const CUSTOMER_NOT_FOUND = 'Customer not found, please try again with another token.';

    // Customer doesn't have tracker ?!
    public const CUSTOMER_NO_TRACKER = 'You don\'t have tracker yet!';

    // This message will be show when te TC24 /customer/current return HTTP_UNAUTHORIZED
    public const INVALID_TOKEN = 'You provide a invalid token, please try again!';

    // This message will be show when te removed successfully
    public const REMOVED = 'Removed successfully!';

    //When the given body doesn't follow the structure
    public const BODY_MALFORMED = 'The body that was sent does not follow the requested structure!';

    // data was inserted
    public const SUCCESSFULLY_INSERTED = 'Success, the given data was inserted!';
}