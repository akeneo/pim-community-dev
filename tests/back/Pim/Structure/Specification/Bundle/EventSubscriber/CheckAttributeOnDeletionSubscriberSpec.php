<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Exception\AttributeRemovalException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;

class CheckAttributeOnDeletionSubscriberSpec extends ObjectBehavior
{
    public function let(
        Connection $dbConnection
    )
    {
        $this->beConstructedWith($dbConnection);
    }

    public function it_throws_an_exception_if_it_is_an_attribute_used_as_label_by_any_family(
        Connection $dbConnection,
        AttributeInterface $attribute,
        Statement $statement
    )
    {
        $event = new RemoveEvent($attribute->getWrappedObject(), 42, ['unitary' => true]);
        $attribute->getId()->willReturn(42);
        $statement->fetchColumn()->willReturn('1');
        $dbConnection->executeQuery(Argument::type('string'), ['attributeIds' => [42]], Argument::cetera())->shouldBeCalled()->willReturn($statement);
        $this->shouldThrow(AttributeRemovalException::class)->during('preRemove', [$event]);
    }

    public function it_does_nothing_if_it_is_not_an_attribute(
        RemoveEvent $event,
        $dbConnection
    )
    {
        $event->getSubject()->willReturn(new \stdClass());
        $dbConnection->executeQuery(Argument::cetera())->shouldNotBeCalled();
        $this->preRemove($event);
    }

    public function it_does_nothing_if_it_has_no_unitary_argument(
        RemoveEvent $event,
        $dbConnection,
        AttributeInterface $attribute
    )
    {
        $event->getSubject()->willReturn($attribute);
        $event->hasArgument('unitary')->willReturn(false);
        $dbConnection->executeQuery(Argument::cetera())->shouldNotBeCalled();
        $this->preRemove($event);
    }

    public function it_does_nothing_if_it_is_not_an_unitary_process(
        RemoveEvent $event,
        $dbConnection,
        AttributeInterface $attribute
    )
    {
        $event->getSubject()->willReturn($attribute);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);
        $dbConnection->executeQuery(Argument::cetera())->shouldNotBeCalled();
        $this->preRemove($event);
    }

    public function it_does_nothing_if_the_event_subject_is_not_an_array(
        RemoveEvent $event,
        $dbConnection
    )
    {
        $event->getSubject()->willReturn(new \stdClass());
        $dbConnection->executeQuery(Argument::cetera())->shouldNotBeCalled();
        $this->bulkpreRemove($event);
    }

    public function it_does_nothing_if_the_event_subject_is_an_empty_array(
        RemoveEvent $event,
        $dbConnection
    )
    {
        $event->getSubject()->willReturn([new \stdClass()]);
        $dbConnection->executeQuery(Argument::cetera())->shouldNotBeCalled();
        $this->bulkpreRemove($event);
    }
}
