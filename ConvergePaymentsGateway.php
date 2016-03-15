<?php

namespace ConvergePaymentsGateway;

use ConvergePaymentsGateway\Config\ConfigKeys;
use ConvergePaymentsGateway\Service\RequestServiceInterface;
use Thelia\Model\Order;
use Thelia\Module\AbstractPaymentModule;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;

class ConvergePaymentsGateway extends AbstractPaymentModule
{

    Const MODULE_DOMAIN = 'convergepaymentsgateway';
    Const DEFAULT_CURRENCY = 'USD';
    
    protected static $defaultConfigValues = [
        ConfigKeys::DEMO_URL => 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do',
        ConfigKeys::PRODUCTION_URL => 'https://www.myvirtualmerchant.com/VirtualMerchant/process.do',
        ConfigKeys::MINIMUM_AMOUNT => '0',
        ConfigKeys::MAXIMUM_AMOUNT => '0',
        ConfigKeys::RECEIPT_LINK_TEXT => 'Continue',
        ConfigKeys::MODE => 'DEMO',
        ConfigKeys::TEST_MODE => '1',
    ];

    public function postActivation(ConnectionInterface $con = null)
    {
        if (!self::getConfigValue('is_initialized', false)) {
            $database = new Database($con);

            $database->insertSql(null, [__DIR__ . "/Config/create.sql", __DIR__ . "/Config/insert.sql"]);
            self::setConfigValue('is_initialized', true);
        }

        /* insert the images from image folder if not already done */
        $moduleModel = $this->getModuleModel();

        if (! $moduleModel->isModuleImageDeployed($con)) {
            $this->deployImageFolder($moduleModel, sprintf('%s/Images', __DIR__), $con);
        }
    }

    public function pay(Order $order)
    {
        /** @var RequestServiceInterface $SRequestService */
        $SRequestService = $this->getContainer()->get('converge.service.sim.request');

        $gatewayUrl = $SRequestService->getDemoURL();
        if($SRequestService->getMode() == 'PRODUCTION') {
            $gatewayUrl = $SRequestService->getProductionURL();
        }

        return $this->generateGatewayFormResponse(
            $order,
            $gatewayUrl,
            $SRequestService->getRequestFields($order, $this->getRequest())
        );
    }

    public function isValidPayment()
    {
        $test_mode = true;
        if(ConfigKeys::TEST_MODE){
            $test_mode = $this->isTestModeAuthorized();
        }

        $validAmount = $this->isValidAmount();
        $checkCurrency = $this->isValidCurrency();

        return $test_mode && $validAmount && $checkCurrency;
    }

    public static function getConfigValue($variableName, $defaultValue = null, $valueLocale = null)
    {
        if ($defaultValue === null && isset(static::$defaultConfigValues[$variableName])) {
            $defaultValue = static::$defaultConfigValues[$variableName];
        }

        return parent::getConfigValue($variableName, $defaultValue, $valueLocale);
    }

    protected function isTestModeAuthorized()
    {
        // If we're in test mode, do not display ConvergePayments on the front office, except for allowed IP addresses.
        $raw_ips = explode("\n", self::getConfigValue(ConfigKeys::IP_AUTHORIZED, ''));

        $allowed_client_ips = array();

        foreach ($raw_ips as $ip) {
            $allowed_client_ips[] = trim($ip);
        }

        $client_ip = $this->getRequest()->getClientIp();

        $valid = in_array($client_ip, $allowed_client_ips);

        return $valid;
    }

    protected function isValidAmount()
    {
        $minAmount = self::getConfigValue(ConfigKeys::MINIMUM_AMOUNT);
        $maxAmount = self::getConfigValue(ConfigKeys::MAXIMUM_AMOUNT);

        $amount = $this->getOrdertotalAmount();

        return $amount > 0 && ($minAmount <= 0 || $amount >= $minAmount) && ($maxAmount <= 0 || $amount <= $maxAmount);
    }

    /**
     * calculate the total order amount
     *
     * @return float|int
     */
    protected function getOrdertotalAmount()
    {
        $session = $this->getRequest()->getSession();
        $order =  $session->getOrder();
        $cart = $session->getSessionCart($this->getContainer()->get('event_dispatcher'));

        $total = $cart->getTaxedAmount($this->getContainer()->get('thelia.taxEngine')->getDeliveryCountry());

        $total += $order->getPostage();

        return $total;
    }

    protected function isValidCurrency()
    {
        $checkCurrency = false;
        $currency =   $this->getCartCurrencyCode();
        if(true === ConvergePaymentsGateway::getConfigValue(ConfigKeys::MULTI_CURRENCIES, false)){
            if(in_array($currency, unserialize(ConvergePaymentsGateway::getConfigValue(ConfigKeys::CURRENCY_AUTHORIZED)))){
                $checkCurrency =  true;
            }
        }
        else {
            if($currency == ConvergePaymentsGateway::DEFAULT_CURRENCY) {
                $checkCurrency = true;
            }
        }

        return $checkCurrency;
    }

    /**
     * get order currency code
     *
     * @return string Code
     */
    protected function getCartCurrencyCode()
    {
        $session = $this->getRequest()->getSession();
        $cart = $session->getSessionCart($this->getContainer()->get('event_dispatcher'));
        $currencyCode = $cart->getCurrency()->getCode();

        return $currencyCode;
    }

}
