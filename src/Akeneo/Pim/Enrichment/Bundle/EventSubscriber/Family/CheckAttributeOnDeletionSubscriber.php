<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Component\Exception\AttributeAsLabelException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsLabel;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Forbid deletion if the attribute is used as label for any family
 *
 */
class CheckAttributeOnDeletionSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'preRemove',
        ];
    }

    /**
     * Check if the attribute is used as label for a family
     *
     * @param RemoveEvent $event
     * @throws AttributeAsLabelException
     */
    public function preRemove(RemoveEvent $event)
    {
        $attributes = $event->getSubject();

        if (!is_array($attributes)) {
            return;
        }
        $attributes = array_filter(
            $attributes,
            fn ($attr): bool => $attr instanceof AttributeInterface
        );
        if ([] === $attributes) {
            return;
        }

        foreach ($attributes as $attribute) {
            if ($attribute instanceof FamilyAttributeAsLabel) {
                $message = sprintf(
                    'Attributes used as labels in a family cannot be removed.'
                );

                throw new AttributeAsLabelException($message);
            }
        }
    }
}
