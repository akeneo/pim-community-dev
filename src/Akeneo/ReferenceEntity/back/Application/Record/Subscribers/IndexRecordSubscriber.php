<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\AttributeUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriber implements EventSubscriberInterface
{
    /** @var RecordIndexerInterface */
    private $recordIndexer;

    /** @var IndexByReferenceEntityInBackgroundInterface */
    private $indexByReferenceEntityInBackground;

    public function __construct(
        RecordIndexerInterface $recordIndexer,
        IndexByReferenceEntityInBackgroundInterface $indexByReferenceEntityInBackground
    ) {
        $this->recordIndexer = $recordIndexer;
        $this->indexByReferenceEntityInBackground = $indexByReferenceEntityInBackground;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RecordUpdatedEvent::class    => 'whenRecordUpdated',
            AttributeUpdatedEvent::class => 'whenAttributeIsUpdated',
            AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
        ];
    }

    public function whenRecordUpdated(RecordUpdatedEvent $recordUpdatedEvent): void
    {
        $this->recordIndexer->index($recordUpdatedEvent->getRecordIdentifier());
    }

    /**
     * The case that interest us is when the required property of the attribute is set to true.
     *
     * @param AttributeUpdatedEvent $attributeUpdatedEvent
     */
    public function whenAttributeIsUpdated(AttributeUpdatedEvent $attributeUpdatedEvent): void
    {
        $this->indexByReferenceEntityInBackground->execute($attributeUpdatedEvent->referenceEntityIdentifier);
    }

    /**
     * The case that interest us is when we remove a required property of the attribute is removed.
     *
     * @param AttributeDeletedEvent $attributeDeletedEvent
     */
    public function whenAttributeIsDeleted(AttributeDeletedEvent $attributeDeletedEvent): void
    {
        $this->indexByReferenceEntityInBackground->execute($attributeDeletedEvent->referenceEntityIdentifier);
    }
}
