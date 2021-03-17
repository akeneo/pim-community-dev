<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;

class UpdateFamilyAttributeAsLabelSpec extends ObjectBehavior
{
    public function let(
        Connection $dbConnection,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->beConstructedWith($dbConnection, $attributeRepository);
    }

    public function it_does_nothing_if_it_is_not_an_attribute(
        RemoveEvent $event,
        $dbConnection
    )
    {
        $event->getSubject()->willReturn(new \stdClass());
        $dbConnection->executeUpdate(Argument::cetera())->shouldNotBeCalled();
        $this->setIdentifierAsAttributeAsLabel($event);
    }

    public function it_does_nothing_if_it_has_no_unitary_argument(
        RemoveEvent $event,
        $dbConnection,
        AttributeInterface $attribute
    )
    {
        $event->getSubject()->willReturn($attribute);
        $event->hasArgument('unitary')->willReturn(false);
        $dbConnection->executeUpdate(Argument::cetera())->shouldNotBeCalled();
        $this->setIdentifierAsAttributeAsLabel($event);
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
        $dbConnection->executeUpdate(Argument::cetera())->shouldNotBeCalled();
        $this->setIdentifierAsAttributeAsLabel($event);
    }

    public function it_does_nothing_if_the_event_subject_is_not_an_array(
        RemoveEvent $event,
        $dbConnection
    )
    {
        $event->getSubject()->willReturn(new \stdClass());
        $dbConnection->executeUpdate(Argument::cetera())->shouldNotBeCalled();
        $this->setBulkIdentifierAsAttributeAsLabel($event);
    }

    public function it_does_nothing_if_the_event_subject_is_an_empty_array(
        RemoveEvent $event,
        $dbConnection
    )
    {
        $event->getSubject()->willReturn([new \stdClass()]);
        $dbConnection->executeUpdate(Argument::cetera())->shouldNotBeCalled();
        $this->setBulkIdentifierAsAttributeAsLabel($event);
    }
}
