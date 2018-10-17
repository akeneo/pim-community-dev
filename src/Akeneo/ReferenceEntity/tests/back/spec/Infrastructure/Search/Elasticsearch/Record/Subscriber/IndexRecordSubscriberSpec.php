<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Subscriber;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Subscriber\IndexRecordSubscriber;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriberSpec extends ObjectBehavior
{
    function let(RecordIndexerInterface $recordIndexer)
    {
        $this->beConstructedWith($recordIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexRecordSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([RecordUpdatedEvent::class => 'whenRecordUpdated']);
    }

    function it_triggers_the_reindexation_of_an_updated_record(RecordIndexerInterface $recordIndexer)
    {
        $recordIdentifier = RecordIdentifier::fromString('starck');
        $recordIndexer->index($recordIdentifier)->shouldBeCalled();

        $this->whenRecordUpdated(new RecordUpdatedEvent($recordIdentifier));
    }
}

