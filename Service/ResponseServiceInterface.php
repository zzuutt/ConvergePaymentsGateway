<?php

namespace ConvergePaymentsGateway\Service;

use Thelia\Model\Order;

/**
 * Service to process SIM (Server Integration Method) gateway responses.
 */
interface ResponseServiceInterface
{
    /**
     * Get the order associated with a gateway response.
     * @param array $response Response fields.
     * @return Order|null Order Order associated to the response, if found.
     */
    public function getOrderFromResponse(array $response);

    /**
     * Change the order status depending on the gateway response.
     * @param array $response Response fields.
     * @param Order $order Order to process.
     * @return bool Whether the order was paid.
     */
    public function payOrderFromResponse(array $response, Order $order);
}
