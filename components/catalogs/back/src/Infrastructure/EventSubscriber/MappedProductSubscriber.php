<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Infrastructure\Service\MappedProductCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class MappedProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MappedProductCollector $mappedProductCollector,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'analyzeMappedProducts',
        ];
    }

    public function analyzeMappedProducts(): void
    {
        $this->mappedProductCollector->analyze();
    }
}
