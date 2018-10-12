<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\RecordIndexerInterface;
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
        $this::getSubscribedEvents()->shouldReturn([RecordDeletedEvent::class => 'whenRecordDeleted']);
    }

    function it_triggers_the_reindexation_of_an_updated_record(
        RecordRepositoryInterface $recordRepository,
        RecordIndexerInterface $recordIndexer,
        Record $record
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('stark');
        $recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode)->willReturn($record);
        $recordIndexer->removeRecordByReferenceEntityIdentifierAndCode('designer', 'stark')->shouldBeCalled();

        $this->whenRecordDeleted(new RecordDeletedEvent($recordCode, $referenceEntityIdentifier));
    }
}

