<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckAttributeIsNotMainIdentifierOnDeletionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreRemove',
        ];
    }

    public function onPreRemove(RemoveEvent $event): void
    {
        $attribute = $event->getSubject();

        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        $isIdentifierType = AttributeTypes::IDENTIFIER === $attribute->getType();
        if ($isIdentifierType && $attribute->isMainIdentifier()) {
            throw new CannotRemoveAttributeException('flash.attribute.main_identifier_not_removable');
        }
    }
}
