<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Event\AttributeDeactivatedEvent;
use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReorderTemplateAttributesOnAttributeDeactivatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GetAttribute $getAttribute,
        private readonly UpdateCategoryTemplateAttributesOrder $updateCategoryTemplateAttributesOrder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AttributeDeactivatedEvent::class => 'updateOrderOfTemplateAttributes',
        ];
    }

    public function updateOrderOfTemplateAttributes(AttributeDeactivatedEvent $event): void
    {
        $attributeCollection = $this->getAttribute->byTemplateUuid($event->getTemplateUuid());
        $reindexedAttributeCollection = $attributeCollection->rebuildWithIndexedAttributes();
        $this->updateCategoryTemplateAttributesOrder->fromAttributeCollection($reindexedAttributeCollection);
    }
}
