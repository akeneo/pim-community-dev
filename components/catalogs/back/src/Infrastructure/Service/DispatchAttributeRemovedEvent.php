<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Service\DispatchAttributeRemovedEventInterface;
use Akeneo\Catalogs\ServiceAPI\Events\AttributeRemovedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchAttributeRemovedEvent implements DispatchAttributeRemovedEventInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(string $catalogId): void
    {
        $this->dispatcher->dispatch(new AttributeRemovedEvent($catalogId));
    }
}
