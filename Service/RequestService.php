<?php

namespace ConvergePaymentsGateway\Service;

use ConvergePaymentsGateway\ConvergePaymentsGateway;
use ConvergePaymentsGateway\Config\ConfigKeys;
use ConvergePaymentsGateway\Config\GatewayResponseType;
use Symfony\Component\Routing\RouterInterface;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\Customer;
use Thelia\Model\Order;
use Thelia\Model\OrderAddress;
use Thelia\Tools\URL;

/**
 * Implementation of the SIM (Server Integration Method) payment form request builder.
 */
class RequestService implements RequestServiceInterface
{
    /**
     * Router for this module.
     * @var RouterInterface
     */
    protected $moduleRouter;

    /**
     * URL tools.
     * @var URL
     */
    protected $URLTools;

    /**
     * @param RouterInterface $moduleRouter Router for this module.
     * @param URL $URLTools URL tools.
     */
    public function __construct(
        RouterInterface $moduleRouter,
        URL $URLTools
    ) {
        $this->moduleRouter = $moduleRouter;
        $this->URLTools = $URLTools;
    }

    public function getMode()
    {
        return ConvergePaymentsGateway::getConfigValue(ConfigKeys::MODE);
    }

    public function getDemoURL()
    {
        return ConvergePaymentsGateway::getConfigValue(ConfigKeys::DEMO_URL);
    }

    public function getProductionURL()
    {
        return ConvergePaymentsGateway::getConfigValue(ConfigKeys::PRODUCTION_URL);
    }

    public function getCallbackURL()
    {
        $callbackURL = ConvergePaymentsGateway::getConfigValue(ConfigKeys::CALLBACK_URL);
        if (empty($callbackURL)) {
            $callbackURL = $this->URLTools->absoluteUrl(
                $this->moduleRouter->generate('converge.front.gateway.callback')
            );
        }

        return $callbackURL;
    }

    public function getRequestFields(Order $order, Request $httpRequest)
    {
        $request = [];

        $this->addBaseFields($request, $order);

        $customer = $order->getCustomer();
        $this->addCustomerFields($request, $customer);

        $this->addCustomerIPFields($request, $httpRequest);

        $billingAddress = $order->getOrderAddressRelatedByInvoiceOrderAddressId();
        $this->addBillingAddressFields($request, $billingAddress);

        $shippingAddress = $order->getOrderAddressRelatedByDeliveryOrderAddressId();
        $this->addShippingAddressFields($request, $shippingAddress);

        switch (ConvergePaymentsGateway::getConfigValue(ConfigKeys::GATEWAY_RESPONSE_TYPE)) {
            case GatewayResponseType::NONE:
                break;
            case GatewayResponseType::RECEIPT_LINK:
                $this->addReceiptLinkFields($request);
                break;
        }

        return $request;
    }

    protected function addBaseFields(array &$request, Order $order)
    {
        $request['ssl_merchant_id'] = ConvergePaymentsGateway::getConfigValue(ConfigKeys::MERCHANT_ID);
        $request['ssl_user_id'] = ConvergePaymentsGateway::getConfigValue(ConfigKeys::USER_ID);
        $request['ssl_pin'] = ConvergePaymentsGateway::getConfigValue(ConfigKeys::PIN);
        $request['ssl_show_form'] = 'true';
        $request['ssl_amount'] = $order->getTotalAmount();
        if(true === ConvergePaymentsGateway::getConfigValue(ConfigKeys::MULTI_CURRENCIES, false)) {
            $request['ssl_currency_code'] = $order->getCurrency()->getCode();
        }
        $request['ssl_invoice_number'] = $order->getRef();
        $request['ssl_transaction_type'] = 'CCSALE';
    }

    protected function addCustomerFields(array &$request, Customer $customer)
    {
        $request['ssl_email'] = $customer->getEmail();
        $request['ssl_first_name'] =  $customer->getFirstname();
        $request['ssl_last_name'] =  $customer->getLastname();
        $request['ssl_customer_id'] = $customer->getRef();
    }

    protected function addCustomerIPFields(array &$request, Request $httpRequest)
    {
        $request['ssl_cardholder_ip'] = $httpRequest->getClientIp();
    }

    protected function addBillingAddressFields(array &$request, OrderAddress $address)
    {
        //$request['ssl_first_name'] =  $customer->getFirstname();
        //$request['ssl_last_name'] =  $customer->getLastname();
        $request['ssl_company'] = $address->getCompany();
        $request['ssl_avs_address'] = $address->getAddress1();
        $request['ssl_address2'] = $address->getAddress2() . ', '. $address->getAddress3();
        $request['ssl_city'] = $address->getCity();
        $request['ssl_avs_zip'] = $address->getZipcode();
        $request['ssl_country'] = $address->getCountry()->getTitle();
        $request['ssl_phone'] = $address->getPhone();
    }

    protected function addShippingAddressFields(array &$request, OrderAddress $address)
    {
        $request['ssl_ship_to_first_name'] = $address->getFirstname();
        $request['ssl_ship_to_last_name'] = $address->getLastname();
        $request['ssl_ship_to_company'] = $address->getCompany();
        $request['ssl_ship_to_address1'] = $address->getAddress1();
        $request['ssl_ship_to_address2'] = $address->getAddress2() . ', '. $address->getAddress3();
        $request['ssl_ship_to_city'] = $address->getCity();
        $request['ssl_ship_to_zip'] = $address->getZipcode();
        $request['ssl_ship_to_country'] = $address->getCountry()->getTitle();
    }

    protected function addReceiptLinkFields(array &$request)
    {
        $request['ssl_receipt_link_method'] = 'POST';
        $request['ssl_receipt_link_url'] = $this->getCallbackURL();
        $request['ssl_receipt_link_text'] = ConvergePaymentsGateway::getConfigValue(ConfigKeys::RECEIPT_LINK_TEXT);
    }


}
