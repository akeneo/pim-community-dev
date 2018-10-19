<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Subscriber;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Subscriber\IndexRecordSubscriber;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexRecordsCommand;
use Akeneo\Tool\Component\Console\CommandLauncher;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriberSpec extends ObjectBehavior
{
    function let(RecordIndexerInterface $recordIndexer, CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($recordIndexer, $commandLauncher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexRecordSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            RecordUpdatedEvent::class    => 'whenRecordUpdated',
            AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
        ]);
    }

    function it_triggers_the_reindexation_of_an_updated_record(RecordIndexerInterface $recordIndexer)
    {
        $recordIdentifier = RecordIdentifier::fromString('starck');
        $recordIndexer->index($recordIdentifier)->shouldBeCalled();

        $this->whenRecordUpdated(new RecordUpdatedEvent($recordIdentifier));
    }

    function it_runs_a_reindexing_command_when_an_attribute_is_removed(CommandLauncher $commandLauncher)
    {
        $this->whenAttributeIsDeleted(
            new AttributeDeletedEvent(
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeIdentifier::fromString('name_designer_123')
            )
        );
        $commandLauncher->executeBackground(IndexRecordsCommand::INDEX_RECORDS_COMMAND_NAME . ' designer');
    }
}

