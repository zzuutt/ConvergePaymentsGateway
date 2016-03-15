<?php

namespace ConvergePaymentsGateway\Controller\Back;

use ConvergePaymentsGateway\ConvergePaymentsGateway;
use ConvergePaymentsGateway\Config\ConfigKeys;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;
use Thelia\Tools\Version\Version;
use Thelia\Core\Thelia;

/**
 * Back-office module configuration controller.
 */
class ConfigController extends BaseAdminController
{
    /**
     * Configuration fields to save directly.
     * @var array
     */
    protected $fieldsToSave = [
        ConfigKeys::MERCHANT_ID,
        ConfigKeys::USER_ID,
        ConfigKeys::PIN,
        ConfigKeys::MINIMUM_AMOUNT,
        ConfigKeys::MAXIMUM_AMOUNT,
        ConfigKeys::MODE,
        ConfigKeys::DEMO_URL,
        ConfigKeys::PRODUCTION_URL,
        ConfigKeys::CALLBACK_URL,
        ConfigKeys::GATEWAY_RESPONSE_TYPE,
        ConfigKeys::RECEIPT_LINK_TEXT,
        ConfigKeys::TEST_MODE,
        ConfigKeys::IP_AUTHORIZED,
        ConfigKeys::MULTI_CURRENCIES,
        ConfigKeys::CURRENCY_AUTHORIZED,
    ];

    /**
     * Save the module configuration.
     * @return Response
     */
    public function saveAction()
    {
        $authResponse = $this->checkAuth(AdminResources::MODULE, ConvergePaymentsGateway::getModuleCode(), AccessManager::UPDATE);
        if (null !== $authResponse) {
            return $authResponse;
        }

        $baseForm = $this->createForm('converge.form.config');
        try {
            $form = $this->validateForm($baseForm, 'POST');

            foreach ($this->fieldsToSave as $field) {
                if ($field != 'currency_authorized') {
                    ConvergePaymentsGateway::setConfigValue($field, $form->get($field)->getData());
                } else {
                    ConvergePaymentsGateway::setConfigValue($field, serialize($form->get($field)->getData()));
                }
            }

            if ($this->getRequest()->get('save_mode') === 'close') {
                return $this->generateRedirectFromRoute('admin.module');
            } else {
                return $this->generateRedirectFromRoute(
                    'admin.module.configure',
                    [],
                    [
                        'module_code' => ConvergePaymentsGateway::getModuleCode(),
                    ]
                );
            }
        } catch (FormValidationException $ex) {
            $message = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("Converge Payments configuration", [], ConvergePaymentsGateway::MODULE_DOMAIN),
            $message,
            $baseForm,
            $ex
        );

        // Before 2.2, the errored form is not stored in session
        if (Version::test(Thelia::THELIA_VERSION, '2.2', false, "<")) {
            return $this->render('module-configure', [ 'module_code' => 'ConvergePaymentsGateway' ]);
        } else {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/ConvergePaymentsGateway'));
        }
    }

    public function downloadLog()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, ConvergePaymentsGateway::getModuleCode(), AccessManager::UPDATE)) {
            return $response;
        }

        $logFilePath = sprintf(THELIA_ROOT."log".DS."%s.log", ConvergePaymentsGateway::MODULE_DOMAIN);

        return Response::create(
            @file_get_contents($logFilePath),
            200,
            array(
                'Content-type' => "text/plain",
                'Content-Disposition' => sprintf('Attachment;filename=ConvergePaymentsGateway-log.txt')
            )
        );

    }
}
