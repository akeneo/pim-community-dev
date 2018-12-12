<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Application\Record\Subscribers\IndexByReferenceEntityInBackgroundInterface;
use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriberSpec extends ObjectBehavior
{
    function let(
        RecordIndexerInterface $recordIndexer,
        IndexByReferenceEntityInBackgroundInterface $indexByReferenceEntityInBackground
    ) {
        $this->beConstructedWith($recordIndexer, $indexByReferenceEntityInBackground);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\ReferenceEntity\Application\Record\Subscribers\IndexRecordSubscriber::class);
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

    function it_runs_a_reindexing_command_when_an_attribute_is_removed(
        IndexByReferenceEntityInBackgroundInterface $indexByReferenceEntityInBackground
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $this->whenAttributeIsDeleted(
            new AttributeDeletedEvent(
                $referenceEntityIdentifier,
                AttributeIdentifier::fromString('name_designer_123')
            )
        );
        $indexByReferenceEntityInBackground->execute($referenceEntityIdentifier)->shouldBeCalled();
    }
}

