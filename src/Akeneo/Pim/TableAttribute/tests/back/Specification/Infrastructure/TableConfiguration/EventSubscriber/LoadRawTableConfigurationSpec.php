<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber\LoadRawTableConfiguration;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoadRawTableConfigurationSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->beConstructedWith($tableConfigurationRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoadRawTableConfiguration::class);
    }

    function it_is_a_doctrine_event_subscriber()
    {
        $this->shouldImplement(EventSubscriber::class);
    }

    function it_does_nothing_when_entity_is_not_an_attribute(
        TableConfigurationRepository $tableConfigurationRepository,
        LifecycleEventArgs $event
    ) {
        $entity = new \stdClass();
        $event->getObject()->willReturn($entity);

        $tableConfigurationRepository->getByAttributeId(Argument::any())->shouldNotBeCalled();

        $this->postLoad($event);
    }

    function it_does_nothing_when_entity_is_not_a_table_attribute(
        TableConfigurationRepository $tableConfigurationRepository,
        LifecycleEventArgs $event,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $event->getObject()->willReturn($attribute);

        $tableConfigurationRepository->getByAttributeId(Argument::any())->shouldNotBeCalled();

        $this->postLoad($event);
    }

    function it_loads_raw_table_configuration_for_a_table_attribute(
        TableConfigurationRepository $tableConfigurationRepository,
        LifecycleEventArgs $event,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getId()->willReturn(42);
        $event->getObject()->willReturn($attribute);

        $tableConfigurationRepository->getByAttributeId(42)->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions(
                [
                    TextColumn::fromNormalized(['code' => 'ingredients', 'labels' => []]),
                    TextColumn::fromNormalized(['code' => 'quantity', 'labels' => []]),
                ]
            )
        );
        $attribute->setRawTableConfiguration([
            ['code' => 'ingredients', 'data_type' => 'text', 'labels' => []],
            ['code' => 'quantity', 'data_type' => 'text', 'labels' => []],
        ])->shouldBeCalled();

        $this->postLoad($event);
    }
}
