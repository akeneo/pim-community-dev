<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to deleted records events in order to remove them from the search engine index.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveRecordFromIndexSubscriber implements EventSubscriberInterface
{
    /** @var RecordIndexerInterface */
    private $recordIndexer;

    public function __construct(RecordIndexerInterface $recordIndexer)
    {
        $this->recordIndexer = $recordIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RecordDeletedEvent::class => 'whenRecordDeleted',
            ReferenceEntityRecordsDeletedEvent::class => 'whenAllRecordsDeleted',
        ];
    }

    /**
     * /!\ Because of the linked attribute, we need to re-save (and reindex) the records that are linked to the deleted
     * record to update their completeness.
     *
     * One idea would be to use a projection to keep track of the linked records between themselves, to easily resave
     * all the records when the record is removed.
     *
     * @param RecordDeletedEvent $recordDeletedEvent
     */
    public function whenRecordDeleted(RecordDeletedEvent $recordDeletedEvent): void
    {
        $this->recordIndexer->removeRecordByReferenceEntityIdentifierAndCode(
            (string) $recordDeletedEvent->getReferenceEntityIdentifier(),
            (string) $recordDeletedEvent->getRecordCode()
        );
    }

    public function whenAllRecordsDeleted(ReferenceEntityRecordsDeletedEvent $recordDeletedEvent): void
    {
        $this->recordIndexer->removeByReferenceEntityIdentifier(
            (string) $recordDeletedEvent->getReferenceEntityIdentifier()
        );
    }
}
