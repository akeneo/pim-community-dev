<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriber implements EventSubscriberInterface
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
        return [RecordUpdatedEvent::class => 'whenRecordUpdated'];
    }

    public function whenRecordUpdated(RecordUpdatedEvent $recordUpdatedEvent): void
    {
        $this->recordIndexer->bulkIndex(
            [$this->recordRepository->getByIdentifier($recordUpdatedEvent->getRecordIdentifier())]
        );
    }
}
