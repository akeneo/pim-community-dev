<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * The configuration of a table attribute is fully managed via DBAL, so if only the table configuration of an attribute
 * is updated, the onFlush and postFlush events won't be triggered when the attribute is saved, which prevents versions
 * from being generated. (@see \Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener)
 *
 * This subscriber forces the unit of work to schedule an update for table attributes, so that the versioning can
 * properly work
 */
class ScheduleTableAttributeForUpdateSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'scheduleForUpdate',
        ];
    }

    public function scheduleForUpdate(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if ($attribute instanceof AttributeInterface && AttributeTypes::TABLE === $attribute->getType()
            && null !== $attribute->getId()) {
            $this->entityManager->getUnitOfWork()->scheduleForUpdate($attribute);
        }
    }
}
