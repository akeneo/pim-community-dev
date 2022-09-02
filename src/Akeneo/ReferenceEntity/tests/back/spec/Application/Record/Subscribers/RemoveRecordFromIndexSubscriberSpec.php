<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Application\Record\Subscribers\RemoveRecordFromIndexSubscriber;
use Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
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
            RecordsDeletedEvent::class => 'whenRecordsDeleted',
            ReferenceEntityRecordsDeletedEvent::class => 'whenAllRecordsDeleted',
        ]);
    }

    public function it_triggers_the_unindexation_of_some_deleted_records(
        RecordIndexerInterface $recordIndexer
    ) {
        $recordIndexer->removeRecordByReferenceEntityIdentifierAndCode('designer', 'stark')->shouldBeCalled();
        $recordIndexer->removeRecordByReferenceEntityIdentifierAndCode('designer', 'lannister')->shouldBeCalled();

        $this->whenRecordsDeleted(new RecordsDeletedEvent(
            [
                RecordIdentifier::fromString('stark_identifier'),
                RecordIdentifier::fromString('lannister_identifier'),
            ],
            [
                RecordCode::fromString('stark'),
                RecordCode::fromString('lannister'),
            ],
            ReferenceEntityIdentifier::fromString('designer')
        ));
    }

    function it_triggers_the_unindexation_of_all_entity_records_when_they_are_deleted(
        RecordIndexerInterface $recordIndexer
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordIndexer->removeByReferenceEntityIdentifier('designer')->shouldBeCalled();

        $this->whenAllRecordsDeleted(new ReferenceEntityRecordsDeletedEvent($referenceEntityIdentifier));
    }
}
