<?php

namespace ConvergePaymentsGateway\Service;

use ConvergePaymentsGateway\ConvergePaymentsGateway;
use ConvergePaymentsGateway\Config\ConfigKeys;
use ConvergePaymentsGateway\ResponseCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;
use ConvergePaymentsGateway\Model\ConvergePayments;

/**
 * Implementation of the SIM (Server Integration Method) gateway response processing.
 */
class ResponseService implements ResponseServiceInterface
{
    /**
     * Event dispatcher.
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getOrderFromResponse(array $response)
    {
        if(!isset($response['ssl_invoice_number']))
        {
            return null;
        }
        return OrderQuery::create()->findOneByRef(
            $response['ssl_invoice_number']
        );
    }

    public function payOrderFromResponse(array $response, Order $order)
    {
        $orderEvent = new OrderEvent($order);

        if(isset($response['errorCode']))
        {
            $this->saveData($order->getId(), $response['errorCode'], $response['errorMessage']);
            return false;
        }


        $responseCode = $response['ssl_result'];
        switch ($responseCode) {
            case ResponseCode::APPROVED:
                $orderStatusPaid = OrderStatusQuery::create()->findOneByCode(OrderStatus::CODE_PAID);
                $orderEvent->setStatus($orderStatusPaid->getId());
                $this->eventDispatcher->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $orderEvent);
                $this->saveData($order->getId(), $response['ssl_result'], $response['ssl_result_message']);
                return true;
            case ResponseCode::DECLINED:
            default:
                $this->saveData($order->getId(), $response['ssl_result'], $response['ssl_result_message']);
                return false;
        }
    }

    public function saveData($orderId, $code, $meaage)
    {
        $convergePayment = new ConvergePayments();
        $convergePayment
            ->setOrderId($orderId)
            ->setMessageId($code)
            ->setMessage($meaage)
            ->save()
        ;
    }
}
