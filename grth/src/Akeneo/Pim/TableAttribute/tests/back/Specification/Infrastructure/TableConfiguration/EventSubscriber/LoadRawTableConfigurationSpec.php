<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber\LoadRawTableConfiguration;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
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

        $tableConfigurationRepository->getByAttributeCode(Argument::any())->shouldNotBeCalled();

        $this->postLoad($event);
    }

    function it_does_nothing_when_entity_is_not_a_table_attribute(
        TableConfigurationRepository $tableConfigurationRepository,
        LifecycleEventArgs $event,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $event->getObject()->willReturn($attribute);

        $tableConfigurationRepository->getByAttributeCode(Argument::any())->shouldNotBeCalled();

        $this->postLoad($event);
    }

    function it_loads_raw_table_configuration_for_a_table_attribute(
        TableConfigurationRepository $tableConfigurationRepository,
        LifecycleEventArgs $event,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('nutrition');
        $event->getObject()->willReturn($attribute);

        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions(
                [
                    SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredients', 'labels' => [], 'is_required_for_completeness' => true]),
                    TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => [], 'is_required_for_completeness' => false]),
                ]
            )
        );
        $attribute->setRawTableConfiguration([
            [
                'id' => ColumnIdGenerator::ingredient(),
                'code' => 'ingredients',
                'data_type' => 'select',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => true,
            ],
            [
                'id' => ColumnIdGenerator::quantity(),
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
            ],
        ])->shouldBeCalled();

        $this->postLoad($event);
    }
}
