<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\CollectApiError;
use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use FOS\RestBundle\Context\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ApiErrorEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private CollectApiError $collectApiError)
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductDomainErrorEvent::class => 'collectProductDomainError',
            ProductValidationErrorEvent::class => 'collectProductValidationError',
            TechnicalErrorEvent::class => 'collectTechnicalError',
            KernelEvents::TERMINATE => 'flushApiErrors',
        ];
    }

    public function collectProductDomainError(ProductDomainErrorEvent $event): void
    {
        $this->collectApiError->collectFromProductDomainError(
            $event->getError(),
            (new Context())->setAttribute('product', $event->getProduct())
        );
    }

    public function collectProductValidationError(ProductValidationErrorEvent $event): void
    {
        $context = (new Context())->setAttribute('product', $event->getProduct());
        $this->collectApiError->collectFromProductValidationError(
            $event->getConstraintViolationList(),
            $context
        );
    }

    public function collectTechnicalError(TechnicalErrorEvent $event): void
    {
        $this->collectApiError->collectFromTechnicalError($event->getError());
    }

    public function flushApiErrors(): void
    {
        $this->collectApiError->flush();
    }
}
