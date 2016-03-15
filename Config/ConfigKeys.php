<?php

namespace ConvergePaymentsGateway\Config;

/**
 * Module configuration keys.
 */
class ConfigKeys
{
    /**
     * Merchant ID.
     * @var string
     */
    const MERCHANT_ID = 'merchant_id';

    /**
     * User ID.
     * @var string
     */
    const USER_ID = 'user_id';

    /**
     * PIN.
     * @var string
     */
    const PIN = 'pin';

    /**
     * Minimum amount.
     * @var string
     */
    const MINIMUM_AMOUNT = 'minimum_amount';

    /**
     * Maximum amount.
     * @var string
     */
    const MAXIMUM_AMOUNT = 'maximum_amount';

    /**
     * Mode.
     * @var string
     */
    const MODE = 'mode';

    /**
     * Demo URL.
     * @var string
     */
    const DEMO_URL = 'demo_url';
    
    /**
     * Production URL.
     * @var string
     */
    const PRODUCTION_URL = 'production_url';

    /**
     * The URL to use as the gateway callback.
     * @var string
     */
    const CALLBACK_URL = 'callback_url';

    /**
     * Text for the link back to the store on the receipt page.
     * @var string
     */
    const RECEIPT_LINK_TEXT = 'receipt_link_text';

    /**
     * Method to be used to get a response from the gateway.
     * @var string
     */
    const GATEWAY_RESPONSE_TYPE = 'gateway_response_type';

    /**
     * Test mode.
     * @var string
     */
    const TEST_MODE = 'test_mode';

    /**
     * List IP authorized in test mode.
     * @var string
     */
    const IP_AUTHORIZED = 'ip_authorized';

    /**
     * Multi currency.
     * @var string
     */
    const MULTI_CURRENCIES = 'multi_currencies';

    /**
     * List currencies authorized.
     * @var string
     */
    const CURRENCY_AUTHORIZED = 'currency_authorized';

}
