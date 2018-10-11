<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\RemoveRecordFromIndexSubscriber;
use PhpSpec\ObjectBehavior;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveRecordFromIndexSubscriberSpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository, RecordIndexerInterface $recordIndexer)
    {
        $this->beConstructedWith($recordRepository, $recordIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveRecordFromIndexSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            RecordDeletedEvent::class => 'whenRecordDeleted',
            ReferenceEntityRecordsDeletedEvent::class => 'whenAllRecordsDeleted',
        ]);
    }

    function it_triggers_the_unindexation_of_an_deleted_record(RecordIndexerInterface $recordIndexer)
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('stark');
        $recordIndexer->removeRecordByReferenceEntityIdentifierAndCode('designer', 'stark')->shouldBeCalled();

        $this->whenRecordDeleted(new RecordDeletedEvent($recordCode, $referenceEntityIdentifier));
    }

    function it_triggers_the_unindexation_of_all_entity_records_when_they_are_deleted(
        RecordIndexerInterface $recordIndexer
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordIndexer->removeByReferenceEntityIdentifier('designer')->shouldBeCalled();

        $this->whenAllRecordsDeleted(new ReferenceEntityRecordsDeletedEvent($referenceEntityIdentifier));
    }
}
