<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Domain\Event\AttributeDeactivatedEvent;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateAttributeCommandHandler
{
    public function __construct(
        private readonly DeactivateAttribute $deactivateAttribute,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeactivateAttributeCommand $command): void
    {
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);

        $this->deactivateAttribute->execute($templateUuid, $attributeUuid);
        $this->eventDispatcher->dispatch(new AttributeDeactivatedEvent($templateUuid, $attributeUuid));
    }
}
