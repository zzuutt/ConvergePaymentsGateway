<?php

namespace ConvergePaymentsGateway\Hook\Front;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

/**
 * Front-office order process hook.
 */
class OrderHook extends BaseHook
{
    /**
     * Replace the order payment gateway redirection page body.
     * Adds support for form fields with duplicate names.
     * @param HookRenderEvent $event
     */
    public function onOrderPaymentGatewayBody(HookRenderEvent $event)
    {
        $event->add(
            $this->render(
                'converge-order-payment-gateway-body.html',
                [
                    'PAYMENT_MODULE' => $event->getArgument('module'),
                ]
            )
        );
    }
}
