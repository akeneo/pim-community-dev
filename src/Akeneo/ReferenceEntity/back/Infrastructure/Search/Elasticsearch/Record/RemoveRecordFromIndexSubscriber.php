<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\ReferenceEntityRecordsDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to deleted records events in order to remove them from the search engine index.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveRecordFromIndexSubscriber implements EventSubscriberInterface
{
    /** @var AttributeRepositoryInterface */
    private $recordRepository;

    /** @var RecordIndexerInterface */
    private $recordIndexer;

    public function __construct(
        RecordRepositoryInterface $recordRepositoryInterface,
        RecordIndexerInterface $recordIndexer
    ) {
        $this->recordRepository = $recordRepositoryInterface;
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
