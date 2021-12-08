<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RecordIndexerInterface $recordIndexer,
        private IndexByReferenceEntityInBackgroundInterface $indexByReferenceEntityInBackground
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RecordUpdatedEvent::class    => 'whenRecordUpdated',
            AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
        ];
    }

    public function whenRecordUpdated(RecordUpdatedEvent $recordUpdatedEvent): void
    {
        $this->recordIndexer->index($recordUpdatedEvent->getRecordIdentifier());
    }

    public function whenAttributeIsDeleted(AttributeDeletedEvent $attributeDeletedEvent): void
    {
        $this->indexByReferenceEntityInBackground->execute($attributeDeletedEvent->referenceEntityIdentifier);
    }
}
