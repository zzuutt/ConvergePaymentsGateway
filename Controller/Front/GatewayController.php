<?php

namespace ConvergePaymentsGateway\Controller\Front;

use ConvergePaymentsGateway\ConvergePaymentsGateway;
use ConvergePaymentsGateway\Config\ConfigKeys;
use ConvergePaymentsGateway\Config\GatewayResponseType;
use ConvergePaymentsGateway\Service\ResponseServiceInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Module\BasePaymentModuleController;

/**
 * Controller for requests from the payment gateway.
 */
class GatewayController extends BasePaymentModuleController
{
    protected function getModuleCode()
    {
        return ConvergePaymentsGateway::getModuleCode();
    }

    /**
     * Process the callback from the payment gateway.
     * @return Response|null The rendered page for non-redirection responses (when relay response is configured).
     * @throws RedirectException For redirection responses (when receipt link is configured).
     */
    public function callbackAction()
    {
        $response = $this->getRequest()->request->all();

        /** @var ResponseServiceInterface $SResponseService */
        $SResponseService = $this->getContainer()->get('converge.service.sim.response');

        $this->getLog()->addInfo(
            $this->getTranslator()->trans('Response parameters : %resp',
                ['%resp' => print_r($response, true)]
            )
        );

        $order = $SResponseService->getOrderFromResponse($response);
        if ($order === null) {
            $this->getLog()->addInfo(
                $this->getTranslator()->trans('Order not found.')
            );
            throw new NotFoundHttpException('Order not found.');
        }

        $this->getLog()->addInfo(
            $this->getTranslator()->trans('Order id: %orderId  ref: %orderRef',
                ['%orderId' => $order->getId(), '%orderRef' => $order->getRef()]
            )
        );

        $orderPaid = $SResponseService->payOrderFromResponse($response, $order);

        if (ConvergePaymentsGateway::getConfigValue(ConfigKeys::GATEWAY_RESPONSE_TYPE) === GatewayResponseType::RELAY_RESPONSE) {
            // for relay response, we have to send a manual redirection page since the Converge Payments gateway
            // seems to interpret an HTTP 302 response code as an error

            if ($orderPaid) {
                $storeURL = $this->retrieveUrlFromRouteId(
                    'order.placed',
                    [],
                    [
                        'order_id' => $order->getId(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                $storeURL = $this->retrieveUrlFromRouteId(
                    'order.failed',
                    [],
                    [
                        'order_id' => $order->getId(),
                        'message' => $this->getTranslator()->trans('Payment error.'),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }

            return $this->render(
                'converge-order-payment-callback',
                [
                    'store_url' => $storeURL,
                ]
            );
        }

        if ($orderPaid) {
            $this->redirectToSuccessPage($order->getId());
        } else {
            $this->redirectToFailurePage($order->getId(), $this->getTranslator()->trans('Payment error.'));
        }

        return null;
    }
}
