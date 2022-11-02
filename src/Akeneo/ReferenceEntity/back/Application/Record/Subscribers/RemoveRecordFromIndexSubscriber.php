<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent;
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
    public function __construct(
        private RecordIndexerInterface $recordIndexer
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RecordsDeletedEvent::class => 'whenRecordsDeleted',
            ReferenceEntityRecordsDeletedEvent::class => 'whenAllRecordsDeleted',
        ];
    }

    public function whenRecordsDeleted(RecordsDeletedEvent $recordsDeletedEvent): void
    {
        foreach ($recordsDeletedEvent->getRecordCodes() as $recordCode) {
            $this->recordIndexer->removeRecordByReferenceEntityIdentifierAndCode(
                (string) $recordsDeletedEvent->getReferenceEntityIdentifier(),
                (string) $recordCode
            );
        }
    }

    public function whenAllRecordsDeleted(ReferenceEntityRecordsDeletedEvent $recordDeletedEvent): void
    {
        $this->recordIndexer->removeByReferenceEntityIdentifier(
            (string) $recordDeletedEvent->getReferenceEntityIdentifier()
        );
    }
}
