<?php

namespace ConvergePaymentsGateway\Config;

/**
 * Available values for the GATEWAY_RESPONSE_TYPE configuration value.
 */
class GatewayResponseType
{
    /**
     * No response.
     * @var string
     */
    const NONE = 'none';

    /**
     * Link back to the callback URL on the receipt page.
     * @var string
     */
    const RECEIPT_LINK = 'receipt_link';

    /**
     * Redirection to the callback URL.
     * @var string
     */
    const RELAY_RESPONSE = 'relay_response';

}
