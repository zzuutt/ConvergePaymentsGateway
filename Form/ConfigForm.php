<?php

namespace ConvergePaymentsGateway\Form;

use ConvergePaymentsGateway\ConvergePaymentsGateway;
use ConvergePaymentsGateway\Config\ConfigKeys;
use ConvergePaymentsGateway\Config\GatewayResponseType;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Currency;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Module configuration form.
 */
class ConfigForm extends BaseForm
{
    public function getName()
    {
        return 'converge_config';
    }

    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                ConfigKeys::MERCHANT_ID,
                'text',
                [
                    'label' => $this->translator->trans('Merchant ID'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::MERCHANT_ID),
                ]
            )
            ->add(
                ConfigKeys::USER_ID,
                'text',
                [
                    'label' => $this->translator->trans('User ID'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::USER_ID),
                ]
            )
            ->add(
                ConfigKeys::PIN,
                'text',
                [
                    'label' => $this->translator->trans('Pin'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::PIN),
                ]
            )
            ->add(
                'minimum_amount',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(['value' => 0 ])
                    ],
                    'label' => $this->translator->trans('Minimum order total', [], ConvergePaymentsGateway::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'minimum_amount',
                        'help' => $this->translator->trans(
                            'Minimum order total in the default currency for which this payment method is available. Enter 0 for no minimum',
                            [],
                            ConvergePaymentsGateway::MODULE_DOMAIN
                        )
                    ],
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::MINIMUM_AMOUNT),
                ]
            )
            ->add(
                'maximum_amount',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual([ 'value' => 0 ])
                    ],
                    'label' => $this->translator->trans('Maximum order total', [], ConvergePaymentsGateway::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'maximum_amount',
                        'help' => $this->translator->trans(
                            'Maximum order total in the default currency for which this payment method is available. Enter 0 for no maximum',
                            [],
                            ConvergePaymentsGateway::MODULE_DOMAIN
                        )
                    ],
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::MINIMUM_AMOUNT),
                ]
            )
            ->add(
                ConfigKeys::MODE,
                'choice',
                [
                    'label' => $this->translator->trans('Mode'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::MODE),
                    'choices' => [
                        'DEMO' => $this->translator->trans('DEMO'),
                        'PRODUCTION' => $this->translator->trans('PRODUCTION')
                    ]
                ]
            )
            ->add(
                ConfigKeys::DEMO_URL,
                'text',
                [
                    'label' => $this->translator->trans('Demo gateway URL'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::DEMO_URL),
                ]
            )
            ->add(
                ConfigKeys::PRODUCTION_URL,
                'text',
                [
                    'label' => $this->translator->trans('Production gateway URL'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::PRODUCTION_URL),
                ]
            )
            ->add(
                ConfigKeys::CALLBACK_URL,
                'text',
                [
                    'label' => $this->translator->trans('Gateway callback URL'),
                    "label_attr" => [
                        "for" => $this->translator->trans('Gateway callback URL'),
                        "help" => $this->translator->trans("If you leave this field empty, the following URL will be taken: %url", ['%url' => ConfigQuery::read('url_site').'/ConvergePaymentsGateway/gateway/callback'])
                    ],
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::CALLBACK_URL),
                    'required' => false,
                ]
            )
            ->add(
                ConfigKeys::RECEIPT_LINK_TEXT,
                'text',
                [
                    'label' => $this->translator->trans('Receipt link text'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::RECEIPT_LINK_TEXT),
                ]
            )
            ->add(
                ConfigKeys::GATEWAY_RESPONSE_TYPE,
                'choice',
                [
                    'label' => $this->translator->trans('Gateway response type'),
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::GATEWAY_RESPONSE_TYPE),
                    'choices' => [
                        //GatewayResponseType::NONE => $this->translator->trans(
                        //    'No response'
                        //),
                        GatewayResponseType::RECEIPT_LINK => $this->translator->trans(
                            'Link back to the store on the receipt page'
                        ),
                    ]
                ]
            )
            ->add(
                ConfigKeys::TEST_MODE,
                'checkbox',
                [
                    "label" => $this->translator->trans('Test mode'),
                    "label_attr" => [
                        "for" => 'test_mode',
                    ],
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::TEST_MODE) == 1,
                    'required' => false,
                ]
            )
            ->add(
                ConfigKeys::IP_AUTHORIZED,
                'textarea',
                [
                    "label" => $this->translator->trans('IP authorized'),
                    "label_attr" => [
                        "for" => 'ip_authorized',
                        "help" => $this->translator->trans("List of IP addresses allowed to use this payment on the front-office when in test mode (your current IP is %ip). One address per line", ['%ip' => $this->getRequest()->getClientIp()])
                    ],
                    "required" => false,
                    "data" => ConvergePaymentsGateway::getConfigValue(ConfigKeys::IP_AUTHORIZED),
                    'attr' => [
                        'rows' => 3
                    ]
                ]
            )
            ->add(
                ConfigKeys::MULTI_CURRENCIES,
                'checkbox',
                [
                    "label" => $this->translator->trans('Multi Currencies'),
                    "label_attr" => [
                        "for" => 'multi_currencies',
                        "help" => $this->translator->trans("Check this option if you have it enabled in your account. And select the authorized currencies.")
                    ],
                    'data' => ConvergePaymentsGateway::getConfigValue(ConfigKeys::MULTI_CURRENCIES) == 1,
                    'required' => false,
                ]
            )
            ->add(
                ConfigKeys::CURRENCY_AUTHORIZED,
                'choice',
                [
                    "constraints" => [
                        new Callback(array(
                            "methods" => array(
                                array($this,
                                    "CheckCurrency")
                            )
                        ))
                    ],
                    "label" => $this->translator->trans('Currency authorized'),
                    "label_attr" => [
                        "for" => 'currency_authorized',
                        "help" => $this->translator->trans("List of currencies allowed to use this payment on the front-office")
                    ],
                    "required" => false,
                    "multiple" => true,
                    'data' => $this->getDataCurrency(),
                    'choices' => $this->getCurrencyList(),
                ]
            );
    }

    public function CheckCurrency($value, ExecutionContextInterface $context)
    {
        $data = $context->getRoot()->getData();
        $multiCurrencies = $data['multi_currencies'];

        if($multiCurrencies){
            if(empty($value)) {
                $context->addViolation($this->translator->trans('Please select a currency.', [], ConvergePaymentsGateway::MODULE_DOMAIN));
            }

        }

       return true;
    }

    private function getDataCurrency()
    {
        $dataValue = array();
        if(ConvergePaymentsGateway::getConfigValue(ConfigKeys::CURRENCY_AUTHORIZED))
        {
            $dataValue = unserialize(ConvergePaymentsGateway::getConfigValue(ConfigKeys::CURRENCY_AUTHORIZED));
        }
        return $dataValue;
    }

    private function getCurrencyList()
    {
        $currencyList = array();
        $currency = CurrencyQuery::create()->orderByCode(Criteria::ASC);
        foreach($currency as $value)
        {
            $currencyCode = $value->getCode();
            $currencyList[$currencyCode] = $currencyCode;
        }
        return $currencyList;
    }
}
