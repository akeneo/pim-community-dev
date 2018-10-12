<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to deleted records events
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
        return [RecordDeletedEvent::class => 'whenRecordDeleted'];
    }

    public function whenRecordDeleted(RecordDeletedEvent $recordDeletedEvent): void
    {
        $this->recordIndexer->bulkRemoveByReferenceEntityIdentifiersAndCodes([
            [
                'reference_entity_identifier' => (string) $recordDeletedEvent->getReferenceEntityIdentifier(),
                'record_code' => (string) $recordDeletedEvent->getRecordCode(),
            ],
        ]);
    }
}
