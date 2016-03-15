<?php

namespace ConvergePaymentsGateway\Service;

use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\Order;

/**
 * Service to build SIM (Server Integration Method) payment form requests.
 */
interface RequestServiceInterface
{
    /**
     * @return string Mode.
     */
    public function getMode();

    /**
     * @return string Demo URL.
     */
    public function getDemoURL();

    /**
     * @return string Production URL.
     */
    public function getProductionURL();

    /**
     * @return string Callback URL to be called by the payment gateway.
     */
    public function getCallbackURL();

    /**
     * Build the payment form request fields.
     * @param Order $order Order to send for payment.
     * @param Request $httpRequest HTTP request.
     * @return array Request fields.
     */
    public function getRequestFields(Order $order, Request $httpRequest);
}
