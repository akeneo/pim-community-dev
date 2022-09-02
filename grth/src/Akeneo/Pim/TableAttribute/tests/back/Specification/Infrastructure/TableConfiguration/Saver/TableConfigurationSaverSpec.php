<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\SelectOptionWasDeleted;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\CompletenessHasBeenUpdated;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfigurationUpdater;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver\TableConfigurationSaver;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TableConfigurationSaverSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        TableConfigurationUpdater $tableConfigurationUpdater,
        EventDispatcher $eventDispatcher
    ) {
        $tableConfigurationFactory = new TableConfigurationFactory([
            TextColumn::DATATYPE => TextColumn::class,
            SelectColumn::DATATYPE => SelectColumn::class,
            BooleanColumn::DATATYPE => BooleanColumn::class,
            NumberColumn::DATATYPE => NumberColumn::class,
            ReferenceEntityColumn::DATATYPE => ReferenceEntityColumn::class,
            MeasurementColumn::DATATYPE => MeasurementColumn::class,
        ]);

        $this->beConstructedWith(
            $tableConfigurationRepository,
            $optionCollectionRepository,
            $tableConfigurationFactory,
            $tableConfigurationUpdater,
            $eventDispatcher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableConfigurationSaver::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_throws_an_exception_when_trying_to_save_anything_but_an_attribute()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('save', [new \stdClass()]);
    }

    function it_does_nothing_if_the_attribute_is_not_a_table(
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $tableConfigurationRepository->save(Argument::cetera())->shouldNotBeCalled();
        $this->save($attribute);
    }

    function it_throws_an_exception_if_attribute_has_no_configuration(
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn(null);

        $tableConfigurationRepository->save(Argument::cetera())->shouldNotBeCalled();
        $this->shouldThrow(\InvalidArgumentException::class)->during('save', [$attribute]);
    }

    function it_saves_a_new_table_configuration(
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willThrow(TableConfigurationNotFoundException::forAttributeCode('nutrition'));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('ingredients'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('quantity'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('time'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::duration()));

        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([
            ['data_type' => 'select', 'code' => 'ingredients', 'labels' => []],
            ['data_type' => 'text', 'code' => 'quantity', 'labels' => []],
            ['data_type' => 'measurement', 'code' => 'time', 'labels' => [], 'measurement_family_code' => 'duration', 'measurement_default_unit_code' => 'second'],
        ]);
        $attribute->getCode()->willReturn('nutrition');

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();
        $this->save($attribute);
    }

    function it_saves_an_existing_table_configuration_using_the_updater(
        TableConfigurationRepository $tableConfigurationRepository,
        TableConfigurationUpdater $tableConfigurationUpdater,
        EventDispatcherInterface $eventDispatcher,
        AttributeInterface $attribute,
    ) {
        $existingTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
        ]);
        $updatedTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'description']),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::duration(),
                'code' => 'time',
                'measurement_family_code' => 'duration',
                'measurement_default_unit_code' => 'second',
            ]),
        ]);
        $rawTableConfiguration = [
            ['data_type' => 'select', 'code' => 'ingredients', 'labels' => []],
            ['data_type' => 'text', 'code' => 'quantity', 'labels' => []],
            ['data_type' => 'measurement', 'code' => 'time', 'labels' => [], 'measurement_family_code' => 'duration', 'measurement_default_unit_code' => 'second'],
        ];

        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn($rawTableConfiguration);
        $attribute->getCode()->willReturn('nutrition');
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willReturn($existingTableConfiguration);
        $tableConfigurationUpdater->update($existingTableConfiguration, $rawTableConfiguration)
            ->willReturn($updatedTableConfiguration);

        $tableConfigurationRepository->save('nutrition', $updatedTableConfiguration)->shouldBeCalled();
        $eventDispatcher->dispatch(new CompletenessHasBeenUpdated('nutrition'))->shouldBeCalled();
        $this->save($attribute);
    }

    function it_saves_select_options(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        EventDispatcher $eventDispatcher,
        AttributeInterface $attribute
    ) {
        $aNumberId = ColumnIdGenerator::generateAsString('a_number');
        $column1 = ['data_type' => 'select', 'code' => 'ingredients', 'labels' => [], 'options' => [['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']]]];
        $column2 = ['data_type' => 'select', 'code' => 'quantity', 'labels' => [], 'options' => [['code' => '100'], ['code' => '8000']]];
        $column3 = ['data_type' => 'number', 'code' => 'a_number', 'labels' => []];
        $column4 = ['data_type' => 'select', 'code' => 'is_allergenic', 'labels' => []];
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([$column1, $column2, $column3, $column4]);
        $attribute->getCode()->willReturn('nutrition');
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willThrow(TableConfigurationNotFoundException::forAttributeCode('nutrition'));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('ingredients'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('quantity'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('a_number'))
            ->willReturn(ColumnId::fromString($aNumberId));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('is_allergenic'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::isAllergenic()));

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();

        $ingredientSelectOption = SelectOptionCollection::fromNormalized([['code' => 'pepper']]);
        $optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))->willReturn($ingredientSelectOption);
        $optionCollectionRepository->save(
            'nutrition',
            ColumnCode::fromString('ingredients'),
            WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::fromNormalized($column1['options']))
        )->shouldBeCalled();
        $deletedPepperEvent = new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('pepper'));
        $eventDispatcher->dispatch($deletedPepperEvent)->shouldBeCalledOnce()->willReturn($deletedPepperEvent);

        $quantitySelectOption = SelectOptionCollection::fromNormalized($column2['options']);
        $optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('quantity'))->willReturn($quantitySelectOption);
        $optionCollectionRepository->save(
            'nutrition',
            ColumnCode::fromString('quantity'),
            WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::fromNormalized($column2['options']))
        )->shouldBeCalled();

        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('a_number'), Argument::any())->shouldNotBeCalled();
        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('is_allergenic'), Argument::any())->shouldNotBeCalled();

        $this->save($attribute);
    }

    function it_does_not_dispatch_event_when_completeness_was_not_updated(
        TableConfigurationRepository $tableConfigurationRepository,
        TableConfigurationUpdater $tableConfigurationUpdater,
        EventDispatcherInterface $eventDispatcher,
        AttributeInterface $attribute,
    ) {
        $existingTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
        ]);
        $newTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'description']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
        ]);

        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn($newTableConfiguration->normalize());
        $attribute->getCode()->willReturn('nutrition');
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willReturn($existingTableConfiguration);
        $tableConfigurationUpdater->update($existingTableConfiguration, $newTableConfiguration->normalize())
            ->willReturn($newTableConfiguration);

        $tableConfigurationRepository->save('nutrition', $newTableConfiguration)->shouldBeCalled();
        $eventDispatcher->dispatch(new CompletenessHasBeenUpdated('nutrition'))->shouldNotBeCalled();
        $this->save($attribute);
    }

    function it_dispatches_event_when_completeness_is_updated(
        TableConfigurationRepository $tableConfigurationRepository,
        TableConfigurationUpdater $tableConfigurationUpdater,
        EventDispatcherInterface $eventDispatcher,
        AttributeInterface $attribute,
    ) {
        $existingTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
        ]);
        $newTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic', 'is_required_for_completeness' => true]),
        ]);

        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn($newTableConfiguration->normalize());
        $attribute->getCode()->willReturn('nutrition');
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willReturn($existingTableConfiguration);
        $tableConfigurationUpdater->update($existingTableConfiguration, $newTableConfiguration->normalize())
            ->willReturn($newTableConfiguration);

        $tableConfigurationRepository->save('nutrition', $newTableConfiguration)->shouldBeCalled();
        $eventDispatcher->dispatch(new CompletenessHasBeenUpdated('nutrition'))->shouldBeCalled();
        $this->save($attribute);
    }
}
