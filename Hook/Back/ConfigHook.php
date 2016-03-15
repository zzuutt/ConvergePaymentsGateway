<?php

namespace ConvergePaymentsGateway\Hook\Back;

use ConvergePaymentsGateway\ConvergePaymentsGateway;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ModuleQuery;
use ConvergePaymentsGateway\Model\ConvergePaymentsQuery;

/**
 * Back-office configuration hooks.
 */
class ConfigHook extends BaseHook
{
    const MAX_TRACE_SIZE_IN_BYTES = 40000;

    /**
     * Render the module configuration page.
     * @param HookRenderEvent $event
     */
    public function onModuleConfiguration(HookRenderEvent $event)
    {
        $logFilePath = sprintf(THELIA_ROOT."log".DS."%s.log", ConvergePaymentsGateway::MODULE_DOMAIN);

        $traces = @file_get_contents($logFilePath);

        if (false === $traces) {
            $traces = $this->translator->trans(
                "The log file '%log' does not exists yet.",
                [ '%log' => $logFilePath ],
                ConvergePaymentsGateway::MODULE_DOMAIN
            );
        } elseif (empty($traces)) {
            $traces = $this->translator->trans("The log file is currently empty.", [], ConvergePaymentsGateway::MODULE_DOMAIN);
        } else {
            // Limit the size of logs in 1MO
            if (strlen($traces) > self::MAX_TRACE_SIZE_IN_BYTES) {
                $traces = substr($traces, strlen($traces) - self::MAX_TRACE_SIZE_IN_BYTES);
                // Cut a first line break;
                if (false !== $lineBreakPos = strpos($traces, "\n")) {
                    $traces = substr($traces, $lineBreakPos+1);
                }

                $traces = $this->translator->trans(
                        "(Previous log is in %file file.)\n",
                        [ '%file' => sprintf("log".DS."%s.log", ConvergePaymentsGateway::MODULE_DOMAIN) ],
                        ConvergePaymentsGateway::MODULE_DOMAIN
                    ) . $traces;
            }
        }

        $module_code = $event->getArgument('modulecode');
        $module = ModuleQuery::create()->findOneByCode($module_code);
        $module_id = $module->getId();

        $event
            ->add($this->render('converge-config.html',
                [
                'module_id' => $module_id,
                'trace_content' => nl2br($traces)
                ]
            ))
            ->add($this->addCSS('assets/css/style.css'))
        ;
    }

    public function onModuleConfigurationAddJs(HookRenderEvent $event)
    {
        $module_code = $event->getArgument('modulecode');
        $module = ModuleQuery::create()->findOneByCode($module_code);
        $module_id = $module->getId();

        $event
            ->add($this->addJS('assets/js/dropzone.js'))
            ->add($this->addJS('assets/js/image-upload.js'))
            ->add($this->addJS('assets/js/jquery-ui-1.10.3.custom.min.js'))
            ->add($this->render('assets/js/script.html',[
                'module_id' => $module_id
            ]))
        ;
    }

    public function showPaymentInfo(HookRenderEvent $event)
    {
        $orderId = $event->getArgument('order_id');

        if (null !== $order = ConvergePaymentsQuery::create()->findOneByOrderId($orderId)) {
            $event->add($this->render(
                'order-info.html',
                [
                    'order_id' => $orderId
                ]
            ));
        }
    }
}
