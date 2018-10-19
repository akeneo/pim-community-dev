<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Subscriber;

use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Subscriber\RemoveRecordFromIndexSubscriber;
use PhpSpec\ObjectBehavior;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveRecordFromIndexSubscriberSpec extends ObjectBehavior
{
    function let(RecordIndexerInterface $recordIndexer)
    {
        $this->beConstructedWith($recordIndexer);
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
