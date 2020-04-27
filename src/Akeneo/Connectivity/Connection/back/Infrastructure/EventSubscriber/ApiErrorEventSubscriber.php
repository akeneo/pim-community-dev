<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\MonitoredControllers;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\CollectApiError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ApiErrorEventSubscriber implements EventSubscriberInterface
{
    /** @var CollectApiError */
    private $collectApiError;

    public function __construct(CollectApiError $collectApiError)
    {
        $this->collectApiError = $collectApiError;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'collectApiError',
            KernelEvents::FINISH_REQUEST => 'saveApiErrors',
        ];
    }

    public function collectApiError(ExceptionEvent $exceptionEvent): void
    {
        if (false === in_array($exceptionEvent->getRequest()->get('_controller'), MonitoredControllers::CONTROLLERS)) {
            return;
        }

        $this->collectApiError->collectFromHttpException($exceptionEvent->getException());
    }

    public function saveApiErrors(FinishRequestEvent $finishRequestEvent): void
    {
        if (false === $finishRequestEvent->isMasterRequest()) {
            return;
        }

        $this->collectApiError->save();
    }
}
