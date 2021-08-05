<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber\ScheduleTableAttributeForUpdateSubscriber;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ScheduleTableAttributeForUpdateSubscriberSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(ScheduleTableAttributeForUpdateSubscriber::class);
    }

    function it_subscribes_to_pre_save_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
    }

    function it_does_nothing_if_the_argument_is_not_a_table_attribute(
        EntityManagerInterface $entityManager,
        ProductInterface $product,
        AttributeInterface $name
    ) {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $entityManager->getUnitOfWork()->shouldNotBeCalled();

        $this->scheduleForUpdate(new GenericEvent($product));
        $this->scheduleForUpdate(new GenericEvent($name));
    }

    function it_does_nothing_if_the_argument_the_table_attribute_is_nopt_persisted_yet(
        EntityManagerInterface $entityManager,
        AttributeInterface $table
    ) {
        $table->getType()->willReturn(AttributeTypes::TABLE);
        $table->getType()->willReturn(null);
        $entityManager->getUnitOfWork()->shouldNotBeCalled();

        $this->scheduleForUpdate(new GenericEvent($table));
    }

    function it_schedules_a_table_attribute_for_update(
        EntityManagerInterface $entityManager,
        UnitOfWork $unitOfWork,
        AttributeInterface $table
    ) {
        $table->getType()->willReturn(AttributeTypes::TABLE);
        $table->getId()->willReturn(42);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->scheduleForUpdate($table)->shouldBeCalled();

        $this->scheduleForUpdate(new GenericEvent($table->getWrappedObject()));
    }
}
