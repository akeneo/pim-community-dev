<?php

declare(strict_types=1);


namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Catalogs\ServiceAPI\Events\InvalidCatalogDisabledEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchInvalidCatalogDisabledEvent implements DispatchInvalidCatalogDisabledEventInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(string $catalogId): void
    {
        $this->dispatcher->dispatch(new InvalidCatalogDisabledEvent($catalogId));
    }
}
